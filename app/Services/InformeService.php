<?php

namespace App\Services;

use App\Models\Designacion;
use App\Models\InformeDiario;
use App\Models\Institucion;
use App\Models\ItemInforme;
use App\Models\MarcaComputada;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use TCPDF;

class InformeService
{
    public function __construct(
        private readonly CalendarioService $calendarioService,
        private readonly LicenciaService   $licenciaService,
    ) {}

    /**
     * Genera el informe diario de una institución para una fecha.
     * Si ya existe uno para esa fecha, lo recalcula eliminando los items previos.
     */
    public function generarInformeDiario(Institucion $institucion, string|Carbon $fecha): InformeDiario
    {
        $fecha = Carbon::parse($fecha);

        $informe = InformeDiario::where('id_institucion', $institucion->id)
            ->whereDate('fecha', $fecha->toDateString())
            ->first();

        $attributes = ['generado_en' => now(), 'estado' => 'generado', 'id_generado_por' => null];

        if ($informe) {
            $informe->update($attributes);
        } else {
            $informe = InformeDiario::create(array_merge(
                ['id_institucion' => $institucion->id, 'fecha' => $fecha->toDateString()],
                $attributes
            ));
        }

        // Limpiar items anteriores si se regenera
        $informe->items()->delete();

        // Designaciones vigentes en la institución
        $designaciones = Designacion::vigente()
            ->porInstitucion($institucion->id)
            ->with('usuario', 'cargo')
            ->get();

        DB::transaction(function () use ($informe, $designaciones, $fecha, $institucion) {
            foreach ($designaciones as $designacion) {
                $this->procesarDesignacion($informe, $designacion, $fecha, $institucion);
            }
        });

        return $informe->fresh();
    }

    private function procesarDesignacion(
        InformeDiario $informe,
        Designacion $designacion,
        Carbon $fecha,
        Institucion $institucion
    ): void {
        $usuario = $designacion->usuario;

        // ── Verificar si debía trabajar ────────────────────────────────────
        $jornada = $this->calendarioService->calcularJornadaEfectiva($usuario, $designacion, $fecha);
        $debiaTrabajar = !empty($jornada['horarios']);

        // ── Licencia vigente ───────────────────────────────────────────────
        $licencias = $this->licenciaService->licenciasVigentes($usuario, $fecha);
        if ($licencias->isNotEmpty()) {
            ItemInforme::create([
                'id_informe_diario'  => $informe->id,
                'id_usuario'         => $usuario->id,
                'id_designacion'     => $designacion->id,
                'tipo_novedad'       => 'licencia',
                'detalle'            => 'Licencia: ' . $licencias->first()->tipoLicencia->nombre,
                'minutos_trabajados' => 0,
                'requiere_atencion'  => false,
            ]);
            return;
        }

        // ── Feriado o suspensión total ─────────────────────────────────────
        if (!$debiaTrabajar) {
            $eventos = $this->calendarioService->obtenerEventosFecha($institucion, $fecha);
            $tipoNovedad = 'feriado';
            $detalle     = 'Día no laborable.';

            foreach ($eventos as $evento) {
                if ($evento->tipo === 'suspension_total') {
                    $tipoNovedad = 'suspension';
                    $detalle     = "Suspensión: {$evento->titulo}";
                    break;
                }
            }

            ItemInforme::create([
                'id_informe_diario'  => $informe->id,
                'id_usuario'         => $usuario->id,
                'id_designacion'     => $designacion->id,
                'tipo_novedad'       => $tipoNovedad,
                'detalle'            => $detalle,
                'minutos_trabajados' => 0,
                'requiere_atencion'  => false,
            ]);
            return;
        }

        // ── Buscar marca computada ─────────────────────────────────────────
        /** @var MarcaComputada|null $marca */
        $marca = MarcaComputada::deUsuario($usuario->id)
            ->enFecha($fecha)
            ->where('id_designacion', $designacion->id)
            ->first();

        if (!$marca) {
            // ── Verificar si hay paro que aplica al empleado ───────────────
            $paro = $this->calendarioService->paroAplicaAEmpleado(
                $institucion, $fecha, $usuario, $designacion
            );

            if ($paro) {
                ItemInforme::create([
                    'id_informe_diario'  => $informe->id,
                    'id_usuario'         => $usuario->id,
                    'id_designacion'     => $designacion->id,
                    'tipo_novedad'       => 'paro',
                    'detalle'            => "Adhirió al paro: {$paro->titulo}",
                    'razon_ausencia'     => 'paro',
                    'minutos_trabajados' => 0,
                    'requiere_atencion'  => false,
                ]);
                return;
            }

            // ── Verificar si hay aviso de ausencia ─────────────────────────
            $aviso = $usuario->avisos()
                ->where('id_designacion', $designacion->id)
                ->enFecha($fecha->toDateString())
                ->first();

            if ($aviso) {
                ItemInforme::create([
                    'id_informe_diario'  => $informe->id,
                    'id_usuario'         => $usuario->id,
                    'id_designacion'     => $designacion->id,
                    'tipo_novedad'       => 'ausencia_justificada',
                    'detalle'            => "Aviso de {$aviso->tipo}: {$aviso->motivo}",
                    'razon_ausencia'     => $aviso->tipo,
                    'minutos_trabajados' => 0,
                    'requiere_atencion'  => false,
                ]);
            } else {
                ItemInforme::create([
                    'id_informe_diario'  => $informe->id,
                    'id_usuario'         => $usuario->id,
                    'id_designacion'     => $designacion->id,
                    'tipo_novedad'       => 'error_atencion_urgente',
                    'detalle'            => 'Sin marca ni justificación. Requiere atención inmediata.',
                    'minutos_trabajados' => 0,
                    'requiere_atencion'  => true,
                ]);
            }
            return;
        }

        // ── Marca existente: clasificar novedad ────────────────────────────
        $tipoNovedad = match ($marca->tipo) {
            'tardanza'   => 'tardanza',
            'ausencia'   => $this->tieneJustificacion($usuario, $designacion, $fecha)
                ? 'ausencia_justificada'
                : 'ausencia_injustificada',
            'licencia'   => 'licencia',
            'feriado'    => 'feriado',
            'suspension' => 'suspension',
            default      => 'presente',
        };

        $requiereAtencion = $marca->tiene_error
            || $tipoNovedad === 'ausencia_injustificada';

        ItemInforme::create([
            'id_informe_diario'  => $informe->id,
            'id_usuario'         => $usuario->id,
            'id_designacion'     => $designacion->id,
            'id_marca_computada' => $marca->id,
            'tipo_novedad'       => $tipoNovedad,
            'detalle'            => $marca->tiene_error
                ? implode('; ', $marca->errores ?? [])
                : null,
            'hora_entrada'       => $marca->hora_entrada,
            'hora_salida'        => $marca->hora_salida,
            'minutos_trabajados' => $marca->minutos_trabajados,
            'requiere_atencion'  => $requiereAtencion,
        ]);
    }

    private function tieneJustificacion(User $usuario, Designacion $designacion, Carbon $fecha): bool
    {
        return $usuario->avisos()
            ->where('id_designacion', $designacion->id)
            ->enFecha($fecha->toDateString())
            ->exists();
    }

    // ── Exportaciones ──────────────────────────────────────────────────────

    public function exportarExcel(InformeDiario $informe): string
    {
        $informe->load(['institucion', 'items.usuario', 'items.designacion.cargo']);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Informe Diario');

        // Encabezado
        $sheet->setCellValue('A1', 'Informe Diario - ' . $informe->institucion->nombre);
        $sheet->setCellValue('A2', 'Fecha: ' . $informe->fecha->format('d/m/Y'));

        $fila = 4;
        $sheet->fromArray(
            ['Apellidos', 'Nombres', 'Cargo', 'Novedad', 'Entrada', 'Salida', 'Min. Trabajados', 'Atención'],
            null, "A{$fila}"
        );

        // Estilo del encabezado
        $sheet->getStyle("A{$fila}:H{$fila}")->getFont()->setBold(true);
        $fila++;

        $coloresExcel = [
            'error_atencion_urgente' => 'FFCCCC',
            'tardanza'               => 'FFFFCC',
            'presente'               => 'CCFFCC',
            'licencia'               => 'CCE5FF',
            'feriado'                => 'CCE5FF',
            'suspension'             => 'CCE5FF',
        ];

        foreach ($informe->items as $item) {
            $sheet->fromArray([
                $item->usuario->apellidos,
                $item->usuario->nombres,
                $item->designacion->cargo->nombre ?? '',
                $item->tipo_novedad,
                $item->hora_entrada ?? '',
                $item->hora_salida ?? '',
                $item->minutos_trabajados,
                $item->requiere_atencion ? 'SÍ' : '',
            ], null, "A{$fila}");

            $color = $coloresExcel[$item->tipo_novedad] ?? 'FFFFFF';
            $sheet->getStyle("A{$fila}:H{$fila}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB($color);

            $fila++;
        }

        // Autoajustar columnas
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $rutaTmp = sys_get_temp_dir() . '/informe_' . $informe->id . '_' . time() . '.xlsx';
        (new Xlsx($spreadsheet))->save($rutaTmp);

        return $rutaTmp;
    }

    public function exportarPDF(InformeDiario $informe): string
    {
        $informe->load(['institucion', 'items.usuario', 'items.designacion.cargo']);

        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8');
        $pdf->SetCreator('Kairos');
        $pdf->SetTitle('Informe Diario - ' . $informe->fecha->format('d/m/Y'));
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();

        // Logo institucional
        $logoRelativo = $informe->institucion?->logoEfectivo();
        if ($logoRelativo) {
            $logoPath = \Illuminate\Support\Facades\Storage::disk('public')->path($logoRelativo);
            if (file_exists($logoPath)) {
                $pdf->Image($logoPath, 10, 8, 30, 0, '', '', 'T', false, 300);
            }
        }

        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Informe Diario — ' . $informe->institucion->nombre, 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, 'Fecha: ' . $informe->fecha->format('d/m/Y'), 0, 1, 'C');
        $pdf->Ln(4);

        $colores = [
            'error_atencion_urgente' => [255, 204, 204],
            'tardanza'               => [255, 255, 204],
            'presente'               => [204, 255, 204],
            'licencia'               => [204, 229, 255],
            'feriado'                => [204, 229, 255],
            'suspension'             => [204, 229, 255],
        ];

        $anchos = [50, 40, 35, 35, 20, 20, 25, 20];
        $cabeceras = ['Apellidos', 'Nombres', 'Cargo', 'Novedad', 'Entrada', 'Salida', 'Min.', 'Atención'];

        $pdf->SetFillColor(220, 220, 220);
        $pdf->SetFont('helvetica', 'B', 9);
        foreach ($cabeceras as $i => $cab) {
            $pdf->Cell($anchos[$i], 7, $cab, 1, 0, 'C', true);
        }
        $pdf->Ln();

        $pdf->SetFont('helvetica', '', 8);
        foreach ($informe->items as $item) {
            $rgb = $colores[$item->tipo_novedad] ?? [255, 255, 255];
            $pdf->SetFillColor(...$rgb);

            $valores = [
                $item->usuario->apellidos,
                $item->usuario->nombres,
                $item->designacion->cargo->nombre ?? '',
                $item->tipo_novedad,
                $item->hora_entrada ?? '',
                $item->hora_salida ?? '',
                (string) $item->minutos_trabajados,
                $item->requiere_atencion ? 'SÍ' : '',
            ];

            foreach ($valores as $i => $val) {
                $pdf->Cell($anchos[$i], 6, $val, 1, 0, 'L', true);
            }
            $pdf->Ln();
        }

        $rutaTmp = sys_get_temp_dir() . '/informe_' . $informe->id . '_' . time() . '.pdf';
        $pdf->Output($rutaTmp, 'F');

        return $rutaTmp;
    }
}

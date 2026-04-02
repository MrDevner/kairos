<?php

namespace Tests\Feature;

use App\Models\Cargo;
use App\Models\DeclaracionJurada;
use App\Models\Dependencia;
use App\Models\Designacion;
use App\Models\Dispositivo;
use App\Models\EventoCalendario;
use App\Models\HorarioDdjj;
use App\Models\InformeDiario;
use App\Models\Institucion;
use App\Models\ItemInforme;
use App\Models\Licencia;
use App\Models\MarcaComputada;
use App\Models\TipoLicencia;
use App\Models\Usuario;
use App\Services\InformeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InformeTest extends TestCase
{
    use RefreshDatabase;

    private InformeService $service;
    private Institucion    $inst;
    private Usuario        $usuario;
    private Designacion    $designacion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(InformeService::class);

        $this->inst = Institucion::create([
            'nombre'        => 'FING Test',
            'sigla'         => 'FING_T',
            'tipo'          => 'facultad',
            'configuracion' => null,
            'activa'        => true,
        ]);

        $dep = Dependencia::create([
            'nombre'         => 'Depto Test',
            'sigla'          => 'DTEST',
            'id_institucion' => $this->inst->id,
            'activa'         => true,
        ]);

        $cargo = Cargo::create([
            'nombre'          => 'Personal Administrativo',
            'tipo'            => 'cargo',
            'horas_semanales' => 35,
            'id_institucion'  => $this->inst->id,
            'activo'          => true,
        ]);

        $this->usuario = Usuario::create([
            'documento' => '30100200',
            'apellidos' => 'Test',
            'nombres'   => 'Informe',
            'email'     => 'informe@test.test',
            'password'  => bcrypt('password'),
            'activo'    => true,
        ]);

        $this->designacion = Designacion::create([
            'id_usuario'     => $this->usuario->id,
            'id_cargo'       => $cargo->id,
            'id_institucion' => $this->inst->id,
            'id_dependencia' => $dep->id,
            'fecha_inicio'   => '2026-01-01',
            'activa'         => true,
        ]);

        // DDJJ aprobada: lunes-viernes 08:00-15:00
        $ddjj = DeclaracionJurada::create([
            'id_usuario'    => $this->usuario->id,
            'id_designacion' => $this->designacion->id,
            'fecha_inicio'  => '2026-01-01',
            'estado'        => 'aprobada',
        ]);

        foreach (['lunes', 'martes', 'miercoles', 'jueves', 'viernes'] as $dia) {
            HorarioDdjj::create([
                'id_declaracion_jurada' => $ddjj->id,
                'dia_semana'            => $dia,
                'hora_entrada'          => '08:00',
                'hora_salida'           => '15:00',
                'modalidad'             => 'presencial',
            ]);
        }
    }

    // ── Generación completa ─────────────────────────────────────────────────

    #[Test]
    public function genera_informe_con_marca_computada_normal(): void
    {
        // Marca computada normal para lunes 2026-03-09
        MarcaComputada::create([
            'id_usuario'          => $this->usuario->id,
            'id_designacion'      => $this->designacion->id,
            'fecha'               => '2026-03-09',
            'hora_entrada'        => '08:00:00',
            'hora_salida'         => '15:00:00',
            'tipo'                => 'normal',
            'minutos_trabajados'  => 420,
            'minutos_obligatorios' => 420,
            'minutos_extra'       => 0,
            'minutos_faltantes'   => 0,
            'tiene_error'         => false,
            'tiene_observacion'   => false,
            'errores'             => [],
            'observaciones'       => [],
        ]);

        $informe = $this->service->generarInformeDiario($this->inst, '2026-03-09');

        $this->assertInstanceOf(InformeDiario::class, $informe);
        $this->assertEquals('generado', $informe->estado);
        $this->assertEquals(1, $informe->items()->count());

        $item = $informe->items()->first();
        $this->assertEquals($this->usuario->id, $item->id_usuario);
        $this->assertEquals('presente', $item->tipo_novedad); // 'normal' en MarcaComputada → 'presente' en ItemInforme
        $this->assertFalse((bool) $item->requiere_atencion);
    }

    #[Test]
    public function genera_item_de_ausencia_si_no_hay_marca_computada(): void
    {
        // No hay MarcaComputada → debe generar novedad de ausencia injustificada
        $informe = $this->service->generarInformeDiario($this->inst, '2026-03-09');

        $item = $informe->items()->first();

        $this->assertNotNull($item);
        // Sin marca computada ni aviso → el servicio genera error_atencion_urgente
        $this->assertEquals('error_atencion_urgente', $item->tipo_novedad);
        $this->assertTrue((bool) $item->requiere_atencion);
    }

    #[Test]
    public function feriado_genera_item_sin_atencion(): void
    {
        EventoCalendario::create([
            'id_institucion' => $this->inst->id,
            'titulo'         => 'Feriado Nacional',
            'fecha'          => '2026-03-09',
            'tipo'           => 'feriado',
            'afecta_computo' => true,
        ]);

        $informe = $this->service->generarInformeDiario($this->inst, '2026-03-09');

        $item = $informe->items()->first();

        $this->assertNotNull($item);
        $this->assertEquals('feriado', $item->tipo_novedad);
        $this->assertFalse((bool) $item->requiere_atencion);
    }

    #[Test]
    public function licencia_aprobada_genera_item_tipo_licencia(): void
    {
        $tipo = TipoLicencia::create([
            'nombre'                 => 'Enfermedad',
            'computo'                => 'dias_corridos',
            'afecta'                 => 'designacion',
            'dias_maximos'           => 30,
            'requiere_documentacion' => false,
            'activo'                 => true,
        ]);

        Licencia::create([
            'id_usuario'        => $this->usuario->id,
            'id_designacion'    => $this->designacion->id,
            'id_tipo_licencia'  => $tipo->id,
            'id_registrado_por' => $this->usuario->id,
            'fecha_inicio'      => '2026-03-09',
            'fecha_fin'         => '2026-03-09',
            'dias_computados'   => 1,
            'estado'            => 'aprobada',
            'motivo'            => 'Gripe',
        ]);

        $informe = $this->service->generarInformeDiario($this->inst, '2026-03-09');

        $item = $informe->items()->first();

        $this->assertNotNull($item);
        $this->assertEquals('licencia', $item->tipo_novedad);
        $this->assertFalse((bool) $item->requiere_atencion);
    }

    #[Test]
    public function marca_con_error_genera_item_que_requiere_atencion(): void
    {
        MarcaComputada::create([
            'id_usuario'          => $this->usuario->id,
            'id_designacion'      => $this->designacion->id,
            'fecha'               => '2026-03-09',
            'hora_entrada'        => '08:00:00',
            'hora_salida'         => null,
            'tipo'                => 'ausencia',
            'minutos_trabajados'  => 0,
            'minutos_obligatorios' => 420,
            'minutos_extra'       => 0,
            'minutos_faltantes'   => 420,
            'tiene_error'         => true,
            'tiene_observacion'   => false,
            'errores'             => ['Sin marca de salida.'],
            'observaciones'       => [],
        ]);

        $informe = $this->service->generarInformeDiario($this->inst, '2026-03-09');

        $item = $informe->items()->first();

        $this->assertNotNull($item);
        $this->assertTrue((bool) $item->requiere_atencion);
    }

    #[Test]
    public function regenerar_informe_reemplaza_items_anteriores(): void
    {
        // Generar por primera vez
        $this->service->generarInformeDiario($this->inst, '2026-03-09');

        $idPrimero = InformeDiario::first()->id;
        $itemsAntes = ItemInforme::where('id_informe_diario', $idPrimero)->count();

        // Regenerar
        $this->service->generarInformeDiario($this->inst, '2026-03-09');

        $itemsDespues = ItemInforme::where('id_informe_diario', $idPrimero)->count();

        // No debe acumular items duplicados
        $this->assertEquals($itemsAntes, $itemsDespues);
        $this->assertEquals(1, InformeDiario::count()); // sigue siendo uno
    }
}

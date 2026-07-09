<?php

namespace Tests\Feature;

use App\Models\Cargo;
use App\Models\Dependencia;
use App\Models\Designacion;
use App\Models\EventoCalendario;
use App\Models\Institucion;
use App\Models\Licencia;
use App\Models\TipoLicencia;
use App\Models\User;
use App\Services\LicenciaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LicenciaTest extends TestCase
{
    use RefreshDatabase;

    private LicenciaService $service;
    private Institucion     $inst;
    private User         $admin;
    private User         $empleado;
    private Designacion     $designacion;
    private TipoLicencia    $tipoCorridos;
    private TipoLicencia    $tipoHabiles;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(LicenciaService::class);

        $this->inst = Institucion::create([
            'nombre'        => 'FING Test',
            'sigla'         => 'FING_T',
            'tipo'          => 'facultad',
            'configuracion' => null,
            'activa'        => true,
        ]);

        Dependencia::create([
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

        $this->admin = User::create([
            'documento' => '99000001',
            'apellidos' => 'Admin',
            'nombres'   => 'Test',
            'email'     => 'admin@test.test',
            'password'  => bcrypt('password'),
            'activo'    => true,
        ]);

        $this->empleado = User::create([
            'documento' => '30000001',
            'apellidos' => 'Empleado',
            'nombres'   => 'Test',
            'email'     => 'empleado@test.test',
            'password'  => bcrypt('password'),
            'activo'    => true,
        ]);

        $this->designacion = Designacion::create([
            'id_usuario'     => $this->empleado->id,
            'id_cargo'       => $cargo->id,
            'id_institucion' => $this->inst->id,
            'id_dependencia' => Dependencia::first()->id,
            'fecha_inicio'   => '2026-01-01',
            'activa'         => true,
        ]);

        $this->tipoCorridos = TipoLicencia::create([
            'nombre'                 => 'Enfermedad',
            'computo'                => 'dias_corridos',
            'afecta'                 => 'designacion',
            'dias_maximos'           => 30,
            'requiere_documentacion' => true,
            'activo'                 => true,
        ]);

        $this->tipoHabiles = TipoLicencia::create([
            'nombre'                 => 'Estudio',
            'computo'                => 'dias_habiles',
            'afecta'                 => 'designacion',
            'dias_maximos'           => 10,
            'requiere_documentacion' => false,
            'activo'                 => true,
        ]);
    }

    // ── Auto-registro bloqueado ─────────────────────────────────────────────

    #[Test]
    public function un_usuario_no_puede_registrar_su_propia_licencia(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->registrar([
            'id_usuario'      => $this->empleado->id,
            'id_tipo_licencia' => $this->tipoCorridos->id,
            'fecha_inicio'    => '2026-03-10',
            'fecha_fin'       => '2026-03-14',
            'motivo'          => 'Gripe',
        ], registradoPor: $this->empleado); // mismo usuario
    }

    #[Test]
    public function admin_puede_registrar_licencia_de_otro_usuario(): void
    {
        $licencia = $this->service->registrar([
            'id_usuario'      => $this->empleado->id,
            'id_tipo_licencia' => $this->tipoCorridos->id,
            'fecha_inicio'    => '2026-03-10',
            'fecha_fin'       => '2026-03-14',
            'motivo'          => 'Gripe',
        ], registradoPor: $this->admin);

        $this->assertDatabaseHas('licencias', [
            'id_usuario'        => $this->empleado->id,
            'id_registrado_por' => $this->admin->id,
            'estado'            => 'pendiente',
        ]);
    }

    // ── Cómputo de días corridos ────────────────────────────────────────────

    #[Test]
    public function calcular_dias_corridos_incluye_fines_de_semana(): void
    {
        // Lunes a domingo: 7 días corridos
        $dias = $this->service->calcularDias($this->tipoCorridos, '2026-03-09', '2026-03-15');

        $this->assertEquals(7, $dias);
    }

    // ── Cómputo de días hábiles ─────────────────────────────────────────────

    #[Test]
    public function calcular_dias_habiles_excluye_fines_de_semana(): void
    {
        // Semana 09-15 mar: 5 días hábiles (lunes a viernes)
        $dias = $this->service->calcularDias($this->tipoHabiles, '2026-03-09', '2026-03-15', $this->inst);

        $this->assertEquals(5, $dias);
    }

    #[Test]
    public function calcular_dias_habiles_excluye_feriados(): void
    {
        // Crear un feriado el miércoles 11/mar
        EventoCalendario::create([
            'id_institucion' => $this->inst->id,
            'titulo'         => 'Feriado Test',
            'fecha'          => '2026-03-11',
            'tipo'           => 'feriado',
            'afecta_computo' => true,
        ]);

        // Semana 09-15 mar con feriado el miércoles → 4 hábiles
        $dias = $this->service->calcularDias($this->tipoHabiles, '2026-03-09', '2026-03-15', $this->inst);

        $this->assertEquals(4, $dias);
    }

    // ── Aprobación ──────────────────────────────────────────────────────────

    #[Test]
    public function aprobar_licencia_calcula_dias_y_cambia_estado(): void
    {
        $licencia = Licencia::create([
            'id_usuario'        => $this->empleado->id,
            'id_designacion'    => $this->designacion->id,
            'id_tipo_licencia'  => $this->tipoCorridos->id,
            'id_registrado_por' => $this->admin->id,
            'fecha_inicio'      => '2026-03-10',
            'fecha_fin'         => '2026-03-12',
            'dias_computados'   => 0,
            'estado'            => 'pendiente',
            'motivo'            => 'Gripe',
        ]);

        $this->service->aprobar($licencia, $this->admin);

        $this->assertDatabaseHas('licencias', [
            'id'             => $licencia->id,
            'estado'         => 'aprobada',
            'dias_computados' => 3, // 10, 11, 12 = 3 días corridos
        ]);
    }

    #[Test]
    public function no_se_puede_aprobar_una_licencia_ya_aprobada(): void
    {
        $licencia = Licencia::create([
            'id_usuario'        => $this->empleado->id,
            'id_tipo_licencia'  => $this->tipoCorridos->id,
            'id_registrado_por' => $this->admin->id,
            'fecha_inicio'      => '2026-03-10',
            'fecha_fin'         => '2026-03-12',
            'dias_computados'   => 3,
            'estado'            => 'aprobada',
            'motivo'            => 'Gripe',
        ]);

        $this->expectException(\LogicException::class);

        $this->service->aprobar($licencia, $this->admin);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Cargo;
use App\Models\DeclaracionJurada;
use App\Models\Dependencia;
use App\Models\Designacion;
use App\Models\HorarioDdjj;
use App\Models\Institucion;
use App\Models\Usuario;
use App\Services\DDJJService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DDJJTest extends TestCase
{
    use RefreshDatabase;

    private DDJJService $service;
    private Usuario     $usuario;
    private Designacion $designacion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(DDJJService::class);

        $inst = Institucion::create([
            'nombre'          => 'FING Test',
            'sigla'           => 'FING_T',
            'tipo'            => 'facultad',
            'configuracion'   => null,
            'activa'          => true,
        ]);

        $dep = Dependencia::create([
            'nombre'         => 'Depto Test',
            'sigla'          => 'DTEST',
            'id_institucion' => $inst->id,
            'activa'         => true,
        ]);

        $cargo = Cargo::create([
            'nombre'          => 'Profesor Titular',
            'tipo'            => 'cargo',
            'horas_semanales' => 20,
            'id_institucion'  => $inst->id,
            'activo'          => true,
        ]);

        $this->usuario = Usuario::create([
            'documento' => '11111111',
            'apellidos' => 'Test',
            'nombres'   => 'Usuario',
            'email'     => 'test@kairos.test',
            'password'  => bcrypt('password'),
            'activo'    => true,
        ]);

        $this->designacion = Designacion::create([
            'id_usuario'     => $this->usuario->id,
            'id_cargo'       => $cargo->id,
            'id_institucion' => $inst->id,
            'id_dependencia' => $dep->id,
            'fecha_inicio'   => '2026-01-01',
            'activa'         => true,
        ]);
    }

    // ── Superposición ───────────────────────────────────────────────────────

    #[Test]
    public function detecta_superposicion_de_horarios(): void
    {
        // DDJJ existente con lunes 08:00 - 12:00
        $ddjj = DeclaracionJurada::create([
            'id_usuario'    => $this->usuario->id,
            'id_designacion' => $this->designacion->id,
            'fecha_inicio'  => '2026-01-01',
            'estado'        => 'aprobada',
        ]);

        HorarioDdjj::create([
            'id_declaracion_jurada' => $ddjj->id,
            'dia_semana'            => 'lunes',
            'hora_entrada'          => '08:00',
            'hora_salida'           => '12:00',
            'modalidad'             => 'presencial',
        ]);

        // Nuevo horario que se superpone
        $this->expectException(ValidationException::class);

        $this->service->validarSuperposicion($this->usuario, [
            ['dia_semana' => 'lunes', 'hora_entrada' => '10:00', 'hora_salida' => '14:00'],
        ]);
    }

    #[Test]
    public function acepta_horarios_sin_superposicion(): void
    {
        $ddjj = DeclaracionJurada::create([
            'id_usuario'    => $this->usuario->id,
            'id_designacion' => $this->designacion->id,
            'fecha_inicio'  => '2026-01-01',
            'estado'        => 'aprobada',
        ]);

        HorarioDdjj::create([
            'id_declaracion_jurada' => $ddjj->id,
            'dia_semana'            => 'lunes',
            'hora_entrada'          => '08:00',
            'hora_salida'           => '12:00',
            'modalidad'             => 'presencial',
        ]);

        // Horario en día diferente — no debe lanzar
        $this->service->validarSuperposicion($this->usuario, [
            ['dia_semana' => 'martes', 'hora_entrada' => '08:00', 'hora_salida' => '12:00'],
        ]);

        $this->assertTrue(true); // llegó sin excepción
    }

    // ── Horas máximas ───────────────────────────────────────────────────────

    #[Test]
    public function rechaza_horarios_que_superan_horas_del_cargo(): void
    {
        // Cargo tiene 20 hs/sem = 1200 min; 5 días × 5h = 25h > 20h
        $this->expectException(ValidationException::class);

        $horarios = [];
        foreach (['lunes', 'martes', 'miercoles', 'jueves', 'viernes'] as $dia) {
            $horarios[] = ['dia_semana' => $dia, 'hora_entrada' => '08:00', 'hora_salida' => '13:00'];
        }

        $this->service->validarHorasMaximas($this->designacion, $horarios);
    }

    #[Test]
    public function acepta_horarios_dentro_de_las_horas_del_cargo(): void
    {
        // 4 días × 5h = 20h == cargo (20h)
        $horarios = [];
        foreach (['lunes', 'martes', 'miercoles', 'jueves'] as $dia) {
            $horarios[] = ['dia_semana' => $dia, 'hora_entrada' => '08:00', 'hora_salida' => '13:00'];
        }

        $this->service->validarHorasMaximas($this->designacion, $horarios);

        $this->assertTrue(true);
    }

    // ── Dos DDJJ activas ────────────────────────────────────────────────────

    #[Test]
    public function presentar_lanza_si_ya_existe_ddjj_activa_para_designacion(): void
    {
        // Primera DDJJ aprobada
        DeclaracionJurada::create([
            'id_usuario'    => $this->usuario->id,
            'id_designacion' => $this->designacion->id,
            'fecha_inicio'  => '2026-01-01',
            'estado'        => 'aprobada',
        ]);

        // Segunda DDJJ en borrador que quiere presentarse
        $segunda = DeclaracionJurada::create([
            'id_usuario'    => $this->usuario->id,
            'id_designacion' => $this->designacion->id,
            'fecha_inicio'  => '2026-03-01',
            'estado'        => 'borrador',
        ]);

        $this->expectException(ValidationException::class);

        $this->service->presentar($segunda);
    }
}

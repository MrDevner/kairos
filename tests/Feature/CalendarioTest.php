<?php

namespace Tests\Feature;

use App\Models\Cargo;
use App\Models\CondicionEvento;
use App\Models\DeclaracionJurada;
use App\Models\Dependencia;
use App\Models\Designacion;
use App\Models\EventoCalendario;
use App\Models\HorarioDdjj;
use App\Models\Institucion;
use App\Models\Usuario;
use App\Services\CalendarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CalendarioTest extends TestCase
{
    use RefreshDatabase;

    private CalendarioService $service;
    private Institucion       $inst;
    private Usuario           $usuario;
    private Designacion       $designacion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(CalendarioService::class);

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
            'nombres'   => 'Usuario',
            'email'     => 'cal@test.test',
            'password'  => bcrypt('password'),
            'activo'    => true,
            'sexo'      => 'F',
        ]);

        $this->designacion = Designacion::create([
            'id_usuario'     => $this->usuario->id,
            'id_cargo'       => $cargo->id,
            'id_institucion' => $this->inst->id,
            'id_dependencia' => $dep->id,
            'fecha_inicio'   => '2026-01-01',
            'activa'         => true,
        ]);

        // DDJJ aprobada con horario lunes-viernes 08:00-15:00
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

    // ── esHabil ─────────────────────────────────────────────────────────────

    #[Test]
    public function lunes_sin_eventos_es_dia_habil(): void
    {
        $this->assertTrue($this->service->esHabil($this->inst, '2026-03-09')); // lunes
    }

    #[Test]
    public function sabado_no_es_dia_habil(): void
    {
        $this->assertFalse($this->service->esHabil($this->inst, '2026-03-07')); // sábado
    }

    #[Test]
    public function feriado_no_es_dia_habil(): void
    {
        EventoCalendario::create([
            'id_institucion' => $this->inst->id,
            'titulo'         => 'Feriado Nacional',
            'fecha'          => '2026-03-09',
            'tipo'           => 'feriado',
            'afecta_computo' => true,
        ]);

        $this->assertFalse($this->service->esHabil($this->inst, '2026-03-09'));
    }

    // ── Suspensión parcial ──────────────────────────────────────────────────

    #[Test]
    public function suspension_parcial_no_marca_dia_como_inhabil(): void
    {
        EventoCalendario::create([
            'id_institucion' => $this->inst->id,
            'titulo'         => 'Corte de luz',
            'fecha'          => '2026-03-09',
            'tipo'           => 'suspension_parcial',
            'afecta_computo' => true,
        ]);

        // suspension_parcial no inhabilita el día completo
        $this->assertTrue($this->service->esHabil($this->inst, '2026-03-09'));
    }

    // ── calcularJornadaEfectiva: retiro anticipado ───────────────────────────

    #[Test]
    public function retiro_anticipado_reduce_hora_salida(): void
    {
        // Evento condicional con retiro 60 minutos aplicado por cargo
        $evento = EventoCalendario::create([
            'id_institucion' => $this->inst->id,
            'titulo'         => 'Tarde libre',
            'fecha'          => '2026-03-09', // lunes
            'tipo'           => 'evento_condicional',
            'afecta_computo' => true,
        ]);

        CondicionEvento::create([
            'id_evento_calendario' => $evento->id,
            'tipo_condicion'       => 'cargo',
            'valor_condicion'      => (string) $this->designacion->id_cargo,
            'efecto'               => 'retiro_anticipado',
            'minutos_afectados'    => 60,
        ]);

        $jornada = $this->service->calcularJornadaEfectiva($this->usuario, $this->designacion, '2026-03-09');

        $this->assertNotEmpty($jornada['horarios']);
        // Salida original 15:00 - 60 min = 14:00
        $this->assertEquals('14:00', $jornada['horarios'][0]['hora_salida']);
    }

    // ── calcularJornadaEfectiva: jornada mínima ─────────────────────────────

    #[Test]
    public function jornada_muy_corta_requiere_revision(): void
    {
        // Crear evento que reduce la jornada a casi nada por cargo (retiro 390 min de 420)
        $evento = EventoCalendario::create([
            'id_institucion' => $this->inst->id,
            'titulo'         => 'Jornada cortísima',
            'fecha'          => '2026-03-09',
            'tipo'           => 'evento_condicional',
            'afecta_computo' => true,
        ]);

        CondicionEvento::create([
            'id_evento_calendario' => $evento->id,
            'tipo_condicion'       => 'cargo',
            'valor_condicion'      => (string) $this->designacion->id_cargo,
            'efecto'               => 'retiro_anticipado',
            'minutos_afectados'    => 390, // deja 30 min de 420
        ]);

        $jornada = $this->service->calcularJornadaEfectiva($this->usuario, $this->designacion, '2026-03-09');

        $this->assertTrue($jornada['requiere_revision']);
    }

    // ── diasHabilesEntre ─────────────────────────────────────────────────────

    #[Test]
    public function cuenta_correctamente_dias_habiles_en_semana_completa(): void
    {
        // Semana 09-13 mar: lunes a viernes = 5 días, sin feriados
        $dias = $this->service->diasHabilesEntre($this->inst, '2026-03-09', '2026-03-13');

        $this->assertEquals(5, $dias);
    }

    #[Test]
    public function descuenta_feriado_del_conteo_de_dias_habiles(): void
    {
        EventoCalendario::create([
            'id_institucion' => $this->inst->id,
            'titulo'         => 'Feriado',
            'fecha'          => '2026-03-10', // martes
            'tipo'           => 'feriado',
            'afecta_computo' => true,
        ]);

        $dias = $this->service->diasHabilesEntre($this->inst, '2026-03-09', '2026-03-13');

        $this->assertEquals(4, $dias);
    }
}

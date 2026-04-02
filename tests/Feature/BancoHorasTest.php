<?php

namespace Tests\Feature;

use App\Models\BancoHoras;
use App\Models\Cargo;
use App\Models\Dependencia;
use App\Models\Designacion;
use App\Models\Institucion;
use App\Models\MovimientoBancoHoras;
use App\Models\Usuario;
use App\Services\BancoHorasService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BancoHorasTest extends TestCase
{
    use RefreshDatabase;

    private BancoHorasService $service;
    private Institucion       $inst;
    private Usuario           $usuario;
    private Designacion       $designacion;
    private Usuario           $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(BancoHorasService::class);

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

        $this->admin = Usuario::create([
            'documento' => '99000001',
            'apellidos' => 'Admin',
            'nombres'   => 'Test',
            'email'     => 'admin@bh.test',
            'password'  => bcrypt('password'),
            'activo'    => true,
        ]);

        $this->usuario = Usuario::create([
            'documento' => '30100200',
            'apellidos' => 'Test',
            'nombres'   => 'BancoHoras',
            'email'     => 'bh@test.test',
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
    }

    // ── obtenerOCrearBanco ──────────────────────────────────────────────────

    #[Test]
    public function crea_banco_con_saldo_cero_si_no_existe(): void
    {
        $banco = $this->service->obtenerOCrearBanco($this->usuario);

        $this->assertEquals(0, $banco->saldo_minutos);
        $this->assertDatabaseHas('bancos_horas', ['id_usuario' => $this->usuario->id]);
    }

    // ── acreditarExtra ──────────────────────────────────────────────────────

    #[Test]
    public function no_acredita_si_banco_no_esta_autorizado(): void
    {
        $banco = BancoHoras::create([
            'id_usuario'         => $this->usuario->id,
            'id_designacion'     => null,
            'saldo_minutos'      => 0,
            'autorizado_acumular' => false,
            'autorizado_negativo' => false,
        ]);

        $this->service->acreditarExtra($banco, 60);

        $this->assertEquals(0, $banco->fresh()->saldo_minutos);
        $this->assertDatabaseMissing('movimientos_banco_horas', ['id_banco_horas' => $banco->id]);
    }

    #[Test]
    public function acredita_extra_si_banco_autorizado(): void
    {
        $banco = BancoHoras::create([
            'id_usuario'          => $this->usuario->id,
            'id_designacion'      => null,
            'saldo_minutos'       => 0,
            'autorizado_acumular' => true,
            'autorizado_negativo' => false,
        ]);

        $this->service->acreditarExtra($banco, 90);

        $this->assertEquals(90, $banco->fresh()->saldo_minutos);
        $this->assertDatabaseHas('movimientos_banco_horas', [
            'id_banco_horas' => $banco->id,
            'tipo'           => 'extra',
            'minutos'        => 90,
        ]);
    }

    // ── debitarFaltante ─────────────────────────────────────────────────────

    #[Test]
    public function no_debita_si_dejaria_saldo_negativo_sin_autorizacion(): void
    {
        $banco = BancoHoras::create([
            'id_usuario'          => $this->usuario->id,
            'id_designacion'      => null,
            'saldo_minutos'       => 30,
            'autorizado_acumular' => true,
            'autorizado_negativo' => false,
        ]);

        $this->service->debitarFaltante($banco, 60); // 30 - 60 = -30, no permitido

        $this->assertEquals(30, $banco->fresh()->saldo_minutos);
    }

    #[Test]
    public function debita_si_autorizado_negativo(): void
    {
        $banco = BancoHoras::create([
            'id_usuario'          => $this->usuario->id,
            'id_designacion'      => null,
            'saldo_minutos'       => 30,
            'autorizado_acumular' => true,
            'autorizado_negativo' => true,
        ]);

        $this->service->debitarFaltante($banco, 60);

        $this->assertEquals(-30, $banco->fresh()->saldo_minutos);
    }

    // ── saldo_horas helper ──────────────────────────────────────────────────

    #[Test]
    public function saldo_horas_convierte_minutos_a_horas(): void
    {
        $banco = BancoHoras::create([
            'id_usuario'          => $this->usuario->id,
            'id_designacion'      => null,
            'saldo_minutos'       => 150, // 2.5 horas
            'autorizado_acumular' => true,
            'autorizado_negativo' => false,
        ]);

        $this->assertEquals(2.5, $banco->saldoHoras());
    }

    // ── ajusteManual ────────────────────────────────────────────────────────

    #[Test]
    public function ajuste_manual_registra_movimiento_y_actualiza_saldo(): void
    {
        $banco = BancoHoras::create([
            'id_usuario'          => $this->usuario->id,
            'id_designacion'      => null,
            'saldo_minutos'       => 0,
            'autorizado_acumular' => false,
            'autorizado_negativo' => false,
        ]);

        $this->service->ajusteManual($banco, 120, 'Ajuste por error de sistema', $this->admin);

        $this->assertEquals(120, $banco->fresh()->saldo_minutos);
        $this->assertDatabaseHas('movimientos_banco_horas', [
            'id_banco_horas'    => $banco->id,
            'tipo'              => 'ajuste_manual',
            'minutos'           => 120,
            'id_registrado_por' => $this->admin->id,
        ]);
    }
}

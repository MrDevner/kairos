<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('apellidos');
            $table->string('nombres');
            $table->string('documento')->unique();
            $table->string('email')->nullable()->unique();
            $table->string('telefono', 30)->nullable();
            $table->string('domicilio', 255)->nullable();

            $table->foreignId('id_ciudad_domicilio')->nullable()->constrained('ciudades')->nullOnDelete();
            $table->foreignId('id_estado_nacimiento')->nullable()->constrained('estados')->nullOnDelete();
            $table->foreignId('id_pais_nacimiento')->nullable()->constrained('paises')->nullOnDelete();

            $table->enum('sexo', ['M', 'F', 'X'])->nullable();
            $table->string('foto')->nullable();
            $table->string('password')->nullable();
            $table->string('pin_marca', 60)->nullable();
            $table->string('google_id')->nullable()->index();
            $table->boolean('activo')->default(true);
            $table->string('token_recuerdo', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};

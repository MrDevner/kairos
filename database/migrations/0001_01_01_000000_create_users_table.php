<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('apellidos');
            $table->string('nombres');
            $table->string('documento')->unique();
            $table->string('email')->nullable()->unique();
            $table->string('telefono', 30)->nullable();
            $table->string('domicilio', 255)->nullable();

            // Las FK de ubicación (ciudades/estados/países) se agregan en
            // database/migrations/2026_03_31_000004_add_ubicacion_foreign_keys_to_users_table.php,
            // una vez que esas tablas existen.
            $table->unsignedBigInteger('id_ciudad_domicilio')->nullable();
            $table->unsignedBigInteger('id_estado_nacimiento')->nullable();
            $table->unsignedBigInteger('id_pais_nacimiento')->nullable();

            $table->enum('sexo', ['M', 'F', 'X'])->nullable();
            $table->date('nacimiento')->nullable();
            $table->string('foto')->nullable();
            $table->string('password')->nullable();
            $table->string('pin_marca', 60)->nullable();
            $table->string('google_id')->nullable()->index();
            $table->boolean('activo')->default(true);
            $table->string('token_recuerdo', 100)->nullable();
            $table->string('token', 64)->nullable()->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

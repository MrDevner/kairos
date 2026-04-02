<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_licencia', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->enum('computo', ['dias_corridos', 'dias_habiles']);
            $table->enum('afecta', ['usuario', 'designacion']);
            $table->integer('dias_maximos')->nullable();
            $table->boolean('requiere_documentacion')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_licencia');
    }
};

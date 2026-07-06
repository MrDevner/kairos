<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            // Único a nivel de columna: rechaza duplicados incluso entre borrados (soft delete).
            $table->string('slug')->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_categorias');
    }
};

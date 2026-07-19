<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('prospectos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellido');
            $table->string('email');
            $table->string('telefono', 20)->nullable(); // Opcional
            $table->string('vehiculo_interes'); // Ej: SUV 2026
            $table->enum('estado', [
                'prospeccion',
                'calificacion',
                'negociacion',
                'cierre'
            ])->default('prospeccion');
            $table->string('vendedor')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prospectos');
    }
};

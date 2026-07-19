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
        // Forzamos el nombre correcto en español: 'cotizaciones'
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();
            
            // LLAVES FORÁNEAS: Conectan esta tabla con las otras dos
            $table->foreignId('prospecto_id')->constrained('prospectos')->onDelete('cascade');
            $table->foreignId('vehiculo_id')->constrained('vehiculos')->onDelete('cascade');
            
            $table->decimal('precio_final', 10, 2);
            $table->string('estado')->default('pendiente'); // pendiente, aprobada, rechazada
            $table->text('observaciones')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cotizacions');
    }
};

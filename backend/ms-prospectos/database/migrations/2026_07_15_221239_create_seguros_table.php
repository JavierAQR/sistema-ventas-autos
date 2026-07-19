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
        Schema::create('seguros', function (Blueprint $table) {
            $table->id();
            // Lo asociamos a la transacción de venta (Cotización)
            $table->foreignId('venta_id')
                ->constrained('ventas')
                ->onDelete('cascade');

            $table->string('aseguradora');

            $table->string('tipo_seguro');

            $table->decimal('prima_esperada',10,2);

            $table->decimal('prima_real',10,2)
                ->nullable();

            $table->enum('estado', [
                'prospectado',
                'vendido'
            ])->default('prospectado');

            $table->text('observaciones')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seguros');
    }
};

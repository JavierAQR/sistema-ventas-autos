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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('prospecto_id')
                ->constrained()
                ->cascadeOnUpdate();

            $table->foreignId('vehiculo_id')
                ->constrained()
                ->cascadeOnUpdate();

            $table->foreignId('vendedor_id')
                ->constrained('vendedores')
                ->cascadeOnUpdate();

            $table->decimal('monto_venta', 10, 2);

            $table->enum('estado', [
                'realizada',
                'fallida'
            ]);

            $table->text('motivo_perdida')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};

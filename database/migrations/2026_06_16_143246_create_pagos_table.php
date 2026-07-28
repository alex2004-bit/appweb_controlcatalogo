<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cotizacion_id')->constrained('cotizaciones')->restrictOnDelete();
            $table->decimal('monto', 12, 2);
            $table->string('metodo_pago', 30);
            $table->string('referencia', 100)->nullable();
            $table->string('estado', 20)->default('pendiente');
            $table->date('fecha_pago');
            $table->string('comprobante')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('cotizacion_id');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};

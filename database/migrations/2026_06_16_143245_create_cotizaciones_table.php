<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->foreignId('cliente_id')->constrained('clientes')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('impuesto', 12, 2)->default(0);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('estado', 20)->default('borrador');
            $table->text('notas')->nullable();
            $table->text('condiciones')->nullable();
            $table->text('observaciones')->nullable();
            $table->string('pdf_generado')->nullable();
            $table->integer('validez_dias')->default(30);
            $table->timestamp('aprobada_at')->nullable();
            $table->timestamp('rechazada_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('estado');
            $table->index('cliente_id');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizaciones');
    }
};

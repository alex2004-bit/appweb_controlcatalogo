<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 200);
            $table->string('email', 255);
            $table->string('telefono', 20)->nullable();
            $table->string('empresa', 200)->nullable();
            $table->text('mensaje');
            $table->string('tipo', 20)->default('otro');
            $table->string('estado', 20)->default('pendiente');
            $table->boolean('leido')->default(false);
            $table->timestamp('leido_at')->nullable();
            $table->foreignId('leido_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->timestamps();

            $table->index('estado');
            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultas');
    }
};

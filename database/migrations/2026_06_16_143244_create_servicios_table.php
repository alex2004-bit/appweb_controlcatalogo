<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->constrained('categorias')->restrictOnDelete();
            $table->string('nombre', 200);
            $table->string('slug', 220)->unique();
            $table->text('descripcion')->nullable();
            $table->string('imagen')->nullable();
            $table->json('caracteristicas')->nullable();
            $table->decimal('precio', 12, 2)->default(0);
            $table->string('duracion_estimada', 50)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('categoria_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicios');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->foreignId('vendedor_id')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->string('referido_por', 120)->nullable()->after('vendedor_id');
            $table->string('direccion_entrega', 255)->nullable()->after('notas');
            $table->timestamp('fecha_entrega')->nullable()->after('direccion_entrega');
            $table->string('telefono_contacto', 30)->nullable()->after('fecha_entrega');
            $table->decimal('anticipo', 12, 2)->default(0)->after('total');
            $table->unsignedTinyInteger('numero_cuotas')->default(1)->after('anticipo');
            $table->unsignedInteger('plazo_dias')->nullable()->after('numero_cuotas');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vendedor_id');
            $table->dropColumn(['referido_por', 'direccion_entrega', 'fecha_entrega', 'telefono_contacto', 'anticipo', 'numero_cuotas', 'plazo_dias']);
        });
    }
};

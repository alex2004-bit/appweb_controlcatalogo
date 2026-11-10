<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->string('ciudad', 120)->nullable()->after('direccion_entrega');
            $table->string('provincia', 120)->nullable()->after('ciudad');
            $table->string('tipo_entrega', 20)->default('tienda')->after('provincia');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn(['ciudad', 'provincia', 'tipo_entrega']);
        });
    }
};

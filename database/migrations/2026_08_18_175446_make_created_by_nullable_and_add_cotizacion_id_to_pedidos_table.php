<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->change();
            $table->foreignId('cotizacion_id')->nullable()->after('cliente_id')->constrained('cotizaciones')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cotizacion_id');
            $table->foreignId('created_by')->nullable(false)->change();
        });
    }
};

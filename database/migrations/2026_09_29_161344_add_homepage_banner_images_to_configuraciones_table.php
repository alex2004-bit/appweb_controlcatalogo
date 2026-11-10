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
        Schema::table('configuraciones', function (Blueprint $table) {
            $table->string('banner_imagen_1')->nullable()->after('qr_pago');
            $table->string('banner_imagen_2')->nullable()->after('banner_imagen_1');
            $table->string('banner_imagen_3')->nullable()->after('banner_imagen_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuraciones', function (Blueprint $table) {
            $table->dropColumn(['banner_imagen_1', 'banner_imagen_2', 'banner_imagen_3']);
        });
    }
};

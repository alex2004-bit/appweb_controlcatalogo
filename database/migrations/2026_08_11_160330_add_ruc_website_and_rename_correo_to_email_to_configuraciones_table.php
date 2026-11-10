<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuraciones', function (Blueprint $table) {
            $table->string('ruc', 20)->nullable()->after('nombre_empresa');
            $table->string('website')->nullable()->after('telefono');
            $table->renameColumn('correo', 'email');
        });
    }

    public function down(): void
    {
        Schema::table('configuraciones', function (Blueprint $table) {
            $table->dropColumn(['ruc', 'website']);
            $table->renameColumn('email', 'correo');
        });
    }
};

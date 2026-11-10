<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $columns = [
            'productos' => ['imagen'],
            'galeria' => ['imagen'],
            'configuraciones' => ['logo', 'qr_pago', 'banner_imagen_1', 'banner_imagen_2', 'banner_imagen_3'],
            'pagos' => ['comprobante'],
        ];

        foreach ($columns as $table => $tableColumns) {
            foreach ($tableColumns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }

                DB::table($table)
                    ->where($column, 'like', '/storage/%')
                    ->update([
                        $column => DB::raw("SUBSTRING({$column}, 10)"),
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columns = [
            'productos' => ['imagen'],
            'galeria' => ['imagen'],
            'configuraciones' => ['logo', 'qr_pago', 'banner_imagen_1', 'banner_imagen_2', 'banner_imagen_3'],
            'pagos' => ['comprobante'],
        ];

        foreach ($columns as $table => $tableColumns) {
            foreach ($tableColumns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }

                DB::table($table)
                    ->whereNotNull($column)
                    ->where($column, 'not like', 'http://%')
                    ->where($column, 'not like', 'https://%')
                    ->where($column, 'not like', '/storage/%')
                    ->update([
                        $column => DB::raw("CONCAT('/storage/', {$column})"),
                    ]);
            }
        }
    }
};

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
        if (! Schema::hasTable('categorias')) {
            return;
        }

        $videovigilanciaId = DB::table('categorias')->where('slug', 'camaras')->value('id');

        if (! $videovigilanciaId) {
            return;
        }

        DB::table('categorias')->where('id', $videovigilanciaId)->update([
            'nombre' => 'Videovigilancia',
            'slug' => 'videovigilancia',
            'descripcion' => 'Cámaras y grabadores para sistemas de videovigilancia.',
            'parent_id' => null,
        ]);

        $grabadoresId = DB::table('categorias')->where('slug', 'dvr-nvr')->value('id');

        if ($grabadoresId) {
            DB::table('categorias')->where('id', $grabadoresId)->update([
                'nombre' => 'Otros grabadores',
                'slug' => 'otros-grabadores',
                'descripcion' => 'Grabadores sin una subcategoría más específica.',
                'parent_id' => $videovigilanciaId,
            ]);

            if (Schema::hasTable('servicios')) {
                DB::table('servicios')
                    ->where('categoria_id', $grabadoresId)
                    ->update(['categoria_id' => $videovigilanciaId]);
            }
        }

        DB::table('categorias')
            ->whereIn('slug', ['nvr-ip', 'dvr-hd'])
            ->update(['parent_id' => $videovigilanciaId]);

        DB::table('categorias')
            ->where('slug', 'sistemas-de-alarma')
            ->update([
                'nombre' => 'Alarmas',
                'slug' => 'alarmas',
                'descripcion' => 'Sistemas y dispositivos de alarma.',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('categorias')) {
            return;
        }

        $videovigilanciaId = DB::table('categorias')->where('slug', 'videovigilancia')->value('id');
        $grabadoresId = DB::table('categorias')->where('slug', 'otros-grabadores')->value('id');

        if (! $videovigilanciaId) {
            return;
        }

        DB::table('categorias')
            ->whereIn('slug', ['nvr-ip', 'dvr-hd'])
            ->update(['parent_id' => $grabadoresId]);

        if ($grabadoresId) {
            DB::table('categorias')->where('id', $grabadoresId)->update([
                'nombre' => 'DVR / NVR',
                'slug' => 'dvr-nvr',
                'descripcion' => 'Grabadores digitales y de red',
                'parent_id' => null,
            ]);

            if (Schema::hasTable('servicios')) {
                DB::table('servicios')
                    ->where('categoria_id', $videovigilanciaId)
                    ->where('slug', 'mantenimiento-de-equipos')
                    ->update(['categoria_id' => $grabadoresId]);
            }
        }

        DB::table('categorias')->where('id', $videovigilanciaId)->update([
            'nombre' => 'Cámaras',
            'slug' => 'camaras',
            'descripcion' => 'Cámaras de seguridad y videovigilancia',
        ]);

        DB::table('categorias')->where('slug', 'alarmas')->update([
            'nombre' => 'Sistemas de Alarma',
            'slug' => 'sistemas-de-alarma',
            'descripcion' => 'Sistemas de alarma y detección',
        ]);
    }
};

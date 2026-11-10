<?php

namespace App\Console\Commands;

use App\Services\DigicorpCatalogImporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('digicorp:import')]
#[Description('Importa el catálogo de Digicorp desde el PDF (storage/app/imports/digicorp_products.json)')]
class ImportDigicorpCatalog extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(DigicorpCatalogImporter $importer): int
    {
        try {
            $stats = $importer->run();
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Importación de Digicorp finalizada.');
        $this->table(
            ['Métrica', 'Cantidad'],
            [
                ['Productos importados', $stats['importados']],
                ['Productos actualizados', $stats['actualizados']],
                ['Omitidos por duplicados', $stats['omitidos_duplicados']],
                ['Productos sin nombre (omitidos)', $stats['sin_nombre']],
                ['Imágenes copiadas', $stats['imagenes_copiadas']],
                ['Imágenes con problema', $stats['imagenes_problema']],
            ]
        );

        return self::SUCCESS;
    }
}

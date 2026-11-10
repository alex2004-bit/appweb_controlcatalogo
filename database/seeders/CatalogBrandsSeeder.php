<?php

namespace Database\Seeders;

use App\Models\Marca;
use App\Models\Producto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogBrandsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = collect([
            'Dahua',
            'Hikvision',
            'TP-Link',
            'Seagate',
            'ZKTeco',
            'Imou',
            'Dimax',
        ])->mapWithKeys(function (string $name): array {
            $brand = Marca::withTrashed()->firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'nombre' => $name,
                    'activo' => true,
                ],
            );

            if ($brand->trashed()) {
                $brand->restore();
            }

            if (! $brand->activo) {
                $brand->update(['activo' => true]);
            }

            return [$name => $brand];
        })->all();

        $this->assignProductsByCategory($brands);
    }

    /**
     * Associate existing products with the most appropriate brand for their category.
     *
     * @param  array<string, Marca>  $brands
     */
    private function assignProductsByCategory(array $brands): void
    {
        Producto::query()
            ->whereNull('marca_id')
            ->whereHas('categoria', fn ($query) => $query->whereIn('slug', ['camaras-ip', 'camaras-analogicas', 'nvr-ip', 'dvr-hd']))
            ->update(['marca_id' => $brands['Dahua']->id]);

        Producto::query()
            ->whereNull('marca_id')
            ->whereHas('categoria', fn ($query) => $query->where('slug', 'sensores'))
            ->update(['marca_id' => $brands['ZKTeco']->id]);

        Producto::query()
            ->whereNull('marca_id')
            ->whereHas('categoria', fn ($query) => $query->where('slug', 'cerraduras-electricas'))
            ->update(['marca_id' => $brands['ZKTeco']->id]);
    }
}

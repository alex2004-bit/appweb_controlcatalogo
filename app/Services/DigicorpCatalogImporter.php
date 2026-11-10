<?php

namespace App\Services;

use App\Models\Categoria;
use App\Models\Configuracion;
use App\Models\Marca;
use App\Models\Producto;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DigicorpCatalogImporter
{
    private const JSON_PATH = 'imports/digicorp_products.json';

    private const IMG_SOURCE_DIR = 'imports/digicorp_images';

    private const IMG_DEST_DIR = 'public/productos';

    private const CATEGORY_MAP = [
        'Cámaras de Seguridad' => 'Videovigilancia',
        'Grabadores DVR/NVR' => 'Videovigilancia',
        'Fuentes de Poder' => 'Videovigilancia',
        'Accesorios' => 'Videovigilancia',
        'Control de Acceso' => null,
    ];

    private const BRANDS = ['Dahua', 'imou', 'Dimax', 'TP-Link', 'Seagate'];

    public function run(): array
    {
        $path = storage_path('app/'.self::JSON_PATH);

        if (! File::exists($path)) {
            throw new \RuntimeException("No existe el archivo de datos: {$path}");
        }

        $products = json_decode(File::get($path), true);

        if (! is_array($products)) {
            throw new \RuntimeException('El archivo de datos no contiene JSON válido.');
        }

        $this->backup();

        $categories = $this->ensureCategories();
        $brands = $this->ensureBrands();

        $stats = [
            'importados' => 0,
            'actualizados' => 0,
            'omitidos_duplicados' => 0,
            'sin_nombre' => 0,
            'imagenes_copiadas' => 0,
            'imagenes_problema' => 0,
        ];

        $seen = [];

        foreach ($products as $data) {
            $nombre = $this->cleanName($data['nombre'] ?? null) ?: trim((string) ($data['modelo'] ?? ''));

            if ($nombre === '') {
                $stats['sin_nombre']++;

                continue;
            }

            $sku = $data['modelo'] ? strtoupper(trim($data['modelo'])) : null;

            $dedupKey = $sku ?? Str::slug($nombre);

            if (isset($seen[$dedupKey])) {
                $stats['omitidos_duplicados']++;

                continue;
            }

            $seen[$dedupKey] = true;

            $producto = $sku
                ? Producto::where('sku', $sku)->first()
                : Producto::where('slug', Str::slug($nombre))->first();

            $especificaciones = $this->buildEspecificaciones($data['descripcion'] ?? []);

            $precio = ($data['precio'] ?? null) !== null && ! ($data['precio_dudoso'] ?? false)
                ? $data['precio']
                : null;

            $attributes = [
                'categoria_id' => $categories[$data['categoria'] ?? 'Accesorios'] ?? null,
                'marca_id' => $brands[$data['marca'] ?? null] ?? null,
                'nombre' => $nombre,
                'descripcion' => $this->buildDescripcion($data['descripcion'] ?? []),
                'especificaciones' => $especificaciones,
                'sku' => $sku,
                'precio' => $precio,
                'precio_anterior' => null,
                'stock' => $this->parseStock($data['stock'] ?? null),
                'activo' => true,
            ];

            if ($producto) {
                $producto->update($attributes);
                $stats['actualizados']++;
            } else {
                $attributes['slug'] = $this->uniqueSlug($nombre);
                $producto = Producto::create($attributes);
                $stats['importados']++;
            }

            $imagen = $this->storeImagen($data['imagen_file'] ?? null);

            if ($imagen) {
                $producto->update(['imagen' => $imagen]);
                $stats['imagenes_copiadas']++;
            } elseif (($data['imagen_file'] ?? null) !== null) {
                $stats['imagenes_problema']++;
            }
        }

        $this->ensureConfiguracion();

        return $stats;
    }

    private function backup(): void
    {
        $dir = storage_path('app/imports/backups');
        File::ensureDirectoryExists($dir);

        $file = $dir.'/digicorp_backup_'.now()->format('Ymd_His').'.json';

        $data = [
            'categorias' => Categoria::withTrashed()->get(['id', 'nombre', 'slug', 'parent_id', 'activo'])->toArray(),
            'marcas' => Marca::withTrashed()->get(['id', 'nombre', 'slug', 'activo'])->toArray(),
            'productos' => Producto::withTrashed()->get([
                'id', 'categoria_id', 'marca_id', 'nombre', 'slug', 'descripcion',
                'especificaciones', 'sku', 'precio', 'precio_anterior', 'stock', 'imagen', 'activo',
            ])->toArray(),
        ];

        File::put($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function ensureCategories(): array
    {
        $videovigilancia = Categoria::firstOrCreate(['slug' => 'videovigilancia'], [
            'nombre' => 'Videovigilancia',
            'activo' => true,
        ]);

        $result = [];

        foreach (self::CATEGORY_MAP as $nombre => $parentName) {
            $categoria = Categoria::firstOrCreate(['slug' => Str::slug($nombre)], [
                'nombre' => $nombre,
                'parent_id' => $parentName ? $videovigilancia->id : null,
                'activo' => true,
            ]);

            $result[$nombre] = $categoria->id;
        }

        return $result;
    }

    private function ensureBrands(): array
    {
        $result = [];

        foreach (self::BRANDS as $nombre) {
            $marca = Marca::firstOrCreate(['slug' => Str::slug($nombre)], [
                'nombre' => $nombre,
                'activo' => true,
            ]);

            $result[$nombre] = $marca->id;
        }

        return $result;
    }

    private function storeImagen(?string $file): ?string
    {
        if (! $file) {
            return null;
        }

        $source = storage_path('app/'.self::IMG_SOURCE_DIR.'/'.$file);

        if (! File::exists($source)) {
            return null;
        }

        Storage::disk('public')->putFileAs('productos', new \Illuminate\Http\File($source), $file);

        return 'productos/'.$file;
    }

    private function parseStock(?string $stock): ?int
    {
        if (! $stock) {
            return null;
        }

        $match = preg_match('/(\d+)/', $stock, $matches);

        return $match ? (int) $matches[1] : null;
    }

    private function cleanName(?string $name): ?string
    {
        if (! $name) {
            return null;
        }

        return trim(rtrim($name, '* '));
    }

    private function buildDescripcion(array $desc): ?string
    {
        if (empty($desc)) {
            return null;
        }

        return implode("\n", array_map(fn ($line) => ltrim($line, '* '), $desc));
    }

    private function buildEspecificaciones(array $desc): array
    {
        $especificaciones = [];

        foreach (array_values($desc) as $i => $line) {
            $especificaciones['caracteristica_'.str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT)] = ltrim($line, '* ');
        }

        return $especificaciones;
    }

    private function ensureConfiguracion(): void
    {
        $config = Configuracion::first();

        if ($config) {
            $config->update(['moneda' => 'Bs']);
        }
    }
}

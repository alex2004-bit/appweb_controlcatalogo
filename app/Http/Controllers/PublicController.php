<?php

namespace App\Http\Controllers;

use App\Http\Requests\Frontend\CotizacionRequest;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Configuracion;
use App\Models\Consulta;
use App\Models\Cotizacion;
use App\Models\DetalleCotizacion;
use App\Models\Marca;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Servicio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PublicController extends Controller
{
    public function home(): Response
    {
        $destacados = Producto::with('categoria', 'marca')
            ->where('activo', true)
            ->latest()
            ->take(6)
            ->get()
            ->map(fn (Producto $producto) => $this->withPublicImageUrl($producto));

        $stats = [
            ['label' => 'Productos', 'value' => Producto::where('activo', true)->count().'+', 'color' => 'blue'],
            ['label' => 'Servicios', 'value' => Servicio::where('activo', true)->count().'+', 'color' => 'amber'],
            ['label' => 'Clientes', 'value' => '1,200+', 'color' => 'emerald'],
            ['label' => 'Años', 'value' => '10+', 'color' => 'blue'],
        ];

        $configuracion = Configuracion::query()->first();

        return Inertia::render('public/Home', [
            'destacados' => $destacados,
            'stats' => $stats,
            'bannerImages' => [
                $configuracion?->banner_imagen_1 ? $this->publicImageUrl($configuracion->banner_imagen_1) : null,
                $configuracion?->banner_imagen_2 ? $this->publicImageUrl($configuracion->banner_imagen_2) : null,
                $configuracion?->banner_imagen_3 ? $this->publicImageUrl($configuracion->banner_imagen_3) : null,
            ],
        ]);
    }

    public function catalog(Request $request): Response
    {
        $query = Producto::with('categoria', 'marca')->where('activo', true);

        if ($request->filled('categoria')) {
            $categoryId = (int) $request->input('categoria');
            $categoryIds = Categoria::query()
                ->where('activo', true)
                ->where(fn ($categories) => $categories->whereKey($categoryId)->orWhere('parent_id', $categoryId))
                ->pluck('id');

            $query->whereIn('categoria_id', $categoryIds);
        }

        if ($request->filled('marca')) {
            $query->where('marca_id', $request->marca);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nombre', 'like', "%{$s}%")
                    ->orWhere('descripcion', 'like', "%{$s}%")
                    ->orWhere('sku', 'like', "%{$s}%");
            });
        }

        if ($request->filled('precio_min')) {
            $query->where('precio', '>=', (float) $request->precio_min);
        }

        if ($request->filled('precio_max')) {
            $query->where('precio', '<=', (float) $request->precio_max);
        }

        $sort = $request->sort ?? 'latest';
        match ($sort) {
            'price_asc' => $query->orderBy('precio'),
            'price_desc' => $query->orderBy('precio', 'desc'),
            'name_asc' => $query->orderBy('nombre'),
            'name_desc' => $query->orderBy('nombre', 'desc'),
            default => $query->latest(),
        };

        $productos = $query->paginate(12)
            ->withQueryString()
            ->through(fn (Producto $producto) => $this->withPublicImageUrl($producto));

        $categorias = Categoria::query()
            ->where('activo', true)
            ->where(fn ($query) => $query
                ->whereHas('productos', fn ($products) => $products->where('activo', true))
                ->orWhereHas('children.productos', fn ($products) => $products->where('activo', true)))
            ->with('parent:id,nombre')
            ->with(['children' => fn ($children) => $children
                ->where('activo', true)
                ->withCount(['productos' => fn ($products) => $products->where('activo', true)])])
            ->withCount(['productos' => fn ($products) => $products->where('activo', true)])
            ->orderByRaw('parent_id IS NOT NULL')
            ->orderBy('parent_id')
            ->orderBy('nombre')
            ->get(['id', 'parent_id', 'nombre', 'slug'])
            ->each(function (Categoria $categoria): void {
                $categoria->productos_count += $categoria->children->sum('productos_count');
                $categoria->unsetRelation('children');
            });

        $marcas = Marca::where('activo', true)
            ->withCount(['productos' => fn ($q) => $q->where('activo', true)])
            ->get(['id', 'nombre', 'slug', 'logo']);

        return Inertia::render('public/Productos', [
            'productos' => $productos,
            'categorias' => $categorias,
            'marcas' => $marcas,
            'filters' => $request->only(['search', 'categoria', 'marca', 'precio_min', 'precio_max', 'sort']),
        ]);
    }

    public function catalogShow(Producto $producto): Response
    {
        if (! $producto->activo) {
            abort(404);
        }

        $producto->load(['categoria', 'marca', 'imagenes' => fn ($q) => $q->where('activo', true)->orderBy('id')]);

        $relacionados = Producto::with('categoria')
            ->where('categoria_id', $producto->categoria_id)
            ->where('id', '!=', $producto->id)
            ->where('activo', true)
            ->take(4)
            ->get();

        $imagenes = $producto->imagenes->pluck('imagen')->filter()->values()->all();
        if ($producto->imagen && ! in_array($producto->imagen, $imagenes)) {
            array_unshift($imagenes, $producto->imagen);
        }
        $imagenes = array_map(fn (string $imagen): string => $this->publicImageUrl($imagen), $imagenes);

        return Inertia::render('public/ProductoDetalle', [
            'producto' => [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'slug' => $producto->slug,
                'descripcion' => $producto->descripcion,
                'especificaciones' => $producto->especificaciones,
                'sku' => $producto->sku,
                'codigo_fabricante' => $producto->sku,
                'precio' => $producto->precio,
                'imagen' => $producto->imagen ? $this->publicImageUrl($producto->imagen) : null,
                'imagenes' => $imagenes,
                'stock' => $producto->stock,
                'marca' => $producto->marca ? [
                    'id' => $producto->marca->id,
                    'nombre' => $producto->marca->nombre,
                    'slug' => $producto->marca->slug,
                    'logo' => $producto->marca->logo ? $this->publicImageUrl($producto->marca->logo) : null,
                ] : null,
                'categoria' => $producto->categoria ? [
                    'nombre' => $producto->categoria->nombre,
                    'slug' => $producto->categoria->slug,
                ] : null,
            ],
            'relacionados' => $relacionados->map(fn ($r) => [
                'id' => $r->id,
                'nombre' => $r->nombre,
                'slug' => $r->slug,
                'precio' => $r->precio,
                'imagen' => $r->imagen ? $this->publicImageUrl($r->imagen) : null,
                'categoria' => $r->categoria ? ['nombre' => $r->categoria->nombre] : null,
            ]),
        ]);
    }

    public function newProducts(): Response
    {
        $productos = Producto::with('categoria', 'marca')
            ->where('activo', true)
            ->latest()
            ->paginate(12)
            ->through(fn (Producto $producto) => $this->withPublicImageUrl($producto));

        return Inertia::render('public/Novedades', [
            'productos' => $productos,
        ]);
    }

    private function publicImageUrl(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/storage/')) {
            $path = substr($path, 9);
        } elseif (str_starts_with($path, '/')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    private function withPublicImageUrl(Producto $producto): Producto
    {
        $producto->imagen = $producto->imagen
            ? $this->publicImageUrl($producto->imagen)
            : null;

        return $producto;
    }

    public function offers(): Response
    {
        $productos = Producto::with('categoria', 'marca')
            ->where('activo', true)
            ->whereNotNull('precio_anterior')
            ->where('precio_anterior', '>', 0)
            ->latest()
            ->paginate(12)
            ->through(fn (Producto $producto) => $this->withPublicImageUrl($producto));

        return Inertia::render('public/Ofertas', [
            'productos' => $productos,
        ]);
    }

    public function services(): Response
    {
        $servicios = Servicio::with('categoria')
            ->where('activo', true)
            ->latest()
            ->get()
            ->map(function (Servicio $servicio): Servicio {
                $servicio->imagen = $servicio->imagen
                    ? $this->publicImageUrl($servicio->imagen)
                    : null;

                return $servicio;
            });

        $categorias = Categoria::where('activo', true)
            ->whereHas('servicios', fn ($q) => $q->where('activo', true))
            ->get(['id', 'nombre', 'slug']);

        return Inertia::render('public/Servicios', [
            'servicios' => $servicios,
            'categorias' => $categorias,
        ]);
    }

    public function nosotros(): Response
    {
        return Inertia::render('public/Nosotros');
    }

    public function contacto(): Response
    {
        return Inertia::render('public/Contacto');
    }

    public function sendContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telefono' => 'nullable|string|max:50',
            'empresa' => 'nullable|string|max:200',
            'mensaje' => 'required|string|max:5000',
        ]);

        Consulta::create([
            'nombre' => $validated['nombre'],
            'email' => $validated['email'],
            'telefono' => $validated['telefono'] ?? '',
            'empresa' => $validated['empresa'] ?? '',
            'mensaje' => $validated['mensaje'],
            'tipo' => 'web',
            'estado' => 'pendiente',
            'leido' => false,
        ]);

        return to_route('contacto')->with('contact_sent', true);
    }

    public function menuData(): JsonResponse
    {
        $categorias = Categoria::where('activo', true)
            ->whereNull('parent_id')
            ->withCount(['productos' => fn ($q) => $q->where('activo', true)])
            ->get(['id', 'nombre', 'slug']);

        $marcas = Marca::where('activo', true)
            ->withCount(['productos' => fn ($q) => $q->where('activo', true)])
            ->get(['id', 'nombre', 'slug', 'logo']);

        return response()->json([
            'categorias' => $categorias,
            'marcas' => $marcas->map(function (Marca $marca): Marca {
                $marca->logo = $marca->logo
                    ? $this->publicImageUrl($marca->logo)
                    : null;

                return $marca;
            }),
        ]);
    }

    public function storeCotizacion(CotizacionRequest $request): JsonResponse
    {
        // Rate limiting adicional por IP (además del middleware)
        $key = 'cotizar:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            Log::warning('Rate limit exceeded for cotizar', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'message' => 'Demasiadas solicitudes. Intente nuevamente en un minuto.',
            ], 429);
        }

        $validated = $request->validated();

        // Validación adicional: máximo total de unidades
        $totalUnidades = collect($validated['items'])->sum('cantidad');
        if ($totalUnidades > 200) {
            Log::warning('Cotización con muchas unidades', [
                'ip' => $request->ip(),
                'total_unidades' => $totalUnidades,
                'items_count' => count($validated['items']),
            ]);

            return response()->json([
                'message' => 'El total de unidades solicitadas excede el límite permitido (200).',
            ], 422);
        }

        if (! empty($validated['email'])) {
            $fingerprint = $validated['email'].'|'.md5(json_encode($validated['items']));
            $duplicateKey = 'cotizar_dup:'.$fingerprint;

            if (RateLimiter::attempts($duplicateKey) > 0) {
                Log::warning('Intento de cotización duplicada', [
                    'ip' => $request->ip(),
                    'email' => $validated['email'],
                ]);

                return response()->json([
                    'message' => 'Ya se envió una cotización similar recientemente. Espere unos minutos.',
                ], 429);
            }

            RateLimiter::hit($duplicateKey, 300);
        }

        RateLimiter::hit($key, 60);

        return DB::transaction(function () use ($validated) {
            // Agrupar items por item_id para validar stock total
            $aggregatedItems = $this->aggregateItems($validated['items']);

            $productIds = collect($aggregatedItems)->pluck('item_id');

            $productos = Producto::where('activo', true)
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $detalles = [];
            $subtotal = 0;

            foreach ($aggregatedItems as $item) {
                $producto = $productos->get($item['item_id']);

                if (! $producto) {
                    continue;
                }

                $cantidad = $item['cantidad'];

                if ($producto->stock !== null && $producto->stock < $cantidad) {
                    return response()->json([
                        'message' => "Stock insuficiente para {$producto->nombre}. Disponible: {$producto->stock}, solicitado: {$cantidad}.",
                    ], 422);
                }

                $itemSubtotal = (float) $producto->precio * $cantidad;
                $subtotal += $itemSubtotal;

                $detalles[] = [
                    'item_type' => Producto::class,
                    'item_id' => $producto->id,
                    'item_nombre' => $producto->nombre,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $producto->precio,
                    'descuento' => 0,
                    'subtotal' => $itemSubtotal,
                ];
            }

            if (empty($detalles)) {
                return response()->json(['message' => 'No se encontraron productos válidos.'], 422);
            }

            $cliente = null;

            if (! empty($validated['email'])) {
                $cliente = Cliente::where('email', $validated['email'])->first();
            }

            if (! $cliente) {
                $cliente = Cliente::create([
                    'tipo_persona' => 'natural',
                    'nombre' => $validated['nombre'],
                    'email' => $validated['email'] ?? null,
                    'telefono' => $validated['telefono'] ?? null,
                    'documento' => $validated['documento'] ?? null,
                    'direccion' => $validated['direccion'] ?? null,
                ]);
            } elseif (! empty($validated['telefono']) || ! empty($validated['direccion']) || ! empty($validated['documento'])) {
                $cliente->update([
                    'telefono' => $validated['telefono'] ?? $cliente->telefono,
                    'direccion' => $validated['direccion'] ?? $cliente->direccion,
                    'documento' => $validated['documento'] ?? $cliente->documento,
                ]);
            }

            $impuesto = $subtotal * Configuracion::impuestoRate();
            $total = $subtotal + $impuesto;
            $tipoEntrega = match ($validated['tipo_entrega'] ?? null) {
                'provincia' => 'Envío a provincia',
                'domicilio' => 'Envío a domicilio',
                'recoger' => 'Recoger en tienda',
                'tienda' => 'Entrega en tienda',
                default => null,
            };
            $notasCotizacion = collect([
                'Cotización solicitada desde la web.',
                $tipoEntrega ? 'Tipo de entrega: '.$tipoEntrega : null,
                in_array($validated['tipo_entrega'] ?? null, ['provincia', 'domicilio'], true) && filled($validated['direccion'] ?? null)
                    ? 'Dirección: '.$validated['direccion']
                    : null,
                in_array($validated['tipo_entrega'] ?? null, ['provincia', 'domicilio'], true) && filled($validated['ciudad'] ?? null)
                    ? 'Ciudad: '.$validated['ciudad']
                    : null,
                ($validated['tipo_entrega'] ?? null) === 'provincia' && filled($validated['provincia'] ?? null)
                    ? 'Provincia: '.$validated['provincia']
                    : null,
                filled($validated['notas'] ?? null) ? 'Instrucciones: '.$validated['notas'] : null,
            ])->filter()->implode("\n");

            // Create cotizacion first to get the auto-increment ID, then generate code
            $cotizacion = Cotizacion::create([
                'cliente_id' => $cliente->id,
                'created_by' => null,
                'subtotal' => $subtotal,
                'impuesto' => $impuesto,
                'descuento' => 0,
                'total' => $total,
                'estado' => 'enviada',
                'notas' => $notasCotizacion,
                'validez_dias' => 30,
            ]);

            // Generate code using the actual ID
            $codigo = 'COT-'.now()->format('Ymd').'-'.str_pad($cotizacion->id, 4, '0', STR_PAD_LEFT);
            $cotizacion->update(['codigo' => $codigo]);

            foreach ($detalles as $detalle) {
                DetalleCotizacion::create([
                    'cotizacion_id' => $cotizacion->id,
                    ...$detalle,
                ]);
            }

            // NOTA: No se crea Pedido automáticamente ni se descuenta stock.
            // Una cotización es solo una solicitud de precio. El stock se descuenta
            // cuando el admin convierte la cotización en un pedido confirmado.

            return response()->json([
                'codigo' => $cotizacion->codigo,
                'total' => $total,
                'qr_pago' => ($qrPago = Configuracion::query()->first()?->qr_pago)
                    ? $this->publicImageUrl($qrPago)
                    : null,
                'message' => 'Cotización enviada correctamente. Nos pondremos en contacto pronto.',
            ]);
        });
    }

    /**
     * Agrupa items por item_id sumando sus cantidades.
     * Evita que el mismo producto en múltiples líneas supere el stock sin detectarlo.
     */
    private function aggregateItems(array $items): array
    {
        $aggregated = [];

        foreach ($items as $item) {
            $key = 'producto_'.$item['id'];

            if (! isset($aggregated[$key])) {
                $aggregated[$key] = [
                    'item_id' => $item['id'],
                    'cantidad' => 0,
                ];
            }

            $aggregated[$key]['cantidad'] += (int) $item['cantidad'];
        }

        return array_values($aggregated);
    }
}

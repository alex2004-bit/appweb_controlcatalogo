<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Consulta;
use App\Models\Marca;
use App\Models\Producto;
use App\Models\Servicio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            ->get();

        $stats = [
            ['label' => 'Productos', 'value' => Producto::where('activo', true)->count().'+', 'color' => 'blue'],
            ['label' => 'Servicios', 'value' => Servicio::where('activo', true)->count().'+', 'color' => 'amber'],
            ['label' => 'Clientes', 'value' => '1,200+', 'color' => 'emerald'],
            ['label' => 'Años', 'value' => '10+', 'color' => 'blue'],
        ];

        return Inertia::render('public/Home', [
            'destacados' => $destacados,
            'stats' => $stats,
        ]);
    }

    public function catalog(Request $request): Response
    {
        $query = Producto::with('categoria', 'marca')->where('activo', true);

        if ($request->filled('categoria')) {
            $query->where('categoria_id', $request->categoria);
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

        $productos = $query->paginate(12)->withQueryString();

        $categorias = Categoria::where('activo', true)
            ->whereHas('productos', fn ($q) => $q->where('activo', true))
            ->withCount(['productos' => fn ($q) => $q->where('activo', true)])
            ->get(['id', 'nombre', 'slug']);

        $marcas = Marca::where('activo', true)
            ->whereHas('productos', fn ($q) => $q->where('activo', true))
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

        $producto->load('categoria', 'marca');

        $relacionados = Producto::with('categoria')
            ->where('categoria_id', $producto->categoria_id)
            ->where('id', '!=', $producto->id)
            ->where('activo', true)
            ->take(4)
            ->get();

        return Inertia::render('public/ProductoDetalle', [
            'producto' => $producto,
            'relacionados' => $relacionados,
        ]);
    }

    public function newProducts(): Response
    {
        $productos = Producto::with('categoria', 'marca')
            ->where('activo', true)
            ->latest()
            ->paginate(12);

        return Inertia::render('public/Novedades', [
            'productos' => $productos,
        ]);
    }

    public function offers(): Response
    {
        $productos = Producto::with('categoria', 'marca')
            ->where('activo', true)
            ->whereNotNull('precio_anterior')
            ->where('precio_anterior', '>', 0)
            ->latest()
            ->paginate(12);

        return Inertia::render('public/Ofertas', [
            'productos' => $productos,
        ]);
    }

    public function services(): Response
    {
        $servicios = Servicio::with('categoria')
            ->where('activo', true)
            ->latest()
            ->get();

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

    public function menuData(): \Illuminate\Http\JsonResponse
    {
        $categorias = Categoria::where('activo', true)
            ->whereNull('parent_id')
            ->withCount(['productos' => fn ($q) => $q->where('activo', true)])
            ->get(['id', 'nombre', 'slug']);

        $marcas = Marca::where('activo', true)
            ->whereHas('productos', fn ($q) => $q->where('activo', true))
            ->withCount(['productos' => fn ($q) => $q->where('activo', true)])
            ->get(['id', 'nombre', 'slug', 'logo']);

        return response()->json([
            'categorias' => $categorias,
            'marcas' => $marcas,
        ]);
    }
}

<?php

namespace App\Http\Middleware;

use App\Models\Configuracion;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Spatie\Permission\Models\Role;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'currentTeam' => fn () => $user?->currentTeam ? $user->toUserTeam($user->currentTeam) : null,
            'teams' => fn () => $user?->toUserTeams(includeCurrent: true) ?? [],
            'roles' => fn () => $user ? Role::with('permissions')->get() : [],
            'configuracion' => fn () => Configuracion::query()->first()?->only('qr_pago', 'whatsapp', 'moneda', 'impuesto') ?? ['qr_pago' => null, 'whatsapp' => null, 'moneda' => 'Bs', 'impuesto' => 18],
        ];
    }
}

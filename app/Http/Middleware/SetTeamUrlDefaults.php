<?php

namespace App\Http\Middleware;

use App\Concerns\HasTeams;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetTeamUrlDefaults
{
    /**
     * Set the default URL parameters for team-based routes.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            /** @var HasTeams $user */
            $team = $user->currentTeam ?? $user->personalTeam();

            if ($team) {
                URL::defaults([
                    'current_team' => $team->slug,
                    'team' => $team->slug,
                ]);
            }
        }

        return $next($request);
    }
}

<?php

namespace Filament\Jetstream\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Filament\Jetstream\Jetstream;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AcceptPendingTeamInvitation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only check for pending invitations if user is authenticated
        if (Filament::auth()->check() && Jetstream::plugin()->hasTeamsFeatures()) {
            Jetstream::plugin()->acceptPendingTeamInvitation();
        }

        return $response;
    }
}

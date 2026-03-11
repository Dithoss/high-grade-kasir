<?php

namespace App\Http\Middleware;

use App\Services\SettingService;
use Closure;
use Illuminate\Http\Request;

class CheckMaintenanceMode
{
    public function __construct(protected SettingService $settings) {}

    public function handle(Request $request, Closure $next)
    {
        if ($this->settings->isMaintenanceMode()) {
            if (auth()->check() && auth()->user()->hasRole('admin')) {
                return $next($request);
            }

            return response()->view('errors.maintenance', [
                'message' => $this->settings->maintenanceMessage(),
            ], 503);
        }

        return $next($request);
    }
}
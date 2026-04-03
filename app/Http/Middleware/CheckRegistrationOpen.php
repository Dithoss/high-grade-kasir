<?php

namespace App\Http\Middleware;

use App\Services\SettingService;
use Closure;
use Illuminate\Http\Request;

class CheckRegistrationOpen
{
    public function __construct(protected SettingService $settings) {}

    public function handle(Request $request, Closure $next)
    {
        if (!$this->settings->isRegistrationOpen()) {
            return redirect()->route('login')
                ->with('error', 'Registrasi sedang ditutup sementara.');
        }

        return $next($request);
    }
}
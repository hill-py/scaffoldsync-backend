<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Rate limiter khusus endpoint login (mencegah brute force)
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip())->response(function () {
                return response()->json([
                    'status' => 429,
                    'message' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam 1 menit.',
                ], 429);
            });
        });

        // Rate limiter standar seluruh endpoint API
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id_user ?: $request->ip())->response(function () {
                return response()->json([
                    'status' => 429,
                    'message' => 'Batas kuota panggilan API terlampaui (60 request/menit).',
                ], 429);
            });
        });
    }
}
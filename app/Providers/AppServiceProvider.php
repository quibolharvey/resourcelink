<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
{
    Inertia::share([
        'auth' => function () {
            return [
                'user' => auth()->user() ? array_merge(
                    auth()->user()->only('id', 'name', 'email'),
                    ['roles' => auth()->user()->getRoleNames()]
                ) : null,
            ];
        },
    ]);
}}

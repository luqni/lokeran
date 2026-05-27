<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use App\Models\UserToken;

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
    public function boot(): void
    {
        if ($this->app->environment('production') || env('FORCE_HTTPS', false)) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        Event::listen(function (Registered $event) {
            UserToken::create([
                'user_id' => $event->user->id,
                'token_balance' => 10,
            ]);
        });
    }
}

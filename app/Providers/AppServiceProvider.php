<?php

namespace App\Providers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        View::composer(['layouts.app', 'partials.navbar', 'partials.footer', 'partials.character-module'], function ($view) {
            if (Auth::check()) {
                $view->with('character', Auth::user()->character()->with([
                    'city.villages',
                    'currentCity.villages',
                    'stats',
                    'badges',
                ])->withCount('posts')->first());
            }
        });

        View::composer(['partials.navbar', 'partials.character-module'], function ($view) {
            if (Auth::check()) {
                $character = Auth::user()->character;
                $unreadNotifCount = $character
                    ? Notification::where('user_id', Auth::id())->unread()->count()
                    : 0;
                $view->with('unreadNotifCount', $unreadNotifCount);
            }
        });
    }
}

<?php

namespace App\Providers;

use App\Models\ReportNotification;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        View::composer('layouts.app', function ($view) {
            $user = auth()->user();

            $notifications = collect();
            $unreadCount = 0;

            if ($user) {
                $notifications = ReportNotification::query()
                    ->with('review')
                    ->where('user_id', $user->id)
                    ->latest()
                    ->limit(8)
                    ->get();

                $unreadCount = ReportNotification::query()
                    ->where('user_id', $user->id)
                    ->whereNull('read_at')
                    ->count();
            }

            $view->with([
                'navNotifications' => $notifications,
                'navUnreadCount' => $unreadCount,
            ]);
        });
    }
}

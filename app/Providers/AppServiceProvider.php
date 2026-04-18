<?php

namespace App\Providers;
use App\Observers\UserObserver;
use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\View\Composers\SiteContentComposer;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFour();
        User::observe(UserObserver::class);

        // Share homepage editable content with home/index views
        View::composer(['home', 'index'], SiteContentComposer::class);
    }
}
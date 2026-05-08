<?php

namespace App\Providers;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View; 
use App\Models\Category;
use App\Models\Footer;
use App\Models\Product;        


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
           View::share('categories', Category::where('status', 1)->get());
    View::composer('*', function ($view) {
        $view->with('footer', Footer::first()); // Lấy 1 footer duy nhất
    });
    }
}

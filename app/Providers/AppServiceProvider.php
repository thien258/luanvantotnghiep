<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Footer;
use App\Models\Festival;
use App\Models\Product;
use Illuminate\Pagination\Paginator;


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
        View::share('brands', Brand::where('status', 1)->get());
        View::share('festivals', Festival::where('status', 1)->get());

        Paginator::useBootstrapFive();
        View::composer('*', function ($view) {
            $view->with('footer', Footer::first()); // Lấy 1 footer duy nhất

        });
    }
}

<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;
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
        // Force URL root theo APP_URL trong .env (cần thiết khi dùng ngrok/proxy)
        $appUrl = config('app.url');
        if ($appUrl && $appUrl !== 'http://localhost' && $appUrl !== 'http://localhost:8000') {
            URL::forceRootUrl(rtrim($appUrl, '/'));
            if (str_starts_with($appUrl, 'https://')) {
                URL::forceScheme('https');
            }
        }

        Festival::expireOutdated();

        View::share('categories', Category::where('status', 1)->get());
        View::share('brands', Brand::where('status', 1)->get());
        View::share('festivals', Festival::active()->get());

        Paginator::useBootstrapFive();
        View::composer('*', function ($view) {
            $view->with('footer', Footer::first()); // Lấy 1 footer duy nhất

        });
    }
}

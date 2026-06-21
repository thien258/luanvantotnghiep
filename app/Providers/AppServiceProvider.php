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

/**
 * AppServiceProvider — Service Provider khởi động toàn bộ ứng dụng.
 *
 * Chạy trước mọi request, dùng để:
 *   - Fix URL cho ngrok/proxy (pagination link đúng domain)
 *   - Chia sẻ dữ liệu dùng chung cho mọi view (categories, brands, festivals)
 *   - Cấu hình pagination dùng Bootstrap 5
 *   - Tự động hết hạn Festival quá ngày
 */
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Đăng ký binding service container nếu cần (hiện tại không dùng)
    }

    public function boot(): void
    {
        // ── FIX URL CHO NGROK / REVERSE PROXY ────────────────────────
        // Khi dùng ngrok, request đến ngrok trước rồi forward về localhost:8000
        // Nếu không force, pagination link sẽ tạo ra URL localhost thay vì ngrok URL
        // Đọc thẳng từ $_ENV để tránh đọc config cache cũ
        $appUrl = $_ENV['APP_URL'] ?? config('app.url');

        if ($appUrl && $appUrl !== 'http://localhost' && $appUrl !== 'http://localhost:8000') {
            // Buộc tất cả URL (link, route, asset) dùng APP_URL thay vì request host
            URL::forceRootUrl(rtrim($appUrl, '/'));

            // Nếu APP_URL là https thì buộc scheme https (cho link redirect, payOS, etc.)
            if (str_starts_with($appUrl, 'https://')) {
                URL::forceScheme('https');
            }

            // Pagination mặc định dùng request()->url() → trả về localhost khi qua ngrok
            // Override bằng url()->current() để tôn trọng forceRootUrl ở trên
            Paginator::currentPathResolver(fn () => url()->current());
        }

        // ── TỰ ĐỘNG HẾT HẠN FESTIVAL ─────────────────────────────────
        // Gọi method static để set status=0 cho các festival đã qua end_date
        Festival::expireOutdated();

        // ── CHIA SẺ DỮ LIỆU CHO MỌI VIEW ────────────────────────────
        // Dùng View::share để tất cả blade đều có sẵn biến này
        View::share('categories', Category::where('status', 1)->get()); // navbar category
        View::share('brands',     Brand::where('status', 1)->get());    // navbar brand
        View::share('festivals',  Festival::active()->get());           // navbar festival

        // ── PAGINATION STYLE ──────────────────────────────────────────
        // Dùng Bootstrap 5 thay vì Tailwind (mặc định của Laravel 12)
        Paginator::useBootstrapFive();

        // ── FOOTER CHO MỌI VIEW ───────────────────────────────────────
        // View composer chạy mỗi khi bất kỳ view nào được render
        View::composer('*', function ($view) {
            $view->with('footer', Footer::first()); // lấy footer đầu tiên trong DB
        });
    }
}

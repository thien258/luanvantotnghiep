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
 * AppServiceProvider — Service Provider trung tâm, khởi động ứng dụng.
 *
 * Chạy trước mọi request HTTP, dùng để:
 *   1. Fix URL khi deploy qua ngrok / reverse proxy (pagination, redirect đúng domain)
 *   2. Tự động tắt festival đã hết hạn mỗi khi app boot
 *   3. Chia sẻ dữ liệu dùng chung (categories, brands, festivals) cho mọi blade view
 *   4. Cấu hình pagination dùng Bootstrap 5 thay vì Tailwind
 *   5. Inject footer vào mọi view qua View Composer
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Đăng ký binding vào service container.
     * Hiện tại chưa cần bind gì thêm.
     */
    public function register(): void
    {
        // Đăng ký binding service container nếu cần (hiện tại không dùng)
    }

    /**
     * Khởi động các service sau khi toàn bộ provider đã được đăng ký.
     */
    public function boot(): void
    {
        // ── 1. FIX URL CHO NGROK / REVERSE PROXY ─────────────────────────────
        // Khi dùng ngrok, request đi: browser → ngrok → localhost:8000
        // Nếu không forceRootUrl, Laravel sẽ tạo link theo host = localhost:8000
        // dẫn đến pagination link, redirect sai domain (không dùng được từ bên ngoài)
        $appUrl = $_ENV['APP_URL'] ?? config('app.url');

        if ($appUrl && $appUrl !== 'http://localhost' && $appUrl !== 'http://localhost:8000') {
            // Buộc tất cả URL được tạo (url(), route(), asset()) dùng APP_URL
            URL::forceRootUrl(rtrim($appUrl, '/'));

            // Nếu APP_URL là https → buộc scheme https
            // Cần thiết cho PayOS callback và các link absolute trong email
            if (str_starts_with($appUrl, 'https://')) {
                URL::forceScheme('https');
            }

            // Paginator mặc định dùng request()->url() → trả localhost khi qua ngrok
            // Override để dùng url()->current() vốn tôn trọng forceRootUrl ở trên
            Paginator::currentPathResolver(fn () => url()->current());
        }

        // ── 2. TỰ ĐỘNG HẾT HẠN FESTIVAL ──────────────────────────────────────
        // Gọi mỗi khi app boot: set status=0 cho festival có end_date < hôm nay
        // Tránh phải chạy cron riêng chỉ để tắt festival
        Festival::expireOutdated();

        // ── 3. CHIA SẺ DỮ LIỆU DÙNG CHUNG CHO MỌI VIEW ──────────────────────
        // View::share inject biến vào TẤT CẢ view → navbar luôn có đủ dữ liệu
        // Chỉ lấy bản ghi đang active (status = 1) để tránh hiển thị dữ liệu ẩn
        View::share('categories', Category::where('status', 1)->get()); // Menu danh mục
        View::share('brands',     Brand::where('status', 1)->get());    // Menu thương hiệu
        View::share('festivals',  Festival::active()->get());           // Banner festival

        // ── 4. PAGINATION STYLE ───────────────────────────────────────────────
        // Laravel 12 mặc định dùng Tailwind CSS cho pagination
        // Project này dùng Bootstrap 5 nên cần override
        Paginator::useBootstrapFive();

        // ── 5. FOOTER CHO MỌI VIEW ────────────────────────────────────────────
        // View Composer: chạy mỗi khi bất kỳ view nào được render ('*' = tất cả)
        // Dùng Composer thay vì View::share vì footer ít thay đổi, chỉ cần lazy load
        View::composer('*', function ($view) {
            // Lấy bản ghi footer đầu tiên (hệ thống chỉ có 1 footer)
            $view->with('footer', Footer::first());
        });
    }
}

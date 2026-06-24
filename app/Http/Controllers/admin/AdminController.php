<?php

namespace App\Http\Controllers\admin;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\WarehouseStockLog;

/**
 * AdminController — Trang tổng quan (Dashboard) của admin.
 *
 * Hiển thị các số liệu thống kê:
 *   - 4 thẻ tổng quan: doanh thu, đơn hàng, người dùng, sản phẩm
 *   - Biểu đồ doanh thu theo tháng (năm hiện tại)
 *   - Top 5 sản phẩm bán chạy
 *   - Sản phẩm bán chậm (tỷ lệ bán ≤ 5% so với nhập)
 *   - Sản phẩm sắp hết kho (tồn < 5)
 *
 * Chỉ user có role = 'admin' mới truy cập được (middleware trong constructor).
 */
class AdminController extends Controller
{
    public function __construct()
    {
        // Yêu cầu đăng nhập
        $this->middleware('auth');

        // Chỉ admin mới vào được — user thường bị chặn 403
        $this->middleware(function ($request, $next) {
            if (Auth::user()->role !== 'admin') {
                abort(403, 'Bạn không có quyền truy cập trang này.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        // ── 1. TỔNG QUAN ──────────────────────────────────────────────

        // Doanh thu: tổng total_price của các đơn đã giao thành công (status = 4)
        $totalRevenue = DB::table('orders')->where('status', 4)->sum('total_price');

        // Tổng đơn hàng — bỏ qua đơn chờ thanh toán PayOS (status = 0)
        $totalOrders = DB::table('orders')->where('status', '!=', 0)->count();

        // Tổng người dùng — không tính tài khoản admin
        $totalUsers = DB::table('users')->where('role', '!=', 'admin')->count();

        // Tổng sản phẩm đang bán (status = 1)
        $totalProducts = Product::where('status', 1)->count();

        // ── 2. DOANH THU THEO THÁNG (năm hiện tại) ────────────────────

        $year = now()->year;

        // Gom doanh thu theo tháng từ các đơn hoàn tất (status = 4)
        $revenueRows = DB::table('orders')
            ->selectRaw('MONTH(updated_at) as month, SUM(total_price) as revenue')
            ->where('status', 4)
            ->whereYear('updated_at', $year)
            ->groupBy(DB::raw('MONTH(updated_at)'))
            ->get()
            ->keyBy('month'); // index bằng số tháng để tra nhanh (1-12)

        // Đảm bảo đủ 12 phần tử, tháng chưa có doanh thu = 0
        // Đơn vị: triệu VNĐ (chia 1_000_000) để biểu đồ gọn
        $monthlyRevenue = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyRevenue[] = isset($revenueRows[$m])
                ? round($revenueRows[$m]->revenue / 1000000, 1)
                : 0;
        }

        // ── 3. TOP 5 SẢN PHẨM BÁN CHẠY ───────────────────────────────

        // Chỉ tính từ đơn hoàn tất (status = 4) — phản ánh doanh số thực
        $topSelling = DB::table('order_details')
            ->join('orders', 'order_details.idOrder', '=', 'orders.id')
            ->join('products', 'order_details.idProduct', '=', 'products.id')
            ->select(
                'products.id',
                'products.title',
                DB::raw('SUM(order_details.quantity) as total_sold'),
                DB::raw('SUM(order_details.quantity * order_details.price) as total_revenue')
            )
            ->where('orders.status', 4)
            ->groupBy('products.id', 'products.title')
            ->orderBy('total_sold', 'desc')
            ->take(5)
            ->get();

        // ── 4. SẢN PHẨM BÁN CHẬM ─────────────────────────────────────

        // Định nghĩa "bán chậm": Sản phẩm nhập kho >= 7 ngày mà tỷ lệ bán < 30%
        // TODO: Đổi thành 30 ngày khi có đủ dữ liệu
        $slowProducts = [];
        $daysThreshold = 7; // TODO: Đổi thành 30 sau
        $daysAgo = now()->subDays($daysThreshold);

        // Lấy các log nhập kho từ N ngày trước trở về trước, nhóm theo product_id
        $oldImportLogs = WarehouseStockLog::where('type', 'import')
            ->where('created_at', '<=', $daysAgo)
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy('product_id');

        foreach ($oldImportLogs as $productId => $logs) {
            $product = Product::find($productId);
            if (!$product) continue;

            // Tính tổng số lượng nhập từ các lần nhập >= N ngày
            $totalImported = $logs->sum('quantity');
            
            // Lấy log nhập đầu tiên để biết ngày nhập
            $firstImportDate = $logs->first()->created_at;
            $daysInStock = now()->diffInDays($firstImportDate);

            // Tổng số đã bán từ đơn hoàn tất (status = 4)
            $totalSold = DB::table('order_details')
                ->join('orders', 'order_details.idOrder', '=', 'orders.id')
                ->where('order_details.idProduct', $product->id)
                ->where('orders.status', 4)
                ->sum('order_details.quantity');

            // Tính tỉ lệ bán
            $saleRate = $totalImported > 0 ? ($totalSold / $totalImported) * 100 : 0;

            // Cảnh báo nếu tỉ lệ < 30%
            if ($saleRate < 30) {
                $product->total_import = $totalImported;
                $product->total_sold = $totalSold;
                $product->sale_rate = round($saleRate, 1);
                $product->days_in_stock = $daysInStock;
                $product->first_import_date = $firstImportDate;
                $slowProducts[] = $product;
            }
        }

        // Giới hạn 5 sản phẩm để tránh bảng quá dài
        $slowProducts = \array_slice($slowProducts, 0, 5);


        // ── 5. SẢN PHẨM SẮP HẾT KHO ──────────────────────────────────

        // Ngưỡng < 5 — đồng nhất với màu cảnh báo trong product-list.blade.php
        $lowStockProducts = Product::where('quantity', '>', 0)
            ->where('quantity', '<', 5)
            ->orderBy('quantity', 'asc') // sắp xếp theo tồn kho tăng dần (nguy hiểm nhất lên đầu)
            ->take(5)
            ->get();

        return view('admin.home.home-list', compact(
            'totalRevenue',
            'totalOrders',
            'totalUsers',
            'totalProducts',
            'monthlyRevenue',
            'topSelling',
            'slowProducts',
            'lowStockProducts'
        ));
    }
}

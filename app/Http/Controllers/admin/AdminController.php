<?php

namespace App\Http\Controllers\admin;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\WarehouseStockLog;
use App\Http\Controllers\admin\SaleSpeedHelper;

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
    // public function __construct()
    // {
    //     // Yêu cầu đăng nhập
    //     $this->middleware('auth');

    //     // Chỉ admin mới vào được — user thường bị chặn 403
    //     $this->middleware(function ($request, $next) {
    //         if (Auth::user()->role !== 'admin') {
    //             abort(403, 'Bạn không có quyền truy cập trang này.');
    //         }
    //         return $next($request);
    //     });
    // }

    public function index()
    {
        $role = Auth::user()->role;

        // Director: chỉ được xem trang doanh thu riêng
        if ($role === 'director') {
            return $this->directorDashboard();
        }

        // Các role không phải admin không được vào dashboard tổng quan
        if ($role !== 'admin') {
            if ($role === 'warehouse') {
                return redirect()->route('admin.orders.index');
            }
            if ($role === 'manufacturer') {
                return redirect()->route('admin.supplier-offers.index');
            }
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        // ── 1. TỔNG QUAN (admin — KHÔNG hiển thị doanh thu) ───────────

        // Doanh thu bị ẩn với admin — set null để view biết không render
        $totalRevenue = null;

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

        // Chỉ tính từ đơn hoàn tất (status = 4) trong 30 ngày gần nhất — nhất quán với bán chậm
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
            ->where('orders.created_at', '>=', now()->subDays(30))
            ->groupBy('products.id', 'products.title')
            ->orderBy('total_sold', 'desc')
            ->take(5)
            ->get();

        // ── 4. SẢN PHẨM BÁN CHẬM ─────────────────────────────────────
        // Dùng SaleSpeedHelper — logic mới: tỷ lệ bán/nhập sau 30 ngày kể từ lần nhập gần nhất
        $allActiveProducts = Product::with('festivals')
            ->where('status', 1)
            ->orWhere('quantity', '>', 0)
            ->get();

        $slowItems = array_values(SaleSpeedHelper::getSlowProducts($allActiveProducts));

        // Chuyển về format cũ để view dùng được
        $slowProducts = array_slice(array_map(function ($item) {
            $p = $item->product;
            $p->sold_30 = $item->sold_after;
            $p->stock   = $item->imported_qty > 0
                ? $item->imported_qty        // SP đã nhập kho → dùng qty nhập
                : (int) $item->product->quantity; // fallback → dùng tồn kho hiện tại
            return $p;
        }, $slowItems), 0, 5);

        // ── 6. SẮP HẾT HẠN (HSD) ─────────────────────────────────────

        // Lấy top 5 lô có HSD gần nhất chưa hết hạn, trong vòng 365 ngày tới
        // Logic đơn giản: không FIFO, không trừ số đã bán — chỉ cần lô còn HSD thì hiện
        $todayStr  = now()->toDateString();
        $today365  = now()->addDays(365)->toDateString();

        $expiringBatches = WarehouseStockLog::where('type', 'import')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', $todayStr)
            ->whereDate('expiry_date', '<=', $today365)
            ->selectRaw('product_id, expiry_date, SUM(quantity) as total_import')
            ->groupBy('product_id', 'expiry_date')
            ->orderBy('expiry_date', 'asc')
            ->get()
            ->map(function ($row) {
                $product = Product::find($row->product_id);
                if (!$product) return null;

                $expiryStr = $row->expiry_date instanceof \Carbon\Carbon
                    ? $row->expiry_date->toDateString()
                    : (string) $row->expiry_date;

                return (object) [
                    'product'     => $product,
                    'expiry_date' => $expiryStr,
                    'qty_left'    => (int) $row->total_import,
                    'days_left'   => (int) now()->diffInDays(\Carbon\Carbon::parse($expiryStr), false),
                ];
            })
            ->filter()
            ->sortBy('days_left')
            ->take(5)
            ->values()
            ->all();


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
            'lowStockProducts',
            'expiringBatches'
        ));
    }

    /**
     * Dashboard dành riêng cho Giám đốc (director).
     * Hiển thị doanh thu và chi phí nhập hàng theo từng tháng.
     */
    private function directorDashboard()
    {
        $year = now()->year;

        // ── Doanh thu theo tháng (đơn hoàn tất status = 4) ─────────────
        $revenueRows = DB::table('orders')
            ->selectRaw('MONTH(updated_at) as month, SUM(total_price) as revenue')
            ->where('status', 4)
            ->whereYear('updated_at', $year)
            ->groupBy(DB::raw('MONTH(updated_at)'))
            ->get()
            ->keyBy('month');

        // ── Chi phí nhập hàng theo tháng (đơn received) ────────────────
        // Lấy từ purchase_orders.total_amount, tháng tính theo updated_at (ngày nhận hàng)
        $importCostRows = DB::table('purchase_orders')
            ->selectRaw('MONTH(updated_at) as month, SUM(total_amount) as cost')
            ->where('status', 'received')
            ->whereYear('updated_at', $year)
            ->groupBy(DB::raw('MONTH(updated_at)'))
            ->get()
            ->keyBy('month');

        // Build mảng 12 tháng cho cả 2 chỉ số (đơn vị: triệu VNĐ)
        $monthlyRevenue    = [];
        $monthlyImportCost = [];
        $monthlyProfit     = [];

        for ($m = 1; $m <= 12; $m++) {
            $rev  = isset($revenueRows[$m])    ? (float) $revenueRows[$m]->revenue    : 0;
            $cost = isset($importCostRows[$m]) ? (float) $importCostRows[$m]->cost    : 0;

            $monthlyRevenue[]    = round($rev  / 1_000_000, 1);
            $monthlyImportCost[] = round($cost / 1_000_000, 1);
            $monthlyProfit[]     = round(($rev - $cost) / 1_000_000, 1);
        }

        // ── Tổng cả năm ─────────────────────────────────────────────────
        $totalRevenue    = DB::table('orders')->where('status', 4)->whereYear('updated_at', $year)->sum('total_price');
        $totalImportCost = DB::table('purchase_orders')->where('status', 'received')->whereYear('updated_at', $year)->sum('total_amount');
        $totalProfit     = $totalRevenue - $totalImportCost;

        // Tổng đơn hàng (không tính đơn chờ PayOS)
        $totalOrders = DB::table('orders')->where('status', '!=', 0)->count();

        return view('admin.home.director-dashboard', compact(
            'totalRevenue',
            'totalImportCost',
            'totalProfit',
            'totalOrders',
            'monthlyRevenue',
            'monthlyImportCost',
            'monthlyProfit',
            'year'
        ));
    }
}

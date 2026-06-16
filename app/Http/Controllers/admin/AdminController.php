<?php

namespace App\Http\Controllers\admin;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Models\Product;
use App\Models\WarehouseStockLog;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
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

        // Tổng đơn hàng (không tính đơn chờ thanh toán PayOS status=0)
        $totalOrders = DB::table('orders')->where('status', '!=', 0)->count();

        // Tổng người dùng (không tính admin)
        $totalUsers = DB::table('users')->where('role', '!=', 'admin')->count();

        // Tổng sản phẩm đang bán
        $totalProducts = Product::where('status', 1)->count();

        // ── 2. DOANH THU THEO THÁNG (năm hiện tại) ────────────────────
        $year = now()->year;

        // Query doanh thu theo tháng từ đơn hoàn tất (status=4)
        $revenueRows = DB::table('orders')
            ->selectRaw('MONTH(updated_at) as month, SUM(total_price) as revenue')
            ->where('status', 4)
            ->whereYear('updated_at', $year)
            ->groupBy(DB::raw('MONTH(updated_at)'))
            ->get()
            ->keyBy('month'); // key bằng số tháng để tra nhanh

        // Đảm bảo đủ 12 tháng, tháng chưa có doanh thu = 0
        // Đơn vị: triệu VNĐ (chia 1.000.000 để biểu đồ gọn)
        $monthlyRevenue = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyRevenue[] = isset($revenueRows[$m])
                ? round($revenueRows[$m]->revenue / 1000000, 1)
                : 0;
        }

        // ── 3. TOP SẢN PHẨM BÁN CHẠY ─────────────────────────────────
        // Chỉ tính từ đơn đã hoàn tất (status=4) để phản ánh đúng doanh số thực
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
        // Bán chậm = tổng đã bán / tổng đã nhập kho <= 5%
        $slowProducts = [];
        $products = Product::where('quantity', '>', 0)->get();

        foreach ($products as $product) {
            $totalImport = WarehouseStockLog::where('product_id', $product->id)
                ->where('type', 'import')
                ->sum('quantity');

            $totalSold = DB::table('order_details')
                ->join('orders', 'order_details.idOrder', '=', 'orders.id')
                ->where('order_details.idProduct', $product->id)
                ->where('orders.status', 4)
                ->sum('order_details.quantity');

            $saleRate = $totalImport > 0 ? ($totalSold / $totalImport) * 100 : 0;

            if ($saleRate <= 5) {
                $product->total_sold = $totalSold;
                $slowProducts[] = $product;
            }
        }
        $slowProducts = \array_slice($slowProducts, 0, 5);

        // ── 5. SẢN PHẨM SẮP HẾT KHO (đồng nhất với product-list: < 5) ─
        $lowStockProducts = Product::where('quantity', '>', 0)
            ->where('quantity', '<', 5)
            ->orderBy('quantity', 'asc')
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

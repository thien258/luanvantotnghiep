<?php

namespace App\Http\Controllers\admin;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * SaleSpeedHelper — Phân loại bán nhanh / bán chậm.
 *
 * Cách hoạt động (đơn giản):
 *   Với mỗi sản phẩm:
 *     1. Tìm ngày nhập kho gần nhất + số lượng nhập ngày đó
 *     2. Đếm số bán được KỂ TỪ ngày nhập đó đến hôm nay
 *     3. Tính tỷ lệ: đã bán / đã nhập * 100
 *
 *   Kết quả:
 *     - Chưa đủ 30 ngày kể từ nhập → "watching" (chờ thêm)
 *     - Tỷ lệ >= 60%               → "fast"     🔥 Bán nhanh
 *     - Tỷ lệ <= 30%               → "slow"     🐢 Bán chậm
 *     - Còn lại (30-60%)           → "normal"   😐 Bình thường
 *     - Chưa nhập kho lần nào      → sold_30=0 thì "slow", còn lại "normal"
 */
class SaleSpeedHelper
{
    // Ngưỡng phân loại
    const FAST_RATIO  = 60; // bán >= 60% lượng nhập → nhanh
    const SLOW_RATIO  = 30; // bán <= 30% lượng nhập → chậm
    const WINDOW_DAYS = 1;  // TEST: chờ 1 ngày (production đổi lại thành 30)

    /**
     * Tính trạng thái bán cho từng sản phẩm.
     *
     * @param  \Illuminate\Database\Eloquent\Collection $products
     * @return array  mảng object, mỗi phần tử gồm:
     *   ->product        : model Product
     *   ->status         : 'fast' | 'slow' | 'normal' | 'watching'
     *   ->ratio          : tỷ lệ % (null nếu chưa có dữ liệu)
     *   ->sold_after     : số bán được sau ngày nhập
     *   ->imported_qty   : số nhập trong lần nhập gần nhất
     *   ->last_import_at : Carbon - ngày nhập gần nhất (null nếu chưa nhập)
     *   ->days_since     : số ngày đã qua kể từ nhập
     *   ->days_left      : số ngày còn chờ (chỉ có khi status = 'watching')
     *   ->sale_rate      : giống ratio (để view cũ dùng)
     *   ->total_sold     : giống sold_after (để view cũ dùng)
     *   ->total_import   : giống imported_qty (để view cũ dùng)
     *   ->id, ->title, ->festivals : copy từ product
     */
    public static function classify(\Illuminate\Database\Eloquent\Collection $products): array
    {
        if ($products->isEmpty()) return [];

        $ids = $products->pluck('id')->all();
        $now = Carbon::now();

        // ── Bước 1: Lấy ngày nhập gần nhất của từng SP ────────────────────
        // Chỉ 1 query cho tất cả SP
        $lastImportRows = DB::table('warehouse_stock_logs')
            ->whereIn('product_id', $ids)
            ->where('type', 'import')
            ->select('product_id', DB::raw('MAX(created_at) as last_at'))
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id'); // index theo product_id để tra nhanh

        // ── Bước 2: Với mỗi SP, tính số nhập + số bán ─────────────────────
        // Vẫn phải loop vì mỗi SP có ngày nhập khác nhau
        // Nhưng mỗi vòng chỉ chạy 2 query nhỏ (đã có index)
        $result = [];

        foreach ($products as $product) {
            $id    = $product->id;
            $stock = (int) $product->quantity;

            // Bỏ qua SP hết hàng
            if ($stock <= 0) continue;

            // ── Trường hợp chưa bao giờ nhập kho ──────────────────────────
            if (!isset($lastImportRows[$id])) {
                // Fallback: đếm bán 30 ngày gần đây
                $sold30 = (int) DB::table('order_details')
                    ->join('orders', 'order_details.idOrder', '=', 'orders.id')
                    ->where('order_details.idProduct', $id)
                    ->where('orders.status', 4)
                    ->where('orders.created_at', '>=', $now->copy()->subDays(30))
                    ->sum('order_details.quantity');

                $result[] = self::build($product, $sold30 === 0 ? 'slow' : 'normal',
                    null, $sold30, 0, null, null, null);
                continue;
            }

            // ── Có lần nhập kho ────────────────────────────────────────────
            $lastImportAt = Carbon::parse($lastImportRows[$id]->last_at);
            $daysSince    = (int) $lastImportAt->diffInDays($now);

            // Tổng nhập trong ngày nhập gần nhất (có thể nhập nhiều lần cùng ngày)
            $importedQty = (int) DB::table('warehouse_stock_logs')
                ->where('product_id', $id)
                ->where('type', 'import')
                ->whereDate('created_at', $lastImportAt->toDateString())
                ->sum('quantity');

            // Tổng bán được KỂ TỪ ngày nhập đó
            $soldAfter = (int) DB::table('order_details')
                ->join('orders', 'order_details.idOrder', '=', 'orders.id')
                ->where('order_details.idProduct', $id)
                ->where('orders.status', 4)
                ->where('orders.created_at', '>=', $lastImportAt)
                ->sum('order_details.quantity');

            // Chưa đủ 30 ngày → đang theo dõi, chưa kết luận
            if ($daysSince < self::WINDOW_DAYS) {
                $ratio = $importedQty > 0
                    ? round($soldAfter / $importedQty * 100, 1)
                    : 0;
                $result[] = self::build($product, 'watching',
                    $ratio, $soldAfter, $importedQty,
                    $lastImportAt, $daysSince, self::WINDOW_DAYS - $daysSince);
                continue;
            }

            // Đủ 30 ngày → tính tỷ lệ và phân loại
            if ($importedQty <= 0) continue; // dữ liệu lỗi, bỏ qua

            $ratio  = round($soldAfter / $importedQty * 100, 1);
            $status = 'normal';
            if ($ratio >= self::FAST_RATIO) $status = 'fast';
            elseif ($ratio <= self::SLOW_RATIO) $status = 'slow';

            $result[] = self::build($product, $status,
                $ratio, $soldAfter, $importedQty,
                $lastImportAt, $daysSince, null);
        }

        return $result;
    }

    // ── Các hàm tiện ích ───────────────────────────────────────────────────

    /** Chỉ lấy SP bán chậm */
    public static function getSlowProducts(\Illuminate\Database\Eloquent\Collection $products): array
    {
        return array_values(array_filter(
            self::classify($products),
            fn($item) => $item->status === 'slow'
        ));
    }

    /** Map [product_id => ['status', 'ratio', 'days_left']] dùng cho badge */
    public static function getStatusMap(\Illuminate\Database\Eloquent\Collection $products): array
    {
        $map = [];
        foreach (self::classify($products) as $item) {
            $map[$item->product->id] = [
                'status'    => $item->status,
                'ratio'     => $item->ratio,
                'days_left' => $item->days_left,
            ];
        }
        return $map;
    }

    // ── Private: tạo object kết quả ────────────────────────────────────────

    private static function build(
        $product,
        string  $status,
        ?float  $ratio,
        int     $soldAfter,
        int     $importedQty,
        ?Carbon $lastImportAt,
        ?int    $daysSince,
        ?int    $daysLeft
    ): object {
        return (object) [
            'product'        => $product,
            'status'         => $status,
            'ratio'          => $ratio,
            'sold_after'     => $soldAfter,
            'imported_qty'   => $importedQty,
            'last_import_at' => $lastImportAt,
            'days_since'     => $daysSince,
            'days_left'      => $daysLeft,
            // aliases cho view cũ dùng được luôn
            'sale_rate'      => $ratio ?? 0,
            'total_sold'     => $soldAfter,
            'total_import'   => $importedQty ?: $product->quantity,
            'id'             => $product->id,
            'title'          => $product->title,
            'festivals'      => $product->festivals ?? collect(),
        ];
    }
}

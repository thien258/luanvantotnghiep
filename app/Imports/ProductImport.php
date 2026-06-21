<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Concentration;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * ProductImport — Import sản phẩm từ file Excel/CSV qua thư viện maatwebsite/excel.
 *
 * Format file chuẩn (dòng đầu là header):
 *   title | image | decription | price | quantity | volume | category | brand | concentration
 *
 * Logic:
 *   - Nếu SP đã tồn tại (tìm theo title) → cộng thêm quantity + cập nhật giá/mô tả
 *   - Nếu SP chưa tồn tại → tạo mới
 *   - category/brand/concentration tìm theo tên (string) thay vì ID → thân thiện hơn khi nhập
 *   - Nếu không tìm được category/brand/concentration → bỏ qua dòng đó + ghi log
 *
 * Implements:
 *   - ToModel: mỗi dòng Excel được xử lý bởi method model()
 *   - WithHeadingRow: bỏ qua dòng đầu tiên (header), dùng tên cột làm key
 */
class ProductImport implements ToModel, WithHeadingRow
{
    /**
     * Xử lý từng dòng trong file Excel/CSV.
     * Trả về Product mới hoặc null (nếu bỏ qua).
     */
    public function model(array $row)
    {
        // Lấy tên SP, bỏ khoảng trắng thừa
        $title = trim($row['title'] ?? '');

        // Dòng trống → bỏ qua
        if (empty($title)) return null;

        // ── Tìm ID theo tên — ưu tiên tên, fallback về ID nếu có ──────
        // Cách này thân thiện hơn: admin chỉ cần ghi "Nam" thay vì "1"
        $categoryId = Category::where('name', $row['category'] ?? '')
            ->value('id') ?? ($row['idcategory'] ?? null);

        $brandId = Brand::where('name', $row['brand'] ?? '')
            ->value('id') ?? ($row['idbrand'] ?? null);

        $concentrationId = Concentration::where('concentration', $row['concentration'] ?? '')
            ->value('id') ?? ($row['idconcentration'] ?? null);

        // Nếu thiếu bất kỳ FK nào → bỏ qua dòng, ghi log để debug
        if (!$categoryId || !$brandId || !$concentrationId) {
            \Illuminate\Support\Facades\Log::warning(
                "ProductImport: bỏ qua dòng '{$title}' — không tìm thấy category/brand/concentration"
            );
            return null;
        }

        // ── SP đã tồn tại → cập nhật, không tạo mới ──────────────────
        $product = Product::where('title', $title)->first();

        if ($product) {
            // Cộng thêm số lượng nhập
            $product->quantity += (int)($row['quantity'] ?? 0);

            // Cập nhật giá nếu file có giá hợp lệ
            if (!empty($row['price']) && $row['price'] > 0) {
                $product->price = $row['price'];
            }

            // Cập nhật mô tả nếu file có
            if (!empty($row['decription'])) {
                $product->decription = $row['decription'];
            }

            $product->save();
            return null; // không tạo record mới, trả null để maatwebsite bỏ qua
        }

        // ── SP chưa tồn tại → tạo mới ────────────────────────────────
        return new Product([
            'title'           => $title,
            'image'           => $row['image']       ?? '',
            'decription'      => $row['decription']  ?? '',
            'price'           => $row['price']       ?? 0,
            'quantity'        => $row['quantity']    ?? 0,
            'volume'          => $row['volume']      ?? '100ml',
            'status'          => $row['status']      ?? 1,   // mặc định: đang bán
            'idConcentration' => $concentrationId,
            'idBrand'         => $brandId,
            'idCategory'      => $categoryId,
        ]);
    }
}

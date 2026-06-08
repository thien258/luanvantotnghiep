<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Concentration;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $title = trim($row['title'] ?? '');
        if (empty($title)) return null;

        // Tìm ID theo tên — thân thiện hơn khi nhập Excel
        $categoryId = Category::where('name', $row['category'] ?? '')
            ->value('id') ?? ($row['idcategory'] ?? null);

        $brandId = Brand::where('name', $row['brand'] ?? '')
            ->value('id') ?? ($row['idbrand'] ?? null);

        $concentrationId = Concentration::where('concentration', $row['concentration'] ?? '')
            ->value('id') ?? ($row['idconcentration'] ?? null);

        // Nếu không tìm được category thì bỏ qua dòng này
        if (!$categoryId || !$brandId || !$concentrationId) {
            \Illuminate\Support\Facades\Log::warning("ProductImport: bỏ qua dòng '{$title}' — không tìm thấy category/brand/concentration");
            return null;
        }

        $product = Product::where('title', $title)->first();

        if ($product) {
            $product->quantity += (int)($row['quantity'] ?? 0);
            if (!empty($row['price']) && $row['price'] > 0) {
                $product->price = $row['price'];
            }
            if (!empty($row['decription'])) {
                $product->decription = $row['decription'];
            }   
            $product->save();
            return null;
        }

        return new Product([
            'title'           => $title,
            'image'           => $row['image'] ?? '',
            'decription'      => $row['decription'] ?? '',
            'price'           => $row['price'] ?? 0,
            'quantity'        => $row['quantity'] ?? 0,
            'volume'          => $row['volume'] ?? '100ml',
            'status'          => $row['status'] ?? 1,
            'idConcentration' => $concentrationId,
            'idBrand'         => $brandId,
            'idCategory'      => $categoryId,
        ]);
    }
}

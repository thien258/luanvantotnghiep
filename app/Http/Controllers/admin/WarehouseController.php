<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Concentration;
use App\Models\WarehouseReceipt;
use App\Models\WarehouseStockLog;
use App\Models\WarehouseImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

/**
 * WarehouseController — Quản lý kho hàng và nhập kho qua file.
 *
 * Tách ra từ ProductController để file gọn hơn.
 *
 * Gồm 2 luồng chính:
 *
 * LUỒNG 1 — Kho tổng quan (3 tab):
 *   index()  → trang warehouse.blade.php với 3 tab:
 *     Tab 1: Sản phẩm bán chậm (tỷ lệ bán < 20% so với nhập)
 *     Tab 2: Lịch sử biến động tồn kho (warehouse_stock_logs)
 *     Tab 3: Lịch sử phiếu nhập (warehouse_receipts)
 *   store()  → nhập kho thủ công qua form
 *
 * LUỒNG 2 — Upload file → duyệt:
 *   importList()    → danh sách file đã upload, chờ duyệt
 *   importUpload()  → nhân viên kho upload file CSV/Excel
 *   importShow()    → admin xem preview sản phẩm trong file
 *   importApprove() → admin duyệt → cộng tồn kho + tạo receipt + log
 *   importReject()  → admin từ chối file
 */
class WarehouseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // =========================================================================
    // LUỒNG 1: KHO TỔNG QUAN
    // =========================================================================

    /**
     * Trang tổng quan kho — load dữ liệu cho 3 tab.
     */
    public function index()
    {
        [$receipt, $stockLogs, $slowProducts] = $this->getWarehouseData();
        return view('admin.product.warehouse', compact('receipt', 'stockLogs', 'slowProducts'));
    }

    /**
     * Nhập kho thủ công qua form trực tiếp.
     * Nếu SP đã tồn tại → cộng thêm, nếu chưa → tạo mới SP.
     */
    public function store(Request $request)
    {
        $productNames = $request->input('product_name', []);
        $quantities   = $request->input('quantity', []);
        $prices       = $request->input('price', []);
        $notes        = $request->input('note', []);

        // Lọc chỉ giữ các dòng có tên SP và số lượng > 0
        $validItems = [];
        foreach ($productNames as $index => $name) {
            if (!empty($name) && !empty($quantities[$index]) && (int)$quantities[$index] > 0) {
                $validItems[] = [
                    'product_name' => trim($name),
                    'quantity'     => (int)$quantities[$index],
                    'price'        => isset($prices[$index]) ? (int)$prices[$index] : 0,
                    'note'         => trim($notes[$index] ?? ''),
                ];
            }
        }

        if (empty($validItems)) {
            return redirect()->back()->with('error', 'Không tìm thấy dữ liệu hợp lệ để nhập kho.');
        }

        // Tạo phiếu nhập kho — mã tự sinh theo timestamp
        $receiptCode = 'PN' . Carbon::now()->format('ymdHis');
        $receipt = WarehouseReceipt::create([
            'receipt_code' => $receiptCode,
            'supplier'     => $request->input('supplier'),
            'note'         => $request->input('note'),
            'total_items'  => count($validItems),
        ]);

        $successCount = 0;
        foreach ($validItems as $item) {
            $product = Product::where('title', $item['product_name'])->first();

            if ($product) {
                // SP đã có → cộng thêm số lượng
                $product->increment('quantity', $item['quantity']);
                $reasonText = "Nhập kho bổ sung từ phiếu {$receiptCode}";
            } else {
                // SP chưa có → tạo mới với thông tin mặc định
                $product = Product::create([
                    'title'           => $item['product_name'],
                    'image'           => '',
                    'decription'      => "Tự động tạo từ phiếu nhập kho {$receiptCode}",
                    'price'           => $item['price'] > 0 ? $item['price'] * 1.2 : 450000,
                    'quantity'        => $item['quantity'],
                    'volume'          => '100ml',
                    'status'          => 1,
                    'idConcentration' => Concentration::value('id') ?? 1,
                    'idBrand'         => Brand::value('id') ?? 1,
                    'idCategory'      => Category::value('id') ?? 1,
                ]);
                $reasonText = "Tạo mới sản phẩm từ phiếu {$receiptCode}";
            }

            // Ghi log biến động tồn kho
            WarehouseStockLog::create([
                'receipt_id'  => $receipt->id,
                'product_id'  => $product->id,
                'type'        => 'import',
                'quantity'    => $item['quantity'],
                'stock_after' => $product->quantity,
                'reason'      => $reasonText . ($item['note'] ? ' / ' . $item['note'] : ''),
            ]);
            $successCount++;
        }

        return redirect()->route('admin.product.warehouse.index')
            ->with('success', "Nhập kho hoàn tất! Đã cập nhật {$successCount} sản phẩm.");
    }

    // =========================================================================
    // LUỒNG 2: UPLOAD FILE → DUYỆT
    // =========================================================================

    /**
     * Danh sách tất cả file đã upload, sắp theo mới nhất.
     */
    public function importList()
    {
        $imports = WarehouseImport::with('uploader')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.product.import-list', compact('imports'));
    }

    /**
     * Nhân viên kho upload file CSV/Excel.
     * File được lưu vào storage/app/private/warehouse_imports/
     * Tạo bản ghi WarehouseImport với status='pending' chờ admin duyệt.
     */
    public function importUpload(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:csv,xlsx,xls|max:5120',
            'supplier'   => 'nullable|string|max:255',
            'note'       => 'nullable|string|max:1000',
        ], [
            'excel_file.required' => 'Vui lòng chọn file để upload.',
            'excel_file.file'     => 'File không hợp lệ.',
            'excel_file.mimes'    => 'File phải có định dạng CSV, XLSX hoặc XLS.',
            'excel_file.max'      => 'File không được vượt quá 5MB.',
        ]);

        $file = $request->file('excel_file');
        $path = $file->store('warehouse_imports', 'local'); // lưu private

        WarehouseImport::create([
            'file_path'     => $path,
            'original_name' => $file->getClientOriginalName(),
            'supplier'      => $request->input('supplier'),
            'note'          => $request->input('note'),
            'uploaded_by'   => Auth::id(),
            'status'        => 'pending', // chờ admin xem
        ]);

        return redirect()->route('admin.warehouse.imports')
            ->with('success', 'Đã gửi file lên! Chờ admin duyệt.');
    }

    /**
     * Admin xem chi tiết file — đọc file và hiện preview bảng sản phẩm.
     * CSV: đọc trực tiếp bằng fgetcsv, auto-detect delimiter (,  hoặc ;)
     * Excel: dùng RawArrayImport để lấy mảng thô
     */
    public function importShow(WarehouseImport $import)
    {
        $productsPreview = $this->parseImportFile($import);
        $approvedItems     = $import->status === 'approved' ? ($import->approved_items ?? []) : [];
        $approvedByTitle   = collect($approvedItems)->keyBy(
            fn ($item) => mb_strtolower(trim($item['title'] ?? ''))
        );

        return view('admin.product.import-show', compact(
            'import',
            'productsPreview',
            'approvedItems',
            'approvedByTitle',
        ));
    }

    /**
     * Admin duyệt file → nhập kho.
     * Chỉ nhập các SP được tick chọn.
     * SP đã có → cộng qty + cập nhật giá/ảnh/mô tả nếu có.
     * SP chưa có → tạo mới từ thông tin trong file.
     */
    public function importApprove(Request $request, WarehouseImport $import)
    {
        if ($import->status !== 'pending') {
            return redirect()->route('admin.warehouse.imports')
                ->with('error', 'File này đã được xử lý rồi.');
        }

        $selected = $request->input('selected_products', []);
        if (empty($selected)) {
            return back()->with('error', 'Bạn chưa chọn sản phẩm nào.');
        }

        // Lấy tất cả dữ liệu từ form (indexed theo số thứ tự dòng)
        $titles     = $request->input('product_name', []);
        $qtys       = $request->input('quantity', []);
        $prices     = $request->input('price', []);      // giá bán sau khi áp % markup
        $images     = $request->input('image', []);
        $descs      = $request->input('decription', []);
        $volumes    = $request->input('volume', []);
        $cats       = $request->input('category', []);
        $brands     = $request->input('brand', []);
        $concs      = $request->input('concentration', []);
        $unitPrices = $request->input('unit_price', []); // giá nhập từ NSX
        $slOrders   = $request->input('sl_order', []);   // số lượng đã order

        // Tạo phiếu nhập kho
        $code    = 'PN' . now()->format('ymdHis');
        $receipt = WarehouseReceipt::create([
            'receipt_code' => $code,
            'supplier'     => $import->supplier,
            'note'         => $import->note,
            'total_items'  => count($selected),
        ]);

        $count = 0;
        $approvedItems = [];

        foreach ($selected as $i) {
            $title = $titles[$i] ?? null;
            $qty   = (int)($qtys[$i] ?? 0);

            if (!$title || $qty <= 0) continue;

            $price = (float)($prices[$i] ?? 0);
            $image = $images[$i] ?? '';
            $desc  = $descs[$i] ?? '';

            $product = Product::where('title', $title)->first();

            if ($product) {
                // SP đã có → cộng tồn kho + cập nhật thông tin nếu admin đã chỉnh
                $product->increment('quantity', $qty);
                if ($price > 0) $product->update(['price' => $price]);
                if ($image)     $product->update(['image' => $image]);
                if ($desc)      $product->update(['decription' => $desc]);
                $reason = "Nhập kho từ phiếu {$code}";
            } else {
                // SP chưa có → tìm hoặc tạo mới category/brand/concentration theo tên
                $catName   = trim($cats[$i] ?? '');
                $brandName = trim($brands[$i] ?? '');
                $concName  = trim($concs[$i] ?? '');

                // Category: tìm hoặc lấy mặc định
                $catId = $catName 
                    ? (Category::whereRaw('LOWER(name) = ?', [strtolower($catName)])->value('id') ?? Category::value('id') ?? 1)
                    : (Category::value('id') ?? 1);

                // Brand: tìm hoặc TẠO MỚI nếu chưa có
                if ($brandName) {
                    $brand = Brand::whereRaw('LOWER(name) = ?', [strtolower($brandName)])->first();
                    if (!$brand) {
                        $brand = Brand::create(['name' => $brandName]);
                    }
                    $brandId = $brand->id;
                } else {
                    $brandId = Brand::value('id') ?? 1;
                }

                // Concentration: tìm hoặc lấy mặc định
                $concId = $concName
                    ? (Concentration::whereRaw('LOWER(concentration) = ?', [strtolower($concName)])->value('id') ?? Concentration::value('id') ?? 1)
                    : (Concentration::value('id') ?? 1);

                $product = Product::create([
                    'title'           => $title,
                    'image'           => $image,
                    'decription'      => $desc ?: "Nhập từ phiếu {$code}",
                    'price'           => $price ?: 0,
                    'quantity'        => $qty,
                    'volume'          => $volumes[$i] ?? '100ml',
                    'status'          => 1,
                    'idConcentration' => $concId,
                    'idBrand'         => $brandId,
                    'idCategory'      => $catId,
                ]);
                $reason = "Tạo mới sản phẩm từ phiếu {$code}";
            }

            // Ghi log tồn kho
            WarehouseStockLog::create([
                'receipt_id'  => $receipt->id,
                'product_id'  => $product->id,
                'type'        => 'import',
                'quantity'    => $qty,
                'stock_after' => $product->quantity,
                'reason'      => $reason,
            ]);

            $approvedItems[] = [
                'row_index'     => (int) $i,
                'title'         => $title,
                'image'         => $image,
                'decription'    => $desc,
                'unit_price'    => (float)($unitPrices[$i] ?? 0),
                'sl_order'      => (int)($slOrders[$i] ?? 0),
                'quantity'      => $qty,
                'price'         => $price,
                'volume'        => $volumes[$i] ?? '100ml',
                'category'      => $cats[$i] ?? '',
                'brand'         => $brands[$i] ?? '',
                'concentration' => $concs[$i] ?? '',
            ];

            $count++;
        }

        // Đánh dấu file đã duyệt + lưu danh sách SP thực sự nhập kho
        $import->update([
            'status'         => 'approved',
            'approved_items' => $approvedItems,
            'reviewed_by'    => Auth::id(),
            'reviewed_at'    => now(),
        ]);

        return redirect()->route('admin.warehouse.imports')
            ->with('success', "Đã duyệt! Cập nhật {$count} sản phẩm.");
    }

    /**
     * Admin từ chối file — không nhập kho, chỉ đổi status.
     */
    public function importReject(WarehouseImport $import)
    {
        $import->update([
            'status'      => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.warehouse.imports')
            ->with('success', 'Đã từ chối file nhập kho.');
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Lấy dữ liệu cho 3 tab trang kho tổng quan.
     * Trả về: [receipts, stockLogs, slowProducts]
     */
    private function getWarehouseData(): array
    {
        // Tab 3: Tất cả phiếu nhập kho
        $receipt = WarehouseReceipt::orderBy('created_at', 'desc')->get();

        // Tab 2: Log biến động tồn kho (kèm tên SP)
        $stockLogs = WarehouseStockLog::with('product')
            ->orderBy('created_at', 'desc')
            ->get();

        // Tab 1: Sản phẩm bán chậm - logic mới:
        // Lấy các lần nhập kho cách đây >= 7 ngày (có thể đổi thành 30 sau)
        // Tính tỉ lệ đã bán / nhập của batch đó
        // Nếu < 30% → cảnh báo bán chậm
        $slowProducts = [];
        $daysThreshold = 7; // TODO: Đổi thành 30 khi có đủ dữ liệu
        $daysAgo = now()->subDays($daysThreshold);

        // Lấy các log nhập kho từ N ngày trước trở về trước
        $oldImportLogs = WarehouseStockLog::where('type', 'import')
            ->where('created_at', '<=', $daysAgo)
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy('product_id');

        foreach ($oldImportLogs as $productId => $logs) {
            $product = Product::find($productId);
            if (!$product) continue;

            // Tính tổng số lượng nhập từ các lần nhập >= N ngày trước
            $totalImported = $logs->sum('quantity');
            
            // Lấy log nhập đầu tiên để biết ngày nhập
            $firstImportDate = $logs->first()->created_at;
            $daysInStock = now()->diffInDays($firstImportDate);

            // Tính tổng đã bán (từ tất cả đơn hàng)
            $totalSold = Schema::hasTable('order_details')
                ? DB::table('order_details')->where('idProduct', $product->id)->sum('quantity')
                : 0;

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

        return [$receipt, $stockLogs, $slowProducts];
    }

    /**
     * Đọc file CSV/Excel và trả về mảng preview sản phẩm.
     */
    private function parseImportFile(WarehouseImport $import): array
    {
        $filePath        = Storage::disk('local')->path($import->file_path);
        $productsPreview = [];
        $ext             = strtolower(pathinfo($import->original_name, PATHINFO_EXTENSION));

        if (!file_exists($filePath)) {
            return $productsPreview;
        }

        if ($ext === 'csv') {
            $fileContent = file_get_contents($filePath);

            $enc = mb_detect_encoding($fileContent, ['UTF-8', 'GBK', 'ISO-8859-1'], true);
            if ($enc && $enc !== 'UTF-8') {
                $fileContent = mb_convert_encoding($fileContent, 'UTF-8', $enc);
            }

            $stream = fopen('php://memory', 'r+');
            fwrite($stream, $fileContent);
            rewind($stream);

            $headerLine = fgetcsv($stream, 1000, ',');
            $delimiter  = (count($headerLine) <= 1) ? ';' : ',';
            if ($delimiter === ';') {
                rewind($stream);
                fgetcsv($stream, 1000, ';');
            }

            while (($data = fgetcsv($stream, 1000, $delimiter)) !== false) {
                if (!empty($data[0])) {
                    $productsPreview[] = $this->mapRow($data);
                }
            }
            fclose($stream);
        } else {
            $importer = new \App\Imports\RawArrayImport();
            Excel::import($importer, $filePath);
            $sheet = $importer->data;

            if (!empty($sheet)) {
                array_shift($sheet);
                foreach ($sheet as $data) {
                    if (!empty($data[0])) {
                        $productsPreview[] = $this->mapRow(array_values($data));
                    }
                }
            }
        }

        return $productsPreview;
    }

    /**
     * Ánh xạ 1 dòng CSV/Excel thành array chuẩn để hiển thị preview.
     *
     * Format file chuẩn (theo thứ tự cột):
     *   [0] title         — Tên sản phẩm
     *   [1] image         — URL ảnh
     *   [2] decription    — Mô tả (typo giữ nguyên)
     *   [3] unit_price    — Giá nhập từ NSX
     *   [4] sl_order      — Số lượng đã order
     *   [5] quantity      — Số lượng thực tế nhập kho
     *   [6] volume        — Dung tích (VD: 100ml)
     *   [7] category      — Tên danh mục
     *   [8] brand         — Tên thương hiệu
     *   [9] concentration — Nồng độ (VD: EDP)
     */
    private function mapRow(array $d): array
    {
        return [
            'title'         => trim($d[0] ?? ''),
            'image'         => trim($d[1] ?? ''),
            'decription'    => trim($d[2] ?? ''),
            'unit_price'    => (float) str_replace(',', '', trim((string)($d[3] ?? '0'))),
            'sl_order'      => (int) trim((string)($d[4] ?? '0')),
            'quantity'      => (int) trim((string)($d[5] ?? '0')),
            'volume'        => trim($d[6] ?? '100ml'),
            'category'      => trim($d[7] ?? ''),
            'brand'         => trim($d[8] ?? ''),
            'concentration' => trim($d[9] ?? ''),
            'note'          => '',
        ];
    }
}

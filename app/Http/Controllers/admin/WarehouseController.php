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
 * Tách ra từ ProductController để giảm kích thước file và tăng tính rõ ràng.
 * Bao gồm:
 *  - Trang tổng quan kho (3 tab: bán chậm, biến động, lịch sử nhập)
 *  - Nhập kho thủ công
 *  - Luồng upload file → duyệt / từ chối
 */
class WarehouseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // =========================================================================
    // KHO TỔNG QUAN (3 TAB)
    // =========================================================================

    /**
     * Trang tổng quan kho (admin.product.warehouse.index).
     */
    public function index()
    {
        [$receipt, $stockLogs, $slowProducts] = $this->getWarehouseData();
        return view('admin.product.warehouse', compact('receipt', 'stockLogs', 'slowProducts'));
    }

    /**
     * Nhập kho thủ công qua form (admin.product.warehouse.store).
     * Admin nhập tên + số lượng, hệ thống tự tìm hoặc tạo sản phẩm.
     */
    public function store(Request $request)
    {
        $productNames = $request->input('product_name', []);
        $quantities   = $request->input('quantity', []);
        $prices       = $request->input('price', []);
        $notes        = $request->input('note', []);

        // Lọc dòng hợp lệ: có tên và số lượng > 0
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
                $product->increment('quantity', $item['quantity']);
                $reasonText = "Nhập kho bổ sung từ phiếu {$receiptCode}";
            } else {
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
    // LUỒNG FILE: NHÂN VIÊN UPLOAD → ADMIN DUYỆT
    // =========================================================================

    /**
     * Danh sách file nhập kho (admin.warehouse.imports).
     */
    public function importList()
    {
        $imports = WarehouseImport::with('uploader')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('admin.product.import-list', compact('imports'));
    }

    /**
     * Nhân viên upload file CSV/Excel (admin.warehouse.imports.upload).
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
            'supplier.max'        => 'Tên nhà cung cấp không được vượt quá 255 ký tự.',
            'note.max'            => 'Ghi chú không được vượt quá 1000 ký tự.',
        ]);

        $file = $request->file('excel_file');
        $path = $file->store('warehouse_imports', 'local');

        WarehouseImport::create([
            'file_path'     => $path,
            'original_name' => $file->getClientOriginalName(),
            'supplier'      => $request->input('supplier'),
            'note'          => $request->input('note'),
            'uploaded_by'   => Auth::id(),
            'status'        => 'pending',
        ]);

        return redirect()->route('admin.warehouse.imports')
            ->with('success', 'Đã gửi file lên! Chờ admin duyệt.');
    }

    /**
     * Admin xem chi tiết file + preview sản phẩm (admin.warehouse.imports.show).
     */
    public function importShow(WarehouseImport $import)
    {
        $filePath        = Storage::disk('local')->path($import->file_path);
        $productsPreview = [];
        $ext             = strtolower(pathinfo($import->original_name, PATHINFO_EXTENSION));

        if (file_exists($filePath)) {
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
                if ($delimiter === ';') { rewind($stream); fgetcsv($stream, 1000, ';'); }
                while (($data = fgetcsv($stream, 1000, $delimiter)) !== false) {
                    if (!empty($data[0])) $productsPreview[] = $this->mapRow($data);
                }
                fclose($stream);
            } else {
                $importer = new \App\Imports\RawArrayImport();
                Excel::import($importer, $filePath);
                $sheet = $importer->data;
                if (!empty($sheet)) {
                    array_shift($sheet);
                    foreach ($sheet as $data) {
                        if (!empty($data[0])) $productsPreview[] = $this->mapRow(array_values($data));
                    }
                }
            }
        }

        return view('admin.product.import-show', compact('import', 'productsPreview'));
    }

    /**
     * Admin duyệt file → nhập kho (admin.warehouse.imports.approve).
     */
    public function importApprove(Request $request, WarehouseImport $import)
    {
        if ($import->status !== 'pending') {
            return redirect()->route('admin.warehouse.imports')->with('error', 'File này đã được xử lý rồi.');
        }

        $selected = $request->input('selected_products', []);
        if (empty($selected)) {
            return back()->with('error', 'Bạn chưa chọn sản phẩm nào.');
        }

        $titles  = $request->input('product_name', []);
        $qtys    = $request->input('quantity', []);
        $prices  = $request->input('price', []);
        $images  = $request->input('image', []);
        $descs   = $request->input('decription', []);
        $volumes = $request->input('volume', []);
        $cats    = $request->input('category', []);
        $brands  = $request->input('brand', []);
        $concs   = $request->input('concentration', []);

        $code    = 'PN' . now()->format('ymdHis');
        $receipt = WarehouseReceipt::create([
            'receipt_code' => $code,
            'supplier'     => $import->supplier,
            'note'         => $import->note,
            'total_items'  => count($selected),
        ]);

        $count = 0;
        foreach ($selected as $i) {
            $title = $titles[$i] ?? null;
            $qty   = (int)($qtys[$i] ?? 0);
            if (!$title || $qty <= 0) continue;

            $price = (float)($prices[$i] ?? 0);
            $image = $images[$i] ?? '';
            $desc  = $descs[$i] ?? '';

            $product = Product::where('title', $title)->first();
            if ($product) {
                $product->increment('quantity', $qty);
                if ($price > 0) $product->update(['price' => $price]);
                if ($image)     $product->update(['image' => $image]);
                if ($desc)      $product->update(['decription' => $desc]);
                $reason = "Nhập kho từ phiếu {$code}";
            } else {
                $catId   = Category::where('name', $cats[$i] ?? '')->value('id') ?? Category::value('id') ?? 1;
                $brandId = Brand::where('name', $brands[$i] ?? '')->value('id') ?? Brand::value('id') ?? 1;
                $concId  = Concentration::where('concentration', $concs[$i] ?? '')->value('id') ?? Concentration::value('id') ?? 1;
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

            WarehouseStockLog::create([
                'receipt_id'  => $receipt->id,
                'product_id'  => $product->id,
                'type'        => 'import',
                'quantity'    => $qty,
                'stock_after' => $product->quantity,
                'reason'      => $reason,
            ]);
            $count++;
        }

        $import->update(['status' => 'approved', 'reviewed_by' => Auth::id(), 'reviewed_at' => now()]);
        return redirect()->route('admin.warehouse.imports')->with('success', "Đã duyệt! Cập nhật {$count} sản phẩm.");
    }

    /**
     * Admin từ chối file (admin.warehouse.imports.reject).
     */
    public function importReject(WarehouseImport $import)
    {
        $import->update(['status' => 'rejected', 'reviewed_by' => Auth::id(), 'reviewed_at' => now()]);
        return redirect()->route('admin.warehouse.imports')->with('success', 'Đã từ chối file nhập kho.');
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Lấy dữ liệu cho 3 tab trang kho.
     */
    private function getWarehouseData(): array
    {
        $receipt    = WarehouseReceipt::orderBy('created_at', 'desc')->get();
        $stockLogs  = WarehouseStockLog::with('product')->orderBy('created_at', 'desc')->get();
        $slowProducts = [];

        foreach (Product::where('quantity', '>', 0)->get() as $product) {
            $totalImport = WarehouseStockLog::where('product_id', $product->id)->where('type', 'import')->sum('quantity');
            $totalSold   = Schema::hasTable('order_details')
                ? DB::table('order_details')->where('idProduct', $product->id)->sum('quantity')
                : 0;
            $importBase  = $totalImport > 0 ? $totalImport : $product->quantity;
            $saleRate    = $importBase > 0 ? ($totalSold / $importBase) * 100 : 0;

            if ($saleRate < 20) {
                $product->total_import = $importBase;
                $product->total_sold   = $totalSold;
                $product->sale_rate    = round($saleRate, 1);
                $slowProducts[]        = $product;
            }
        }

        return [$receipt, $stockLogs, $slowProducts];
    }

    /**
     * Map 1 dòng CSV/Excel thành array preview chuẩn.
     * Thứ tự: title | image | decription | price | quantity | volume | category | brand | concentration
     */
    private function mapRow(array $d): array
    {
        return [
            'title'         => trim($d[0] ?? ''),
            'image'         => trim($d[1] ?? ''),
            'decription'    => trim($d[2] ?? ''),
            'price'         => (float) str_replace(',', '', trim((string)($d[3] ?? '0'))),
            'quantity'      => (int) trim((string)($d[4] ?? '0')),
            'volume'        => trim($d[5] ?? '100ml'),
            'category'      => trim($d[6] ?? ''),
            'brand'         => trim($d[7] ?? ''),
            'concentration' => trim($d[8] ?? ''),
            'note'          => '',
        ];
    }
}

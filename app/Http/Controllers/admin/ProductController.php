<?php

namespace App\Http\Controllers\admin;

use App\Models\Product;
use App\Models\Category;
use App\Models\Concentration;
use App\Models\Brand;
use App\Models\Festival;
use App\Models\WarehouseReceipt;
use App\Models\WarehouseStockLog;
use App\Models\WarehouseImport;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

/**
 * ProductController - Quản lý sản phẩm và kho hàng trong khu vực Admin.
 *
 * Bao gồm các chức năng:
 *  - CRUD sản phẩm (resource route)
 *  - Gợi ý sản phẩm theo từ khóa (AJAX)
 *  - Quản lý kho: xem tổng quan, nhập tay
 *  - Luồng nhập kho qua file: upload → duyệt / từ chối
 */
class ProductController extends Controller
{
    /**
     * Constructor: yêu cầu đăng nhập và chia sẻ danh sách sản phẩm cho tất cả view.
     */
    public function __construct()
    {
        // Bắt buộc đăng nhập mới vào được các route của controller này
        $this->middleware("auth");

        // Lấy toàn bộ sản phẩm sắp xếp theo id giảm dần, chia sẻ cho mọi view
        $products = Product::orderBy('id', 'desc')->get();
        View::share('products', $products);
    }

    // =========================================================================
    // RESOURCE CRUD SẢN PHẨM
    // =========================================================================

    /**
     * Hiển thị danh sách tất cả sản phẩm (admin.product.index).
     * Eager-load quan hệ festivals để tránh N+1 query.
     */
    public function index()
    {
        // Lấy sản phẩm kèm thông tin festival liên kết
        $products = Product::with('festivals')->get();
        return view('admin.product.product-list', compact('products'));
    }

    /**
     * Hiển thị form thêm sản phẩm mới (admin.product.create).
     * Truyền danh sách danh mục, nồng độ, thương hiệu và festival đang active.
     */
    public function create()
    {
        $categories    = Category::all();
        $concentrations = Concentration::all();
        // Chỉ lấy festival đang hoạt động (status = 1)
        $festivals     = Festival::where('status', 1)->get();
        $brands        = Brand::all();

        return view('admin.product.add', compact('categories', 'concentrations', 'brands', 'festivals'));
    }

    /**
     * Lưu sản phẩm mới vào database (admin.product.store).
     * Sau khi tạo sản phẩm, gắn các festival được chọn (nếu có).
     */
    public function store(Request $request)
    {
        // Tạo bản ghi sản phẩm mới với dữ liệu từ form
        $product = Product::create([
            'title'          => $request->title,
            'image'          => $request->image,
            'decription'     => $request->decription,
            'status'         => $request->status ?? 1,        // mặc định active
            'price'          => $request->price ?? 0,
            'quantity'       => $request->quantity ?? 0,
            'volume'         => $request->volume,
            'idConcentration' => $request->idConcentration,
            'idCategory'     => $request->idCategory,
            'idBrand'        => $request->idBrand,
        ]);

        if ($product) {
            // Gắn festival nếu admin có chọn (many-to-many)
            if ($request->has('idFestival') && is_array($request->idFestival)) {
                $product->festivals()->attach($request->idFestival);
            }
            return redirect()->route('admin.product.index');
        } else {
            return back();
        }
    }

    /**
     * Hiển thị form chỉnh sửa sản phẩm (admin.product.edit).
     * Lấy thêm danh sách các festival đã được gắn với sản phẩm này.
     */
    public function edit($id)
    {
        $product        = Product::findOrFail($id);
        $categories     = Category::all();
        $concentrations = Concentration::all();
        $brands         = Brand::all();
        $festivals      = Festival::where('status', 1)->get();

        // Lấy mảng ID các festival đang gắn với sản phẩm (để pre-check trên form)
        $selectedFestivalIds = $product->festivals()->pluck('festivals.id')->toArray();

        return view('admin.product.edit', compact(
            'product', 'categories', 'concentrations', 'brands', 'festivals', 'selectedFestivalIds'
        ));
    }

    /**
     * Cập nhật thông tin sản phẩm (admin.product.update).
     * Dùng sync() để đồng bộ lại danh sách festival (tự xóa / thêm).
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // Cập nhật các trường thông tin cơ bản
        $product->update([
            'title'          => $request->title,
            'image'          => $request->image,
            'decription'     => $request->decription,
            'status'         => $request->status,
            'price'          => $request->price ?? 0,
            'quantity'       => $request->quantity ?? 0,
            'volume'         => $request->volume,
            'idConcentration' => $request->idConcentration,
            'idCategory'     => $request->idCategory,
            'idBrand'        => $request->idBrand,
        ]);

        // sync() sẽ tự động xóa các festival không còn được chọn và thêm festival mới
        $festivalIds = $request->input('idFestival', []);
        $product->festivals()->sync($festivalIds);

        return redirect()->route('admin.product.index');
    }

    /**
     * Xóa sản phẩm khỏi database (admin.product.destroy).
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return back()->with('error', 'Sản phẩm không tồn tại.');
        }

        $product->delete();
        return redirect()->route('admin.product.index');
    }

    // =========================================================================
    // GỢI Ý SẢN PHẨM (AJAX)
    // =========================================================================

    /**
     * Trả về danh sách sản phẩm gợi ý theo từ khóa (admin.product.suggest).
     * Dùng cho ô tìm kiếm sản phẩm nhanh dạng AJAX, tối đa 5 kết quả.
     */
    public function suggest(Request $request)
    {
        $keyword = $request->keyword;

        // Nếu không có từ khóa thì trả về mảng rỗng
        if (empty($keyword)) {
            return response()->json([]);
        }

        // Tìm kiếm sản phẩm theo tên (LIKE), chỉ chọn các trường cần thiết
        $products = Product::where('title', 'LIKE', "%{$keyword}%")
            ->select('id', 'title', 'image', 'status')
            ->take(5)
            ->get();

        return response()->json($products);
    }

    // =========================================================================
    // QUẢN LÝ KHO: XEM TỔNG QUAN VÀ NHẬP TAY
    // =========================================================================

    /**
     * [Private] Lấy dữ liệu tổng quan cho 3 tab trang kho:
     *  - Tab 1: Danh sách phiếu nhập kho
     *  - Tab 2: Lịch sử tồn kho (stock log)
     *  - Tab 3: Danh sách sản phẩm bán chậm (tỉ lệ bán = 0%)
     *
     * Được dùng chung bởi warehouseIndex().
     */
    private function getWarehouseData()
    {
        // Lấy toàn bộ phiếu nhập kho, mới nhất trước
        $receipt   = WarehouseReceipt::orderBy('created_at', 'desc')->get();

        // Lấy toàn bộ log tồn kho kèm thông tin sản phẩm
        $stockLogs = WarehouseStockLog::with('product')->orderBy('created_at', 'desc')->get();

        // Tính sản phẩm bán chậm: có tồn kho > 0 nhưng tỉ lệ bán về 0%
        $allProducts = Product::where('quantity', '>', 0)->get();
        $slowProducts = [];

        foreach ($allProducts as $product) {
            // Tổng số lượng đã nhập kho cho sản phẩm này
            $totalImport = WarehouseStockLog::where('product_id', $product->id)
                ->where('type', 'import')
                ->sum('quantity');

            // Tổng số lượng đã bán (từ bảng order_details nếu tồn tại)
            $totalSold = 0;
            if (Schema::hasTable('order_details')) {
                $totalSold = DB::table('order_details')
                    ->where('idProduct', $product->id)
                    ->sum('quantity');
            }

            // Tính tỉ lệ bán ra so với tổng nhập (%)
            $saleRate = $totalImport > 0 ? ($totalSold / $totalImport) * 100 : 0;

            // Chỉ đưa vào danh sách bán chậm nếu chưa bán được cái nào
            if ($saleRate <= 0) {
                $product->total_import = $totalImport > 0 ? $totalImport : $product->quantity;
                $product->total_sold   = $totalSold;
                $product->sale_rate    = round($saleRate, 1);
                $slowProducts[]        = $product;
            }
        }

        return [$receipt, $stockLogs, $slowProducts];
    }

    /**
     * Hiển thị trang tổng quan kho hàng (admin.product.warehouse.index).
     * Truyền dữ liệu cho 3 tab: phiếu nhập, lịch sử log, hàng bán chậm.
     */
    public function warehouseIndex()
    {
        // Gọi hàm nội bộ để lấy đủ dữ liệu 3 tab
        [$receipt, $stockLogs, $slowProducts] = $this->getWarehouseData();

        return view('admin.product.warehouse', compact('receipt', 'stockLogs', 'slowProducts'));
    }

    /**
     * Xử lý nhập kho thủ công (admin.product.warehouse.store).
     * Admin gõ trực tiếp tên sản phẩm + số lượng trên form để nhập kho nhanh.
     * Nếu sản phẩm chưa tồn tại thì sẽ tự động tạo mới.
     */
    public function warehouseStore(Request $request)
    {
        // Lấy danh sách sản phẩm, số lượng, giá, ghi chú từ form (dạng mảng)
        $productNames = $request->input('product_name', []);
        $quantities   = $request->input('quantity', []);
        $prices       = $request->input('price', []);
        $notes        = $request->input('note', []);

        // Lọc các dòng hợp lệ: có tên và số lượng > 0
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

        // Không có dòng hợp lệ thì quay lại với thông báo lỗi
        if (empty($validItems)) {
            return redirect()->back()->with('error', 'Không tìm thấy dữ liệu hợp lệ để nhập kho.');
        }

        // Tạo mã phiếu nhập theo định dạng PN + timestamp
        $receiptCode = 'PN' . Carbon::now()->format('ymdHis');
        $receipt = WarehouseReceipt::create([
            'receipt_code' => $receiptCode,
            'supplier'     => $request->input('supplier'),
            'note'         => $request->input('note'),
            'total_items'  => count($validItems),
        ]);

        $successCount = 0;

        foreach ($validItems as $item) {
            // Tìm sản phẩm theo tên chính xác
            $product = Product::where('title', $item['product_name'])->first();

            if ($product) {
                // Sản phẩm đã tồn tại: cộng thêm số lượng nhập
                $product->increment('quantity', $item['quantity']);
                $reasonText = "Nhập kho bổ sung từ phiếu {$receiptCode}";
            } else {
                // Sản phẩm chưa có: lấy ID mặc định từ category/brand/concentration
                $defaultCategoryId     = Category::value('id') ?? 1;
                $defaultBrandId        = Brand::value('id') ?? 1;
                $defaultConcentrationId = Concentration::value('id') ?? 1;

                // Tạo sản phẩm mới với giá bán = giá nhập * 1.2 (hoặc mặc định 450.000)
                $product = Product::create([
                    'title'           => $item['product_name'],
                    'image'           => '',
                    'decription'      => "Sản phẩm mới tự động đồng bộ từ file nhập kho {$receiptCode}",
                    'price'           => $item['price'] > 0 ? $item['price'] * 1.2 : 450000,
                    'quantity'        => $item['quantity'],
                    'volume'          => '100ml',
                    'status'          => 1,
                    'idConcentration' => $defaultConcentrationId,
                    'idBrand'         => $defaultBrandId,
                    'idCategory'      => $defaultCategoryId,
                ]);
                $reasonText = "Tạo mới sản phẩm tự động từ file nhập kho {$receiptCode}";
            }

            // Ghi log lịch sử tồn kho cho từng dòng nhập
            WarehouseStockLog::create([
                'receipt_id'  => $receipt->id,
                'product_id'  => $product->id,
                'type'        => 'import',
                'quantity'    => $item['quantity'],
                'stock_after' => $product->quantity,   // tồn kho sau khi nhập
                'reason'      => $reasonText . ($item['note'] ? ' / ' . $item['note'] : ''),
            ]);

            $successCount++;
        }

        return redirect()->route('admin.product.warehouse.index')
            ->with('success', "Duyệt nhập kho hoàn tất! Đã cập nhật thành công {$successCount} sản phẩm.");
    }

    // =========================================================================
    // LUỒNG NHẬP KHO QUA FILE: NHÂN VIÊN UPLOAD → ADMIN DUYỆT
    // =========================================================================

    /**
     * Hiển thị danh sách tất cả file nhập kho đã được upload (admin.warehouse.imports).
     * Nhân viên và Admin đều xem được danh sách này.
     */
    public function importList()
    {
        // Lấy danh sách file kèm thông tin người upload, mới nhất trước
        $imports = WarehouseImport::with('uploader')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.product.import-list', compact('imports'));
    }

    /**
     * Nhân viên kho upload file CSV/Excel chờ admin duyệt (admin.warehouse.imports.upload).
     * File được lưu vào storage/app/warehouse_imports với trạng thái 'pending'.
     */
    public function importUpload(Request $request)
    {
        // Validate: bắt buộc có file, đúng định dạng, tối đa 5MB
        $request->validate([
            'excel_file' => 'required|file|mimes:csv,xlsx,xls|max:5120',
            'supplier'   => 'nullable|string|max:255',
            'note'       => 'nullable|string|max:1000',
        ]);

        $file = $request->file('excel_file');

        // Lưu file vào disk 'local' (storage/app/warehouse_imports/)
        $path = $file->store('warehouse_imports', 'local');

        // Tạo bản ghi theo dõi file với trạng thái chờ duyệt
        WarehouseImport::create([
            'file_path'     => $path,
            'original_name' => $file->getClientOriginalName(), // tên file gốc của nhân viên
            'supplier'      => $request->input('supplier'),
            'note'          => $request->input('note'),
            'uploaded_by'   => Auth::id(),   // ID nhân viên upload
            'status'        => 'pending',    // trạng thái chờ duyệt
        ]);

        return redirect()->route('admin.warehouse.imports')
            ->with('success', 'Đã gửi file lên! Chờ admin duyệt.');
    }

    /**
     * Admin xem chi tiết một file nhập kho + preview danh sách sản phẩm (admin.warehouse.imports.show).
     * Hỗ trợ cả file CSV và Excel (.xlsx, .xls).
     */
    public function importShow(WarehouseImport $import)
    {
        // Lấy đường dẫn tuyệt đối của file trên server (Laravel 11: lưu trong storage/app/private/)
        $filePath       = Storage::disk('local')->path($import->file_path);
        $productsPreview = [];
        $ext            = strtolower(pathinfo($import->original_name, PATHINFO_EXTENSION));

        if (file_exists($filePath)) {
            if ($ext === 'csv') {
                // --- ĐỌC FILE CSV ---
                $fileContent = file_get_contents($filePath);

                // Tự động phát hiện encoding và chuyển về UTF-8 (hỗ trợ tiếng Việt có dấu)
                $enc = mb_detect_encoding($fileContent, ['UTF-8', 'GBK', 'ISO-8859-1'], true);
                if ($enc && $enc !== 'UTF-8') {
                    $fileContent = mb_convert_encoding($fileContent, 'UTF-8', $enc);
                }

                // Mở stream bộ nhớ để xử lý nội dung CSV
                $stream = fopen('php://memory', 'r+');
                fwrite($stream, $fileContent);
                rewind($stream);

                // Đọc dòng header để xác định delimiter (dấu phẩy hoặc chấm phẩy)
                $headerLine = fgetcsv($stream, 1000, ',');
                $delimiter  = (count($headerLine) <= 1) ? ';' : ',';

                // Nếu delimiter là ';', rewind và đọc lại header với delimiter đúng
                if ($delimiter === ';') {
                    rewind($stream);
                    fgetcsv($stream, 1000, ';');
                }

                // Đọc từng dòng dữ liệu (bỏ qua dòng rỗng)
                while (($data = fgetcsv($stream, 1000, $delimiter)) !== false) {
                    if (!empty($data[0])) {
                        $productsPreview[] = $this->mapRowToPreview($data);
                    }
                }
                fclose($stream);
            } else {
                // --- ĐỌC FILE EXCEL (.xlsx / .xls) ---
                $importer = new \App\Imports\RawArrayImport();
                Excel::import($importer, $filePath);
                $sheet = $importer->data;

                if (!empty($sheet)) {
                    array_shift($sheet); // bỏ dòng header (dòng đầu tiên)
                    foreach ($sheet as $data) {
                        if (!empty($data[0])) {
                            $productsPreview[] = $this->mapRowToPreview(array_values($data));
                        }
                    }
                }
            }
        }

        return view('admin.product.import-show', compact('import', 'productsPreview'));
    }

    /**
     * [Private] Chuyển một dòng dữ liệu thô (mảng) thành mảng preview chuẩn.
     * Áp dụng cho cả CSV lẫn Excel, theo thứ tự cột quy ước.
     *
     * Thứ tự cột: Tên | Ảnh | Mô tả | Giá | SL | Dung tích | Danh mục | Thương hiệu | Nồng độ
     */
    private function mapRowToPreview(array $data): array
    {
        return [
            'title'         => trim($data[0] ?? ''),                                     // Cột A: Tên sản phẩm
            'image'         => trim($data[1] ?? ''),                                     // Cột B: Ảnh (URL)
            'decription'    => trim($data[2] ?? ''),                                     // Cột C: Mô tả
            'price'         => (float) str_replace(',', '', trim((string)($data[3] ?? '0'))), // Cột D: Giá nhập
            'quantity'      => (int) trim((string)($data[4] ?? '0')),                    // Cột E: Số lượng
            'volume'        => trim($data[5] ?? '100ml'),                                // Cột F: Dung tích
            'category'      => trim($data[6] ?? ''),                                     // Cột G: Danh mục
            'brand'         => trim($data[7] ?? ''),                                     // Cột H: Thương hiệu
            'concentration' => trim($data[8] ?? ''),                                     // Cột I: Nồng độ
            'note'          => '',                                                        // Ghi chú (để trống khi preview)
        ];
    }

    /**
     * Admin duyệt file nhập kho → lưu sản phẩm được chọn vào database (admin.warehouse.imports.approve).
     * Tạo phiếu nhập kho (WarehouseReceipt) và ghi log tồn kho cho từng sản phẩm.
     * Nếu sản phẩm chưa tồn tại thì tự động tạo mới.
     */
    public function importApprove(Request $request, WarehouseImport $import)
    {
        // Chỉ xử lý file đang ở trạng thái chờ duyệt
        if ($import->status !== 'pending') {
            return redirect()->route('admin.warehouse.imports')
                ->with('error', 'File này đã được xử lý rồi.');
        }

        // Lấy danh sách index các sản phẩm được admin tick chọn
        $selectedIndexes = $request->input('selected_products', []);
        if (empty($selectedIndexes)) {
            return back()->with('error', 'Bạn chưa chọn sản phẩm nào.');
        }

        // Lấy toàn bộ dữ liệu sản phẩm từ form (theo từng index)
        $titles         = $request->input('product_name', []);
        $quantities     = $request->input('quantity', []);
        $prices         = $request->input('price', []);
        $images         = $request->input('image', []);
        $decriptions    = $request->input('decription', []);
        $volumes        = $request->input('volume', []);
        $categories     = $request->input('category', []);
        $brands         = $request->input('brand', []);
        $concentrations = $request->input('concentration', []);

        // Tạo phiếu nhập kho tổng cho đợt duyệt này
        $receiptCode = 'PN' . now()->format('ymdHis');
        $receipt = WarehouseReceipt::create([
            'receipt_code' => $receiptCode,
            'supplier'     => $import->supplier,
            'note'         => $import->note,
            'total_items'  => count($selectedIndexes),
        ]);

        $successCount = 0;

        foreach ($selectedIndexes as $index) {
            $title        = $titles[$index] ?? null;
            $qty          = (int)($quantities[$index] ?? 0);
            $price        = (float)($prices[$index] ?? 0);
            $image        = $images[$index] ?? '';
            $decription   = $decriptions[$index] ?? '';
            $volume       = $volumes[$index] ?? '100ml';
            $categoryName = $categories[$index] ?? '';
            $brandName    = $brands[$index] ?? '';
            $concentName  = $concentrations[$index] ?? '';

            // Bỏ qua dòng thiếu tên hoặc số lượng không hợp lệ
            if (!$title || $qty <= 0) continue;

            // Tìm sản phẩm theo tên chính xác
            $product = Product::where('title', $title)->first();

            if ($product) {
                // Sản phẩm đã có: cộng số lượng và cập nhật giá/ảnh/mô tả nếu được cung cấp
                $product->increment('quantity', $qty);
                if ($price > 0)       $product->update(['price' => $price]);
                if (!empty($image))   $product->update(['image' => $image]);
                if (!empty($decription)) $product->update(['decription' => $decription]);
                $reason = "Nhập kho từ phiếu {$receiptCode}";
            } else {
                // Sản phẩm chưa có: tìm ID từ tên category/brand/concentration, tạo mới
                $catId   = Category::where('name', $categoryName)->value('id')
                         ?? Category::value('id') ?? 1;
                $brandId = Brand::where('name', $brandName)->value('id')
                         ?? Brand::value('id') ?? 1;
                $concId  = Concentration::where('concentration', $concentName)->value('id')
                         ?? Concentration::value('id') ?? 1;

                $product = Product::create([
                    'title'           => $title,
                    'image'           => $image,
                    'decription'      => $decription ?: "Nhập từ phiếu {$receiptCode}",
                    'price'           => $price ?: 0,
                    'quantity'        => $qty,
                    'volume'          => $volume,
                    'status'          => 1,
                    'idConcentration' => $concId,
                    'idBrand'         => $brandId,
                    'idCategory'      => $catId,
                ]);
                $reason = "Tạo mới sản phẩm từ phiếu {$receiptCode}";
            }

            // Ghi log tồn kho để theo dõi lịch sử và tính hàng bán chậm
            WarehouseStockLog::create([
                'receipt_id'  => $receipt->id,
                'product_id'  => $product->id,
                'type'        => 'import',
                'quantity'    => $qty,
                'stock_after' => $product->quantity,  // tồn kho sau khi nhập
                'reason'      => $reason,
            ]);

            $successCount++;
        }

        // Cập nhật trạng thái file sang 'approved' và ghi lại người duyệt
        $import->update([
            'status'      => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.warehouse.imports')
            ->with('success', "Đã duyệt! Cập nhật {$successCount} sản phẩm vào kho.");
    }

    /**
     * Admin từ chối file nhập kho (admin.warehouse.imports.reject).
     * Cập nhật trạng thái file sang 'rejected' và lưu thông tin người từ chối.
     */
    public function importReject(WarehouseImport $import)
    {
        $import->update([
            'status'      => 'rejected',
            'reviewed_by' => Auth::id(),   // ID admin từ chối
            'reviewed_at' => now(),
        ]);

        return redirect()->route('admin.warehouse.imports')
            ->with('success', 'Đã từ chối file nhập kho.');
    }
}

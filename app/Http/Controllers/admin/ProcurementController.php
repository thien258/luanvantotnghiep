<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ProcurementRequest;
use App\Models\ProcurementRequestItem;
use App\Models\ManuFacturer;
use App\Models\Product;
use App\Models\SupplierOffer;
use App\Models\SupplierOfferItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * ProcurementController — Quản lý yêu cầu thu mua công khai.
 *
 * Admin tạo yêu cầu → NSX thấy danh sách SP cần nhập → NSX chào giá
 * → Admin xem + so sánh → chọn NSX → tạo PurchaseOrder
 */
class ProcurementController extends Controller
{
    public function __construct()
    {
      $this->middleware(function ($request, $next) {
        $role = auth()->user()->role;
        if (!in_array($role, ['admin', 'manufacturer'])) {
            abort(403);
        }
        return $next($request);
    });
    }

    // Danh sách tất cả yêu cầu thu mua
    public function index()
    {
        $user = auth()->user();
        $query = ProcurementRequest::with(['items.product', 'offers'])
            ->orderBy('created_at', 'desc');

        $requests = $query->paginate(15);

        // Nếu là manufacturer, truyền manufacturer_id để blade lọc đúng số báo giá
        $myManufacturerId = null;
        if ($user->role === 'manufacturer') {
            $myManufacturerId = $user->manufacturer?->id;
        }

        return view('admin.procurement.index', compact('requests', 'myManufacturerId'));
    }

    // Tạo yêu cầu thu mua từ modal trang sản phẩm
    public function store(Request $request)
    {
        $request->validate([
            'product_ids'   => 'required|array|min:1',
            'product_ids.*' => 'exists:products,id',
            'deadline'      => 'nullable|date|after:today',
        ], [
            'product_ids.required' => 'Vui lòng chọn ít nhất 1 sản phẩm.',
            'deadline.after'       => 'Hạn chót phải sau ngày hôm nay.',
        ]);

        $productIds = $request->input('product_ids', []);
        $qtySuggest = $request->input('qty_suggest', []);

        // Sinh request_code tự động: PRQ-YYYYMMDD-001
        $count = ProcurementRequest::whereDate('created_at', today())->count() + 1;
        $code  = 'PRQ-' . now()->format('Ymd') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

        // Tạo đầu yêu cầu
        $req = ProcurementRequest::create([
            'request_code' => $code,
            'status'       => 'open',
            'note'         => $request->input('note'),
            'deadline'     => $request->input('deadline') ?? now()->addDays(7)->toDateString(),
            'created_by'   => Auth::id(),
        ]);

        // Tạo từng dòng sản phẩm
        foreach ($productIds as $pid) {
            $product = Product::find($pid);
            if (!$product) continue;

            ProcurementRequestItem::create([
                'request_id'   => $req->id,
                'product_id'   => $pid,
                'product_name' => $product->title,
                'qty_needed'   => (int)($qtySuggest[$pid] ?? 10),
                'note'         => '',
            ]);
        }

        return redirect()->route('admin.procurement.show', $req->id)
            ->with('success', 'Đã đăng yêu cầu nhập hàng ' . $code . ' — NSX có thể xem và chào giá.');
    }

    // Chi tiết yêu cầu + danh sách báo giá NSX đã gửi
    public function show(string $id)
    {
        $procRequest = ProcurementRequest::with([
            'items.product.brand',
            'items.product.category',
            'items.product.concentration',
            'offers.manufacturer',
            'offers.items',
            'creator',
        ])->findOrFail($id);

        // manufacturer chỉ thấy báo giá của mình trong danh sách offers
        if (auth()->user()->role === 'manufacturer') {
            $manufacturer = auth()->user()->manufacturer;
            if ($manufacturer) {
                $procRequest->setRelation(
                    'offers',
                    $procRequest->offers->where('manufacturer_id', $manufacturer->id)
                );
            } else {
                $procRequest->setRelation('offers', collect());
            }
        }

        return view('admin.procurement.show', compact('procRequest'));
    }

    // Đóng yêu cầu — không nhận báo giá nữa
    public function close(string $id)
    {
        $procRequest = ProcurementRequest::findOrFail($id);

        if ($procRequest->status === 'closed') {
            return redirect()->back()->with('error', 'Yêu cầu này đã được đóng trước đó.');
        }

        $procRequest->update(['status' => 'closed']);

        return redirect()->back()
            ->with('success', 'Đã đóng yêu cầu ' . $procRequest->request_code . '.');
    }

    // Xuất file Excel mẫu để NSX chào giá
    public function exportTemplate(string $id)
    {
        $procRequest = ProcurementRequest::with([
            'items.product.brand',
            'items.product.category',
            'items.product.concentration',
        ])->findOrFail($id);

        $filename = 'mau-bao-gia-' . $procRequest->request_code . '-' . now()->format('Ymd') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($procRequest) {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8 cho Excel hiển thị đúng tiếng Việt
            fputs($handle, "\xEF\xBB\xBF");

            // Header 10 cột - format chuẩn để upload vào trang import
            fputcsv($handle, [
                'title',
                'image',
                'decription',
                'unit_price',
                'sl_order',
                'quantity',
                'volume',
                'category',
                'brand',
                'concentration'
            ]);

            // Mỗi item trong request → 1 dòng
            foreach ($procRequest->items as $item) {
                $product = $item->product;
                fputcsv($handle, [
                    $item->product_name,                              // Tên SP
                    $product?->image ?? '',                           // URL ảnh
                    $product?->decription ?? '',                      // Mô tả
                    '',                                               // Giá nhập - NSX điền
                    $item->qty_needed,                                // Số lượng shop cần
                    '',                                               // SL thực tế - để trống
                    $product?->volume ?? '100ml',                     // Dung tích
                    $product?->category?->name ?? '',                 // Category
                    $product?->brand?->name ?? '',                    // Brand
                    $product?->concentration?->concentration ?? '',   // Nồng độ
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Upload file Excel báo giá từ NSX → đọc → tạo SupplierOffer + items
    public function uploadOffer(Request $request, string $id)
    {
        $procRequest = ProcurementRequest::findOrFail($id);

        $request->validate([
            'manufacturer_id' => 'required|exists:manufacturers,id',
            'file'            => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ], [
            'manufacturer_id.required' => 'Vui lòng chọn nhà sản xuất.',
            'file.required'            => 'Vui lòng chọn file báo giá.',
            'file.mimes'               => 'Chỉ chấp nhận file .xlsx, .xls, .csv.',
        ]);

        // Đọc file giống SupplierOfferController::upload()
        $rows = $this->readOfferFile($request->file('file'));

        // DEBUG: Log số dòng đọc được
        Log::info('ProcurementController uploadOffer - Rows count: ' . count($rows));
        if (!empty($rows)) {
            Log::info('First row: ' . json_encode($rows[0]));
        }

        if (empty($rows)) {
            return back()->with('error', 'File không có dữ liệu hoặc sai định dạng. Vui lòng kiểm tra lại file CSV.');
        }

        // Sinh offer_code
        $count     = SupplierOffer::whereDate('created_at', today())->count() + 1;
        $offerCode = 'OFR-' . now()->format('Ymd') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

        $offer = SupplierOffer::create([
            'manufacturer_id' => $request->manufacturer_id,
            'request_id'      => $procRequest->id, // liên kết về yêu cầu
            'offer_code'      => $offerCode,
            'note'            => $request->input('note'),
            'status'          => 'submitted',
            'submitted_at'    => now(),
        ]);

        $itemCount = 0;
        foreach ($rows as $row) {
            // Lấy tên SP - thử nhiều key khác nhau
            $productName = trim($row['title'] ?? $row['product_name'] ?? '');
            
            // Bỏ qua dòng nếu thiếu tên SP hoặc là dòng trống
            if (empty($productName) || strlen($productName) < 2) continue;
            
            // Lấy giá nhập - thử nhiều key
            $unitPriceRaw = $row['unit_price'] ?? $row['price'] ?? $row['gia_nhap'] ?? '0';
            $unitPrice = (float) str_replace([',', ' ', '.'], '', trim((string)$unitPriceRaw));

            // Tìm SP trong database (case-insensitive)
            $product = Product::whereRaw('LOWER(TRIM(title)) = ?', [strtolower(trim($productName))])->first();

            SupplierOfferItem::create([
                'offer_id'     => $offer->id,
                'product_id'   => $product?->id,
                'product_name' => $productName,
                'unit_price'   => $unitPrice,
                'note'         => trim($row['note'] ?? $row['decription'] ?? $row['description'] ?? ''),
            ]);
            
            $itemCount++;
        }

        return redirect()->route('admin.procurement.show', $id)
            ->with('success', "Đã đọc file báo giá {$offerCode} từ " . $offer->manufacturer->name . " — {$itemCount} sản phẩm.");
    }

    // Đọc file Excel/CSV → trả về mảng rows với key = tên cột
    private function readOfferFile($file): array
    {
        $ext     = strtolower($file->getClientOriginalExtension());
        $path    = $file->getRealPath();
        $rows    = [];
        $headers = [];

        if ($ext === 'csv') {
            $content = file_get_contents($path);
            
            // Remove UTF-8 BOM from the entire content first
            $content = str_replace("\xEF\xBB\xBF", '', $content);
            
            // Detect encoding và convert về UTF-8
            $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
            if ($encoding && $encoding !== 'UTF-8') {
                $content = mb_convert_encoding($content, 'UTF-8', $encoding);
            }
            
            // Tách theo dòng
            $lines = explode("\n", $content);
            $isHeader = true;
            
            foreach ($lines as $line) {
                $line = trim($line);
                
                // Bỏ qua dòng trống hoàn toàn
                if (empty($line)) continue;
                
                // Thử tách bằng tab trước, nếu không được thì dùng dấu phẩy
                $data = str_getcsv($line, "\t");
                if (count($data) <= 1) {
                    $data = str_getcsv($line, ",");
                }
                
                // Lọc bỏ các cell trống
                $cleanData = array_map('trim', $data);
                
                // Nếu tất cả cells đều trống → skip
                if (empty(array_filter($cleanData))) continue;
                
                if ($isHeader) {
                    // Chuyển headers thành lowercase
                    $headers = array_map(function($h) {
                        return strtolower(trim($h));
                    }, $cleanData);
                    $isHeader = false;
                    continue;
                }
                
                // Pad data để khớp số lượng headers
                $rows[] = array_combine($headers, array_pad($cleanData, count($headers), ''));
            }
        } else {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
            $sheet       = $spreadsheet->getActiveSheet();
            $isHeader    = true;
            foreach ($sheet->getRowIterator() as $row) {
                $cells = [];
                foreach ($row->getCellIterator() as $cell) {
                    $cells[] = $cell->getValue();
                }
                
                // Bỏ qua dòng trống
                if (empty(array_filter($cells))) continue;
                
                if ($isHeader) {
                    $headers  = array_map(fn($h) => strtolower(trim((string)$h)), $cells);
                    $isHeader = false;
                    continue;
                }
                
                $rows[] = array_combine($headers, array_pad($cells, count($headers), ''));
            }
        }

        return $rows;
    }
}

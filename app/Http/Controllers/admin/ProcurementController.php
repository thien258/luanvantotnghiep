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
        $this->middleware('auth');
    }

    // Danh sách tất cả yêu cầu thu mua
    public function index()
    {
        $requests = ProcurementRequest::with('items.product')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.procurement.index', compact('requests'));
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
                'note'         => 'Tồn kho hiện tại: ' . $product->quantity,
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

        if (empty($rows)) {
            return back()->with('error', 'File không có dữ liệu hoặc sai định dạng.');
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

        foreach ($rows as $row) {
            $productName = trim($row['title'] ?? $row['product_name'] ?? '');
            $unitPrice   = (float) str_replace([',', ' '], '', $row['price'] ?? $row['unit_price'] ?? 0);

            if (empty($productName) || $unitPrice <= 0) continue;

            $product = Product::whereRaw('LOWER(title) = ?', [strtolower($productName)])->first();

            SupplierOfferItem::create([
                'offer_id'     => $offer->id,
                'product_id'   => $product?->id,
                'product_name' => $productName,
                'unit_price'   => $unitPrice,
                'note'         => trim($row['note'] ?? $row['decription'] ?? ''),
            ]);
        }

        return redirect()->route('admin.procurement.show', $id)
            ->with('success', 'Đã đọc file báo giá ' . $offerCode . ' từ ' . $offer->manufacturer->name . '.');
    }

    // Đọc file Excel/CSV → trả về mảng rows với key = tên cột
    private function readOfferFile($file): array
    {
        $ext     = strtolower($file->getClientOriginalExtension());
        $path    = $file->getRealPath();
        $rows    = [];
        $headers = [];

        if ($ext === 'csv') {
            $handle   = fopen($path, 'r');
            $isHeader = true;
            while (($line = fgetcsv($handle)) !== false) {
                if ($isHeader) {
                    $headers  = array_map(fn($h) => strtolower(trim($h)), $line);
                    $isHeader = false;
                    continue;
                }
                if (!empty(array_filter($line))) {
                    $rows[] = array_combine($headers, array_pad($line, count($headers), ''));
                }
            }
            fclose($handle);
        } else {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
            $sheet       = $spreadsheet->getActiveSheet();
            $isHeader    = true;
            foreach ($sheet->getRowIterator() as $row) {
                $cells = [];
                foreach ($row->getCellIterator() as $cell) {
                    $cells[] = $cell->getValue();
                }
                if ($isHeader) {
                    $headers  = array_map(fn($h) => strtolower(trim((string)$h)), $cells);
                    $isHeader = false;
                    continue;
                }
                if (!empty(array_filter($cells))) {
                    $rows[] = array_combine($headers, array_pad($cells, count($headers), ''));
                }
            }
        }

        return $rows;
    }
}

<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SupplierOffer;
use App\Models\SupplierOfferItem;
use App\Models\User;
use App\Models\Product;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * SupplierOfferController — Quản lý báo giá từ Nhà Sản Xuất (NSX).
 *
 * Luồng:
 *   1. index()  — Admin xem danh sách báo giá + form upload file
 *   2. upload() — Admin upload file Excel/CSV của NSX
 *                 → đọc file → tạo SupplierOffer + SupplierOfferItem
 *                 → redirect sang show() để xem chi tiết
 *   3. show()   — Admin xem bảng SP, tick chọn + điền số lượng → bấm "Đặt hàng"
 *                 (form POST sang PurchaseOrderController@store)
 *   4. reject() — Admin từ chối báo giá → status = 'rejected'
 */
class SupplierOfferController extends Controller
{
    // =========================================================================
    // INDEX — Danh sách báo giá + form upload trên cùng 1 trang
    // =========================================================================
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $role = auth()->user()->role;
            if (!in_array($role, ['admin', 'manufacturer', 'director', 'root'])) {
                abort(403);
            }
            return $next($request);
        });
    }
    public function index()
    {
        $user = auth()->user();
        $query = SupplierOffer::with('manufacturer')
            ->orderByRaw("FIELD(status, 'submitted', 'accepted', 'rejected', 'draft')")
            ->orderBy('created_at', 'desc');

        // manufacturer chỉ thấy báo giá của mình
        if ($user->role === 'manufacturer') {
            if (!$user->id) {
                return view('admin.supplier-offer.index', [
                    'offers'        => collect(),
                    'manufacturers' => collect(),
                ]);
            }
            $query->where('manufacturer_id', $user->id);
        }

        $offers = $query->paginate(15);
        $manufacturers = User::where('role', 'manufacturer')->orderBy('name')->get();

        return view('admin.supplier-offer.index', compact('offers', 'manufacturers'));
    }

    // =========================================================================
    // UPLOAD — Nhận file Excel/CSV từ NSX → tạo báo giá trong DB
    // =========================================================================

    public function upload(Request $request)
    {
        $request->validate([
            'manufacturer_id' => 'required|exists:users,id',
            'file'            => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ], [
            'manufacturer_id.required' => 'Vui lòng chọn nhà sản xuất.',
            'file.required'            => 'Vui lòng chọn file Excel.',
            'file.mimes'               => 'Chỉ chấp nhận file .xlsx, .xls, .csv.',
        ]);

        $file = $request->file('file');

        // Đọc file → trả về mảng rows (mỗi row là 1 dòng SP, key = tên cột header)
        $rows = $this->readFile($file);

        if (empty($rows)) {
            return back()->with('error', 'File không có dữ liệu hoặc sai định dạng.');
        }

        // Sinh offer_code tự động: OFR-YYYYMMDD-001, 002, ...
        // Đếm số báo giá đã tạo trong ngày hôm nay để tăng số thứ tự
        $count = SupplierOffer::whereDate('created_at', today())->count() + 1;
        $offerCode = 'OFR-' . now()->format('Ymd') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

        // Tạo đầu phiếu báo giá
        $offer = SupplierOffer::create([
            'manufacturer_id' => $request->manufacturer_id,
            'offer_code'      => $offerCode,
            'note'            => $request->note,
            'status'          => 'submitted', // mặc định là đã gửi, chờ admin xem
            'submitted_at'    => now(),
        ]);

        // Lưu từng dòng sản phẩm từ file vào supplier_offer_items
        foreach ($rows as $row) {
            // Đọc tên SP từ cột 'title' hoặc 'product_name' (linh hoạt với nhiều format file)
            $productName = trim($row['title'] ?? $row['product_name'] ?? '');

            // Đọc giá chào từ cột 'price' hoặc 'unit_price', xóa dấu phẩy/khoảng trắng
            $unitPrice = (float) str_replace([',', ' '], '', $row['price'] ?? $row['unit_price'] ?? 0);

            // Bỏ qua dòng trống hoặc giá = 0
            if (empty($productName) || $unitPrice <= 0) continue;

            // Tìm SP trong hệ thống theo tên (không phân biệt hoa thường)
            // Nếu tìm thấy → lưu product_id để liên kết, nếu không → để null
            $product = Product::whereRaw('LOWER(title) = ?', [strtolower($productName)])->first();

            // Đọc ghi chú từ cột 'decription' (typo trong file gốc) hoặc 'note'
            $note = trim($row['decription'] ?? $row['description'] ?? $row['note'] ?? '');

            SupplierOfferItem::create([
                'offer_id'     => $offer->id,
                'product_id'   => $product?->id, // null nếu SP chưa có trong hệ thống
                'product_name' => $productName,
                'unit_price'   => $unitPrice,
                'note'         => $note,
            ]);
        }

        // Redirect sang trang chi tiết để admin xem + tick SP đặt hàng
        return redirect()->route('admin.supplier-offers.show', $offer->id)
            ->with('success', 'Đã đọc file và tạo báo giá ' . $offerCode . '. Chọn sản phẩm cần đặt hàng bên dưới.');
    }

    // =========================================================================
    // SHOW — Xem chi tiết báo giá, admin tick + điền qty → đặt hàng
    // =========================================================================

    public function show(string $id)
    {
        $offer = SupplierOffer::with([
            'manufacturer',
            'items.product.category',
            'items.product.brand',
            'items.product.concentration',
            'purchaseOrder'
        ])->findOrFail($id);

        // manufacturer chỉ xem offer của mình
        if (auth()->user()->role === 'manufacturer') {
            if ($offer->manufacturer_id !== auth()->id()) {
                abort(403, 'Bạn không có quyền xem báo giá này.');
            }
        }

        return view('admin.supplier-offer.show', compact('offer'));
    }

    // =========================================================================
    // REJECT — Từ chối báo giá
    // =========================================================================

    public function reject(string $id)
    {
        $offer = SupplierOffer::findOrFail($id);
        $offer->update(['status' => 'rejected']);

        return redirect()->route('admin.supplier-offers.index')
            ->with('success', 'Đã từ chối báo giá ' . $offer->offer_code . '.');
    }

    // =========================================================================
    // PRIVATE — Đọc file Excel hoặc CSV
    // =========================================================================

    /**
     * Đọc file và trả về mảng các row, mỗi row là associative array theo tên cột header.
     * Ví dụ: [['title' => 'Chanel No5', 'price' => '2500000', ...], ...]
     *
     * Hỗ trợ: .csv, .xlsx, .xls
     * Dòng đầu tiên luôn là header (tên cột) → bỏ qua, dùng làm key.
     */
    private function readFile($file): array
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
                    // Dòng đầu tiên là tên cột → chuẩn hóa thành lowercase, trim khoảng trắng
                    $headers  = array_map(fn($h) => strtolower(trim($h)), $line);
                    $isHeader = false;
                    continue;
                }
                // Bỏ qua dòng hoàn toàn trống
                if (!empty(array_filter($line))) {
                    // Ghép tên cột với giá trị, padding nếu dòng thiếu cột
                    $rows[] = array_combine($headers, array_pad($line, count($headers), ''));
                }
            }
            fclose($handle);
        } else {
            // xlsx / xls dùng PhpSpreadsheet để đọc
            $spreadsheet = IOFactory::load($path);
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

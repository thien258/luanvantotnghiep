<?php

namespace App\Http\Controllers;

use App\Models\ProcurementRequest;
use App\Models\ManuFacturer;
use App\Models\SupplierOffer;
use App\Models\SupplierOfferItem;
use Illuminate\Http\Request;

/**
 * ProcurementPublicController — Trang cho NSX xem yêu cầu nhập hàng và chào giá.
 *
 * Luồng:
 *   NSX vào /procurement → thấy danh sách yêu cầu đang mở
 *   → bấm "Chào giá" → điền giá từng SP → submit
 *   → tạo SupplierOffer (có request_id liên kết) → admin thấy trong trang chi tiết yêu cầu
 */
class ProcurementPublicController extends Controller
{
    /**
     * Danh sách yêu cầu đang mở — tất cả NSX đều thấy.
     */
    public function index()
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, ProcurementRequest> $requests */
        $requests = ProcurementRequest::with('items.product')
            ->where('status', 'open')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('procurement.index', compact('requests'));
    }

    /**
     * Chi tiết 1 yêu cầu + form chào giá.
     */
    public function show(string $id)
    {
        /** @var ProcurementRequest $procRequest */
        $procRequest = ProcurementRequest::with([
            'items.product.brand',
            'items.product.category',
            'items.product.concentration',
        ])->where('status', 'open')->findOrFail($id);

        /** @var \Illuminate\Database\Eloquent\Collection<int, ManuFacturer> $manufacturers */
        $manufacturers = ManuFacturer::orderBy('name')->get();

        return view('procurement.show', compact('procRequest', 'manufacturers'));
    }

    /**
     * NSX submit form chào giá → tạo SupplierOffer liên kết với yêu cầu.
     */
    public function submitOffer(Request $request, string $id)
    {
        /** @var ProcurementRequest $procRequest */
        $procRequest = ProcurementRequest::where('status', 'open')->findOrFail($id);

        $request->validate([
            'manufacturer_id'           => 'required|exists:manufacturers,id',
            'items'                     => 'required|array|min:1',
            'items.*.product_id'        => 'nullable|exists:products,id',
            'items.*.product_name'      => 'required|string',
            'items.*.unit_price'        => 'required|numeric|min:1',
        ], [
            'manufacturer_id.required'      => 'Vui lòng chọn nhà sản xuất.',
            'items.*.unit_price.required'   => 'Vui lòng nhập giá chào.',
            'items.*.unit_price.min'        => 'Giá chào phải lớn hơn 0.',
        ]);

        // Sinh offer_code
        $count     = SupplierOffer::whereDate('created_at', today())->count() + 1;
        $offerCode = 'OFR-' . now()->format('Ymd') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

        // Tạo SupplierOffer liên kết với yêu cầu thu mua
        $offer = SupplierOffer::create([
            'manufacturer_id' => $request->manufacturer_id,
            'request_id'      => $procRequest->id, // liên kết về yêu cầu
            'offer_code'      => $offerCode,
            'note'            => $request->input('note'),
            'status'          => 'submitted',
            'submitted_at'    => now(),
        ]);

        // Tạo từng dòng SP chào giá
        foreach ($request->items as $item) {
            SupplierOfferItem::create([
                'offer_id'     => $offer->id,
                'product_id'   => $item['product_id'] ?? null,
                'product_name' => $item['product_name'],
                'unit_price'   => $item['unit_price'],
                'note'         => $item['note'] ?? null,
            ]);
        }

        return redirect()->route('procurement.show', $id)
            ->with('success', 'Đã gửi báo giá ' . $offerCode . ' thành công! Admin sẽ xem xét và liên hệ.');
    }
}

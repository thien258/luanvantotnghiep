<?php

// =========================================================================
// IMPORTS — Khai báo các Controller được dùng trong file này
// =========================================================================

use App\Http\Controllers\admin\BrandController;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\admin\ConcentrationController;
use App\Http\Controllers\admin\FooterController;
use App\Http\Controllers\admin\TitleController;
use App\Http\Controllers\admin\ProductController;
use App\Http\Controllers\admin\ContactAdminController;
use App\Http\Controllers\admin\FestivalController;
use App\Http\Controllers\admin\OrderAdminController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\admin\ManufacturerController;
use App\Http\Controllers\admin\SupplierOfferController;   // Báo giá NSX
use App\Http\Controllers\admin\PurchaseOrderController;   // Đơn đặt hàng NSX
use App\Http\Controllers\admin\ProcurementController;     // Yêu cầu thu mua công khai
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductShowController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserAddressController;
use App\Http\Controllers\admin\WarehouseController;


// =========================================================================
// KHU VỰC 0: PUBLIC ROUTES — Không cần đăng nhập
// (PayOS callback, xác nhận giao hàng qua QR)
// =========================================================================

// Khách quét QR trên thùng hàng → xác nhận đã nhận
Route::get('/delivery/confirm/{code}',  [OrderController::class, 'confirmDelivery'])->name('order.confirm-delivery');
Route::post('/delivery/confirm/{code}', [OrderController::class, 'submitConfirmDelivery'])->name('order.submit-confirm-delivery');

// PayOS callback sau khi thanh toán xong → trang thông báo thành công
Route::get('/order/payos-success', [OrderController::class, 'payosSuccess'])->name('payos.success');

// PayOS hủy → xóa đơn + hoàn giỏ hàng
Route::get('/order/{id}/payos-cancel', [OrderController::class, 'payosCancel'])->name('order.payos-cancel');

// PayOS webhook → nhận thông báo thanh toán, cập nhật status 0→1
Route::post('/api/payos-webhook', [OrderController::class, 'payosWebhook'])->name('payos.webhook');


// =========================================================================
// KHU VỰC 1: FRONTEND — Không cần đăng nhập
// (Trang chủ, danh sách SP, chi tiết SP, tìm kiếm)
// =========================================================================

Route::get('/',                   [HomeController::class, 'index'])->name('welcome');
Route::get('/gucci-demo',         [HomeController::class, 'showGucciProduct'])->name('gucci.demo');
Route::get('/manufacturer-demo',  [HomeController::class, 'showManufacturerProducts'])->name('manufacturer.demo');
Route::get('/search',             [HomeController::class, 'search'])->name('home.search');
Route::get('/search-suggest',     [HomeController::class, 'suggest'])->name('search.suggest');    // AJAX gợi ý
Route::get('/show-products',      [ProductShowController::class, 'showProducts'])->name('show_products');
Route::get('/product/{id}',       [HomeController::class, 'single_product'])->name('single_product');
Route::get('/category_product/{category}', [HomeController::class, 'category_product'])->name('category_product');
Route::get('/brand_product/{brand}',       [HomeController::class, 'brand_product'])->name('brand_product');
Route::get('/festival_product/{festival}', [HomeController::class, 'festival_product'])->name('festival_product');

// Trang đăng ký (view riêng, không dùng auth scaffolding mặc định)
Route::get('/register', fn() => view('register'))->name('register');

// Override trang login mặc định → dùng view login.blade.php đẹp của project
Route::get('/login', fn() => view('login'))->name('login');

// Đăng nhập / đăng ký / xác minh email (tự generate bởi Auth::routes)
Auth::routes(['verify' => true]);

// Đăng xuất qua GET (override route mặc định POST của Laravel)
Route::get('logout', [HomeController::class, 'logout'])->name('logout');


// =========================================================================
// KHU VỰC 2: USER — Cần đăng nhập
// (Giỏ hàng, thanh toán, đơn hàng, địa chỉ, hồ sơ)
// =========================================================================

Route::middleware('auth')->group(function () {

    // Hồ sơ cá nhân
    Route::resource('profile', ProfileController::class);

    // Liên hệ
    Route::resource('contact', ContactController::class);

    // Giỏ hàng
    Route::resource('carts', CartController::class);

    // Bình luận sản phẩm
    Route::resource('comments', App\Http\Controllers\CommentController::class);

    // ── Luồng đặt hàng ───────────────────────────────────────────────

    // Bước 1: Chọn SP trong giỏ → lưu IDs vào session → redirect sang trang thanh toán
    Route::post('/order/checkout', [OrderController::class, 'checkout'])->name('order.checkout');

    // Bước 2: Submit form thanh toán → tạo Order + redirect PayOS hoặc COD
    Route::post('/order/place', [OrderController::class, 'placeOrder'])->name('order.place');

    // Lịch sử đơn hàng
    Route::get('/order/history',      [OrderController::class, 'history'])->name('order.history');
    Route::get('/order/history/{id}', [OrderController::class, 'historyDetail'])->name('order.history.detail');

    // Trang QR VietQR (fallback khi PayOS chưa config)
    Route::get('/order/{id}/payment', [OrderController::class, 'paymentForm'])->name('order.payment');

    // Khách xác nhận đã chuyển khoản thủ công
    Route::post('/order/{id}/confirm-paid', [OrderController::class, 'confirmPaid'])->name('order.confirmPaid');

    // Khách hủy đơn (chỉ được hủy khi status=1, chưa xuất kho)
    Route::post('/order/{id}/cancel', [OrderController::class, 'cancelOrder'])->name('order.cancel');

    // Khách yêu cầu hoàn hàng (status=4 → 5)
    Route::post('/order/history/return/{id}', [OrderController::class, 'customerReturn'])->name('order.customer-return');

    // Resource route cho Order (index, show, ...)
    Route::resource('order', OrderController::class);

    // ── Sổ địa chỉ nhận hàng (AJAX) ─────────────────────────────────
    Route::resource('addresses', UserAddressController::class);
    Route::patch('/addresses/{id}/default', [UserAddressController::class, 'setDefault'])->name('addresses.setDefault');
});


// =========================================================================
// KHU VỰC 3: ADMIN — Prefix /admin, tên route bắt đầu bằng admin.
// Kiểm tra role='admin' thực hiện trong AdminController::__construct()
// =========================================================================

Route::prefix('admin')->name('admin.')->middleware(['auth','role:admin,warehouse,manufacturer'])->group(function () {

    // Dashboard — trang tổng quan thống kê
    Route::get('/', [App\Http\Controllers\admin\AdminController::class, 'index'])->name('dashboard');

    // ── Danh mục & Sản phẩm ─────────────────────────────────────────
    Route::resource('category',      CategoryController::class);
    Route::resource('brand',         BrandController::class);
    Route::resource('concentration', ConcentrationController::class);
    Route::resource('festival',      FestivalController::class);

    // Chọn SP cho festival (many-to-many qua checkbox)
    Route::get('/festival/{festival}/products',       [FestivalController::class, 'selectProducts'])->name('festival.selectProducts');
    Route::post('/festival/{festival}/products/update', [FestivalController::class, 'updateProducts'])->name('festival.updateProducts');

    // AJAX gợi ý sản phẩm (dùng trong form tìm kiếm admin)
    Route::get('/product-suggest', [ProductController::class, 'suggest'])->name('product.suggest');

    /*
     * QUAN TRỌNG: Route kho hàng PHẢI đặt TRƯỚC Route::resource('product', ...)
     * Vì nếu để sau, Laravel sẽ hiểu /product/warehouse là show($id='warehouse')
     * dẫn đến lỗi 404 hoặc gọi sai method.
     */
    Route::get('product/warehouse',        [WarehouseController::class, 'index'])->name('product.warehouse.index');
    Route::post('product/warehouse/store', [WarehouseController::class, 'store'])->name('product.warehouse.store');
    Route::post('product/warehouse/attach-festival', [WarehouseController::class, 'attachToFestival'])->name('product.warehouse.attach-festival');

    // Tạo yêu cầu nhập hàng từ modal trang sản phẩm
    Route::post('product/order-request', [ProductController::class, 'createOrderRequest'])->name('product.createOrderRequest');

    // CRUD sản phẩm (đứng SAU các route lẻ để tránh conflict)
    Route::resource('product', ProductController::class);

    // Nhập kho qua file (nhân viên upload → admin duyệt)
    Route::get('warehouse/imports',                     [WarehouseController::class, 'importList'])->name('warehouse.imports');
    Route::post('warehouse/imports/upload',              [WarehouseController::class, 'importUpload'])->name('warehouse.imports.upload');
    Route::get('warehouse/imports/{import}',             [WarehouseController::class, 'importShow'])->name('warehouse.imports.show');
    Route::post('warehouse/imports/{import}/approve',    [WarehouseController::class, 'importApprove'])->name('warehouse.imports.approve');
    Route::post('warehouse/imports/{import}/reject',     [WarehouseController::class, 'importReject'])->name('warehouse.imports.reject');

    // ── Đơn hàng khách ──────────────────────────────────────────────

    /*
     * QUAN TRỌNG: Route lẻ xử lý action phải đặt TRƯỚC Route::resource('orders', ...)
     * Nếu để sau, /orders/{id}/update-status sẽ bị bắt nhầm bởi resource route show($id)
     */
    Route::post('/orders/{id}/update-status', [OrderAdminController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::get('/orders/{order}/return',      [OrderAdminController::class, 'returnOrder'])->name('orders.return');
    Route::post('/orders/{order}/return',     [OrderAdminController::class, 'processReturn'])->name('orders.processReturn');
    Route::get('/orders-damaged',             [OrderAdminController::class, 'damagedList'])->name('orders.damaged');

    // Resource CRUD đơn hàng (index, show, ...)
    Route::resource('orders', OrderAdminController::class);

    // ── Nhà Sản Xuất (NSX) ──────────────────────────────────────────

    // Tạo tài khoản user cho NSX (gắn role = manufacturer)
    Route::post('manufacturer/{id}/create-account', [ManufacturerController::class, 'createAccount'])->name('manufacturer.create-account');
    // Quản lý danh sách NSX (CRUD cơ bản)
    Route::resource('manufacturer', ManufacturerController::class);

    // Báo giá NSX: upload file → xem → tick SP → đặt hàng
    Route::resource('supplier-offers', SupplierOfferController::class)->only(['index', 'show']);
    Route::post('supplier-offers/upload',       [SupplierOfferController::class, 'upload'])->name('supplier-offers.upload');
    Route::post('supplier-offers/{id}/reject',  [SupplierOfferController::class, 'reject'])->name('supplier-offers.reject');

    /*
     * Luồng yêu cầu thu mua:
     *   Admin tạo ProcurementRequest (open) → NSX xem và upload file chào giá
     *   → Admin tạo PurchaseOrder từ báo giá → NSX xác nhận → nhận hàng → nhập kho
     */
    Route::resource('procurement', ProcurementController::class)->only(['index', 'store', 'show']);
    Route::post('procurement/{id}/close',        [ProcurementController::class, 'close'])->name('procurement.close');
    Route::get('procurement/{id}/export-template', [ProcurementController::class, 'exportTemplate'])->name('procurement.export-template');
    Route::post('procurement/{id}/upload-offer', [ProcurementController::class, 'uploadOffer'])->name('procurement.upload-offer');
    Route::resource('purchase-orders', PurchaseOrderController::class)->only(['index', 'store', 'show']);
    Route::post('purchase-orders/{id}/status',   [PurchaseOrderController::class, 'updateStatus'])->name('purchase-orders.status');
    Route::post('purchase-orders/{id}/receive',  [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
    Route::get('purchase-orders/{id}/export-csv',   [PurchaseOrderController::class, 'exportCsv'])->name('purchase-orders.export-csv');
    Route::get('purchase-orders/{id}/export-excel', [PurchaseOrderController::class, 'exportExcel'])->name('purchase-orders.export-excel');

    // ── Người dùng & Nội dung ───────────────────────────────────────
    Route::resource('user',    UserController::class);
    Route::resource('footer',  FooterController::class);
    Route::resource('title',   TitleController::class);
    Route::resource('contacts', ContactAdminController::class);
});

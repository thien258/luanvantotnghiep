<?php

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
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductShowController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use \App\Http\Controllers\UserAddressController;

// =========================================================================
// ROUTE XÁC NHẬN NHẬN HÀNG (Public — khách quét QR không cần đăng nhập)
// =========================================================================
Route::get('/delivery/confirm/{code}', [OrderController::class, 'confirmDelivery'])->name('order.confirm-delivery');
Route::post('/delivery/confirm/{code}', [OrderController::class, 'submitConfirmDelivery'])->name('order.submit-confirm-delivery');

// PayOS callback — public, không cần đăng nhập
Route::get('/order/payos-success', [OrderController::class, 'payosSuccess'])->name('payos.success');
Route::get('/order/{id}/payos-cancel', [OrderController::class, 'payosCancel'])->name('order.payos-cancel');
Route::post('/api/payos-webhook', [OrderController::class, 'payosWebhook'])->name('payos.webhook');

// =========================================================================
// KHU VỰC 1: CÁC ROUTE KHÔNG CẦN ĐĂNG NHẬP (GIAO DIỆN CHỦ, SẢN PHẨM, SEARCH)
// =========================================================================
Route::get('/', [HomeController::class, 'index'])->name('welcome');
Route::get('/search', [HomeController::class, 'search'])->name('home.search');
Route::get('/search-suggest', [HomeController::class, 'suggest'])->name('search.suggest');
Route::get('/show-products', [ProductShowController::class, 'showProducts'])->name('show_products');
Route::get('/product/{id}', [HomeController::class, 'single_product'])->name('single_product');

Route::get('/category_product/{category}', [HomeController::class, 'category_product'])->name('category_product');
Route::get('/brand_product/{brand}', [HomeController::class, 'brand_product'])->name('brand_product');
Route::get('/festival_product/{festival}', [HomeController::class, 'festival_product'])->name('festival_product');

Route::get('/register', function () {
    return view('register');
})->name('register');

Auth::routes(['verify' => true]);
Route::get('logout', [HomeController::class, 'logout'])->name('logout');


// =========================================================================
// KHU VỰC 2: CÁC ROUTE DÀNH CHO USER ĐÃ ĐĂNG NHẬP (GIỎ HÀNG, THANH TOÁN, ĐỊA CHỈ)
// =========================================================================
Route::middleware('auth')->group(function () {
    Route::resource('profile', ProfileController::class);
    Route::resource('contact', ContactController::class);
    Route::resource('carts', CartController::class);
    Route::resource('comments', App\Http\Controllers\CommentController::class);

    // --- CỤM XỬ LÝ ĐƠN HÀNG CỦA KHÁCH KHÁCH HÀNG ---
    Route::post('/order/checkout', [OrderController::class, 'checkout'])->name('order.checkout');
    Route::post('/order/place', [OrderController::class, 'placeOrder'])->name('order.place');

    Route::get('/order/history', [OrderController::class, 'history'])->name('order.history');
    Route::get('/order/history/{id}', [OrderController::class, 'historyDetail'])->name('order.history.detail');

    Route::get('/order/{id}/payment', [OrderController::class, 'paymentForm'])->name('order.payment');
    Route::post('/order/{id}/confirm-paid', [OrderController::class, 'confirmPaid'])->name('order.confirmPaid');
    Route::post('/order/{id}/cancel', [OrderController::class, 'cancelOrder'])->name('order.cancel');
    Route::post('/order/history/return/{id}', [OrderController::class, 'customerReturn'])->name('order.customer-return');
    Route::resource('order', OrderController::class);

    // Sổ địa chỉ nhận hàng (AJAX)
    Route::resource('addresses', UserAddressController::class);
    Route::patch('/addresses/{id}/default', [UserAddressController::class, 'setDefault'])->name('addresses.setDefault');
});


// =========================================================================
// KHU VỰC 3: CÁC ROUTE QUẢN TRỊ ADMIN (PREFIX /ADMIN)
// =========================================================================
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [App\Http\Controllers\admin\AdminController::class, 'index'])->name('dashboard');

    Route::resource('category', CategoryController::class);
    Route::resource('brand', BrandController::class);
    Route::resource('concentration', ConcentrationController::class);
    Route::resource('festival', FestivalController::class);
    Route::resource('contacts', ContactAdminController::class);
    // Route hiển thị giao diện 3 Tab kho và đối soát bán chậm
    Route::get('product/warehouse', [\App\Http\Controllers\admin\WarehouseController::class, 'index'])->name('product.warehouse.index');
    Route::post('product/warehouse/store', [\App\Http\Controllers\admin\WarehouseController::class, 'store'])->name('product.warehouse.store');

    // NHÂN VIÊN KHO: Upload file chờ admin duyệt
    Route::get('warehouse/imports', [\App\Http\Controllers\admin\WarehouseController::class, 'importList'])->name('warehouse.imports');
    Route::post('warehouse/imports/upload', [\App\Http\Controllers\admin\WarehouseController::class, 'importUpload'])->name('warehouse.imports.upload');

    // ADMIN: Xem chi tiết + duyệt
    Route::get('warehouse/imports/{import}', [\App\Http\Controllers\admin\WarehouseController::class, 'importShow'])->name('warehouse.imports.show');
    Route::post('warehouse/imports/{import}/approve', [\App\Http\Controllers\admin\WarehouseController::class, 'importApprove'])->name('warehouse.imports.approve');
    Route::post('warehouse/imports/{import}/reject', [\App\Http\Controllers\admin\WarehouseController::class, 'importReject'])->name('warehouse.imports.reject');


    // ĐÃ CHUYỂN VỀ ĐÂY: Route lẻ xử lý cập nhật trạng thái nằm ĐÚNG KHU VỰC Admin và ĐỨNG TRÊN Resource
    Route::post('/orders/{id}/update-status', [OrderAdminController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::get('/orders/{order}/return', [OrderAdminController::class, 'returnOrder'])->name('orders.return');
    Route::post('/orders/{order}/return', [OrderAdminController::class, 'processReturn'])->name('orders.processReturn');
    Route::get('/orders-damaged', [OrderAdminController::class, 'damagedList'])->name('orders.damaged');
    Route::resource('orders', OrderAdminController::class);
    Route::get('/product-suggest', [ProductController::class, 'suggest'])->name('product.suggest');
    Route::resource('product', ProductController::class);
    Route::resource('user', UserController::class);
    Route::resource('footer', FooterController::class);
    Route::resource('title', TitleController::class);
    Route::get('/festival/{festival}/products', [FestivalController::class, 'selectProducts'])->name('festival.selectProducts');
    Route::post('/festival/{festival}/products/update', [FestivalController::class, 'updateProducts'])->name('festival.updateProducts');
});

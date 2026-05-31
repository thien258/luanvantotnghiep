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

// =========================================================================
// KHU VỰC 1: CÁC ROUTE KHÔNG CẦN ĐĂNG NHẬP (GIAO DIỆN CHỦ, SẢN PHẨM, SEARCH)
// =========================================================================
Route::get('/', [HomeController::class, 'index'])->name('welcome');
Route::get('/search', [HomeController::class, 'search'])->name('home.search');
Route::get('/search-suggest', [HomeController::class, 'suggest'])->name('search.suggest');
Route::get('/show-products', [ProductShowController::class, 'showProducts'])->name('show_products');
Route::get('/product/{id}', [HomeController::class, 'single_product'])->name('single_product');

// Lọc sản phẩm theo danh mục/thương hiệu
Route::get('/category_product/{category}', [HomeController::class, 'category_product'])->name('category_product');
Route::get('/brand_product/{brand}', [HomeController::class, 'brand_product'])->name('brand_product');
Route::get('/festival_product/{festival}', [HomeController::class, 'festival_product'])->name('festival_product');

// Đăng ký, đăng nhập hệ thống
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

    // --- CỤM XỬ LÝ ĐƠN HÀNG (QUY TẮC: ROUTE LẺ ĐẶT LÊN TRÊN RESOURCE) ---
    Route::post('/order/checkout', [OrderController::class, 'checkout'])->name('order.checkout');
    Route::post('/order/place', [OrderController::class, 'placeOrder'])->name('order.place');
    
    // Đã đảo thứ tự: Các route lịch sử và thanh toán VietQR đưa lên trước resource
    Route::get('/order/history', [OrderController::class, 'history'])->name('order.history');
    
    // Đã sửa 'orderDetail' thành 'historyDetail' cho đồng bộ khớp với file Controller của bạn
    Route::get('/order/history/{id}', [OrderController::class, 'historyDetail'])->name('order.history.detail');
    
    Route::get('/order/{id}/payment', [OrderController::class, 'paymentForm'])->name('order.payment');
    Route::post('/order/{id}/confirm-paid', [OrderController::class, 'confirmPaid'])->name('order.confirmPaid');
    Route::post('/order/{id}/cancel', [OrderController::class, 'cancelOrder'])->name('order.cancel');

    // Nằm dưới cùng cụm order để tránh nuốt mất các URL dạng chữ (/history)
    Route::resource('order', OrderController::class);

    // Sổ địa chỉ nhận hàng (AJAX)
    Route::resource('addresses', \App\Http\Controllers\UserAddressController::class);
    Route::patch('/addresses/{id}/default', [\App\Http\Controllers\UserAddressController::class, 'setDefault'])->name('addresses.setDefault');
});


// =========================================================================
// KHU VỰC 3: CÁC ROUTE QUẢN TRỊ ADMIN (PREFIX /ADMIN)
// =========================================================================
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [App\Http\Controllers\admin\AdminController::class, 'index'])->name('dashboard');
    
    // Quản lý các tài nguyên danh mục, thương hiệu, sản phẩm
    Route::resource('category', CategoryController::class);
    Route::resource('brand', BrandController::class);
    Route::resource('concentration', ConcentrationController::class);
    Route::resource('festival', FestivalController::class);
    Route::resource('contacts', ContactAdminController::class);
    Route::resource('orders', OrderAdminController::class);
    Route::resource('user', UserController::class);
    Route::resource('footer', FooterController::class);
    Route::resource('title', TitleController::class);
    
    // Các route bổ sung phục vụ tính năng Suggest sản phẩm và Festival
    Route::get('/product-suggest', [ProductController::class, 'suggest'])->name('product.suggest');
    Route::resource('product', ProductController::class);
    Route::get('/festival/{festival}/products', [FestivalController::class, 'selectProducts'])->name('festival.selectProducts');
    Route::post('/festival/{festival}/products/update', [FestivalController::class, 'updateProducts'])->name('festival.updateProducts');
});
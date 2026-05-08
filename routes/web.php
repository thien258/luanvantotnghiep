<?php
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\admin\FooterController;
use App\Http\Controllers\admin\TitleController;
use App\Http\Controllers\admin\ProductController;
use App\Http\Controllers\admin\ContactAdminController;
use App\Http\Controllers\admin\OrderAdminController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoveController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductShowController;
use App\Http\Controllers\admin\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

        Route::prefix('admin')->name('admin.')->group(function () {
            Route::resource('category', CategoryController::class);

            Route::get('/', [App\Http\Controllers\admin\AdminController::class, 'index'])
                ->name('dashboard');

            Route::resource('product',ProductController::class);
                Route::resource('contacts',ContactAdminController::class);
                Route::resource('orders',OrderAdminController::class);
                Route::resource('user',UserController::class);
                Route::resource('footer',FooterController::class);
                Route::resource('title',TitleController::class);
        });
   Route::resource('contact', ContactController::class);
   Route::resource('loves',LoveController::class);
   Route::resource('order', OrderController::class)->middleware('auth');
   Route::resource('show-products', ProductShowController::class);
Route::resource('comments', App\Http\Controllers\CommentController::class);
Route::get('/', [App\Http\Controllers\HomeController::class,'index'])->name('welcome');
Route::get('/category_product/{category}', [App\Http\Controllers\HomeController::class,'category_product'])->name('category_product');
Route::get('/category_product/single_product/{category}', [App\Http\Controllers\HomeController::class,'single_product'])->name('single_product');


Route::get('/register', function () {
    return view('register');
})->name('register');



Auth::routes();
Route::get('logout',[HomeController::class,'logout'])->name('logout');

Route::post('/logout', [HomeController::class, 'logout'])->name('logouts');
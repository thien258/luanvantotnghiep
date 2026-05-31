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
    use App\Http\Controllers\ContactController;
    use App\Http\Controllers\HomeController;
    use App\Http\Controllers\CartController;

    use App\Http\Controllers\OrderController;
    use App\Http\Controllers\ProductShowController;
    use App\Http\Controllers\admin\UserController;
    use App\Http\Controllers\Auth\ForgotPasswordController;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\Auth;
    use App\Http\Controllers\ProfileController;

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('category', CategoryController::class);
        Route::resource('brand', BrandController::class);
        Route::resource('concentration', ConcentrationController::class);
       
        Route::resource('festival', FestivalController::class);

        Route::get('/', [App\Http\Controllers\admin\AdminController::class, 'index'])
            ->name('dashboard');
        Route::get('/product-suggest', [App\Http\Controllers\admin\ProductController::class, 'suggest'])->name('product.suggest');
        Route::resource('product', ProductController::class);
        Route::resource('contacts', ContactAdminController::class);
        Route::resource('orders', OrderAdminController::class);
        Route::resource('user', UserController::class);
        Route::resource('footer', FooterController::class);
        Route::resource('title', TitleController::class);
        Route::get('/festival/{festival}/products', [FestivalController::class, 'selectProducts'])->name('festival.selectProducts');
        Route::post('/festival/{festival}/products/update', [FestivalController::class, 'updateProducts'])->name('festival.updateProducts');
    });
    Route::resource('contact', ContactController::class);
    Route::resource('carts', CartController::class);
 
    Route::get('/show-products', [ProductShowController::class, 'showProducts'])->name('show_products');
    Route::resource('comments', App\Http\Controllers\CommentController::class);
    Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('welcome');
    Route::get('/search', [App\Http\Controllers\HomeController::class, 'search'])->name('home.search');
    Route::get('/search-suggest', [App\Http\Controllers\HomeController::class, 'suggest'])->name('search.suggest');
    Route::get('/category_product/{category}', [App\Http\Controllers\HomeController::class, 'category_product'])->name('category_product');
    Route::get('/brand_product/{brand}', [App\Http\Controllers\HomeController::class, 'brand_product'])->name('brand_product');
    Route::get('/festival_product/{festival}', [App\Http\Controllers\HomeController::class, 'festival_product'])->name('festival_product');



    Route::get('/product/{id}', [App\Http\Controllers\HomeController::class, 'single_product'])->name('single_product');

    Route::get('/register', function () {
        return view('register');
    })->name('register');






    Auth::routes(['verify' => true]);
    Route::get('logout', [HomeController::class, 'logout'])->name('logout');
    Route::middleware('auth')->group(function () {
        Route::resource('profile', ProfileController::class);
        Route::post('/order/checkout', [OrderController::class, 'checkout'])->name('order.checkout');
        Route::post('/order/place', [OrderController::class, 'placeOrder'])->name('order.place');
        Route::resource('order', OrderController::class);
        Route::get('/order/{id}/payment', [OrderController::class, 'paymentForm'])->name('order.payment');
        Route::post('/order/{id}/confirm-paid', [OrderController::class, 'confirmPaid'])->name('order.confirmPaid');
        Route::post('/order/{id}/cancel', [OrderController::class, 'cancelOrder'])->name('order.cancel');

        // User addresses (AJAX) — resource + setDefault extra
        Route::resource('addresses', \App\Http\Controllers\UserAddressController::class);
        Route::patch('/addresses/{id}/default', [\App\Http\Controllers\UserAddressController::class, 'setDefault'])->name('addresses.setDefault');
    });

    // Route::post('/logout', [HomeController::class, 'logout'])->name('logouts');

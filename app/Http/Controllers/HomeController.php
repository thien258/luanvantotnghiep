<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Footer;
use App\Models\Title;
use App\Models\Concentration;
use App\Models\Festival;
use App\Models\Product;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * HomeController — Xử lý trang chủ và các trang danh sách sản phẩm frontend.
 *
 * Các tính năng:
 *   - Trang chủ: lọc theo nồng độ
 *   - Trang danh mục: lọc theo brand, concentration, giá; sort
 *   - Trang thương hiệu: lọc theo category, concentration, giá; sort
 *   - Trang chi tiết sản phẩm
 *   - Trang festival: lọc theo quy tắc discount cao nhất
 *   - Tìm kiếm + gợi ý tìm kiếm (AJAX)
 */
class HomeController extends Controller
{
    public function __construct()
    {
        // Chia sẻ danh sách category đang active sang TẤT CẢ views
        // để navbar luôn hiển thị đúng danh mục
        View::share('categories', Category::where('status', '1')->get());
    }

    // =========================================================================
    // TRANG CHỦ
    // =========================================================================

    public function index(Request $request)
    {
        // Lấy các nồng độ đang active để hiển thị filter
        $concentrations = Concentration::where("status", '1')->get();

        // Query sản phẩm đang bán, chỉ hiện SP thuộc danh mục, brand, concentration active
        $query = Product::where('status', 1)
            ->whereHas('category', function ($q) {
                $q->where('status', 1);
            })
            ->whereHas('brand', function ($q) {
                $q->where('status', 1);
            })
            ->whereHas('concentration', function ($q) {
                $q->where('status', 1);
            });

        // Filter theo nồng độ nếu người dùng chọn
        if ($request->has('concentrations') && is_array($request->concentrations)) {
            $query->whereIn('idConcentration', $request->concentrations);
        }

        $products = $query->get();
        $title    = Title::all();
        $footers  = Footer::all();

        // Lấy festivals active và lọc sản phẩm theo logic discount cao nhất
        $activeFestivals = $this->getFestivalsWithFilteredProducts();

        // Lấy brands để hiển thị logo
        $brands = Brand::where('status', 1)->take(12)->get();

        return view('index', compact("products", "footers", "title", "concentrations", "activeFestivals", "brands"));
    }

    /**
     * Lấy các festival đang active và lọc sản phẩm theo quy tắc:
     * - Nếu 1 sản phẩm thuộc nhiều festival, chỉ hiển thị ở festival có discount cao nhất
     * - Festival có discount thấp hơn sẽ ẩn sản phẩm đó
     * - Mỗi festival chỉ lấy tối đa 6 sản phẩm để hiển thị ở trang chủ
     */
    private function getFestivalsWithFilteredProducts()
    {
        $today = \Carbon\Carbon::today()->toDateString();
        
        // Lấy tất cả festival active, sắp xếp theo discount cao -> thấp
        $festivals = Festival::active()
            ->with(['products' => function($query) {
                $query->where('products.status', 1)->with('festivals');
            }])
            ->orderByDesc('discount')
            ->get();

        // Lọc sản phẩm cho từng festival
        $festivals->each(function($festival) use ($today) {
            /** @var \App\Models\Festival $festival */
            $filteredProducts = $festival->products->filter(function($product) use ($festival, $today) {
                /** @var \App\Models\Product $product */
                // Tìm discount cao nhất từ tất cả festival active của sản phẩm này
                $maxActiveDiscount = $product->festivals
                    ->where('status', 1)
                    ->filter(fn($f) =>
                        $f->start_date->toDateString() <= $today &&
                        $f->end_date->toDateString() >= $today
                    )
                    ->max('discount') ?? 0;
                
                // Chỉ hiển thị nếu festival đang xem có discount >= cao nhất
                return $festival->discount >= $maxActiveDiscount;
            })->take(6); // Chỉ lấy 6 sản phẩm cho trang chủ

            // Ghi đè collection products với products đã lọc
            $festival->setRelation('products', $filteredProducts);
        });

        // Chỉ trả về các festival có ít nhất 1 sản phẩm sau khi lọc
        return $festivals->filter(fn($festival) => $festival->products->count() > 0);
    }

    // =========================================================================
    // ĐĂNG XUẤT
    // =========================================================================

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }

    // =========================================================================
    // TRANG DANH MỤC
    // =========================================================================

    public function category_product(Request $request, $id)
    {
        $category          = Category::find($id);
        $title             = Title::all();
        $footers           = Footer::all();
        $all_concentrations = Concentration::where('status', '1')->get();
        $all_brands        = Brand::where('status', '1')->get();

        // Query SP theo danh mục
        $query = Product::where('idCategory', $id)->where('status', '1')
            ->whereHas('brand', function ($q) {
                $q->where('status', 1);
            })
            ->whereHas('concentration', function ($q) {
                $q->where('status', 1);
            });

        // Filter nồng độ
        if ($request->has('concentrations') && is_array($request->concentrations)) {
            $query->whereIn('idConcentration', $request->concentrations);
        }

        // Filter thương hiệu
        if ($request->has('brands') && is_array($request->brands)) {
            $query->whereIn('idBrand', $request->brands);
        }

        // Eager load festivals để tính giá giảm
        $products = $query->with('festivals')->get();
        /** @var \Illuminate\Database\Eloquent\Collection|\App\Models\Product[] $products */
        if ($request->has('min_price') && $request->has('max_price')) {
            $min      = (int) $request->min_price;
            $max      = (int) $request->max_price;
            $products = $products->filter(function ($product) use ($min, $max) {
                $finalPrice = $product->getDiscountedPrice();
                return $finalPrice >= $min && $finalPrice <= $max;
            });
        }

        // Sort theo giá sau giảm
        if ($request->sort == 'price_asc') {
            $products = $products->sortBy(fn($p) => $p->getDiscountedPrice())->values();
        } elseif ($request->sort == 'price_desc') {
            $products = $products->sortByDesc(fn($p) => $p->getDiscountedPrice())->values();
        } else {
            $products = $products->sortByDesc('created_at')->values(); // mặc định: mới nhất
        }

        $products = $this->manualPaginate($products->values(), 5, $request);

        return view('layout.category_product', compact(
            'all_concentrations', 'all_brands', 'products', 'category', 'title', 'footers'
        ));
    }

    // =========================================================================
    // TRANG THƯƠNG HIỆU
    // =========================================================================

    public function brand_product(Request $request, $id)
    {
        $brand              = Brand::find($id);
        $title              = Title::all();
        $footers            = Footer::all();
        $all_concentrations = Concentration::where('status', '1')->get();
        $categories         = Category::where('status', '1')->get();

        $query = Product::where('idBrand', $id)->where('status', '1')
            ->whereHas('category', function ($q) {
                $q->where('status', 1);
            })
            ->whereHas('concentration', function ($q) {
                $q->where('status', 1);
            });

        // Filter nồng độ
        if ($request->has('concentrations') && is_array($request->concentrations)) {
            $query->whereIn('idConcentration', $request->concentrations);
        }

        // Filter danh mục
        if ($request->has('categories') && is_array($request->categories)) {
            $query->whereIn('idCategory', $request->categories);
        }

        $products = $query->with('festivals')->get();
        /** @var \Illuminate\Database\Eloquent\Collection|\App\Models\Product[] $products */

        // Filter giá sau giảm
        if ($request->has('min_price') && $request->has('max_price')) {
            $min      = (int) $request->min_price;
            $max      = (int) $request->max_price;
            $products = $products->filter(function ($product) use ($min, $max) {
                $finalPrice = $product->getDiscountedPrice();
                return $finalPrice >= $min && $finalPrice <= $max;
            });
        }

        // Sort
        if ($request->sort == 'price_asc') {
            $products = $products->sortBy(fn($p) => $p->getDiscountedPrice())->values();
        } elseif ($request->sort == 'price_desc') {
            $products = $products->sortByDesc(fn($p) => $p->getDiscountedPrice())->values();
        } else {
            $products = $products->sortByDesc('created_at')->values();
        }

        $products = $this->manualPaginate($products->values(), 12, $request);

        return view('layout.brand_product', compact(
            'all_concentrations', 'categories', 'products', 'brand', 'title', 'footers'
        ));
    }

    // =========================================================================
    // CHI TIẾT SẢN PHẨM
    // =========================================================================

    public function single_product($id)
    {
        // Load comment để hiển thị đánh giá, concentration để hiển thị thẻ nồng độ
        $product = Product::with(['comment', 'concentration'])
            ->where('status', 1)
            ->findOrFail($id);

        return view('layout.single_product', compact('product'));
    }

    // =========================================================================
    // TÌM KIẾM
    // =========================================================================

    /**
     * Gợi ý tìm kiếm — AJAX trả về JSON, tối đa 3 sản phẩm.
     * Dùng cho thanh search live (live-search.js).
     */
    public function suggest(Request $request)
    {
        $keyword = $request->keyword;
        if (empty($keyword)) return response()->json([]);

        $products = Product::where('title', 'LIKE', "%{$keyword}%")
            ->where('status', 1)
            ->whereHas('category', function ($q) { $q->where('status', 1); })
            ->whereHas('brand', function ($q) { $q->where('status', 1); })
            ->whereHas('concentration', function ($q) { $q->where('status', 1); })
            ->select('id', 'title', 'image', 'price')
            ->take(3)
            ->get();

        return response()->json($products);
    }

    /**
     * Trang kết quả tìm kiếm — hỗ trợ filter + sort giống trang danh mục.
     */
    public function search(Request $request)
    {
        $keyword            = $request->keyword;
        $all_brands         = Brand::where('status', '1')->get();
        $all_concentrations = Concentration::where('status', '1')->get();
        $categories         = Category::where('status', '1')->get();
        $title              = Title::all();
        $footers            = Footer::all();

        $query = Product::where('title', 'LIKE', "%{$keyword}%")->where('status', 1)
            ->whereHas('category', function ($q) {
                $q->where('status', 1);
            })
            ->whereHas('brand', function ($q) {
                $q->where('status', 1);
            })
            ->whereHas('concentration', function ($q) {
                $q->where('status', 1);
            });

        if ($request->has('brands') && is_array($request->brands)) {
            $query->whereIn('idBrand', $request->brands);
        }
        if ($request->has('concentrations') && is_array($request->concentrations)) {
            $query->whereIn('idConcentration', $request->concentrations);
        }
        if ($request->has('categories') && is_array($request->categories)) {
            $query->whereIn('idCategory', $request->categories);
        }

        $products = $query->with('festivals')->get();
        /** @var \Illuminate\Database\Eloquent\Collection|\App\Models\Product[] $products */

        // Filter giá sau giảm
        if ($request->has('min_price') && $request->has('max_price')) {
            $min      = (int) $request->min_price;
            $max      = (int) $request->max_price;
            $products = $products->filter(fn($product) =>
                $product->getDiscountedPrice() >= $min &&
                $product->getDiscountedPrice() <= $max
            );
        }

        if ($request->sort == 'price_asc') {
            $products = $products->sortBy(fn($p) => $p->getDiscountedPrice())->values();
        } elseif ($request->sort == 'price_desc') {
            $products = $products->sortByDesc(fn($p) => $p->getDiscountedPrice())->values();
        } else {
            $products = $products->sortByDesc('created_at')->values();
        }

        $products = $this->manualPaginate($products->values(), 12, $request);

        return view('search_result', compact(
            'products', 'keyword', 'all_brands', 'all_concentrations', 'categories', 'title', 'footers'
        ));
    }

    // =========================================================================
    // TRANG FESTIVAL
    // =========================================================================

    /**
     * Trang danh sách sản phẩm trong festival.
     *
     * Logic đặc biệt: Nếu 1 sản phẩm có 2 festival đang active,
     * chỉ hiển thị ở festival có discount CAO NHẤT.
     * Festival có discount thấp hơn sẽ ẩn SP đó.
     * → Khi festival cao hơn hết hạn, SP sẽ hiện lại ở festival thấp hơn.
     */
    public function festival_product(Request $request, $id)
    {
        $festival = Festival::findOrFail($id);

        // Không cho vào trang nếu festival đã hết hạn hoặc chưa bắt đầu
        if (!$festival->isActive()) {
            return redirect()->route('show_products')
                ->with('error', 'Chương trình khuyến mãi đã kết thúc hoặc chưa diễn ra.');
        }

        $title              = Title::all();
        $footers            = Footer::all();
        $all_concentrations = Concentration::where('status', '1')->get();
        $categories         = Category::where('status', '1')->get();

        // Lấy SP đang bán VÀ thuộc festival này
        $query = Product::where('status', '1')
            ->whereHas('festivals', function ($q) use ($id) {
                $q->where('festivals.id', $id);
            })
            ->whereHas('category', function ($q) {
                $q->where('status', 1);
            })
            ->whereHas('brand', function ($q) {
                $q->where('status', 1);
            })
            ->whereHas('concentration', function ($q) {
                $q->where('status', 1);
            });

        if ($request->has('concentrations') && is_array($request->concentrations)) {
            $query->whereIn('idConcentration', $request->concentrations);
        }
        if ($request->has('categories') && is_array($request->categories)) {
            $query->whereIn('idCategory', $request->categories);
        }

        $products = $query->with('festivals')->get();
        /** @var \Illuminate\Database\Eloquent\Collection|\App\Models\Product[] $products */

        // ── LỌC THEO DISCOUNT CAO NHẤT ───────────────────────────────
        $today    = \Carbon\Carbon::today()->toDateString();
        $products = $products->filter(function ($product) use ($festival, $today) {
            // Tìm discount cao nhất từ tất cả festival active của SP này
            $maxActiveDiscount = $product->festivals
                ->where('status', 1)
                ->filter(fn($f) =>
                    $f->start_date->toDateString() <= $today &&
                    $f->end_date->toDateString() >= $today
                )
                ->max('discount') ?? 0;

            // Chỉ hiển thị nếu festival đang xem có discount = cao nhất
            return $festival->discount >= $maxActiveDiscount;
        })->values();

        // Filter giá — dùng giá của đúng festival đang xem
        if ($request->has('min_price') && $request->has('max_price')) {
            $min      = (int) $request->min_price;
            $max      = (int) $request->max_price;
            $products = $products->filter(function ($product) use ($min, $max, $festival) {
                $finalPrice = $product->getDiscountedPrice($festival);
                return $finalPrice >= $min && $finalPrice <= $max;
            });
        }

        // Sort theo giá của festival đang xem
        if ($request->sort == 'price_asc') {
            $products = $products->sortBy(fn($p) => $p->getDiscountedPrice($festival))->values();
        } elseif ($request->sort == 'price_desc') {
            $products = $products->sortByDesc(fn($p) => $p->getDiscountedPrice($festival))->values();
        } else {
            $products = $products->sortByDesc('created_at')->values();
        }

        $products = $this->manualPaginate($products->values(), 12, $request);

        return view('layout.festival_product', compact(
            'festival', 'all_concentrations', 'categories', 'products', 'title', 'footers'
        ));
    }

    // =========================================================================
    // HELPER — Manual paginate từ Collection (giữ được filter giá discount)
    // =========================================================================

    /**
     * Tạo LengthAwarePaginator từ một Collection đã được filter/sort.
     * Dùng khi không thể paginate() trực tiếp trên query vì cần filter sau get().
     */
    private function manualPaginate($collection, int $perPage, Request $request): LengthAwarePaginator
    {
        $page     = (int) $request->input('page', 1);
        $total    = $collection->count(); // tổng TRƯỚC khi cắt trang
        $items    = $collection->forPage($page, $perPage); // items của trang hiện tại

        return new LengthAwarePaginator(
            $items,
            $total,  // đúng tổng sau filter
            $perPage,
            $page,
            [
                'path'  => $request->url(),
                'query' => $request->except('page'),
            ]
        );
    }

    // =========================================================================
    // DEMO GUCCI — Hiển thị 1 sản phẩm Gucci (cho giảng viên)
    // =========================================================================

    /**
     * Trang demo hiển thị 1 sản phẩm Gucci cụ thể.
     * Dùng để trình bày cho giảng viên cách lấy và hiển thị sản phẩm.
     */
    public function showGucciProduct()
    {
        // Lấy 1 sản phẩm Gucci đầu tiên trong database
        $product = Product::whereHas('brand', function($query) {
            $query->where('title', 'GUCCI');
        })->with(['brand', 'category', 'concentration', 'festivals'])->first();

        // Fallback: Nếu không có sản phẩm Gucci, lấy sản phẩm đầu tiên bất kỳ
        if (!$product) {
            $product = Product::with(['brand', 'category', 'concentration', 'festivals'])->first();
        }

        $title   = Title::all();
        $footers = Footer::all();
        $brands = Brand::where('status', 1)->get();
        $festivals = Festival::active()->get();

        return view('gucci-demo', compact('product', 'title', 'footers', 'brands', 'festivals'));
    }

    // =========================================================================
    // DEMO NSX — Hiển thị sản phẩm từ NSX "a" (CÁCH 1 - Đơn giản)
    // =========================================================================

    /**
     * Trang demo hiển thị sản phẩm từ NSX "a".
     * CÁCH 1: Lấy trực tiếp từ bảng manufacturers_product (many-to-many).
     * Đơn giản và nhanh hơn so với query qua Purchase Order.
     */
    public function showManufacturerProducts()
    {
        // Lấy NSX "a" với eager loading relationships
        $manufacturer = \App\Models\User::where('name', 'a')
            ->with(['products.brand', 'products.category', 'products.concentration'])
            ->first();

        $products = collect();
        
        if ($manufacturer) {
            // Lấy sản phẩm từ relationship có sẵn (manufacturers_product)
            // Lọc chỉ lấy sản phẩm đang bán (status = 1)
            $products = $manufacturer->products
                ->where('status', 1)
                ->values();
        }

        return view('manufacturer-demo', compact('manufacturer', 'products'));
    }
}

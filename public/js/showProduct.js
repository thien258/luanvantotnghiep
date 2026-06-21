// showProduct.js — Xử lý filter + sort sản phẩm không reload trang (AJAX)
// Dùng cho: show-product.blade.php, category_product, brand_product, festival_product, search_result
//
// Luồng:
//   1. User tick checkbox / kéo slider giá / đổi sort
//   2. buildQueryParams() gom tất cả filter thành URLSearchParams
//   3. updateUrl() cập nhật URL trên thanh địa chỉ (không reload)
//   4. loadProducts() fetch HTML từ server, cắt lấy #product-container và #pagination-container
//   5. Cập nhật DOM — sản phẩm + phân trang thay đổi mà không reload trang

(function () {

    function initProductFilters() {

        // Lấy form filter (#filterForm) trong trang
        const form = document.getElementById('filterForm');

        // Nếu trang không có filter form thì thoát sớm
        if (!form) {
            return;
        }

        // Gom tất cả giá trị trong form thành URLSearchParams để gửi lên server
        function buildQueryParams() {

            const params = new URLSearchParams();

            // Lấy tất cả field trong form (checkbox, hidden input, ...)
            new FormData(form).forEach(function (value, key) {
                // Bỏ qua giá trị rỗng
                if (value !== '') {
                    params.append(key, value);
                }
            });

            // Thêm giá trị sort từ select box (nằm ngoài form)
            const sortEl = document.getElementById('sort');
            if (sortEl) {
                params.set('sort', sortEl.value);
            }

            return params;
        }

        // Cập nhật URL trên thanh địa chỉ mà không reload trang
        // Dùng History API để giữ state filter khi user bấm Back
        function updateUrl(params) {
            const query  = params.toString();
            const newUrl = query
                ? window.location.pathname + '?' + query  // có filter → thêm query string
                : window.location.pathname;               // không filter → URL sạch

            window.history.pushState({}, '', newUrl);     // cập nhật URL không reload
            return newUrl;
        }

        // Fetch HTML từ server và cập nhật sản phẩm + phân trang
        function loadProducts() {

            const params = buildQueryParams();
            const url    = updateUrl(params);             // cập nhật URL trước

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest' // báo server đây là AJAX request
                }
            })
                .then(function (response) {
                    return response.text();               // nhận HTML dạng text
                })
                .then(function (html) {

                    // Parse HTML nhận được thành DOM ảo để tìm các element cần thiết
                    const doc = new DOMParser().parseFromString(html, 'text/html');

                    // ── Cập nhật danh sách sản phẩm ──────────────────────────
                    const newProducts = doc.querySelector('#product-container');
                    const container   = document.querySelector('#product-container');
                    if (newProducts && container) {
                        container.innerHTML = newProducts.innerHTML; // thay nội dung SP
                    }

                    // ── Cập nhật phân trang ───────────────────────────────────
                    // Nếu filter ra ít SP hơn perPage → server không render links()
                    // → #pagination-container sẽ rỗng → tự ẩn trên trang
                    const newPagination = doc.querySelector('#pagination-container');
                    const paginationEl  = document.querySelector('#pagination-container');
                    if (paginationEl) {
                        paginationEl.innerHTML = newPagination ? newPagination.innerHTML : '';
                    }

                })
                .catch(function (err) {
                    console.error('loadProducts error:', err); // log lỗi nếu có
                });
        }

        // Expose hàm loadProducts ra ngoài để các script khác có thể gọi nếu cần
        window.applyProductFilters = loadProducts;

        // Lắng nghe sự kiện đổi sort → load lại sản phẩm ngay
        const sort = document.getElementById('sort');
        if (sort) {
            sort.addEventListener('change', loadProducts);
        }

        // Lắng nghe tất cả checkbox trong form → tick/untick là load lại ngay
        form.querySelectorAll('input[type="checkbox"]').forEach(function (item) {
            item.addEventListener('change', loadProducts);
        });

        // ── Khởi tạo slider giá (noUiSlider) ─────────────────────────
        const slider = document.getElementById('price-range');

        // Nếu không có slider hoặc thư viện noUiSlider chưa load thì thoát
        if (!slider || typeof noUiSlider === 'undefined') {
            return;
        }

        // Lấy các input ẩn lưu giá trị min/max để gửi lên server
        const minInput   = document.getElementById('min_price');
        const maxInput   = document.getElementById('max_price');
        // Lấy các span hiển thị giá trực quan cho người dùng
        const minDisplay = document.getElementById('price-min-display');
        const maxDisplay = document.getElementById('price-max-display');

        // Lấy giá trị hiện tại từ URL (nếu đã filter trước đó) hoặc dùng mặc định
        const currentMin = parseInt(minInput.value, 10) || 0;
        const currentMax = parseInt(maxInput.value, 10) || 10000000;

        // Tạo slider với 2 tay kéo (min và max)
        noUiSlider.create(slider, {
            start: [currentMin, currentMax],  // vị trí ban đầu của 2 tay kéo
            connect: true,                    // tô màu vùng giữa 2 tay kéo
            step: 100000,                     // bước nhảy 100.000₫ mỗi lần kéo
            range: {
                min: 0,
                max: 10000000                 // tối đa 10 triệu
            },
            format: {
                // Hiển thị số theo định dạng Việt Nam có dấu chấm phân cách
                to: function (value) {
                    return Math.round(value).toLocaleString('vi-VN');
                },
                // Đọc lại giá trị từ string (bỏ dấu chấm) để tính toán
                from: function (value) {
                    return Number(value.replace(/[^0-9.-]+/g, ''));
                }
            }
        });

        // Khi kéo slider → cập nhật hiển thị và giá trị input ẩn theo thời gian thực
        slider.noUiSlider.on('update', function (values, handle) {
            if (handle === 0) {
                // Tay kéo trái = giá MIN
                minDisplay.innerHTML = values[0] + 'đ';
                minInput.value = values[0].replace(/\./g, ''); // bỏ dấu chấm để gửi số thuần
            } else {
                // Tay kéo phải = giá MAX
                maxDisplay.innerHTML = values[1] + 'đ';
                maxInput.value = values[1].replace(/\./g, '');
            }
        });

        // Khi thả tay kéo (change = khi dừng kéo, không gọi liên tục như update)
        // → load lại sản phẩm theo khoảng giá mới
        slider.noUiSlider.on('change', loadProducts);
    }

    // Khởi chạy khi DOM đã sẵn sàng
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProductFilters);
    } else {
        // DOM đã load rồi (script được gọi sau khi trang render xong)
        initProductFilters();
    }

})(); // IIFE — bọc trong function tự gọi để tránh ô nhiễm global scope

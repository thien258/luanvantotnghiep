// selectProductFestival.js — Tìm kiếm sản phẩm trong trang chọn SP cho Festival (admin)
//
// Dùng cho: admin/Festival/select_products.blade.php
// Luồng:
//   1. Admin gõ vào ô tìm kiếm (#searchInput)
//   2. Sau 300ms debounce → gửi AJAX GET đến /admin/festival/{id}/products?search=...
//   3. Server trả về HTML các dòng tbody → cập nhật vào #product-tbody
//   → Không reload trang, chỉ cập nhật bảng

document.addEventListener('DOMContentLoaded', function () {

    // Ô input tìm kiếm và bảng tbody chứa danh sách sản phẩm
    const searchInput = document.getElementById('searchInput');
    const tbody       = document.getElementById('product-tbody');

    // Nếu không có 2 element này thì trang không phải trang chọn SP → thoát
    if (!searchInput || !tbody) return;

    let timeout = null; // dùng cho debounce — tránh gọi AJAX liên tục mỗi ký tự

    searchInput.addEventListener('input', function () {

        // Hủy timer cũ nếu user vẫn đang gõ
        clearTimeout(timeout);

        // Đợi 300ms sau khi dừng gõ mới gửi request (debounce)
        timeout = setTimeout(function () {

            const query      = searchInput.value;               // từ khóa tìm kiếm
            const festivalId = searchInput.dataset.festivalId;  // ID festival từ data attribute

            // Gọi AJAX lấy danh sách SP khớp từ khóa
            fetch('/admin/festival/' + festivalId + '/products?search=' + encodeURIComponent(query), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest', // báo server đây là AJAX
                    'Accept': 'text/html'                 // nhận về HTML
                }
            })
            .then(function (response) { return response.text(); })
            .then(function (data) {
                tbody.innerHTML = data; // thay nội dung tbody bằng kết quả mới
            })
            .catch(function (error) {
                console.error('selectProductFestival error:', error);
            });

        }, 300); // debounce 300ms

    });

});

document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('search-input');
    const searchSuggestions = document.getElementById('search-suggestions');

    if (searchInput && searchSuggestions) {
        // Lắng nghe mỗi khi người dùng gõ phím
        searchInput.addEventListener('input', function () {
            let keyword = this.value.trim();

            if (keyword.length > 0) {
                // 🌟 SỬA ĐÂY: Thêm dấu '/' ở đầu để luôn trỏ về gốc web (root)
                // Dù ông ở trang nào (/carts, /product/1, ...) nó đều gọi về đúng http://localhost:8000/search-suggest
                fetch(window.APP_URL + '/search-suggest?keyword=' + encodeURIComponent(keyword), {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        searchSuggestions.innerHTML = '';

                        if (data.length > 0) {
                            data.forEach(item => {
                                // 🌟 SỬA ĐÂY: Trỏ đúng route xem chi tiết sản phẩm của ông
                                // Lưu ý: Kiểm tra lại trong web.php route chi tiết là '/product/{id}' hay '/show_product/{id}'
                                // Sửa dòng này:
                                let detailUrl = window.APP_URL + `/product/${item.id}`;

                                let formattedPrice = parseInt(item.price).toLocaleString('vi-VN') + 'đ';
                                let html = `
                                <a href="${detailUrl}" class="dropdown-item d-flex align-items-center gap-2 py-2 border-bottom text-decoration-none" style="white-space: normal;">
                                    <img src="${item.image}" alt="" style="width: 48px; height: 48px; object-fit: contain; background: #f8f9fa; border-radius: 4px; flex-shrink: 0;">
                                    <div style="flex: 1; min-width: 0;">
                                        <div class="small fw-bold text-dark lh-sm" style="white-space: normal; word-break: break-word;">${item.title}</div>
                                        <div class="small fw-bold text-danger mt-1">${formattedPrice}</div>
                                    </div>
                                </a>
                            `;
                                searchSuggestions.innerHTML += html;
                            });

                            searchSuggestions.style.display = 'block';
                        } else {
                            console.log("Không có sản phẩm nào khớp với từ khóa");
                            searchSuggestions.innerHTML = '<div class="p-3 text-center small text-muted">Không tìm thấy sản phẩm.</div>';
                            searchSuggestions.style.display = 'block';
                        }
                    })
                    .catch(error => console.error('Lỗi tìm kiếm AJAX:', error));
            } else {
                searchSuggestions.style.display = 'none';
            }
        });

        // Click ra ngoài thì ẩn bảng gợi ý
        document.addEventListener('click', function (e) {
            if (!searchInput.contains(e.target) && !searchSuggestions.contains(e.target)) {
                searchSuggestions.style.display = 'none';
            }
        });
    }
});
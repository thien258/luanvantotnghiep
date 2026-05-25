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
                                <a href="${detailUrl}" class="dropdown-item d-flex align-items-center justify-content-between py-2 border-bottom text-wrap text-decoration-none" style="white-space: normal;">
                                    <div class="d-flex align-items-center" style="max-width: 75%; overflow: hidden;">
                                        <img src="${item.image}" alt="" style="width: 40px; height: 40px; object-fit: contain; margin-right: 10px; background: #f8f9fa; border-radius: 4px; flex-shrink: 0;">
                                        <span class="small fw-bold text-dark lh-sm text-truncate">${item.title}</span>
                                    </div>
                                    <span class="small fw-bold text-danger ms-2 text-end flex-shrink-0">${formattedPrice}</span>
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
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('search-input');
    const searchSuggestions = document.getElementById('search-suggestions');

    if (searchInput && searchSuggestions) {
        // Lắng nghe mỗi khi người dùng gõ phím
        searchInput.addEventListener('input', function () {
            let keyword = this.value.trim();

            if (keyword.length > 0) {
                // Gọi dữ liệu ngầm lên Route suggest
                fetch(`/search-suggest?keyword=${encodeURIComponent(keyword)}`)
                    .then(response => response.json())
                    .then(data => {
                        searchSuggestions.innerHTML = ''; // Xóa sạch kết quả cũ của lần gõ trước

                        if (data.length > 0) {
                            // Duyệt qua danh sách sản phẩm và tạo giao diện
                            data.forEach(item => {

                                // CHÚ Ý: Đổi '/product/' thành route trỏ tới trang chi tiết thực tế của ông
                                let detailUrl = `/category_product/single_product/${item.id}`;

                                // Định dạng số tiền thành kiểu 1.500.000đ
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
                            // Nếu không tìm ra món nào
                            searchSuggestions.innerHTML = '<div class="p-3 text-center small text-muted">Không tìm thấy sản phẩm.</div>';
                            searchSuggestions.style.display = 'block';
                        }
                    })
                    .catch(error => console.error('Lỗi tìm kiếm AJAX:', error));
            } else {
                searchSuggestions.style.display = 'none';
            }
        });

        // Tính năng xịn: Khách click chuột ra chỗ khác trên màn hình thì tự động ẩn bảng đi cho đỡ vướng
        document.addEventListener('click', function (e) {
            if (!searchInput.contains(e.target) && !searchSuggestions.contains(e.target)) {
                searchSuggestions.style.display = 'none';
            }
        });
    }
});
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('admin-search-input');
    
    // Lấy tất cả các dòng (tr) nằm trong phần thân bảng (tbody)
    const tableRows = document.querySelectorAll('tbody tr');

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            // Lấy chữ khách gõ, chuyển về chữ thường để so sánh cho chuẩn
            let keyword = this.value.toLowerCase().trim();

            // Duyệt qua từng dòng trong bảng
            tableRows.forEach(row => {
                // Bỏ qua dòng thông báo "Không tìm thấy..." (có chứa thuộc tính colspan)
                if (row.querySelector('td[colspan]')) return;

                // Dựa vào code của ông, cột Tên sản phẩm đang nằm ở thẻ <td> có class="fw-bold"
                let titleColumn = row.querySelector('td.fw-bold'); 
                
                if (titleColumn) {
                    let titleText = titleColumn.textContent.toLowerCase();
                    
                    // Nếu tên sản phẩm chứa từ khóa -> Hiển thị dòng đó
                    if (titleText.includes(keyword)) {
                        row.style.display = '';
                    } 
                    // Nếu không chứa -> Ẩn dòng đó đi
                    else {
                        row.style.display = 'none';
                    }
                }
            });
        });
    }
});
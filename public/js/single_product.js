document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.variant-btn');
    const displayPrice = document.getElementById('display-price');
    // 🌟 THÊM: Lấy thêm thẻ hiển thị giá gốc cũ để gạch ngang
    const displayOriginalPrice = document.getElementById('display-original-price');
    const displayStock = document.getElementById('display-stock');
    const displayStockStatus = document.getElementById('display-stock-status');
    const hiddenVariantId = document.getElementById('hidden-variant-id');
    const btnAddCart = document.getElementById('btn-add-cart');

    function updateVariantData(btn) {
        const id = btn.getAttribute('data-id');
        const price = parseInt(btn.getAttribute('data-price')) || 0;
        // 🌟 THÊM: Bốc thêm giá đã sale được găm ở file Blade
        const finalPrice = parseInt(btn.getAttribute('data-final-price')) || 0;
        const stock = parseInt(btn.getAttribute('data-stock')) || 0;

        displayStock.innerText = stock;
        hiddenVariantId.value = id;

        // 🌟 ĐÃ SỬA: Logic ẩn/hiện giá kép thông minh khi click chọn dung tích
        if (finalPrice < price) {
            // Có giảm giá: Hiện giá mới đã giảm, bật dòng gạch ngang giá cũ
            displayPrice.innerText = finalPrice.toLocaleString('vi-VN');
            displayOriginalPrice.innerText = price.toLocaleString('vi-VN') + " VNĐ";
            displayOriginalPrice.classList.remove('d-none');
        } else {
            // Không giảm giá: Hiện giá gốc, ẩn dòng gạch ngang đi
            displayPrice.innerText = price.toLocaleString('vi-VN');
            displayOriginalPrice.classList.add('d-none');
        }

        // --- Giữ nguyên toàn bộ logic xử lý Tồn kho / Nút bấm cũ của ông ---
        if (stock <= 0) {
            displayStockStatus.innerText = "Hết hàng";
            displayStockStatus.className = "fw-bold ms-2 text-danger";
            btnAddCart.disabled = true;
            btnAddCart.innerHTML = '<i class="fa-solid fa-ban me-2"></i> HẾT HÀNG';
            btnAddCart.classList.replace('btn-dark', 'btn-secondary');
        } else {
            displayStockStatus.innerText = "Còn hàng";
            displayStockStatus.className = "fw-bold ms-2 text-success";
            btnAddCart.disabled = false;
            btnAddCart.innerHTML = '<i class="fa-solid fa-shopping-cart me-2"></i> THÊM VÀO GIỎ';
            btnAddCart.classList.replace('btn-secondary', 'btn-dark');
        }
    }

    buttons.forEach(btn => {
        btn.addEventListener('click', function() {
            buttons.forEach(b => b.classList.remove('active', 'btn-dark', 'text-white'));
            this.classList.add('active', 'btn-dark', 'text-white');
            updateVariantData(this);
        });
    });

    if (buttons.length > 0) {
        updateVariantData(buttons[0]);
    }
});
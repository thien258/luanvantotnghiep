document.addEventListener('DOMContentLoaded', function() {
    // ========================================================
    // PHẦN 1: LOGIC CHECKBOX VÀ TÍNH TỔNG TIỀN (REAL-TIME CLIENT)
    // ========================================================
    const checkboxes = document.querySelectorAll('.cart-item-checkbox');
    const displaySubtotal = document.getElementById('display-subtotal');
    const displayVat = document.getElementById('display-vat');
    const displayTotal = document.getElementById('display-total');
    const displayCount = document.getElementById('display-count');
    const btnCheckout = document.getElementById('btn-checkout');
    const formCheckout = document.getElementById('form-checkout');
    const hiddenInputsContainer = document.getElementById('hidden-cart-inputs');

    function calculateTotal() {
        let subtotal = 0;
        let count = 0;
        
        if (hiddenInputsContainer) {
            hiddenInputsContainer.innerHTML = '';
        }

        checkboxes.forEach(function(checkbox) {
            if (checkbox.checked) {
                let rawPrice = checkbox.getAttribute('data-price') || "0";
                let rawQty = checkbox.getAttribute('data-quantity') || "1";

                // Lọc sạch các ký tự lạ, ép về kiểu số thuần túy để tính toán
                let price = parseInt(rawPrice.replace(/\D/g, '')) || 0;
                let quantity = parseInt(rawQty) || 1;
                let cartId = checkbox.value;

                subtotal += (price * quantity);
                count++;

                if (hiddenInputsContainer) {
                    hiddenInputsContainer.innerHTML += `<input type="hidden" name="selected_carts[]" value="${cartId}">`;
                }
            }
        });

        let vat = subtotal * 0.10; // Thuế VAT 10%
        let grandTotal = subtotal + vat;

        // Đổ dữ liệu định dạng tiền tệ vi-VN ra màn hình
        if (displaySubtotal) displaySubtotal.innerText = subtotal.toLocaleString('vi-VN') + 'đ';
        if (displayVat) displayVat.innerText = vat.toLocaleString('vi-VN') + 'đ';
        if (displayTotal) displayTotal.innerText = grandTotal.toLocaleString('vi-VN') + 'đ';
        if (displayCount) displayCount.innerText = count;

        if (btnCheckout) {
            btnCheckout.disabled = (count === 0);
        }
    }

    // Lắng nghe sự kiện khi tích chọn hoặc bỏ tích sản phẩm
    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', calculateTotal);
    });

    if (btnCheckout && formCheckout) {
        btnCheckout.addEventListener('click', function() {
            if (!this.disabled) {
                formCheckout.submit();
            }
        });
    }

    // Chạy tính toán tổng tiền lần đầu khi vừa tải xong trang
    calculateTotal();

    // ========================================================
    // PHẦN 2: XỬ LÝ NÚT TĂNG GIẢM SỐ LƯỢNG ĐỒNG BỘ REAL-TIME (AJAX)
    // ========================================================
    const qtyButtons = document.querySelectorAll('.btn-qty-change');

    qtyButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            // Đảm bảo bốc đúng thuộc tính của nút kể cả khi click lệch vào icon bên trong
            let targetBtn = this.closest('.btn-qty-change');
            if (!targetBtn) return;

            let action = targetBtn.getAttribute('data-action');
            let cartId = targetBtn.getAttribute('data-id');
            
            let inputQty = document.getElementById('qty-' + cartId);
            let checkbox = document.querySelector(`.cart-item-checkbox[value="${cartId}"]`);
            
            if (!inputQty) return;
            let currentQty = parseInt(inputQty.value) || 1;

            if (action === 'up') {
                let maxStock = parseInt(targetBtn.getAttribute('data-stock')) || 0;

                if (maxStock > 0 && currentQty >= maxStock) {
                    alert('Số lượng trong kho không đủ để tăng tiếp!');
                    return; 
                }
                currentQty++;
            } else if (action === 'down') {
                if (currentQty > 1) {
                    currentQty--;
                } else {
                    return;
                }
            }

            // 1. Cập nhật số lượng hiển thị tức thì ngoài màn hình Client
            inputQty.value = currentQty;
            
            if (checkbox) {
                checkbox.setAttribute('data-quantity', currentQty);
                checkbox.checked = true; // Tự động tích chọn dòng đó khi bấm tăng/giảm số lượng giống Shopee
            }
            
            // Tính lại tiền mặt Client
            calculateTotal();

            // 2. 🌟 ĐÃ FIX: Bắn request ngầm bọc kèm ID chuẩn xác lên Server
            fetch(`/carts/${cartId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    change: action
                })
            })
            .then(response => {
                if (!response.ok) {
                    // Nếu backend kiểm tra thấy kho hết hoặc dính lỗi bảo mật
                    alert('Cập nhật kho hàng thất bại hoặc vượt quá tồn kho hiện tại!');
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi kết nối hệ thống ngầm, vui lòng thử lại!');
            });
        });
    });
});
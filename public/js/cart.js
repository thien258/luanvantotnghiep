document.addEventListener('DOMContentLoaded', function() {
    // ========================================================
    // PHẦN 1: LOGIC CHECKBOX VÀ TÍNH TỔNG TIỀN REAL-TIME
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

        let vat = subtotal * 0.10; 
        let grandTotal = subtotal + vat;

        if (displaySubtotal) displaySubtotal.innerText = subtotal.toLocaleString('vi-VN') + 'đ';
        if (displayVat) displayVat.innerText = vat.toLocaleString('vi-VN') + 'đ';
        if (displayTotal) displayTotal.innerText = grandTotal.toLocaleString('vi-VN') + 'đ';
        if (displayCount) displayCount.innerText = count;

        if (btnCheckout) {
            btnCheckout.disabled = (count === 0);
        }
    }

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

    calculateTotal();

    // ========================================================
    // PHẦN 2: XỬ LÝ NÚT TĂNG GIẢM SỐ LƯỢNG ĐỒNG BỘ REAL-TIME (KHÔNG REFRESH)
    // ========================================================
    const qtyButtons = document.querySelectorAll('.btn-qty-change');

    // Hàm kiểm tra trạng thái kho để khóa/mở nút bấm ngay lập tức
    function checkButtonStates(cartId, currentQty) {
        const row = document.getElementById('qty-' + cartId)?.closest('.row');
        if (!row) return;

        const btnUp = row.querySelector('[data-action="up"]');
        const btnDown = row.querySelector('[data-action="down"]');
        const maxStock = parseInt(btnUp?.getAttribute('data-stock')) || 0;

        // Nếu số lượng đạt đỉnh kho, block (disabled) luôn nút tăng (+)
        if (btnUp && maxStock > 0) {
            btnUp.disabled = (currentQty >= maxStock);
        }

        // Nếu số lượng về mức tối thiểu (1), block nút giảm (-)
        if (btnDown) {
            btnDown.disabled = (currentQty <= 1);
        }
    }

    // Chạy kiểm tra trạng thái nút bấm cho tất cả sản phẩm ngay khi tải trang
    checkboxes.forEach(cb => {
        let cartId = cb.value;
        let inputQty = document.getElementById('qty-' + cartId);
        if (inputQty) {
            checkButtonStates(cartId, parseInt(inputQty.value) || 1);
        }
    });

    qtyButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            let targetBtn = this.closest('.btn-qty-change');
            if (!targetBtn) return;

            let action = targetBtn.getAttribute('data-action');
            let cartId = targetBtn.getAttribute('data-id');
            
            let inputQty = document.getElementById('qty-' + cartId);
            let checkbox = document.querySelector(`.cart-item-checkbox[value="${cartId}"]`);
            
            if (!inputQty) return;
            let currentQty = parseInt(inputQty.value) || 1;
            let maxStock = parseInt(targetBtn.parentElement.querySelector('[data-action="up"]')?.getAttribute('data-stock')) || 0;

            if (action === 'up') {
                // Nếu bằng hoặc lớn hơn kho thì dừng hình luôn, không tăng giao diện
                if (maxStock > 0 && currentQty >= maxStock) {
                    targetBtn.disabled = true;
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

            // Đồng bộ dữ liệu tạm thời ra giao diện Client
            inputQty.value = currentQty;
            
            if (checkbox) {
                checkbox.setAttribute('data-quantity', currentQty);
                checkbox.checked = true; 
            }
            
            calculateTotal();
            
            // Cập nhật trạng thái Block/Unblock của cụm nút (+ / -) dựa trên số lượng mới
            checkButtonStates(cartId, currentQty);

            // Bắn phương thức PUT ngầm cập nhật Database mà không cần reload trang
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
                    // 🌟 ĐÃ SỬA: Lỗi ngầm thì chỉ ghi nhận lỗi ở tab F12 (Console) cho Dev, không hiện Alert, không Reload trang của khách
                    console.error('Không thể đồng bộ số lượng lên Server, mã lỗi:', response.status);
                }
            })
            .catch(error => {
                console.error('Lỗi kết nối Fetch API:', error);
            });
        });
    });
});
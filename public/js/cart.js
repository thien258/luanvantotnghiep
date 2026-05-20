document.addEventListener('DOMContentLoaded', function() {
        // ========================================================
        // PHẦN 1: LOGIC CHECKBOX VÀ TÍNH TỔNG TIỀN
        // ========================================================
        const checkboxes = document.querySelectorAll('.cart-item-checkbox');
        const displaySubtotal = document.getElementById('display-subtotal');
        const displayVat = document.getElementById('display-vat');
        const displayTotal = document.getElementById('display-total');
        const displayCount = document.getElementById('display-count');
        const btnCheckout = document.getElementById('btn-checkout');
        const formCheckout = document.getElementById('form-checkout');
        const hiddenInputsContainer = document.getElementById('hidden-cart-inputs');

        // Hàm tính toán và cập nhật giao diện
        function calculateTotal() {
            let subtotal = 0;
            let count = 0;
            
            // Xóa hết các input ẩn cũ
            hiddenInputsContainer.innerHTML = '';

            checkboxes.forEach(function(checkbox) {
                if (checkbox.checked) {
                    let price = parseInt(checkbox.getAttribute('data-price'));
                    let quantity = parseInt(checkbox.getAttribute('data-quantity'));
                    let cartId = checkbox.value;

                    subtotal += (price * quantity);
                    count++;

                    // Sinh ra một input ẩn chứa ID giỏ hàng để mang qua trang Thanh Toán
                    hiddenInputsContainer.innerHTML += `<input type="hidden" name="selected_carts[]" value="${cartId}">`;
                }
            });

            let vat = subtotal * 0.10;
            let grandTotal = subtotal + vat;

            // Hiển thị ra màn hình (Định dạng tiền Việt Nam)
            displaySubtotal.innerText = subtotal.toLocaleString('vi-VN') + 'đ';
            displayVat.innerText = vat.toLocaleString('vi-VN') + 'đ';
            displayTotal.innerText = grandTotal.toLocaleString('vi-VN') + 'đ';
            displayCount.innerText = count;

            // Mở khóa nút thanh toán nếu có ít nhất 1 món được chọn
            if (count > 0) {
                btnCheckout.disabled = false;
            } else {
                btnCheckout.disabled = true;
            }
        }

        // Gắn sự kiện click cho từng checkbox
        checkboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', calculateTotal);
        });

        // Bấm nút thanh toán thì submit cái form ẩn
        btnCheckout.addEventListener('click', function() {
            if (!this.disabled) {
                formCheckout.submit();
            }
        });

        // Chạy hàm tính toán lần đầu lúc mới load trang
        calculateTotal();

        // ========================================================
        // PHẦN 2: XỬ LÝ NÚT TĂNG GIẢM SỐ LƯỢNG (KHÔNG AJAX, KHÔNG LOAD TRANG)
        // ========================================================
        const qtyButtons = document.querySelectorAll('.btn-qty-change');

        qtyButtons.forEach(function(btn) {
            btn.addEventListener('click', function() {
                let action = this.getAttribute('data-action');
                let cartId = this.getAttribute('data-id');
                
                // Tìm ô hiển thị số lượng và cái checkbox của món hàng này
                let inputQty = document.getElementById('qty-' + cartId);
                let checkbox = document.querySelector(`.cart-item-checkbox[value="${cartId}"]`);

                let currentQty = parseInt(inputQty.value);

                // Nếu bấm + thì tăng, bấm - thì giảm (tối thiểu là 1)
                if (action === 'up') {
                    currentQty++;
                } else if (action === 'down' && currentQty > 1) {
                    currentQty--;
                }

                // 1. Cập nhật con số mới ra màn hình
                inputQty.value = currentQty;
                
                // 2. Cập nhật con số vào checkbox để hàm tính tiền bên trên nhận diện được
                checkbox.setAttribute('data-quantity', currentQty);

                // 3. Khách đã bấm đổi số lượng thì tự động tick xanh món đó luôn cho tiện
                checkbox.checked = true;

                // 4. Gọi lại hàm tính tổng tiền để cột bên phải tự động nhảy số
                calculateTotal();
            });
        });
});
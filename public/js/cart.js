// cart.js — Xử lý giỏ hàng: tính tổng tiền, tăng/giảm số lượng, chọn SP để thanh toán
//
// Luồng:
//   1. Trang load → calculateTotal() tính tổng từ các checkbox đang checked
//   2. User tick/untick → calculateTotal() cập nhật lại tổng
//   3. User bấm +/- → AJAX PUT /carts/{id} → server cập nhật DB → cập nhật UI
//   4. User bấm "Thanh toán" → gom cart_ids[] → submit form sang OrderController::checkout()

document.addEventListener('DOMContentLoaded', function () {

    // Lấy tất cả checkbox sản phẩm trong giỏ hàng
    const checkboxes     = document.querySelectorAll('.cart-item-checkbox');

    // Các element hiển thị tổng tiền, số lượng, nút thanh toán
    const displaySubtotal = document.getElementById('display-subtotal'); // hiển thị tạm tính
    const displayVat      = document.getElementById('display-vat');      // hiển thị VAT (nếu có)
    const displayTotal    = document.getElementById('display-total');    // hiển thị tổng cộng
    const displayCount    = document.getElementById('display-count');    // hiển thị số SP đã chọn
    const btnCheckout     = document.getElementById('btn-checkout');     // nút Thanh toán

    // Tính lại tổng tiền dựa trên các checkbox đang được chọn
    function calculateTotal() {
        let subtotal = 0;
        let count    = 0;

        checkboxes.forEach(function (cb) {
            if (cb.checked) {
                // Lấy giá và số lượng từ data attribute của checkbox
                subtotal += parseInt(cb.dataset.price) * parseInt(cb.dataset.quantity);
                count++;
            }
        });

        // Tổng = tạm tính (không tính VAT riêng trong hệ thống này)
        const grandTotal = subtotal;

        // Cập nhật hiển thị giá trên UI
        if (displaySubtotal) displaySubtotal.innerText = subtotal.toLocaleString('vi-VN') + 'đ';
        if (displayTotal)    displayTotal.innerText    = grandTotal.toLocaleString('vi-VN') + 'đ';

        // Cập nhật số lượng SP đã chọn và bật/tắt nút thanh toán
        if (displayCount) displayCount.innerText = count;
        if (btnCheckout)  btnCheckout.disabled   = (count === 0); // disable khi không chọn SP nào
    }

    // ── Xử lý nút +/- số lượng ───────────────────────────────────────
    document.querySelectorAll('.btn-qty-change').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id     = this.dataset.id;     // ID cart item
            const action = this.dataset.action; // 'up' hoặc 'down'
            const qtyInput = document.getElementById('qty-' + id); // ô hiển thị số lượng

            // Gửi AJAX lên server để cập nhật số lượng trong DB
            fetch('/carts/' + id, {
                method: 'PUT',
                headers: {
                    'Content-Type':  'application/json',
                    'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ change: action }) // gửi action 'up'/'down'
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    // Cập nhật số lượng hiển thị trên UI
                    qtyInput.value = data.new_quantity;

                    // Cập nhật data-quantity để calculateTotal() đọc đúng
                    const cb = document.querySelector('.cart-item-checkbox[value="' + id + '"]');
                    if (cb) cb.dataset.quantity = data.new_quantity;

                    // Tính lại tổng tiền
                    calculateTotal();
                }
            });
        });
    });

    // ── Lắng nghe tick/untick checkbox → cập nhật tổng ngay ──────────
    checkboxes.forEach(function (cb) {
        cb.addEventListener('change', calculateTotal);
    });

    // Tính tổng lần đầu khi trang vừa load (có thể đã tick sẵn)
    calculateTotal();

    // ── Xử lý nút Thanh toán ─────────────────────────────────────────
    if (btnCheckout) {
        btnCheckout.addEventListener('click', function () {
            // Gom tất cả ID của SP đang được tick
            const checkedIds = [];
            checkboxes.forEach(function (cb) {
                if (cb.checked) checkedIds.push(cb.value);
            });

            if (checkedIds.length === 0) return; // không có SP nào được chọn thì bỏ qua

            // Tạo các input hidden cart_ids[] và thêm vào form
            // Form sẽ POST sang OrderController::checkout() để lưu vào session
            const idsContainer = document.getElementById('checkout-ids');
            idsContainer.innerHTML = ''; // xóa input cũ nếu có

            checkedIds.forEach(function (id) {
                const input   = document.createElement('input');
                input.type    = 'hidden';
                input.name    = 'cart_ids[]'; // tên array Laravel nhận được
                input.value   = id;
                idsContainer.appendChild(input);
            });

            // Submit form → chuyển sang trang thanh toán
            document.getElementById('checkout-form').submit();
        });
    }
});

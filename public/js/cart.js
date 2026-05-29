document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.cart-item-checkbox');
    const displaySubtotal = document.getElementById('display-subtotal');
    const displayVat = document.getElementById('display-vat'); // Thêm ID này vào Blade
    const displayTotal = document.getElementById('display-total');
    const displayCount = document.getElementById('display-count'); // Thêm ID này vào Blade
    const btnCheckout = document.getElementById('btn-checkout');

    function calculateTotal() {
        let subtotal = 0;
        let count = 0;

        checkboxes.forEach(cb => {
            if (cb.checked) {
                subtotal += (parseInt(cb.dataset.price) * parseInt(cb.dataset.quantity));
                count++;
            }
        });

        // Tính toán VAT 10%
        let vat = subtotal * 0.1;
        let grandTotal = subtotal + vat;

        // Cập nhật giao diện
        if (displaySubtotal) displaySubtotal.innerText = subtotal.toLocaleString('vi-VN') + 'đ';
        if (displayVat) displayVat.innerText = vat.toLocaleString('vi-VN') + 'đ';
        if (displayTotal) displayTotal.innerText = grandTotal.toLocaleString('vi-VN') + 'đ';
        
        // Cập nhật nút thanh toán
        if (displayCount) displayCount.innerText = count;
        if (btnCheckout) btnCheckout.disabled = (count === 0);
    }

    // Xử lý tăng giảm số lượng
    document.querySelectorAll('.btn-qty-change').forEach(btn => {
        btn.addEventListener('click', function() {
            let id = this.dataset.id;
            let action = this.dataset.action;
            let qtyInput = document.getElementById('qty-' + id);
            
            fetch(`/carts/${id}`, {
                method: 'PUT',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content 
                },
                body: JSON.stringify({ change: action })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    qtyInput.value = data.new_quantity;
                    // Cập nhật data-quantity để hàm calculateTotal lấy đúng
                    document.querySelector(`.cart-item-checkbox[value="${id}"]`).dataset.quantity = data.new_quantity;
                    calculateTotal();
                }
            });
        });
    });

    // Lắng nghe sự kiện chọn checkbox
    checkboxes.forEach(cb => cb.addEventListener('change', calculateTotal));
    
    // Tính toán lần đầu khi load trang
    calculateTotal();
});
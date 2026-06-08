/**
 * order-history.js
 * Xử lý modal hoàn trả đơn hàng trong trang lịch sử của user
 */
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('returnOrderModal');
    if (!modal) return;

    modal.addEventListener('show.bs.modal', function (event) {
        var btn = event.relatedTarget;
        var orderId = btn ? btn.getAttribute('data-order-id') : '';

        // Điền số đơn vào text trong modal
        var orderText = document.getElementById('modal-order-text');
        if (orderText) {
            orderText.textContent = '#DH' + orderId;
        }

        // Cập nhật action form theo ID đơn
        var form = document.getElementById('returnOrderForm');
        if (form && window.returnRouteTemplate) {
            form.action = window.returnRouteTemplate.replace(':id', orderId);
        }
    });
});

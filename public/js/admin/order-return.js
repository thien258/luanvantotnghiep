/**
 * Xử lý sự kiện nhấn nút "Hoàn hàng" để truyền dữ liệu động vào Modal
 */
$(document).ready(function() {
    const RETURN_MODAL_CONFIG = {
        triggerBtn: '.btn-trigger-return',
        orderText: '#modal-order-text',
        returnForm: '#returnOrderForm',
        reasonTextarea: '#return_reason'
    };

    // Dùng $(document).on('click') để chống lỗi nút bấm bị liệt 
    $(document).on('click', RETURN_MODAL_CONFIG.triggerBtn, function() {
        let orderId = $(this).data('order-id');
        
        $(RETURN_MODAL_CONFIG.orderText).text('#DH' + orderId);
        
        if (window.returnRouteTemplate) {
            let actionUrl = window.returnRouteTemplate.replace(':id', orderId);
            $(RETURN_MODAL_CONFIG.returnForm).attr('action', actionUrl);
        } else {
            console.error("Lỗi: Không tìm thấy biến toàn cục window.returnRouteTemplate.");
        }
        
        $(RETURN_MODAL_CONFIG.reasonTextarea).val('');
    });
});
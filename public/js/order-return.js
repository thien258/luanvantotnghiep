/**
 * order-return.js
 * Xử lý UI trang hoàn hàng admin
 */
function highlightOption() {
    document.getElementById('label-intact').style.borderColor  = '';
    document.getElementById('label-damaged').style.borderColor = '';

    var selected = document.querySelector('input[name="condition"]:checked');
    if (selected) {
        document.getElementById('label-' + selected.value).style.borderColor = '#000';
    }
}

function handleReasonChange(radio) {
    // Reset border lý do
    ['label-reason-bomb', 'label-reason-return'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.style.borderColor = '';
    });

    var parentLabel = radio.closest('label');
    if (parentLabel) parentLabel.style.borderColor = '#000';

    // Hiện/ẩn nút hoàn tiền
    var refundSection = document.getElementById('refund-section');
    if (refundSection) {
        refundSection.style.display = radio.value === 'return' ? 'block' : 'none';
    }
}

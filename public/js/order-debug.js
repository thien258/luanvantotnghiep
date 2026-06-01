/**
 * order-debug.js
 * Debug log cho trang checkout (có thể xóa khi deploy)
 */
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('btn-submit-payment');
    console.log('btn disabled:', btn ? btn.disabled : 'not found');
    console.log('cart rows:', document.querySelectorAll('.cart-item-row').length);
    var totalEl = document.getElementById('summary-total');
    console.log('total:', totalEl ? totalEl.innerText : 'not found');
});

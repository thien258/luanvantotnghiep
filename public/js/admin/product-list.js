/**
 * product-list.js
 * Xử lý trang danh sách sản phẩm admin:
 *   1. CSS cho border trái theo HSD (thay thế inline style)
 *   2. Checkbox "Chọn tất cả" trong modal yêu cầu nhập hàng
 */

document.addEventListener('DOMContentLoaded', function () {

    // ── 1. Border trái theo HSD ──────────────────────────────────────────
    // Class được gán từ Blade: border-left-danger / border-left-warning
    // CSS đặt ở đây để không cần inline style trong blade
    const style = document.createElement('style');
    style.textContent = `
        .border-left-danger  { border-left: 5px solid #dc3545 !important; }
        .border-left-warning { border-left: 5px solid #fd7e14 !important; }
    `;
    document.head.appendChild(style);

    // ── 2. Checkbox "Chọn tất cả" ────────────────────────────────────────
    const selectAll = document.getElementById('selectAllLow');
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            document.querySelectorAll('.low-stock-check')
                .forEach(cb => cb.checked = this.checked);
        });
    }

});

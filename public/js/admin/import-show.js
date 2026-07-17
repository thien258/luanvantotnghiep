// ── import-show.js — logic trang duyệt phiếu nhập kho ──────────────────

// Chọn tất cả / bỏ chọn
document.getElementById('selectAll')?.addEventListener('change', function () {
    document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = this.checked);
    updateSelectedCount();
});

// Cập nhật số SP đang được chọn
function updateSelectedCount() {
    const selected = document.querySelectorAll('.product-checkbox:checked').length;
    const el = document.getElementById('selectedCount');
    if (el) el.textContent = selected;
}

document.querySelectorAll('.product-checkbox').forEach(cb => {
    cb.addEventListener('change', updateSelectedCount);
});

updateSelectedCount();

// Tính giá bán = giá nhập × (1 + % / 100)
function calcSellPrice(index, markup) {
    const cost = parseFloat(document.querySelector(`.cost-input[data-index="${index}"]`)?.value) || 0;
    const sellEl = document.querySelector(`.sell-input[data-index="${index}"]`);
    if (sellEl) sellEl.value = Math.round(cost * (1 + markup / 100));
}

// Nút "Áp dụng" — áp % markup chung cho tất cả dòng, bắt buộc > 0
document.getElementById('applyGlobalMarkup')?.addEventListener('click', function () {
    const globalPct = parseFloat(document.getElementById('globalMarkup')?.value) || 0;
    if (globalPct <= 0) {
        alert('Phần trăm tăng giá phải lớn hơn 0.');
        document.getElementById('globalMarkup')?.focus();
        return;
    }
    document.querySelectorAll('.cost-input').forEach(el => {
        calcSellPrice(el.dataset.index, globalPct);
    });
});

// Xác nhận trước khi submit form duyệt
function confirmApprove() {
    const selected = document.querySelectorAll('.product-checkbox:checked').length;
    if (selected === 0) {
        alert('Vui lòng chọn ít nhất 1 sản phẩm để duyệt.');
        return false;
    }
    return confirm(`Xác nhận duyệt và nhập kho ${selected} sản phẩm đã chọn?`);
}

// Lọc chỉ hiển thị SP đã nhập
document.getElementById('showImportedOnly')?.addEventListener('change', function () {
    document.querySelectorAll('.import-result-row').forEach(row => {
        row.style.display = (this.checked && row.dataset.imported !== '1') ? 'none' : '';
    });
});

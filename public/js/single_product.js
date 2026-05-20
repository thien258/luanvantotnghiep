document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.variant-btn');
    const displayPrice = document.getElementById('display-price');
    const displayStock = document.getElementById('display-stock');
    const displayStockStatus = document.getElementById('display-stock-status');
    const hiddenVariantId = document.getElementById('hidden-variant-id');
    const btnAddCart = document.getElementById('btn-add-cart');

    function updateVariantData(btn) {
        const id = btn.getAttribute('data-id');
        const price = parseInt(btn.getAttribute('data-price'));
        const stock = parseInt(btn.getAttribute('data-stock'));

        displayPrice.innerText = price.toLocaleString('vi-VN');
        displayStock.innerText = stock;
        hiddenVariantId.value = id;

        if (stock <= 0) {
            displayStockStatus.innerText = "Hết hàng";
            displayStockStatus.className = "fw-bold ms-2 text-danger";
            btnAddCart.disabled = true;
            btnAddCart.innerHTML = '<i class="fa-solid fa-ban me-2"></i> HẾT HÀNG';
            btnAddCart.classList.replace('btn-dark', 'btn-secondary');
        } else {
            displayStockStatus.innerText = "Còn hàng";
            displayStockStatus.className = "fw-bold ms-2 text-success";
            btnAddCart.disabled = false;
            btnAddCart.innerHTML = '<i class="fa-solid fa-shopping-cart me-2"></i> THÊM VÀO GIỎ';
            btnAddCart.classList.replace('btn-secondary', 'btn-dark');
        }
    }

    buttons.forEach(btn => {
        btn.addEventListener('click', function() {
            buttons.forEach(b => b.classList.remove('active', 'btn-dark', 'text-white'));
            this.classList.add('active', 'btn-dark', 'text-white');
            updateVariantData(this);
        });
    });

    if (buttons.length > 0) {
        updateVariantData(buttons[0]);
    }
});
// Khởi tạo mảng lưu dữ liệu danh sách địa chỉ nhận về từ Database
let allAddresses = [];

/**
 * Tự động tìm và lấy Token CSRF từ hệ thống cấu hình trong Blade
 */
const getCsrfToken = () => {
    if (window.LaravelConfig && window.LaravelConfig.csrfToken) {
        return window.LaravelConfig.csrfToken;
    }
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
};

// =========================================================================
// PHẦN 1: TỰ ĐỘNG CHẠY KHI TẢI TRANG (ĐỌC ĐỊA CHỈ MẶC ĐỊNH BAN ĐẦU)
// =========================================================================
document.addEventListener('DOMContentLoaded', function () {
    const url = window.LaravelConfig && window.LaravelConfig.addrIndex ? window.LaravelConfig.addrIndex : '/addresses';
    
    fetch(url)
        .then(r => r.json())
        .then(data => {
            allAddresses = data;
            // Tìm địa chỉ nào có thuộc tính mặc định, nếu không có thì lấy phần tử đầu tiên
            const def = data.find(a => a.is_default) || data[0];
            if (def) {
                syncAddressToMainForm(def);
            }
        }).catch(err => console.log("Không thể tải địa chỉ mặc định ban đầu do chưa có dữ liệu trong DB"));
});

// =========================================================================
// PHẦN 2: LOGIC LIÊN QUAN ĐẾN SỔ ĐỊA CHỈ (MODAL BOX & AJAX)
// =========================================================================

/**
 * Mở hộp thoại Modal và kích hoạt tải danh sách địa chỉ mới nhất
 */
function openAddressModal() {
    loadAddresses();
    new bootstrap.Modal(document.getElementById('addressModal')).show();
}

/**
 * Gọi API Fetch để lấy toàn bộ danh sách địa chỉ của User
 */
function loadAddresses() {
    const url = window.LaravelConfig && window.LaravelConfig.addrIndex ? window.LaravelConfig.addrIndex : '/addresses';
    
    fetch(url)
        .then(r => r.json())
        .then(data => {
            allAddresses = data;
            renderAddressList(data);
        });
}

/**
 * Biên dịch mảng dữ liệu sang mã HTML cấu trúc danh sách địa chỉ hiển thị trong Modal
 */
function renderAddressList(list) {
    const container = document.getElementById('address-list');
    if (!list.length) {
        container.innerHTML = '<div class="text-muted small text-center py-2">Chưa có địa chỉ nào. Hãy thêm mới.</div>';
        return;
    }

    container.innerHTML = list.map(addr => `
        <div class="border p-3 mb-2 ${addr.is_default ? 'border-dark' : 'border-light-subtle'}"
             style="cursor: pointer;" onclick="selectAddressFromModal(${addr.id})">
            <div class="d-flex justify-content-between align-items-start gap-3">
                <div style="min-width:0; flex:1;">
                    <div class="fw-bold small mb-1">${addr.name}</div>
                    ${addr.is_default ? '<div class="mb-1"><span class="badge bg-dark" style="font-size:0.6rem;">Mặc định</span></div>' : ''}
                    <div class="text-secondary small">SĐT: ${addr.phone}</div>
                    <div class="text-secondary small">${addr.address}</div>
                </div>
                <div class="d-flex flex-column align-items-end gap-1 flex-shrink-0" onclick="event.stopPropagation()">
                    ${!addr.is_default ? `<button type="button" class="btn btn-outline-secondary btn-sm rounded-0 py-0 px-2" style="font-size:0.7rem;white-space:nowrap;" onclick="setDefault(${addr.id})">Đặt mặc định</button>` : ''}
                    <button type="button" class="btn btn-outline-dark btn-sm rounded-0 py-0 px-2" style="font-size:0.7rem;" onclick="editAddress(${addr.id})">Sửa</button>
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-0 py-0 px-2" style="font-size:0.7rem;" onclick="deleteAddress(${addr.id})">Xóa</button>
                </div>
            </div>
        </div>
    `).join('');
}

/**
 * Xử lý click chọn một địa chỉ cụ thể trong Modal danh sách
 */
function selectAddressFromModal(id) {
    const addr = allAddresses.find(a => a.id === id);
    if (!addr) return;

    syncAddressToMainForm(addr);

    // Tìm và đóng Modal sổ địa chỉ lại
    const modalEl = document.getElementById('addressModal');
    const modalInstance = bootstrap.Modal.getInstance(modalEl);
    if (modalInstance) modalInstance.hide();
}

/**
 * Hàm đồng bộ dữ liệu địa chỉ ra các nhãn hiển thị chính và các ô input ẩn của <form>
 */
function syncAddressToMainForm(addr) {
    // Đẩy dữ liệu vào mảng input hidden để gửi lên dữ liệu OrderController
    document.getElementById('input-fullname').value = addr.name;
    document.getElementById('input-phone').value = addr.phone;
    document.getElementById('input-address').value = addr.address;

    // Thay đổi văn bản hiển thị trên Card giao diện chính trang Checkout
    document.getElementById('selected-name').textContent = addr.name;
    document.getElementById('selected-phone').textContent = addr.phone;
    document.getElementById('selected-address-text').textContent = addr.address;
}

/**
 * Hiển thị form nhập liệu phụ để chuẩn bị thêm một địa chỉ mới
 */
function showAddForm() {
    document.getElementById('form-title').textContent = 'Thêm địa chỉ mới';
    document.getElementById('edit-address-id').value = '';
    document.getElementById('form-name').value = '';
    document.getElementById('form-phone').value = '';
    document.getElementById('form-address').value = '';
    document.getElementById('form-is-default').checked = false;
    document.getElementById('address-form-wrap').style.display = 'block';
    document.getElementById('btn-add-wrap').style.display = 'none';
}

/**
 * Ẩn form phụ nhập liệu địa chỉ đi
 */
function cancelForm() {
    document.getElementById('address-form-wrap').style.display = 'none';
    document.getElementById('btn-add-wrap').style.display = 'block';
}

/**
 * Kích hoạt lôi dữ liệu cũ nạp lên form phụ để chuẩn bị sửa đổi
 */
function editAddress(id) {
    const addr = allAddresses.find(a => a.id === id);
    if (!addr) return;
    document.getElementById('form-title').textContent = 'Sửa địa chỉ';
    document.getElementById('edit-address-id').value = id;
    document.getElementById('form-name').value = addr.name;
    document.getElementById('form-phone').value = addr.phone;
    document.getElementById('form-address').value = addr.address;
    document.getElementById('form-is-default').checked = addr.is_default;
    document.getElementById('address-form-wrap').style.display = 'block';
    document.getElementById('btn-add-wrap').style.display = 'none';
}

/**
 * Gửi Request (POST hoặc PUT) qua kết nối AJAX để tiến hành lưu địa chỉ vào Database
 */
function saveAddress() {
    const id = document.getElementById('edit-address-id').value;
    const body = {
        name: document.getElementById('form-name').value.trim(),
        phone: document.getElementById('form-phone').value.trim(),
        address: document.getElementById('form-address').value.trim(),
        is_default: document.getElementById('form-is-default').checked,
    };

    if (!body.name || !body.phone || !body.address) {
        alert('Vui lòng điền đầy đủ thông tin trường họ tên, SĐT và địa chỉ cụ thể!');
        return;
    }

    const baseUrl = window.LaravelConfig && window.LaravelConfig.addrStore ? window.LaravelConfig.addrStore : '/addresses';
    const url = id ? `${baseUrl}/${id}` : baseUrl;
    const method = id ? 'PUT' : 'POST';

    fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
        body: JSON.stringify(body),
    })
    .then(r => r.json())
    .then(data => {
        cancelForm();
        loadAddresses();
    });
}

/**
 * Gửi Request xóa bản ghi địa chỉ trong Database theo ID
 */
function deleteAddress(id) {
    if (!confirm('Bạn có chắc chắn muốn xóa vĩnh viễn địa chỉ này?')) return;
    fetch(`/addresses/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': getCsrfToken() },
    })
    .then(r => r.json())
    .then(() => loadAddresses());
}

/**
 * Cập nhật thuộc tính đặt một địa chỉ làm địa chỉ giao nhận mặc định
 */
function setDefault(id) {
    fetch(`/addresses/${id}/default`, {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': getCsrfToken() },
    })
    .then(r => r.json())
    .then(() => loadAddresses());
}


// =========================================================================
// PHẦN 3: QUẢN LÝ TĂNG GIẢM SỐ LƯỢNG & PHƯƠNG THỨC THANH TOÁN
// =========================================================================

/**
 * Xử lý tăng hoặc giảm số lượng chai nước hoa trực tiếp trên Summary
 */
function changeQty(button, action) {
    const row = button.closest('.cart-item-row');
    const input = row.querySelector('.item-qty-input');
    const price = parseInt(row.getAttribute('data-price'));
    const lineTotalDisplay = row.querySelector('.line-total-price');
    
    let currentQty = parseInt(input.value);
    if (action === 'up') {
        currentQty += 1;
    } else if (action === 'down' && currentQty > 1) {
        currentQty -= 1;
    }
    input.value = currentQty;
    
    let newLineTotal = price * currentQty;
    lineTotalDisplay.innerText = newLineTotal.toLocaleString('vi-VN') + 'đ';
    calculateGrandTotal();
}

/**
 * Loại bỏ tạm thời một dòng sản phẩm ra khỏi hóa đơn thanh toán hiện tại
 */
function removeRow(button) {
    if (confirm('Bỏ sản phẩm nước hoa này khỏi danh sách thanh toán đơn hàng?')) {
        const row = button.closest('.cart-item-row');
        row.remove();
        calculateGrandTotal();
        
        const remainingItems = document.querySelectorAll('.cart-item-row');
        if (remainingItems.length === 0) {
            document.getElementById('btn-submit-payment').setAttribute('disabled', 'disabled');
            document.querySelector('.checkout-item-list').innerHTML = '<div class="text-center py-4 text-muted small empty-cart-msg">Giỏ hàng thanh toán trống rỗng.</div>';
        }
    }
}

/**
 * Quét toàn bộ hóa đơn để tính toán và cập nhật lại tổng tiền Hóa đơn real-time
 */
function calculateGrandTotal() {
    let total = 0;
    document.querySelectorAll('.cart-item-row').forEach(row => {
        const price = parseInt(row.getAttribute('data-price'));
        const qty = parseInt(row.querySelector('.item-qty-input').value);
        total += price * qty;
    });
    document.getElementById('summary-subtotal').innerText = total.toLocaleString('vi-VN') + 'đ';
    document.getElementById('summary-total').innerText = total.toLocaleString('vi-VN') + 'đ';
}

/**
 * Xử lý click chọn và đồng bộ giá trị phương thức thanh toán ra thẻ input ẩn gửi lên server
 */
function selectPayment(boxElement, method) {
    // Xóa kích hoạt bôi đen của các box thanh toán khác
    document.querySelectorAll('.btn-payment').forEach(box => {
        box.classList.remove('border-dark');
        box.classList.add('border-light-subtle');
        box.querySelector('div:first-child').className = 'text-secondary mb-2';
        box.querySelector('div:last-child').className = 'text-uppercase fw-bold text-secondary';
    });
    
    // Bôi đậm viền card phương thức vừa chọn
    boxElement.classList.remove('border-light-subtle');
    boxElement.classList.add('border-dark');
    boxElement.querySelector('div:first-child').className = 'text-dark mb-2';
    boxElement.querySelector('div:last-child').className = 'text-uppercase fw-bold text-dark';
    
    // CẬP NHẬT CHÍNH XÁC GIÁ TRỊ VÀO Ô INPUT ẨN ĐỂ CONTROLLER NHẬN BIẾT ĐƯỢC
    document.getElementById('input-payment').value = method;
}
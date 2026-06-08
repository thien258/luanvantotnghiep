/**
 * Bộ lọc đơn hàng bằng AJAX cho hệ thống Quản lý đơn hàng
 * Sử dụng chung Route Index của Controller
 */
$(document).ready(function() {
    // 1. Cấu hình các selector trên giao diện (Có thể đổi tên ID/Class ở đây nếu giao diện của bạn khác)
    const CONFIG = {
        statusSelect: '#filter-status',     // ID của ô select lọc trạng thái
        searchInput: 'input[name="q"]',     // Selector của ô nhập từ khóa tìm kiếm
        searchForm: '#search-form',         // ID của form tìm kiếm (nếu có)
        tableBody: 'table tbody',            // Selector của nơi chứa danh sách hàng (rows)
        orderCount: '.order-count',         // Lớp hiển thị tổng số lượng đơn hàng
        ajaxUrl: window.location.pathname   // Lấy trực tiếp URL của trang hiện tại để gửi Request
    };

    // 2. Hàm chính thực hiện gửi request AJAX để lọc dữ liệu
    function fetchFilteredOrders() {
        // Lấy giá trị hiện tại của bộ lọc trạng thái và từ khóa tìm kiếm
        let statusVal = $(CONFIG.statusSelect).val() || '';
        let keywordVal = $(CONFIG.searchInput).val() || '';

        // Thêm hiệu ứng mờ bảng trong lúc chờ tải dữ liệu (tăng trải nghiệm người dùng)
        $(CONFIG.tableBody).css('opacity', '0.5');

        $.ajax({
            url: CONFIG.ajaxUrl,
            type: "GET",
            data: {
                status: statusVal,
                q: keywordVal
            },
            dataType: "json",
            success: function(response) {
                // Thay thế ruột của bảng bằng dữ liệu HTML mới do Controller render trả về
                $(CONFIG.tableBody).html(response.html);
                
                // Cập nhật lại số đếm tổng đơn hàng trên màn hình (nếu có phần tử hiển thị)
                if ($(CONFIG.orderCount).length > 0) {
                    $(CONFIG.orderCount).text(response.count);
                }
            },
            error: function(xhr, status, error) {
                console.error("Lỗi lọc đơn hàng AJAX:", error);
                alert('Không thể kết nối đến hệ thống để lọc dữ liệu. Vui lòng thử lại!');
            },
            complete: function() {
                // Trả lại độ sáng bình thường cho bảng sau khi tải xong
                $(CONFIG.tableBody).css('opacity', '1');
            }
        });
    }

    // 3. Bắt sự kiện thay đổi (Change) trên ô Select trạng thái
    $(CONFIG.statusSelect).on('change', function() {
        fetchFilteredOrders();
    });

    // 4. Hỗ trợ thêm: Nếu Admin đang gõ tìm kiếm bằng từ khóa, tự động kết hợp lọc luôn mà không cần tải lại trang
    let searchTimeout = null;
    $(CONFIG.searchInput).on('keyup input', function(e) {
        // Nếu nhấn Enter thì chạy lọc ngay lập tức
        if (e.keyCode === 13) {
            e.preventDefault();
            clearTimeout(searchTimeout);
            fetchFilteredOrders();
            return;
        }

        // Chờ Admin gõ xong 500ms (Debounce) rồi mới tự động gửi AJAX để tránh spam request liên tục
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            fetchFilteredOrders();
        }, 500);
    });

    // Chặn sự kiện submit load lại trang của form tìm kiếm gốc (nếu có) để chạy hoàn toàn bằng AJAX
    if ($(CONFIG.searchForm).length > 0) {
        $(CONFIG.searchForm).on('submit', function(e) {
            e.preventDefault();
            fetchFilteredOrders();
        });
    }
});
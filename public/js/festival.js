// festival.js — Đếm ngược thời gian kết thúc Festival
//
// Dùng cho: layout/festival_product.blade.php
// HTML cần có: <div id="festival-countdown" data-end-date="2026-12-31 23:59:59">
//   Bên trong có 4 span: .countdown-days, .countdown-hours, .countdown-minutes, .countdown-seconds
//
// Luồng:
//   1. Đọc data-end-date từ element #festival-countdown
//   2. Mỗi giây cập nhật lại ngày/giờ/phút/giây còn lại
//   3. Khi hết hạn → hiển thị "HẾT HẠN"

(function () {

    // Thêm số 0 ở đầu nếu < 10 (VD: 9 → "09")
    function pad(value) {
        return String(value).padStart(2, '0');
    }

    function initFestivalCountdown() {

        // Tìm element chứa countdown trên trang
        const countdownEl = document.getElementById('festival-countdown');

        // Không có element → thoát (trang không phải festival)
        if (!countdownEl) {
            return;
        }

        // Lấy ngày kết thúc từ data attribute (truyền từ Blade)
        const endDateRaw = countdownEl.dataset.endDate;

        // Không có ngày → hiển thị "Không xác định"
        if (!endDateRaw) {
            countdownEl.innerHTML = '<span class="text-muted">Không xác định</span>';
            return;
        }

        // Chuyển string ngày thành timestamp milliseconds
        const endDate = new Date(endDateRaw).getTime();

        // Ngày không hợp lệ → hiển thị "Không xác định"
        if (Number.isNaN(endDate)) {
            countdownEl.innerHTML = '<span class="text-muted">Không xác định</span>';
            return;
        }

        // Lấy các element con để cập nhật số
        const daysEl    = countdownEl.querySelector('.countdown-days');
        const hoursEl   = countdownEl.querySelector('.countdown-hours');
        const minutesEl = countdownEl.querySelector('.countdown-minutes');
        const secondsEl = countdownEl.querySelector('.countdown-seconds');

        // Hàm tính và cập nhật đồng hồ đếm ngược
        function updateCountdown() {

            // Khoảng cách từ hiện tại đến ngày kết thúc (ms)
            const distance = endDate - Date.now();

            // Hết hạn → thay toàn bộ nội dung bằng thông báo
            if (distance <= 0) {
                countdownEl.innerHTML = '<span class="text-muted fw-bold">HẾT HẠN</span>';
                return false; // trả về false để dừng setInterval
            }

            // Tính ngày, giờ, phút, giây từ milliseconds
            const days    = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours   = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Cập nhật từng element với số đã pad
            daysEl.textContent    = pad(days);
            hoursEl.textContent   = pad(hours);
            minutesEl.textContent = pad(minutes);
            secondsEl.textContent = pad(seconds);

            return true; // còn thời gian → tiếp tục chạy
        }

        // Chạy lần đầu ngay lập tức, nếu còn thời gian thì set interval 1 giây
        if (updateCountdown()) {
            setInterval(updateCountdown, 1000);
        }
    }

    // Khởi chạy khi DOM sẵn sàng
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFestivalCountdown);
    } else {
        initFestivalCountdown(); // DOM đã load rồi
    }

})(); // IIFE — bọc để tránh ô nhiễm global scope

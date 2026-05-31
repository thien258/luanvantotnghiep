(function () {

    function pad(value) {
        return String(value).padStart(2, '0');
    }

    function initFestivalCountdown() {

        const countdownEl = document.getElementById('festival-countdown');

        if (!countdownEl) {
            return;
        }

        const endDateRaw = countdownEl.dataset.endDate;

        if (!endDateRaw) {
            countdownEl.innerHTML = '<span class="text-muted">Không xác định</span>';
            return;
        }

        const endDate = new Date(endDateRaw).getTime();

        if (Number.isNaN(endDate)) {
            countdownEl.innerHTML = '<span class="text-muted">Không xác định</span>';
            return;
        }

        const daysEl = countdownEl.querySelector('.countdown-days');
        const hoursEl = countdownEl.querySelector('.countdown-hours');
        const minutesEl = countdownEl.querySelector('.countdown-minutes');
        const secondsEl = countdownEl.querySelector('.countdown-seconds');

        function updateCountdown() {

            const distance = endDate - Date.now();

            if (distance <= 0) {
                countdownEl.innerHTML = '<span class="text-muted fw-bold">HẾT HẠN</span>';
                return false;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            daysEl.textContent = pad(days);
            hoursEl.textContent = pad(hours);
            minutesEl.textContent = pad(minutes);
            secondsEl.textContent = pad(seconds);

            return true;

        }

        if (updateCountdown()) {
            setInterval(updateCountdown, 1000);
        }

    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFestivalCountdown);
    } else {
        initFestivalCountdown();
    }

})();

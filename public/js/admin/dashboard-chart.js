// dashboard-chart.js — Biểu đồ doanh thu theo tháng trên trang Dashboard admin
//
// Dùng cho: admin/home/home-list.blade.php
// Thư viện: Chart.js (CDN)
//
// Luồng:
//   1. Blade truyền mảng doanh thu 12 tháng (đơn vị VNĐ thô) qua data attribute
//   2. JS đọc → tự chọn đơn vị hiển thị phù hợp (đồng / nghìn / triệu)
//   3. Khởi tạo Bar Chart với Chart.js

document.addEventListener("DOMContentLoaded", function () {

    const canvas = document.getElementById("dashboardBarChart");
    if (!canvas) return;

    // Dữ liệu thô từ controller (đơn vị: VNĐ)
    const rawData = JSON.parse(canvas.getAttribute("data-monthly"));

    // ── Tự động chọn đơn vị phù hợp ────────────────────────────────────
    const maxVal = Math.max(...rawData, 1);

    let divisor, unit;
    if (maxVal >= 1_000_000) {
        divisor = 1_000_000;
        unit    = "Triệu ₫";
    } else if (maxVal >= 1_000) {
        divisor = 1_000;
        unit    = "Nghìn ₫";
    } else {
        divisor = 1;
        unit    = "₫";
    }

    // Làm tròn 1 chữ số thập phân
    const monthlyData = rawData.map(v => Math.round(v / divisor * 10) / 10);

    // Cập nhật label trên card header nếu có
    const unitLabel = document.getElementById("chartUnitLabel");
    if (unitLabel) unitLabel.textContent = unit;

    const ctx = canvas.getContext("2d");

    new Chart(ctx, {
        type: "bar",

        data: {
            labels: ["T1","T2","T3","T4","T5","T6","T7","T8","T9","T10","T11","T12"],
            datasets: [{
                label: "Doanh thu (" + unit + ")",
                backgroundColor:      "#111111",
                hoverBackgroundColor: "#444444",
                data: monthlyData,
                barThickness: 22
            }]
        },

        options: {
            maintainAspectRatio: false,
            responsive: true,

            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return context.parsed.y + " " + unit;
                        }
                    }
                }
            },

            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: "#f8f9fa" },
                    ticks: {
                        callback: function (value) {
                            // Rút gọn nhãn trục Y cho gọn
                            if (divisor === 1_000_000) return value + "M";
                            if (divisor === 1_000)     return value + "K";
                            return value + "₫";
                        },
                        font: { size: 10 }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 10 } }
                }
            }
        }
    });
});

// dashboard-chart.js — Biểu đồ doanh thu theo tháng trên trang Dashboard admin
//
// Dùng cho: admin/home/home-list.blade.php
// Thư viện: Chart.js (CDN)
//
// Luồng:
//   1. Blade truyền mảng doanh thu 12 tháng qua data attribute:
//      <canvas id="dashboardBarChart" data-monthly="[45,30,...]">
//   2. JS đọc data-monthly → JSON.parse → array số
//   3. Khởi tạo Bar Chart với Chart.js

document.addEventListener("DOMContentLoaded", function () {

    // Tìm element canvas chứa biểu đồ
    const canvas = document.getElementById("dashboardBarChart");

    // Nếu không có canvas (trang không phải dashboard) → thoát
    if (!canvas) return;

    // Đọc mảng doanh thu 12 tháng từ data attribute (Blade encode JSON vào HTML)
    // Đơn vị: Triệu VNĐ (chia 1.000.000 ở controller trước khi truyền vào)
    const monthlyData = JSON.parse(canvas.getAttribute("data-monthly"));

    // Lấy context 2D để vẽ biểu đồ
    const ctx = canvas.getContext("2d");

    // Khởi tạo Bar Chart
    new Chart(ctx, {
        type: "bar", // biểu đồ cột

        data: {
            // Nhãn trục X: 12 tháng
            labels: ["T1","T2","T3","T4","T5","T6","T7","T8","T9","T10","T11","T12"],

            datasets: [{
                label: "Doanh thu (Triệu VNĐ)",
                backgroundColor:      "#111111", // màu cột bình thường (đen)
                hoverBackgroundColor: "#444444", // màu cột khi hover (xám đậm)
                data: monthlyData,               // dữ liệu 12 tháng
                barThickness: 22                 // độ rộng mỗi cột (px)
            }]
        },

        options: {
            maintainAspectRatio: false, // cho phép canvas tự co giãn theo container
            responsive: true,           // tự điều chỉnh theo kích thước màn hình

            plugins: {
                legend: { display: false }, // ẩn legend (không cần vì chỉ có 1 dataset)

                // Tùy chỉnh tooltip khi hover
                tooltip: {
                    callbacks: {
                        // Hiển thị giá trị kèm đơn vị "M ₫"
                        label: function (context) {
                            return context.parsed.y + "M ₫";
                        }
                    }
                }
            },

            scales: {
                // Trục Y (dọc)
                y: {
                    beginAtZero: true,           // bắt đầu từ 0
                    grid: { color: "#f8f9fa" },  // màu đường lưới (xám nhạt)
                    ticks: {
                        // Thêm "M" sau mỗi số trên trục Y
                        callback: function (value) { return value + "M"; },
                        font: { size: 10 }
                    }
                },

                // Trục X (ngang)
                x: {
                    grid: { display: false }, // ẩn đường lưới dọc
                    ticks: { font: { size: 10 } }
                }
            }
        }
    });
});

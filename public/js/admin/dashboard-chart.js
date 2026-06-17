document.addEventListener("DOMContentLoaded", function () {
    var canvas = document.getElementById("dashboardBarChart");
    if (!canvas) return;

    // Nhận data từ data attribute (truyền từ blade qua HTML)
    var monthlyData = JSON.parse(canvas.getAttribute("data-monthly"));

    var ctx = canvas.getContext("2d");
    new Chart(ctx, {
        type: "bar",
        data: {
            labels: ["T1","T2","T3","T4","T5","T6","T7","T8","T9","T10","T11","T12"],
            datasets: [{
                label: "Doanh thu (Triệu VNĐ)",
                backgroundColor: "#111111",
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
                            return context.parsed.y + "M ₫";
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: "#f8f9fa" },
                    ticks: {
                        callback: function (value) { return value + "M"; },
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

document.addEventListener("DOMContentLoaded", function () {
    if (!salesData || salesData.length === 0) {
        console.error("No data available for charts.");
        return;
    }

    const labels = salesData.map(item => item.product_name);
    const sales = salesData.map(item => item.sales);

    const ctxBar = document.getElementById('salesChart').getContext('2d');

    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Sales',
                data: sales,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});

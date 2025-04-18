<?php
include 'db_connect.php';

$sql = "SELECT * FROM sales_data ORDER BY date ASC";
$result = $conn->query($sql);

// Prepare data points
$data_points = [];
$total_sales = 0;
$product_sales = [];

while ($row = $result->fetch_assoc()) {
    $data_points[] = $row;
    $total_sales += $row['sales'];

    // Track top product
    $product = $row['product_name'];
    $product_sales[$product] = ($product_sales[$product] ?? 0) + $row['sales'];
}

arsort($product_sales);
$top_product = key($product_sales);

// Re-fetch for table display
$result = $conn->query($sql);
?>

<!-- 
    Integrate the dashboard into your website to make it easier to identify trends and other key insights. 
    A more user-friendly and well-designed interface will help you achieve a higher score.
-->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Dashboard</title>

    <!-- Styles & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css/animate.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: linear-gradient(180deg, #f4f7fa 0%, #eef1f5 100%);
            font-family: 'Inter', sans-serif;
            transition: background-color 0.3s;
        }

        .sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            background-color: #212529;
            color: white;
            padding: 30px 20px;
        }

        .sidebar h2 {
            font-size: 24px;
            font-weight: 700;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin-top: 30px;
        }

        .sidebar ul li {
            margin-bottom: 20px;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            font-size: 16px;
            display: block;
        }

        .main {
            margin-left: 270px;
            padding: 30px;
        }

        .dashboard-header {
            margin-bottom: 30px;
        }

        .kpi-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .kpi-card {
            flex: 1 1 250px;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 15px;
            transition: transform 0.3s ease;
            animation: fadeInUp 0.7s ease both;
        }

        .kpi-card:hover {
            transform: translateY(-5px);
        }

        .kpi-icon {
            font-size: 30px;
            color: #0d6efd;
        }

        .kpi-value {
            font-size: 20px;
            font-weight: 700;
        }

        .chart-section, .table-section {
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            margin-top: 30px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            animation: fadeIn 1s ease;
        }

        .table thead th {
            background-color: #0d6efd;
            color: white;
        }

        .table tbody tr:hover {
            background-color: #f3f4f6;
        }

        .dark-mode-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #0d6efd;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .dark-mode {
            background-color: #1e1e1e;
            color: #eaeaea;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2><i class="bi bi-speedometer2 me-2"></i>Dashboard</h2>
    <ul>
        <li><a href="#"><i class="bi bi-graph-up me-2"></i>Overview</a></li>
        <li><a href="#"><i class="bi bi-box-seam me-2"></i>Products</a></li>
        <li><a href="#"><i class="bi bi-calendar-week me-2"></i>Reports</a></li>
        <li><a href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
    </ul>
</div>

<div class="main">
    <div class="dashboard-header">
        <h2 class="fw-bold">📊 Sales Dashboard</h2>
        <p class="text-muted">Real-time insights from business performance</p>
    </div>

    <div class="kpi-row">
        <div class="kpi-card">
            <i class="bi bi-cash-coin kpi-icon"></i>
            <div>
                <div class="kpi-value">₱<?php echo number_format($total_sales); ?></div>
                <div class="text-muted">Total Sales</div>
            </div>
        </div>
        <div class="kpi-card">
            <i class="bi bi-star-fill kpi-icon"></i>
            <div>
                <div class="kpi-value"><?php echo $top_product; ?></div>
                <div class="text-muted">Top Product</div>
            </div>
        </div>
        <div class="kpi-card">
            <i class="bi bi-calendar2 kpi-icon"></i>
            <div>
                <div class="kpi-value"><?php echo date('F Y'); ?></div>
                <div class="text-muted">Current Month</div>
            </div>
        </div>
    </div>

    <div class="chart-section">
        <h5 class="mb-3 fw-semibold">📈 Sales Trends</h5>
        <canvas id="salesChart"></canvas>
    </div>

    <div class="table-section">
        <h5 class="mb-3 fw-semibold">📋 Sales Breakdown by Product</h5>
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Sales</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row['product_name']; ?></td>
                            <td><?php echo $row['sales']; ?></td>
                            <td><?php echo $row['date']; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<button class="dark-mode-toggle" onclick="document.body.classList.toggle('dark-mode')">🌙 Toggle Dark Mode</button>

<script>
    const salesData = <?php echo json_encode($data_points); ?>;
    const labels = salesData.map(d => d.date);
    const values = salesData.map(d => d.sales);

    const ctx = document.getElementById('salesChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(13, 110, 253, 0.4)');
    gradient.addColorStop(1, 'rgba(13, 110, 253, 0)');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Sales by Date',
                data: values,
                backgroundColor: gradient,
                borderColor: '#0d6efd',
                borderWidth: 1,
                hoverBackgroundColor: '#0b5ed7'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    ticks: { color: '#666' },
                    grid: { display: false }
                },
                y: {
                    ticks: {
                        color: '#666',
                        callback: function(value) {
                            return '₱' + value;
                        }
                    },
                    grid: { color: '#e0e0e0' }
                }
            }
        }
    });
</script>

</body>
</html>
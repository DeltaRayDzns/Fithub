<?php
@include 'config.php';
session_start();

$admin_id = $_SESSION['admin_id'];
if(!isset($admin_id)){
    header('location:login.php');
    exit;
}

// Metrics for dashboard
$metrics = $conn->query("
    SELECT 
        COUNT(*) AS orders_count,
        SUM(total_price) AS total_revenue,
        SUM(payment_status='completed') AS completed_orders,
        SUM(payment_status='pending') AS pending_orders,
        (SELECT COUNT(*) FROM products) AS products_count,
        (SELECT COUNT(*) FROM products WHERE stock < 5) AS low_stock_count,
        (SELECT COUNT(*) FROM users) AS users_count,
        (SELECT COUNT(*) FROM message) AS messages_count
    FROM orders
")->fetch(PDO::FETCH_ASSOC);

// Monthly sales chart
$sales_labels = $sales_data = [];
for($m=1; $m<=12; $m++){ 
    $sales_labels[] = date('M', mktime(0,0,0,$m,1)); 
    $sales_data[$m]=0; 
}

$monthly_sales = $conn->prepare("
    SELECT MONTH(placed_on) AS month, SUM(total_price) AS total
    FROM orders
    WHERE YEAR(placed_on) = YEAR(CURDATE())
    GROUP BY MONTH(placed_on)
");
$monthly_sales->execute();

while($row=$monthly_sales->fetch(PDO::FETCH_ASSOC)){ 
    $sales_data[(int)$row['month']] = (float)$row['total']; 
}

$sales_data_array = [];
for($m=1;$m<=12;$m++){ 
    $sales_data_array[] = $sales_data[$m]; 
}

// Monthly new users chart
$users_labels = $users_data = [];
for($m=1; $m<=12; $m++){ 
    $users_labels[] = date('M', mktime(0,0,0,$m,1)); 
    $users_data[$m] = 0; 
}

$monthly_users = $conn->prepare("
    SELECT MONTH(created_at) AS month, COUNT(*) AS total_users
    FROM users
    WHERE created_at IS NOT NULL
    GROUP BY MONTH(created_at)
");
$monthly_users->execute();

while($row = $monthly_users->fetch(PDO::FETCH_ASSOC)){ 
    $users_data[(int)$row['month']] = (int)$row['total_users']; 
}

$users_data_array = [];
for($m=1; $m<=12; $m++){ 
    $users_data_array[] = $users_data[$m]; 
}

// Orders by completion (Pending / Completed)
$order_completion_counts = $conn->query("
    SELECT payment_status, COUNT(*) AS count
    FROM orders
    GROUP BY payment_status
")->fetchAll(PDO::FETCH_ASSOC);

$completion_labels = $completion_data = [];
foreach($order_completion_counts as $row){
    $completion_labels[] = ucfirst($row['payment_status']); // 'Pending' or 'Completed'
    $completion_data[] = (int)$row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
<link rel="stylesheet" href="css/admin_style.css">
</head>
<body>

<?php include 'admin_header.php'; ?>

<div class="main-content container-fluid mt-4">

    <h1 class="title">Dashboard Summary</h1>

    <div class="row g-4 mb-5">
        <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6">
            <div class="card text-center shadow-sm p-3"> 
                <i class="fas fa-shopping-cart fa-2x mb-1 text-primary"></i>
                <h6><?= $metrics['orders_count'] ?></h6>
                <p class="small">Orders Placed</p>
                <a href="admin_orders.php" class="btn btn-primary btn-sm">View</a>
            </div>
        </div>

        <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6">
            <div class="card text-center shadow-sm p-3">
                <i class="fas fa-hourglass-half fa-2x mb-1 text-warning"></i>
                <h6><?= $metrics['pending_orders'] ?></h6>
                <p class="small">Pending Orders</p>
                <a href="admin_orders_pending.php" class="btn btn-warning btn-sm">Review</a>
            </div>
        </div>

        <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6">
            <div class="card text-center shadow-sm p-3">
                <i class="fas fa-check fa-2x mb-1 text-success"></i>
                <h6><?= $metrics['completed_orders'] ?></h6>
                <p class="small">Completed Orders</p>
                <a href="admin_orders_completed.php" class="btn btn-success btn-sm">View</a>
            </div>
        </div>

        <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6">
            <div class="card text-center shadow-sm p-3">
                <i class="fas fa-peso-sign fa-2x mb-1 text-success"></i>
                <h6>₱<?= number_format($metrics['total_revenue'] ?? 0, 0) ?></h6>
                <p class="small">Total Revenue</p>
                <a href="admin_orders.php" class="btn btn-success btn-sm">Details</a>
            </div>
        </div>

        <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6">
            <div class="card text-center shadow-sm p-3">
                <i class="fas fa-box fa-2x mb-1 text-info"></i>
                <h6><?= $metrics['products_count'] ?></h6>
                <p class="small">Total Products</p>
                <a href="admin_products.php" class="btn btn-info btn-sm">Manage</a>
            </div>
        </div>

        <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6">
            <div class="card text-center shadow-sm p-3">
                <i class="fas fa-exclamation-triangle fa-2x mb-1 text-danger"></i>
                <h6><?= $metrics['low_stock_count'] ?></h6>
                <p class="small">Low Stock Alert</p>
                <a href="admin_stocks.php?low_stock=1" class="btn btn-danger btn-sm">Check</a>
            </div>
        </div>

        <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6">
            <div class="card text-center shadow-sm p-3">
                <i class="fas fa-users fa-2x mb-1 text-secondary"></i>
                <h6><?= $metrics['users_count'] ?></h6>
                <p class="small">Total Users</p>
                <a href="admin_users.php" class="btn btn-secondary btn-sm">Manage</a>
            </div>
        </div>

        <div class="col-xl-3 col-lg-3 col-md-4 col-sm-6">
            <div class="card text-center shadow-sm p-3">
                <i class="fas fa-envelope fa-2x mb-1 text-secondary"></i>
                <h6><?= $metrics['messages_count'] ?></h6>
                <p class="small">Total Messages</p>
                <a href="admin_contacts.php" class="btn btn-secondary btn-sm">View</a>
            </div>
        </div>
    </div>

    <h4 class="mt-4 mb-3">Quick Actions</h4>
    <div class="d-flex flex-wrap gap-3 mb-5">
        <a href="admin_products.php" class="btn btn-success"><i class="fas fa-plus me-2"></i> Add Product</a>
        <a href="admin_orders.php" class="btn btn-primary"><i class="fas fa-shopping-cart me-2"></i> View Orders</a>
        <a href="admin_orders_pending.php" class="btn btn-warning"><i class="fas fa-hourglass-half me-2"></i> Pending Orders</a>
        <a href="admin_users.php" class="btn btn-info"><i class="fas fa-users me-2"></i> Manage Users</a>
        <a href="admin_contacts.php" class="btn btn-secondary"><i class="fas fa-envelope me-2"></i> Messages</a>
        <a href="admin_stocks.php?low_stock=1" class="btn btn-danger"><i class="fas fa-boxes me-2"></i> Low Stock (<?= $metrics['low_stock_count'] ?>)</a>
    </div>

    <div class="row g-4 mt-4">
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card shadow-sm p-3">
                <h5 class="card-title">Monthly Sales (<?= date('Y') ?>)</h5>
                <div class="chart-container"> 
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card shadow-sm p-3">
                <h5 class="card-title">New Users</h5>
                <div class="chart-container"> 
                    <canvas id="usersChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card shadow-sm p-3">
                <h5 class="card-title">Orders by Completion</h5>
                <div class="chart-container"> 
                    <canvas id="completionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('salesChart').getContext('2d'),{
    type:'line',
    data:{ 
        labels:<?= json_encode($sales_labels); ?>, 
        datasets:[{
            label:'Sales (₱)',
            data:<?= json_encode($sales_data_array); ?>, 
            backgroundColor:'rgba(22,163,74,0.2)', 
            borderColor:'rgba(22,163,74,1)', 
            borderWidth:2, 
            tension:0.3 
        }]
    },
    options:{ 
        responsive:true, 
        maintainAspectRatio: false,
        scales:{ 
            y:{ beginAtZero:true } 
        },
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

new Chart(document.getElementById('usersChart').getContext('2d'),{
    type:'bar',
    data:{
        labels: <?= json_encode($users_labels); ?>,
        datasets:[{
            label:'New Users',
            data: <?= json_encode($users_data_array); ?>,
            backgroundColor:'rgba(14,165,233,0.7)',
            borderColor:'rgba(14,165,233,1)',
            borderWidth:1
        }]
    },
    options:{ 
        responsive:true, 
        maintainAspectRatio: false, 
        scales:{ 
            y:{ beginAtZero:true } 
        },
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

new Chart(document.getElementById('completionChart').getContext('2d'),{
    type:'pie',
    data:{
        labels:<?= json_encode($completion_labels); ?>,
        datasets:[{ 
            data:<?= json_encode($completion_data); ?>, 
            backgroundColor:['rgba(255,193,7,0.7)','rgba(40,167,69,0.7)'] 
        }]
    },
    options:{ 
        responsive:true,
        maintainAspectRatio: false, 
        aspectRatio: 1, 
        plugins: {
            legend: {
                position: 'bottom', 
                labels: {
                    padding: 20
                }
            }
        }
    }
});
</script>

<script src="js/admin_script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

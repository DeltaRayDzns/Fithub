<?php
@include 'config.php';
session_start();

$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)) {
    header('location:login.php');
    exit;
}

// 1. Get Product ID
$pid = isset($_GET['pid']) ? filter_var($_GET['pid'], FILTER_SANITIZE_NUMBER_INT) : null;

if (!$pid) {
    header('location:admin_stocks.php');
    exit;
}

// 2. Fetch Product Details
$select_product = $conn->prepare("SELECT name, stock FROM `products` WHERE id = ?");
$select_product->execute([$pid]);
$product_details = $select_product->fetch(PDO::FETCH_ASSOC);

if (!$product_details) {
    header('location:admin_stocks.php');
    exit;
}

$product_name = htmlspecialchars($product_details['name']);
$current_stock = $product_details['stock'];

// 3. Fetch Stock History - REVISED QUERY (No change needed here from last revision)
$select_history = $conn->prepare("
    SELECT sh.*, u.name AS admin_name 
    FROM `stock_history` sh 
    JOIN `users` u ON sh.admin_id = u.id
    WHERE sh.product_id = ? 
    ORDER BY sh.restock_date DESC
");
$select_history->execute([$pid]);
$history_records = $select_history->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock History for <?= $product_name; ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/admin_style.css">

    <style>
        .table th, .table td { vertical-align: middle; }
        .restock-quantity { 
            font-size: 1.2rem; 
            font-weight: 700;
            color: #198754; /* Success color */
        }
        .admin-name {
            font-style: italic;
            color: #6c757d; /* Muted color */
        }
    </style>
</head>
<body>
<?php include 'admin_header.php'; ?>

<div class="main-content container-fluid mt-4">

    <h1 class="title text-center pb-3 mb-4 border-bottom">
        <i class="fas fa-box-open me-2 text-primary"></i> Stock Audit Log: **<?= $product_name; ?>**
    </h1>

    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
             <div class="card text-center bg-white shadow-lg border-0 rounded-3">
                <div class="card-body py-4">
                    <p class="mb-1 text-muted fs-6 text-uppercase">Current Inventory Level</p>
                    <span class="display-3 fw-bolder text-success"><?= $current_stock; ?></span>
                    <p class="card-text text-secondary">units currently in stock</p>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="h5 mb-0 text-secondary">Restocking Events</h3>
        <a href="admin_stocks.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back to Management</a>
    </div>

    <div class="table-responsive shadow rounded-3 border">
        <table class="table table-light table-hover align-middle mb-0">
            <thead class="table-primary">
                <tr>
                    <th scope="col" class="text-center">#</th>
                    <th scope="col">Restock Date</th>
                    <th scope="col" class="text-center">Quantity Added</th>
                    <th scope="col" class="text-center">New Total Stock</th>
                    <th scope="col">Restocked By</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            if (count($history_records) > 0) {
                $count = 1;
                foreach ($history_records as $record) { 
            ?>
                <tr class="restock-row">
                    <td class="text-center text-muted"><?= $count++; ?></td>
                    <td>
                        <div class="fw-bold"><?= date('M d, Y', strtotime($record['restock_date'])); ?></div>
                        <small class="text-muted"><i class="fas fa-clock me-1"></i> <?= date('h:i:s A', strtotime($record['restock_date'])); ?></small>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-success bg-opacity-75 text-white p-2 restock-quantity">
                           <i class="fas fa-plus-circle me-1"></i> +<?= $record['quantity_added']; ?>
                        </span>
                    </td>
                    <td class="text-center fs-5 fw-bold text-primary">
                        <?= $record['new_total_stock']; ?>
                    </td>
                    <td>
                        <i class="fas fa-user-shield me-1"></i> 
                        <span class="admin-name"><?= htmlspecialchars($record['admin_name']); ?></span>
                    </td>
                </tr>
            <?php 
                } 
            } else {
                echo '<tr><td colspan="5" class="text-center p-4 alert alert-warning mb-0"><i class="fas fa-exclamation-circle me-2"></i> No restock history found for this product yet.</td></tr>';
            }
            ?>
            </tbody>
        </table>
    </div>

</div>
<script src="js/admin_script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
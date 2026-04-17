<?php
@include 'config.php';
session_start();
$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)){ header('location:login.php'); exit; }

$message = [];

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_orders = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
    $delete_orders->execute([$delete_id]);
    header('location:admin_orders_completed.php');
    exit;
}

$select_orders = $conn->prepare("SELECT * FROM `orders` WHERE payment_status='completed' ORDER BY placed_on DESC");
$select_orders->execute();

$summary = $conn->query("
    SELECT 
        SUM(payment_status='pending') AS pending_total,
        SUM(payment_status='completed') AS completed_total
    FROM orders
")->fetch(PDO::FETCH_ASSOC);

$order_view_mode = 'completed';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Completed Orders</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/admin_style.css">
</head>
<body>
<?php include 'admin_header.php'; ?>

<div class="main-content container-fluid py-4">
    <h1 class="text-center fw-bold mb-4">Completed Orders</h1>
    <a href="admin_orders.php" class="btn btn-secondary mb-4"><i class="fas fa-arrow-left me-2"></i> Back to Active Orders</a>
    <?php foreach($message as $msg): ?>
        <div class="alert alert-info alert-dismissible fade show mb-3">
            <i class="fas fa-info-circle me-2"></i><?= htmlspecialchars($msg); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endforeach; ?>

    <div class="row g-4">
        <?php if($select_orders->rowCount() > 0): ?>
            <?php while($order = $select_orders->fetch(PDO::FETCH_ASSOC)): ?>
                <?php include 'order_card.php'; ?>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center p-5 bg-white shadow rounded">No completed orders!</p>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/admin_script.js"></script>
</body>
</html>

<?php
@include 'config.php';
session_start();
$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)) {
    header('location:login.php');
    exit;
}

$message = [];

if (isset($_POST['update_order'])) {
    $order_id = $_POST['order_id'];
    $new_status = filter_var($_POST['update_payment'], FILTER_SANITIZE_STRING);

    $select_order = $conn->prepare("SELECT payment_status FROM `orders` WHERE id = ?");
    $select_order->execute([$order_id]);
    $order = $select_order->fetch(PDO::FETCH_ASSOC);

    if ($order && $order['payment_status'] !== $new_status) {
        $update_status = $conn->prepare("UPDATE `orders` SET payment_status = ? WHERE id = ?");
        $update_status->execute([$new_status, $order_id]);
        $message[] = 'Payment status updated!';
    } else {
        $message[] = 'Payment status unchanged.';
    }
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_orders = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
    $delete_orders->execute([$delete_id]);
    header('location:admin_orders_pending.php');
    exit;
}

$select_orders = $conn->prepare("SELECT * FROM `orders` WHERE payment_status='pending' ORDER BY placed_on DESC");
$select_orders->execute();

$summary = $conn->query("
    SELECT 
        SUM(payment_status='pending') AS pending_total,
        SUM(payment_status='completed') AS completed_total
    FROM orders
")->fetch(PDO::FETCH_ASSOC);

$order_view_mode = 'pending';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pending Orders</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/admin_style.css">
</head>
<body>
<?php include 'admin_header.php'; ?>

<div class="main-content container-fluid py-4">
    <h1 class="text-center fw-bold mb-4">Pending Orders</h1>
    <a href="admin_orders.php" class="btn btn-secondary mb-4"><i class="fas fa-arrow-left me-2"></i> Back to Active Orders</a>
    <?php if (!empty($message)): ?>
        <?php if (is_array($message)): ?>
            <?php foreach ($message as $msg): ?>
                <div class="alert alert-info alert-dismissible fade show mb-3">
                    <i class="fas fa-info-circle me-2"></i><?= htmlspecialchars($msg); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info alert-dismissible fade show mb-3">
                <i class="fas fa-info-circle me-2"></i><?= htmlspecialchars((string)$message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="row g-4">
        <?php if($select_orders->rowCount() > 0): ?>
            <?php while($order = $select_orders->fetch(PDO::FETCH_ASSOC)): ?>
                <?php include 'order_card.php'; ?>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center p-5 bg-white shadow rounded">No pending orders!</p>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/admin_script.js"></script>
</body>
</html>

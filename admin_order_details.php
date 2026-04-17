<?php
@include 'config.php';
session_start();

$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)) {
    header('location:login.php');
    exit;
}

if (!isset($_GET['order_id'])) {
    header('location:admin_orders.php');
    exit;
}

$order_id = $_GET['order_id'];

$select_order = $conn->prepare("SELECT * FROM `orders` WHERE id = ?");
$select_order->execute([$order_id]);

if ($select_order->rowCount() == 0) {
    echo "<script>alert('Order not found!'); window.location='admin_orders.php';</script>";
    exit;
}

$order = $select_order->fetch(PDO::FETCH_ASSOC);

$select_user = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
$select_user->execute([$order['user_id']]);
$user = $select_user->fetch(PDO::FETCH_ASSOC);

function get_status_badge_class($status) {
    switch ($status) {
        case 'completed':
            return 'completed';
        case 'cancelled':
            return 'cancelled'; 
        case 'pending':
        default:
            return 'pending';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order Details - #<?= htmlspecialchars($order['id']); ?> | Admin Panel</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
<link rel="stylesheet" href="css/admin_style.css">
<link rel="stylesheet" href="css/admin_theme.css">
<style>
body {
    background-color: #f8fafc;
}
.order-details-card {
    border-left: 5px solid var(--primary);
}
.order-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.order-header h1 {
    font-size: 1.8rem;
    color: var(--primary);
}
.badge-status {
    font-size: 0.95rem;
    padding: 0.4em 0.75em;
    border-radius: 8px;
    font-weight: 600;
}
.badge-status.completed {
    background-color: #16a34a; 
    color: white;
}
.badge-status.pending {
    background-color: #facc15; 
    color: #000;
}
.badge-status.cancelled { 
    background-color: #dc3545; 
    color: white;
}
.img-preview {
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    transition: transform 0.2s ease;
}
.img-preview:hover {
    transform: scale(1.05);
}
.btn-back {
    background-color: #6c757d;
    color: #fff;
    border: none;
}
.btn-back:hover {
    background-color: #7c848bff;
}
</style>
</head>

<body>
<?php include 'admin_header.php'; ?>

<div class="container mt-4 mb-5">
    <div class="card shadow-sm p-4 order-details-card">
        <div class="order-header mb-3">
            <h1><i class="fas fa-file-invoice me-2"></i> Order #<?= htmlspecialchars($order['id']); ?></h1>
            <span class="badge-status <?= get_status_badge_class($order['payment_status']); ?>">
                <?= ucfirst(htmlspecialchars($order['payment_status'])); ?>
            </span>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm p-3 h-100">
                    <h5 class="fw-bold text-primary mb-3"><i class="fas fa-user me-2"></i> Customer Information</h5>
                    <p><strong>Name:</strong> <?= htmlspecialchars($order['name']); ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($order['email']); ?></p>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($order['number']); ?></p>
                    <p><strong>Address:</strong> <?= htmlspecialchars($order['address']); ?></p>
                    <?php if ($user): ?>
                        <p><strong>Account Created:</strong> <?= htmlspecialchars($user['created_at'] ?? 'N/A'); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border-0 shadow-sm p-3 h-100">
                    <h5 class="fw-bold text-primary mb-3"><i class="fas fa-box-open me-2"></i> Order Information</h5>
                    <p><strong>Products:</strong> <?= htmlspecialchars($order['total_products']); ?></p>
                    <p><strong>Total Price:</strong> <span class="fw-bold text-success">₱<?= number_format($order['total_price'], 2); ?></span></p>
                    <p><strong>Payment Method:</strong> <?= htmlspecialchars($order['method']); ?></p>
                    <p><strong>Placed On:</strong> <?= htmlspecialchars($order['placed_on']); ?></p>
                    
                    <?php if ($order['payment_status'] == 'cancelled' && !empty($order['cancel_reason'])): ?>
                        <div class="alert alert-danger p-2 mt-3 mb-0 small">
                            <i class="fas fa-times-circle me-1"></i>
                            <strong>Cancellation Reason:</strong> <?= htmlspecialchars($order['cancel_reason']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if (!empty($order['image'])): 
            $gcash_path = 'gcash/' . basename($order['image']); 
        ?>
        <div class="card shadow-sm border-0 p-4 mt-4">
            <h5 class="fw-bold text-primary mb-3"><i class="fas fa-image me-2"></i> GCash Screenshot</h5>
            <div class="dropdown my-2">
                <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    View GCash Screenshot
                </button>
                <ul class="dropdown-menu p-3 text-center">
                    <li>
                        <a href="<?= $gcash_path; ?>" target="_blank" class="dropdown-item text-primary mb-2">
                            Open Image in New Tab
                        </a>
                    </li>
                    <li>
                        <img src="<?= $gcash_path; ?>" alt="GCash Screenshot" class="img-fluid rounded" style="max-width:350px;">
                    </li>
                </ul>
            </div>
        </div>
        <?php endif; ?>


        <?php if (!empty($order['id_image_proof'])):
            $id_path = 'id_uploads/' . basename($order['id_image_proof']); 
        ?>
        <div class="card shadow-sm border-0 p-4 mt-4">
            <h5 class="fw-bold text-primary mb-3"><i class="fas fa-id-card me-2"></i> Proof of ID</h5>
            <div class="dropdown my-2">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    View Proof of ID
                </button>
                <ul class="dropdown-menu p-3 text-center">
                    <li>
                        <a href="<?= $id_path; ?>" target="_blank" class="dropdown-item text-secondary mb-2">
                            Open ID in New Tab
                        </a>
                    </li>
                    <li>
                        <img src="<?= $id_path; ?>" alt="Proof of ID" class="img-fluid rounded" style="max-width:350px;">
                    </li>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mt-4">
            <a href="admin_orders.php" class="btn btn-back"><i class="fas fa-arrow-left me-2"></i> Back to Orders</a>
            
            <?php if ($order['payment_status'] == 'pending'): ?>
            <form action="admin_orders_pending.php" method="POST" class="d-inline">
                <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id']); ?>">
                <input type="hidden" name="update_payment" value="completed">
                <input type="hidden" name="update_order" value="1">
                <button type="submit" class="btn btn-success shadow-sm">
                    <i class="fas fa-check-circle me-1"></i> Mark as Completed
                </button>
            </form>
            <?php elseif ($order['payment_status'] == 'completed'): ?>
            <a href="admin_orders_completed.php?archive=<?= $order['id']; ?>" class="btn btn-secondary shadow-sm" onclick="return confirm('Archive this completed order?');">
                <i class="fas fa-archive me-1"></i> Archive Order
            </a>
            <?php elseif ($order['payment_status'] == 'cancelled'): ?>
            <a href="admin_orders_cancelled.php?delete=<?= $order['id']; ?>" class="btn btn-danger shadow-sm" onclick="return confirm('Permanently delete this cancelled order?');">
                <i class="fas fa-trash me-1"></i> Delete Order
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/admin_script.js"></script>
</body>
</html>
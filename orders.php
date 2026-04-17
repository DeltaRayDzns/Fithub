<?php
@include 'config.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    header('Content-Type: application/json');
    $order_id = (int) $_POST['order_id'];
    $reason = trim($_POST['reason']);

    if (!$user_id) {
        echo json_encode(['status' => 'error', 'message' => 'Please log in first.']);
        exit;
    }

    if (empty($reason)) {
        echo json_encode(['status' => 'error', 'message' => 'Please provide a cancellation reason.']);
        exit;
    }

    try {
        $update = $conn->prepare("UPDATE `orders` SET payment_status = 'cancelled', cancel_reason = ?, cancel_date = NOW() WHERE id = ? AND user_id = ? AND payment_status = 'pending'");
        $update->execute([$reason, $order_id, $user_id]);

        if ($update->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Order cancelled successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Order could not be cancelled (maybe already processed).']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>My Orders</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
   <link rel="stylesheet" href="css/style.css">

   <style>
      body { background-color: #C4DFF5; font-family: "Poppins", sans-serif; }
      .placed-orders { padding: 60px 0; min-height: 80vh; }
      .title { text-align: center; font-size: 3.5rem; font-weight: 700; color: #333; margin-bottom: 30px; }
      .filter-container { text-align: center; margin-bottom: 25px; }
      .filter-container select { padding: 8px 12px; border-radius: 6px; border: 1px solid #ccc; font-size: 16px; cursor: pointer; }
      .orders-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 25px; max-width: 1200px; margin: 0 auto; padding: 0 20px; }
      .order-box { background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08); padding: 25px 35px; border: 1px dashed #999; transition: 0.3s; font-family: "Courier New", monospace; position: relative; }
      .order-box:hover { transform: translateY(-5px); box-shadow: 0 8px 18px rgba(0, 0, 0, 0.12); }
      .order-box h3 { text-align: center; font-size: 1.4rem; font-weight: bold; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 1px; }
      .order-box hr { border: none; border-top: 1px dashed #aaa; margin: 10px 0; }
      .order-box p { margin: 6px 0; font-size: 15px; color: #333; }
      .price { color: #007bff; font-weight: bold; }
      .status-line { text-align: center; margin-top: 10px; font-size: 15px; padding-top: 10px; border-top: 1px dashed #ccc; font-weight: bold; }
      .pending { color: #dc3545; }
      .completed { color: #28a745; }
      .cancelled { color: #6c757d; }
      .receipt-footer { text-align: center; font-size: 15px; color: #666; margin-top: 10px; }
      .cancel-btn { font-family: "Poppins", sans-serif; background: #dc3545; color: #fff; border: none; padding: 8px 15px; border-radius: 6px; display: block; margin: 10px auto; font-weight: 600; transition: 0.3s; }
      .cancel-btn:hover { background: #b02a37; }
      .orders-empty { text-align: center; color: #555; font-size: 1.3rem; font-weight: 500; background: #fff; border-radius: 1.5em; box-shadow: 0 3px 10px rgba(0,0,0,0.05); max-width: 500px; margin: 5px auto; padding: 40px 30px; }
      .orders-empty a { display: inline-block; margin-top: 15px; padding: 10px 25px; background: #007bff; color: #fff; border-radius: .5rem; text-decoration: none; transition: 0.3s; }
      .orders-empty a:hover { background: #0056b3; }
   </style>
</head>
<body>

<?php include 'header.php'; ?>

<section class="placed-orders">
   <h1 class="title">My Orders</h1>

   <div class="filter-container">
      <label for="orderFilter" class="me-2 fw-bold">Filter:</label>
      <select id="orderFilter">
         <option value="pending" selected>Pending</option>
         <option value="all">All</option>
         <option value="completed">Completed</option>
         <option value="cancelled">Cancelled</option>
      </select>
   </div>

   <div class="orders-container" id="ordersContainer">
      <?php
      if (!$user_id) {
         echo '
         <div class="orders-empty">
            <p>You’re not logged in yet.</p>
            <p>If you want to place or view your orders, please log in or sign up.</p>
            <a href="login.php">Log In</a>
            <a href="register.php" style="background:#28a745; margin-left:10px;">Sign Up</a>
         </div>';
      } else {
         try {
            $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ? ORDER BY placed_on DESC");
            $select_orders->execute([$user_id]);

            if ($select_orders->rowCount() > 0) {
               while ($order = $select_orders->fetch(PDO::FETCH_ASSOC)) {
                  $status = strtolower($order['payment_status']);
                  ?>
                  <div class="order-box" data-status="<?= $status; ?>">
                     <h3>Order Receipt</h3>
                     <hr>
                     <p>Placed on: <span><?= htmlspecialchars($order['placed_on']); ?></span></p>
                     <p>Name: <span><?= htmlspecialchars($order['name']); ?></span></p>
                     <p>Contact: <span><?= htmlspecialchars($order['number']); ?></span></p>
                     <p>Email: <span><?= htmlspecialchars($order['email']); ?></span></p>
                     <p>Address: <span><?= htmlspecialchars($order['address']); ?></span></p>
                     <hr>
                     <p>Payment Method: <span><?= htmlspecialchars(ucwords($order['method'])); ?></span></p>
                     <p>Items: <span><?= htmlspecialchars($order['total_products']); ?></span></p>
                     <p>Total Price: <span class="price">₱<?= htmlspecialchars($order['total_price']); ?></span></p>
                     <div class="status-line">
                        Status:
                        <span class="<?= htmlspecialchars($status); ?>">
                           <?= htmlspecialchars(ucfirst($status)); ?>
                        </span>
                     </div>

                     <?php if ($status === 'pending'): ?>
                        <button class="cancel-btn" data-id="<?= $order['id']; ?>" data-bs-toggle="modal" data-bs-target="#cancelModal">Cancel Order</button>
                     <?php elseif ($status === 'cancelled'): ?>
                        <p class="text-center text-muted mt-3">Cancelled on <?= htmlspecialchars($order['cancel_date']); ?><br>Reason: <em><?= htmlspecialchars($order['cancel_reason']); ?></em></p>
                     <?php endif; ?>

                     <div class="receipt-footer">Thank you for shopping with us!</div>
                  </div>
                  <?php
               }
            } else {
               echo '<div class="orders-empty"><p>No orders placed yet!</p></div>';
            }
         } catch (PDOException $e) {
            echo '<div class="orders-empty"><p>Error loading orders: ' . htmlspecialchars($e->getMessage()) . '</p></div>';
         }
      }
      ?>
   </div>
</section>

<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="cancelForm">
        <div class="modal-header">
          <h5 class="modal-title" id="cancelModalLabel">Cancel Order</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="order_id" id="cancel_order_id">
          <div class="mb-3">
            <label for="cancel_reason" class="form-label">Please enter your reason for cancellation:</label>
            <textarea name="reason" id="cancel_reason" class="form-control" rows="3" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="cancel_order" class="btn btn-danger w-100">Confirm Cancellation</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    $('.cancel-btn').click(function() {
        $('#cancel_order_id').val($(this).data('id'));
        $('#cancel_reason').val('');
    });

    $('#cancelForm').submit(function(e) {
        e.preventDefault();
        const reason = $('#cancel_reason').val().trim();
        if (!reason) {
            alert('Please enter a reason for cancellation.');
            return;
        }
        $.ajax({
            type: 'POST',
            url: '',
            data: $(this).serialize() + '&cancel_order=1',
            dataType: 'json',
            success: function(response) {
                alert(response.message);
                if (response.status === 'success') location.reload();
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
   const filterSelect = document.getElementById('orderFilter');
   const orders = document.querySelectorAll('.order-box');
   function applyFilter(filter) {
      orders.forEach(order => {
         const status = order.getAttribute('data-status');
         order.style.display = (filter === 'all' || status === filter) ? 'block' : 'none';
      });
   }
   applyFilter('pending');
   filterSelect.addEventListener('change', function() {
      applyFilter(this.value);
   });
});
</script>

</body>
</html>

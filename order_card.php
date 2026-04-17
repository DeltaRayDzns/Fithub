<div class="col-xl-4 col-lg-6 col-md-6 col-sm-12">
    <div class="card bg-white shadow-sm p-4 h-100 border-0 card-shadow-hover position-relative">

        <?php if (isset($order_view_mode) && in_array($order_view_mode, ['completed', 'pending', 'cancelled'])): ?>
            <?php 
                $badge_class = ($order['payment_status'] == 'pending') ? 'bg-warning text-dark' : (($order['payment_status'] == 'cancelled') ? 'bg-danger' : 'bg-success'); 
            ?>
            <span class="position-absolute top-0 end-0 badge rounded-pill m-3 p-2 fw-normal <?= $badge_class; ?>" style="z-index: 10;">
                <?= ucfirst($order['payment_status']); ?>
            </span>
        <?php endif; ?>

        <h5 class="card-title order-card-title text-uppercase border-bottom pb-2 mb-3">
            <i class="fas fa-receipt me-2"></i> Order #<?= $order['id']; ?>
            <?php if (isset($order_view_mode) && $order_view_mode == 'all'): ?>
                <?php 
                    $badge_class_all = ($order['payment_status'] == 'pending') ? 'bg-warning text-dark' : (($order['payment_status'] == 'cancelled') ? 'bg-danger' : 'bg-success'); 
                ?>
                <span class="badge float-end <?= $badge_class_all; ?>">
                    <?= ucfirst($order['payment_status']); ?>
                </span>
            <?php endif; ?>
        </h5>

        <div class="card-text mb-3">
            <p><strong>User ID:</strong> <?= htmlspecialchars($order['user_id']); ?></p>
            <p><strong>Name:</strong> <?= htmlspecialchars($order['name']); ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($order['email']); ?></p>
            <p><strong>Number:</strong> <?= htmlspecialchars($order['number']); ?></p>
            <p><strong>Address:</strong> <?= htmlspecialchars($order['address']); ?></p>

            <div class="mb-2">
                <strong>Products:</strong>
                <ul class="mb-0 ps-3">
                    <?php
                    $products_raw = $order['total_products'] ?? '';
                    
                    if (is_array($products_raw)) {
                        $products = $products_raw; 
                    } else {
                        $products = array_filter(array_map('trim', explode(',', (string)$products_raw)));
                    }

                    if (!empty($products)):
                        foreach ($products as $product):
                            ?>
                            <li><?= htmlspecialchars($product); ?></li>
                            <?php
                        endforeach;
                    else:
                        ?>
                        <li class="text-muted">No products listed</li>
                        <?php
                    endif;
                    ?>
                </ul>
            </div>

            <p><strong>Total Price:</strong> 
                <span class="text-success fw-bold">₱<?= htmlspecialchars($order['total_price']); ?>/-</span>
            </p>
            <p><strong>Payment Method:</strong> <?= htmlspecialchars($order['method']); ?></p>
            
            <?php 
            if ($order['payment_status'] == 'cancelled' && !empty($order['cancel_reason'])): 
            ?>
                <p>
                    <strong class="text-danger">Cancellation Reason:</strong> 
                    <span class="text-muted"><?= htmlspecialchars($order['cancel_reason']); ?></span>
                </p>
            <?php endif; ?>
        </div>

        <?php 
        if ($order_view_mode == 'completed' && !empty($order['image'])): 
            $gcash_path = 'gcash/' . basename($order['image']); 
        ?>
            <div class="dropdown my-2 border-top pt-3">
                <button class="btn btn-outline-info btn-sm dropdown-toggle w-100" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-camera me-1"></i> View GCash Screenshot
                </button>
                <ul class="dropdown-menu p-3" style="min-width: 280px;">
                    <li>
                        <a href="<?= $gcash_path; ?>" target="_blank" class="dropdown-item text-primary mb-2 small fw-bold">
                            Open Image in New Tab
                        </a>
                    </li>
                    <li><img src="<?= $gcash_path; ?>" class="img-fluid rounded border" alt="GCash Screenshot"></li>
                </ul>
            </div>
        <?php endif; ?>

        <?php 
        if ($order_view_mode == 'completed' && !empty($order['id_image_proof'])): 
            $id_path = 'id_uploads/' . basename($order['id_image_proof']);
        ?>
            <div class="dropdown my-2 border-top pt-3">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle w-100" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-id-card me-1"></i> View Proof of ID
                </button>
                <ul class="dropdown-menu p-3" style="min-width: 280px;">
                    <li>
                        <a href="<?= $id_path; ?>" target="_blank" class="dropdown-item text-secondary mb-2 small fw-bold">
                            Open ID in New Tab
                        </a>
                    </li>
                    <li><img src="<?= $id_path; ?>" class="img-fluid rounded border" alt="Proof of ID"></li>
                </ul>
            </div>
        <?php endif; ?>

        <div class="mt-auto pt-3 border-top">
            <?php if ($order_view_mode == 'pending'): ?>
                <form action="" method="POST">
                    <input type="hidden" name="order_id" value="<?= $order['id']; ?>">

                    <label class="form-label small fw-bold mb-1">Update Status:</label>
                    <div class="input-group mb-3">
                        <?php 
                            $select_class = ($order['payment_status']=='pending') ? 'text-bg-warning' : (($order['payment_status']=='cancelled') ? 'text-bg-danger' : 'text-bg-success'); 
                        ?>
                        <select name="update_payment" class="form-select form-select-sm <?= $select_class; ?>">
                            <option value="pending" <?= ($order['payment_status']=='pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="completed" <?= ($order['payment_status']=='completed') ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?= ($order['payment_status']=='cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                        <input type="submit" name="update_order" class="btn btn-primary btn-sm" value="Update">
                    </div>

                    <div class="d-grid gap-2">
                        <a href="#" class="btn btn-success btn-sm mark-as-done-btn" data-order-id="<?= $order['id']; ?>">
                            <i class="fas fa-check-circle me-1"></i> Mark as Done
                        </a>
                    </div>
                </form>

            <?php elseif ($order_view_mode == 'completed'): ?>
                <div class="d-grid gap-2">
                    <a href="admin_orders.php?archive=<?= $order['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Archive this completed order?');">
                        <i class="fas fa-archive me-1"></i> Archive Order
                    </a>
                </div>
            
            <?php elseif ($order_view_mode == 'cancelled'): ?>
                <div class="d-grid gap-2">
                    <a href="admin_orders_cancelled.php?delete=<?= $order['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Permanently delete this cancelled order?');">
                        <i class="fas fa-trash me-1"></i> Archive Order
                    </a>
                </div>

            <?php elseif ($order_view_mode == 'all'): ?>
                <div class="d-grid gap-2">
                    <a href="admin_order_details.php?order_id=<?= $order['id']; ?>" class="btn btn-info btn-sm">
                        <i class="fas fa-eye me-1"></i> View Order
                    </a>
                    
                    <a href="admin_orders.php?archive=<?= $order['id']; ?>" 
                       class="btn btn-secondary btn-sm" 
                       onclick="return confirm('Archive Order #<?= $order['id']; ?>? This will remove it from the active list.');"
                       title="Archive Order">
                        <i class="fas fa-archive me-1"></i> Archive Order
                    </a>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>
<?php
@include 'config.php';
session_start();

$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)) {
    header('location:login.php');
    exit;
}

$message = []; 
$low_stock_threshold = 10;
$select_products = null; // Initialize product result set

// --- 1. Handle Stock Update (POST request) ---
if (isset($_POST['update_stock'])) {
    $pid = filter_var($_POST['pid'], FILTER_SANITIZE_NUMBER_INT);
    $quantity_change = filter_var($_POST['quantity_change'], FILTER_SANITIZE_NUMBER_INT);
    
    if ($quantity_change === 0 || $quantity_change === '' || !is_numeric($_POST['quantity_change'])) {
        $message[] = 'Error: Please enter a valid non-zero number to adjust stock.';
    } else {
        $select_current_stock = $conn->prepare("SELECT stock FROM `products` WHERE id = ?");
        $select_current_stock->execute([$pid]);
        if ($select_current_stock->rowCount() > 0) {
            $current_stock = $select_current_stock->fetchColumn();
            $new_total_stock = $current_stock + $quantity_change;
            $new_total_stock = max(0, $new_total_stock); 
            $log_quantity = $new_total_stock - $current_stock; 

            $update_stock = $conn->prepare("UPDATE `products` SET stock = ? WHERE id = ?");
            if ($update_stock->execute([$new_total_stock, $pid])) {
                
                $log_history = $conn->prepare("INSERT INTO `stock_history` (product_id, admin_id, quantity_added, new_total_stock) VALUES (?, ?, ?, ?)");
                
                if ($log_history->execute([$pid, $admin_id, $log_quantity, $new_total_stock])) {
                    if ($log_quantity > 0) {
                        $message[] = 'Restocked ' . $log_quantity . ' units. New total: ' . $new_total_stock . '!';
                    } else {
                        $message[] = 'Subtracted ' . abs($log_quantity) . ' units. New total: ' . $new_total_stock . '!';
                    }
                } else {
                    $message[] = 'Stock updated, but failed to log history.';
                }
            } else {
                $message[] = 'Error updating stock.';
            }
        } else {
             $message[] = 'Product not found.';
        }
    }
}

// --- 2. Handle Search Query (GET request) ---
$search_query = '';
$results_found = false;

if (isset($_GET['search_btn']) && !empty($_GET['search_box'])) {
    $search_query = htmlspecialchars($_GET['search_box']);
    $search_term = '%' . $search_query . '%';
    
    // Search by product name OR category
    $select_products = $conn->prepare("SELECT * FROM `products` WHERE name LIKE ? OR category LIKE ?");
    $select_products->execute([$search_term, $search_term]);
    
    if ($select_products->rowCount() > 0) {
        $results_found = true;
    } else {
        $message[] = 'No products found matching "' . $search_query . '".';
    }
} else if (isset($_GET['search_btn']) && empty($_GET['search_box'])) {
    $message[] = 'Please enter a product name or category to search.';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Product Search and Stock</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
<link rel="stylesheet" href="css/admin_style.css">

<style>
/* ------------------------------------------------ */
/* Global Styles and Component Sizing */
/* ------------------------------------------------ */

/* Define the base height/width for all action elements for consistency */
:root {
    --element-height: 38px; 
    --element-width: 38px; 
}

.product-image { 
    width: 80px; 
    height: 80px; 
    object-fit: cover; 
    border-radius: 6px; 
}

.table tbody td { 
    font-size: 0.9rem; 
    height: 90px; 
    vertical-align: middle;
}

/* ------------------------------------------------ */
/* Search Form Specific Styles */
/* ------------------------------------------------ */
.search-form {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-bottom: 25px;
}
.search-input {
    width: 350px;
    height: var(--element-height);
    font-size: 1rem;
    padding: 0.375rem 0.75rem;
}
.btn-search {
    height: var(--element-height);
    display: flex;
    align-items: center;
}

/* ------------------------------------------------ */
/* Stock Adjustment Form Styles (copied from admin_stocks) */
/* ------------------------------------------------ */

.stock-form-group { 
    gap: 0.5rem !important; 
}

/* 1. Input Field Style (Consistent Height) */
.stock-adjust-input { 
    width: 100px !important; 
    height: var(--element-height) !important; 
    text-align: center; 
    padding: 0.375rem 0.75rem; 
    font-size: 0.9rem; 
    border: 1px solid #007bff; 
}

/* Validation Error Message */
.error-message { 
    color: #dc3545; 
    font-size: 0.8rem; 
    margin-top: 0.25rem; 
}

/* 2. Standard Action Buttons (Consistent Height) */
.btn-action { 
    height: var(--element-height); 
    padding: 0.375rem 0.75rem; 
    font-size: 0.9rem; 
    display: flex; 
    align-items: center; 
}

/* 3. Square Button Style - APPLIED TO ALL ACTION BUTTONS */
.btn-square {
    width: var(--element-width); 
    height: var(--element-height); 
    padding: 0; 
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 0.9rem; 
}

/* Specific spacing for the 'Apply' button next to the input */
.btn-apply {
    margin-left: 8px !important;
}

/* Color overrides for better accessibility/brand consistency */
.btn-warning {
    color: #333 !important;
}
.btn-warning:hover {
    color: #212529 !important;
}

/* Status and Highlight Styles */
.table-danger td, 
.table-warning td { 
    color: #333; 
}
</style>
</head>
<body>
<?php include 'admin_header.php'; ?>

<div class="main-content container-fluid mt-4">

<h1 class="title text-center pb-3 mb-4 border-bottom"><i class="fas fa-search me-2"></i> PRODUCT SEARCH & STOCK</h1>

<?php 
if (isset($message) && is_array($message)) { 
    foreach ($message as $msg) {
        $alert_class = (strpos($msg, 'Subtracted') !== false || strpos($msg, 'Error') !== false || strpos($msg, 'No products found') !== false) ? 'alert-warning' : 'alert-success';
        $icon_class = (strpos($msg, 'Subtracted') !== false || strpos($msg, 'Error') !== false || strpos($msg, 'No products found') !== false) ? 'fas fa-exclamation-triangle' : 'fas fa-check-circle';

        echo '<div class="alert ' . $alert_class . ' alert-dismissible fade show" role="alert">
                <i class="' . $icon_class . ' me-2"></i>' . htmlspecialchars($msg) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
    }
}
?>

<!-- Search Form -->
<form action="" method="GET" class="search-form">
    <input type="text" name="search_box" value="<?= htmlspecialchars($search_query); ?>" 
           class="form-control search-input" placeholder="Search by product name or category..." required>
    <button type="submit" name="search_btn" class="btn btn-primary btn-search" title="Search Products">
        <i class="fas fa-search me-1"></i> Search
    </button>
</form>

<?php if ($results_found || (isset($_GET['search_btn']) && $select_products->rowCount() > 0)): ?>

<div class="table-responsive shadow-sm rounded mt-4">
<table class="table table-striped table-hover align-middle mb-0">
<thead>
<tr class="table-dark">
<th>Image</th>
<th>Product Name</th>
<th class="text-center">Current Stock</th>
<th class="text-center">Status</th>
<th class="text-center">Adjust Stock</th>
<th class="text-center">History</th>
<th class="text-center">Edit Product</th>
</tr>
</thead>
<tbody>
<?php
    while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) { 
        $current_stock = $fetch_products['stock'] ?? 0;
        if ($current_stock == 0) { $status_class = 'bg-danger'; $status_text = 'Out of Stock'; }
        elseif ($current_stock <= $low_stock_threshold) { $status_class = 'bg-warning text-dark'; $status_text = 'Low Stock'; }
        else { $status_class = 'bg-success'; $status_text = 'In Stock'; }
?>
<tr class="<?= ($current_stock <= $low_stock_threshold && $current_stock > 0) ? 'table-warning' : ($current_stock == 0 ? 'table-danger' : ''); ?>">
<td><img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="Product Image" class="product-image"></td>
<td class="fw-bold"><?= $fetch_products['name']; ?></td>
<td class="text-center fw-bold fs-5"><?= $current_stock; ?></td>
<td class="text-center"><span class="badge <?= $status_class; ?> p-2"><?= $status_text; ?></span></td>
<td class="text-center">
    <!-- ADJUST STOCK FORM -->
    <form action="admin_search.php?search_box=<?= urlencode($search_query); ?>&search_btn=1" method="POST" class="d-flex justify-content-center align-items-center stock-form-group stock-form">
        <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
        <input type="text" name="quantity_change" id="qty-input-<?= $fetch_products['id']; ?>" value="" class="form-control stock-adjust-input" placeholder="+10 or -5" required>
        <!-- Error message container -->
        <div class="error-message" id="error-<?= $fetch_products['id']; ?>"></div>
        <!-- SQUARE APPLY BUTTON (GREEN) -->
        <button type="submit" name="update_stock" class="btn btn-success btn-action btn-square btn-apply" title="Apply Stock Change">
            <i class="fas fa-arrow-right"></i>
        </button>
    </form>
</td>
<td class="text-center">
    <!-- COMPACT HISTORY BUTTON (GRAY) -->
    <div class="d-flex justify-content-center align-items-center h-100">
        <a href="admin_stock_history.php?pid=<?= $fetch_products['id']; ?>" class="btn btn-secondary btn-square btn-action" title="View Restock History">
            <i class="fas fa-history"></i>
        </a>
    </div>
</td>
<td class="text-center">
    <!-- COMPACT EDIT BUTTON (ORANGE) -->
    <div class="d-flex justify-content-center align-items-center h-100">
        <a href="admin_update_product.php?update=<?= $fetch_products['id']; ?>" class="btn btn-warning btn-square btn-action" title="Quick Edit Product">
            <i class="fas fa-edit"></i>
        </a>
    </div>
</td>
</tr>
<?php 
    } 
?>
</tbody>
</table>
</div>

<?php elseif (!isset($_GET['search_btn'])): ?>
    <div class="alert alert-info text-center mt-5" role="alert">
        Enter a product name or category in the search box above to quickly find and manage stock.
    </div>
<?php endif; ?>

</div>

<!-- Client-Side Validation Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('.stock-form');

    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            const input = form.querySelector('input[name="quantity_change"]');
            const errorElement = form.querySelector('.error-message');
            const value = input.value.trim();

            errorElement.textContent = '';
            input.style.borderColor = '#007bff'; 

            if (value === '') {
                event.preventDefault(); 
                errorElement.textContent = 'Enter a value.';
                input.style.borderColor = '#dc3545'; 
                return;
            }
            
            // Regex check for optional sign (+/-) followed by digits
            if (!/^[+-]?\d+$/.test(value)) {
                 event.preventDefault(); 
                errorElement.textContent = 'Use + or - followed by number.';
                input.style.borderColor = '#dc3545'; 
                return;
            }
            
            const numberValue = parseInt(value);
            if (numberValue === 0) {
                 event.preventDefault(); 
                errorElement.textContent = 'Value cannot be zero.';
                input.style.borderColor = '#dc3545'; 
                return;
            }
        });
    });
});
</script>

<script src="js/admin_script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

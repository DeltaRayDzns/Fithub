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
$select_products = null;

if (isset($_POST['update_stock'])) {
    $pid = filter_var($_POST['pid'], FILTER_SANITIZE_NUMBER_INT);
    $quantity_change = filter_var($_POST['quantity_change'], FILTER_SANITIZE_NUMBER_INT);
    
    $original_search_query = isset($_POST['original_search_box']) ? $_POST['original_search_box'] : '';
    $original_category_filter = isset($_POST['original_category_filter']) ? $_POST['original_category_filter'] : ''; 
    $original_page = isset($_POST['original_page']) ? $_POST['original_page'] : 1; // Capture original page

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
                        $_SESSION['stock_message'] = 'Restocked ' . $log_quantity . ' units. New total: ' . $new_total_stock . '!';
                    } else {
                        $_SESSION['stock_message'] = 'Subtracted ' . abs($log_quantity) . ' units. New total: ' . $new_total_stock . '!';
                    }
                } else {
                    $_SESSION['stock_message'] = 'Stock updated, but failed to log history.';
                }
            } else {
                $_SESSION['stock_message'] = 'Error updating stock.';
            }
        } else {
             $_SESSION['stock_message'] = 'Product not found.';
        }
    }
    
    $redirect_url = 'admin_stocks.php';
    $query_parts = [];

    if (!empty($original_search_query)) {
        $query_parts[] = 'search_box=' . urlencode($original_search_query) . '&search_btn=1';
    }
    if (!empty($original_category_filter) && $original_category_filter !== 'all') {
        $query_parts[] = 'category=' . urlencode($original_category_filter);
    }
    if ($original_page > 1) { 
        $query_parts[] = 'page=' . $original_page;
    }

    if (!empty($query_parts)) {
        $redirect_url .= '?' . implode('&', $query_parts);
    }
    
    header('location:' . $redirect_url);
    exit;
}

if (isset($_SESSION['stock_message'])) {
    $message[] = $_SESSION['stock_message'];
    unset($_SESSION['stock_message']);
}

$select_categories = $conn->prepare("SELECT DISTINCT category FROM `products` ORDER BY category ASC");
$select_categories->execute();
$categories = $select_categories->fetchAll(PDO::FETCH_COLUMN);

$search_query = '';
$selected_category = isset($_GET['category']) ? htmlspecialchars($_GET['category']) : 'all'; 
$where_clauses = [];
$params = [];

if (!empty($selected_category) && $selected_category !== 'all') {
    $where_clauses[] = "category = ?";
    $params[] = $selected_category;
}

if (isset($_GET['search_btn']) && !empty($_GET['search_box'])) {
    $search_query = htmlspecialchars($_GET['search_box']);
    $search_term = '%' . $search_query . '%';
    
    $where_clauses[] = "(name LIKE ? OR category LIKE ?)";
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_clause = '';
if (!empty($where_clauses)) {
    $where_clause = " WHERE " . implode(' AND ', $where_clauses);
}

$is_filtered = !empty($search_query) || (!empty($selected_category) && $selected_category !== 'all');

$products_per_page = 15;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;

$count_sql = "SELECT COUNT(*) FROM `products`" . $where_clause;
$count_stmt = $conn->prepare($count_sql);
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();

$total_pages = ceil($total_records / $products_per_page);

if ($current_page > $total_pages && $total_pages > 0) {
    $current_page = $total_pages;
} elseif ($total_pages === 0) {
    $current_page = 1; 
}

$offset = ($current_page - 1) * $products_per_page;

$sql = "SELECT * FROM `products`" . $where_clause . " ORDER BY stock DESC LIMIT ? OFFSET ?";
$select_products = $conn->prepare($sql);

$bind_index = 1;

foreach ($params as $param) {
    $select_products->bindValue($bind_index++, $param, PDO::PARAM_STR);
}

$select_products->bindValue($bind_index++, $products_per_page, PDO::PARAM_INT);

$select_products->bindValue($bind_index++, $offset, PDO::PARAM_INT);

$select_products->execute(); 


function get_pagination_url($page, $search_query, $selected_category) {
    $query_parts = [];
    if (!empty($search_query)) {
        $query_parts[] = 'search_box=' . urlencode($search_query) . '&search_btn=1';
    }
    if (!empty($selected_category) && $selected_category !== 'all') {
        $query_parts[] = 'category=' . urlencode($selected_category);
    }
    if ($page > 1) {
        $query_parts[] = 'page=' . $page;
    }
    
    return 'admin_stocks.php' . (!empty($query_parts) ? '?' . implode('&', $query_parts) : '');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Product Stock Management</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
<link rel="stylesheet" href="css/admin_style.css">

<style>
/* ... (Your existing styles here) ... */
.product-image { 
    width: 80px; 
    height: 80px; 
    object-fit: cover; 
    border-radius: 6px; 
}

:root {
    --element-height: 38px; 
    --element-width: 38px; 
}

.table tbody td { 
    font-size: 0.9rem; 
    height: 90px; 
    vertical-align: middle;
}

.badge.bg-warning {
    background-color: #ffc107 !important;
    color: #333 !important; 
}

.search-container {
    display: flex;
    justify-content: center;
    margin-bottom: 30px;
    padding: 0 15px;
}

.search-form {
    width: 100%; 
    max-width: 600px; 
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    border-radius: 2rem; 
}

@media (max-width: 767px) {
    .search-form {
        max-width: 100%;
        margin-right: 0 !important;
    }
}

.search-input-group {
    position: relative;
}

.search-input-group .form-control {
    border-radius: 2rem 0 0 2rem !important; 
    height: 48px; 
    border-right: none;
    font-size: 1rem;
    padding: 0.75rem 1.5rem; 
    padding-right: 45px; 
    border-color: #dee2e6;
}

.search-input-group .form-control:focus {
    box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
    border-color: #007bff;
}

.search-input-group .input-group-text {
    background-color: #007bff;
    color: white;
    border-radius: 0 2rem 2rem 0 !important; 
    border: 1px solid #007bff;
    padding: 0 1.5rem;
    cursor: pointer;
    height: 48px;
    display: flex;
    align-items: center;
    transition: background-color 0.2s ease;
    font-weight: bold;
}
.search-input-group .input-group-text:hover {
    background-color: #0056b3;
}

.clear-search-btn {
    position: absolute;
    right: 120px; 
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    color: #ced4da;
    padding: 0 10px;
    height: 100%;
    font-size: 1.1rem;
    cursor: pointer;
    opacity: 0;
    pointer-events: none;
    z-index: 100;
    transition: opacity 0.2s ease, color 0.2s ease;
}

.clear-search-btn.active {
    opacity: 1;
    pointer-events: auto;
}

.clear-search-btn:hover {
    color: #dc3545;
}

.category-filter-container {
    margin-bottom: 2rem;
}
.category-radio-group {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 8px; 
}
.category-radio-label {
    border-radius: 1.5rem; 
    padding: 0.4rem 1rem;
    min-width: 100px;
    font-weight: 500;
    cursor: pointer;
    margin: 0; 
    transition: all 0.2s ease;
    border: 1px solid #007bff;
    color: #007bff;
    background-color: transparent;
    text-align: center;
    text-transform: uppercase;
}
.category-radio-label:hover {
    color: #fff;
    background-color: #007bff;
    border-color: #007bff;
    opacity: 0.9;
}

.category-radio-group input:checked + .category-radio-label {
    background-color: #007bff; 
    color: #fff; 
    border-color: #007bff;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    pointer-events: none;
}

.btn-reset-compact {
    width: 48px; 
    height: 48px; 
    border-radius: 50% !important;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 0;
    flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); 
}

.stock-form-group { 
    gap: 0.5rem !important; 
}

.stock-adjust-input { 
    width: 100px !important; 
    height: var(--element-height) !important; 
    text-align: center; 
    padding: 0.375rem 0.75rem; 
    font-size: 0.9rem; 
    border: 1px solid #007bff; 
}

.error-message { 
    color: #dc3545; 
    font-size: 0.8rem; 
    margin-top: 0.25rem; 
}

.btn-action { 
    height: var(--element-height); 
    padding: 0.375rem 0.75rem; 
    font-size: 0.9rem; 
    display: flex; 
    align-items: center; 
}

.btn-square {
    width: var(--element-width); 
    height: var(--element-height); 
    padding: 0; 
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 0.9rem; 
}
.btn-apply {
    margin-left: 8px !important;
}
.btn-warning {
    color: #333 !important;
}
.btn-warning:hover {
    color: #212529 !important;
}

.table-danger td, 
.table-warning td { 
    color: #333; 
}
</style>
</head>
<body>
<?php include 'admin_header.php'; ?>

<div class="container-fluid mt-4">

<h1 class="title text-center pb-3 mb-4 border-bottom"><i class="fas fa-boxes me-2"></i> PRODUCT STOCK MANAGEMENT</h1>

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

<div class="search-container d-flex flex-column flex-md-row align-items-center justify-content-center">
    
    <form action="" method="GET" class="search-form <?= $is_filtered ? 'me-md-3' : ''; ?>">
        <div class="input-group search-input-group">
            <input type="text" name="search_box" id="search-input" value="<?= htmlspecialchars($search_query); ?>" 
                   class="form-control" placeholder="Search by product name or category..." autofocus>
            
            <button type="button" id="clear-search-btn" class="clear-search-btn" title="Clear Search Input">
                <i class="fas fa-times-circle"></i>
            </button>

            <?php if (!empty($selected_category) && $selected_category !== 'all'): ?>
                <input type="hidden" name="category" value="<?= htmlspecialchars($selected_category); ?>">
            <?php endif; ?>
            
            <?php if ($current_page > 1): ?>
                <input type="hidden" name="page" value="<?= $current_page; ?>">
            <?php endif; ?>
            
            <button type="submit" name="search_btn" class="input-group-text" title="Search Products">
                <i class="fas fa-search me-1"></i> Search
            </button>
        </div>
    </form>

    <?php if ($is_filtered): ?>
    <a href="admin_stocks.php" class="btn btn-danger mt-3 mt-md-0 ms-md-3 btn-reset-compact" 
       title="Reset All Filters">
        <i class="fas fa-undo"></i>
    </a>
    <?php endif; ?>
</div>

<div class="category-filter-container text-center mb-5 mt-4">
    <h5 class="mb-2 text-secondary">Filter by Category:</h5>
    <form action="" method="GET" id="category-filter-form" class="d-flex justify-content-center flex-wrap">
        <input type="hidden" name="search_box" value="<?= htmlspecialchars($search_query); ?>">
        
        <?php if ($current_page > 1): ?>
            <input type="hidden" name="page" value="<?= $current_page; ?>">
        <?php endif; ?>
        
        <div class="category-radio-group">
            <?php
                $total_count = $conn->query("SELECT COUNT(*) FROM `products`")->fetchColumn();
                
                $cats_to_display = array_merge(['all'], $categories);

                foreach($cats_to_display as $cat): 
                    
                    $is_all = ($cat === 'all');
                    $display_name = $is_all ? 'All' : htmlspecialchars($cat);
                    $filter_value = $is_all ? 'all' : $cat;
                    
                    $current_count = $total_count;
                    if (!$is_all) {
                        $cat_count = $conn->prepare("SELECT COUNT(*) FROM `products` WHERE category = ?");
                        $cat_count->execute([$cat]);
                        $current_count = $cat_count->fetchColumn();
                    }
                    
                    $is_checked = ($selected_category === $filter_value);
            ?>
            
            <input type="radio" id="cat-<?= $filter_value; ?>" name="category" value="<?= $filter_value; ?>" 
                    class="d-none category-radio" 
                    <?= $is_checked ? 'checked' : ''; ?> onchange="this.form.submit()">
            
            <label for="cat-<?= $filter_value; ?>" 
                    class="category-radio-label">
                <?= $display_name; ?> (<?= $current_count; ?>)
            </label>
            <?php endforeach; ?>
        </div>
    </form>
</div>

<?php if ($total_pages > 1): ?>
<div class="d-flex justify-content-center mb-4">
    <nav aria-label="Stock Page Navigation Top">
        <ul class="pagination pagination-sm shadow-sm">
            
            <li class="page-item <?= ($current_page <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?= get_pagination_url($current_page - 1, $search_query, $selected_category); ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>

            <?php 
            $start_page = max(1, $current_page - 2);
            $end_page = min($total_pages, $current_page + 2);
            
            if ($start_page > 1) {
                echo '<li class="page-item"><a class="page-link" href="' . get_pagination_url(1, $search_query, $selected_category) . '">1</a></li>';
                if ($start_page > 2) {
                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }

            for ($i = $start_page; $i <= $end_page; $i++): 
            ?>
            <li class="page-item <?= ($i == $current_page) ? 'active' : ''; ?>">
                <a class="page-link" href="<?= get_pagination_url($i, $search_query, $selected_category); ?>">
                    <?= $i; ?>
                </a>
            </li>
            <?php endfor; ?>
            
            <?php
            if ($end_page < $total_pages) {
                if ($end_page < $total_pages - 1) {
                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
                echo '<li class="page-item"><a class="page-link" href="' . get_pagination_url($total_pages, $search_query, $selected_category) . '">' . $total_pages . '</a></li>';
            }
            ?>
            
            <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?= get_pagination_url($current_page + 1, $search_query, $selected_category); ?>" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
</div>
<?php endif; ?>
<?php if ($select_products->rowCount() > 0): ?>

<div class="table-responsive shadow-sm rounded">
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
    <form action="" method="POST" class="d-flex justify-content-center align-items-center stock-form-group stock-form">
        <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
        <input type="hidden" name="original_search_box" value="<?= htmlspecialchars($search_query); ?>">
        <input type="hidden" name="original_category_filter" value="<?= htmlspecialchars($selected_category); ?>">
        <input type="hidden" name="original_page" value="<?= $current_page; ?>"> <input type="text" name="quantity_change" id="qty-input-<?= $fetch_products['id']; ?>" value="" class="form-control stock-adjust-input" placeholder="+10 or -5" required>
        <div class="error-message" id="error-<?= $fetch_products['id']; ?>"></div>
        <button type="submit" name="update_stock" class="btn btn-success btn-action btn-square btn-apply" title="Apply Stock Change">
            <i class="fas fa-arrow-right"></i>
        </button>
    </form>
</td>
<td class="text-center">
    <div class="d-flex justify-content-center align-items-center h-100">
        <a href="admin_stock_history.php?pid=<?= $fetch_products['id']; ?>" class="btn btn-secondary btn-square btn-action" title="View Restock History">
            <i class="fas fa-history"></i>
        </a>
    </div>
</td>
<td class="text-center">
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

<?php if ($total_pages > 1): ?>
<div class="d-flex justify-content-center mt-4 mb-5">
    <nav aria-label="Stock Page Navigation Bottom">
        <ul class="pagination pagination-lg shadow-sm">
            
            <li class="page-item <?= ($current_page <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?= get_pagination_url($current_page - 1, $search_query, $selected_category); ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>

            <?php 
            $start_page = max(1, $current_page - 2);
            $end_page = min($total_pages, $current_page + 2);
            
            if ($start_page > 1) {
                echo '<li class="page-item"><a class="page-link" href="' . get_pagination_url(1, $search_query, $selected_category) . '">1</a></li>';
                if ($start_page > 2) {
                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }

            for ($i = $start_page; $i <= $end_page; $i++): 
            ?>
            <li class="page-item <?= ($i == $current_page) ? 'active' : ''; ?>">
                <a class="page-link" href="<?= get_pagination_url($i, $search_query, $selected_category); ?>">
                    <?= $i; ?>
                </a>
            </li>
            <?php endfor; ?>
            
            <?php
            if ($end_page < $total_pages) {
                if ($end_page < $total_pages - 1) {
                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
                echo '<li class="page-item"><a class="page-link" href="' . get_pagination_url($total_pages, $search_query, $selected_category) . '">' . $total_pages . '</a></li>';
            }
            ?>
            
            <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?= get_pagination_url($current_page + 1, $search_query, $selected_category); ?>" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
</div>
<?php endif; ?>
<?php elseif ($is_filtered && $select_products->rowCount() == 0): ?>
    <div class="alert alert-warning text-center mt-5" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i> No products found matching your current filters. Try resetting or adjusting your search.
    </div>
<?php elseif ($select_products->rowCount() == 0): ?>
    <div class="alert alert-info text-center mt-5" role="alert">
        No products are currently available in the database to manage.
    </div>
<?php endif; ?>

</div>

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

    const searchInput = document.getElementById('search-input');
    const clearButton = document.getElementById('clear-search-btn');
    const searchForm = document.querySelector('.search-form');

    function toggleClearButton() {
        if (searchInput.value.length > 0) {
            clearButton.classList.add('active');
        } else {
            clearButton.classList.remove('active');
        }
    }

    toggleClearButton();

    searchInput.addEventListener('input', toggleClearButton);

    clearButton.addEventListener('click', function() {
        if (searchInput.value.length > 0) {
            searchInput.value = '';
            searchForm.submit(); 
        }
    });
});
</script>

<script src="js/admin_script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
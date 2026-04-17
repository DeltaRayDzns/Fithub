<?php
@include 'config.php';
session_start();

$admin_id = $_SESSION['admin_id'] ?? null;
if (!$admin_id) {
    header('location:login.php');
    exit;
}

if (!isset($message) || !is_array($message)) {
    $message = [];
}

if (isset($_SESSION['order_message'])) {
    $message[] = $_SESSION['order_message'];
    unset($_SESSION['order_message']);
}

// NOTE: The archive action logic remains here but the archived status is excluded from the main view and summary.
if (isset($_GET['archive'])) {
    $archive_id = $_GET['archive'];
    $archive_orders = $conn->prepare("UPDATE `orders` SET payment_status = 'archived' WHERE id = ?");
    
    if ($archive_orders->execute([$archive_id])) {
        $_SESSION['order_message'] = 'Order ID ' . $archive_id . ' has been archived and removed from the active list.';
    } else {
         $_SESSION['order_message'] = 'Error archiving Order ID ' . $archive_id . '.';
    }
    
    $redirect_url = 'admin_orders.php';
    $query_parts = [];
    $search_query = isset($_GET['search_box']) ? $_GET['search_box'] : '';
    $current_page = isset($_GET['page']) ? $_GET['page'] : 1;

    if (!empty($search_query)) {
        $query_parts[] = 'search_box=' . urlencode($search_query) . '&search_btn=1';
    }
    if ($current_page > 1) {
        $query_parts[] = 'page=' . $current_page;
    }
    if (!empty($query_parts)) {
        $redirect_url .= '?' . implode('&', $query_parts);
    }

    header('location:' . $redirect_url);
    exit;
}

$search_query = isset($_GET['search_box']) ? htmlspecialchars($_GET['search_box']) : '';
$where_clauses = [];
$params = [];

if (!empty($search_query)) {
    $search_term = '%' . $search_query . '%';
    $where_clauses[] = "(user_id LIKE ? OR id LIKE ? OR name LIKE ?)";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

// Ensure only active statuses are included (i.e., NOT archived)
$where_clauses[] = "payment_status != 'archived'";

$where_clause = '';
if (!empty($where_clauses)) {
    $where_clause = " WHERE " . implode(' AND ', $where_clauses);
}

$is_filtered = !empty($search_query);


$orders_per_page = 15;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;

$count_sql = "SELECT COUNT(*) FROM `orders`" . $where_clause;
$count_stmt = $conn->prepare($count_sql);
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();

$total_pages = ceil($total_records / $orders_per_page);

if ($current_page > $total_pages && $total_pages > 0) {
    $current_page = $total_pages;
} elseif ($total_pages === 0) {
    $current_page = 1; 
}

$offset = ($current_page - 1) * $orders_per_page;

$sql = "SELECT * FROM `orders`" . $where_clause . " ORDER BY placed_on DESC LIMIT ? OFFSET ?";
$select_orders = $conn->prepare($sql);

$bind_index = 1;

foreach ($params as $param) {
    $select_orders->bindValue($bind_index++, $param, PDO::PARAM_STR);
}

$select_orders->bindValue($bind_index++, $orders_per_page, PDO::PARAM_INT);
$select_orders->bindValue($bind_index++, $offset, PDO::PARAM_INT);

$select_orders->execute(); 

// UPDATED: Get summary counts for navigation badges (excluding archived from this count)
$summary = $conn->query("
    SELECT 
        COUNT(*) AS total_active_orders,
        SUM(payment_status='pending') AS pending_total,
        SUM(payment_status='completed') AS completed_total,
        SUM(payment_status='cancelled') AS cancelled_total
    FROM orders
    WHERE payment_status != 'archived'
")->fetch(PDO::FETCH_ASSOC);

function get_pagination_url($page, $search_query) {
    $query_parts = [];
    if (!empty($search_query)) {
        $query_parts[] = 'search_box=' . urlencode($search_query) . '&search_btn=1';
    }
    if ($page > 1) {
        $query_parts[] = 'page=' . $page;
    }
    
    return 'admin_orders.php' . (!empty($query_parts) ? '?' . implode('&', $query_parts) : '');
}

$order_view_mode = 'all'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Active Orders</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
<link rel="stylesheet" href="css/admin_style.css">

<style>
.nav-links a {
    text-decoration: none;
    padding: 0.75rem 1.25rem;
    border-radius: 0.5rem;
    transition: background-color 0.2s, box-shadow 0.2s;
    font-weight: 600;
    color: #495057; 
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    background-color: #fff;
    border: 1px solid #dee2e6;
}
.nav-links a:hover {
    background-color: #007;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}
.nav-links .active-link {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
    box-shadow: 0 4px 10px rgba(0, 123, 255, 0.25);
}
.nav-links .active-link .badge {
    background-color: rgba(255, 255, 255, 0.3) !important;
    color: white !important;
}

.search-outer-container { padding: 0 15px; }
.search-inner-wrapper { max-width: 600px; }
.search-form { width: 100%; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); border-radius: 2rem; }
.btn-reset-compact {
    width: 48px; height: 48px; border-radius: 50% !important;
    display: flex; justify-content: center; align-items: center;
    padding: 0; flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    transition: background-color 0.2s ease;
}
.btn-reset-compact:hover { background-color: #dc3545; }
.search-input-group .form-control {
    border-radius: 2rem 0 0 2rem !important;
    height: 48px; border-right: none;
    padding: 0.75rem 1.5rem; border-color: #dee2e6;
}
.search-input-group .input-group-text {
    background-color: #007bff; color: white;
    border-radius: 0 2rem 2rem 0 !important;
    border: 1px solid #007bff;
    padding: 0 1.5rem; height: 48px;
    font-weight: bold; cursor: pointer;
}
.order-card-container {
    margin-bottom: 2rem; 
}
</style>
</head>
<body>
<?php include 'admin_header.php'; ?>

<div class="main-content container-fluid py-4">
    <h1 class="text-center fw-bold mb-4"><i class="fas fa-receipt me-2"></i> Orders Dashboard</h1>

    <nav class="nav-links d-flex flex-wrap justify-content-center gap-3 mb-5">
        <a href="admin_orders.php" class="active-link">
            Active Orders (All) 
            <span class="badge rounded-pill ms-2"><?= $summary['total_active_orders'] ?? 0 ?></span>
        </a>
        <a href="admin_orders_pending.php" class="text-warning">
            <i class="fas fa-clock me-1"></i> Pending 
            <span class="badge bg-warning text-dark rounded-pill ms-2"><?= $summary['pending_total'] ?? 0 ?></span>
        </a>
        <a href="admin_orders_completed.php" class="text-success">
            <i class="fas fa-check-circle me-1"></i> Completed 
            <span class="badge bg-success rounded-pill ms-2"><?= $summary['completed_total'] ?? 0 ?></span>
        </a>
        <a href="admin_orders_cancelled.php" class="text-danger">
            <i class="fas fa-ban me-1"></i> Cancelled 
            <span class="badge bg-danger rounded-pill ms-2"><?= $summary['cancelled_total'] ?? 0 ?></span>
        </a>
    </nav>

    <h2 class="text-center fw-bold mb-4">Active Orders List</h2>

    <div class="d-flex justify-content-center mb-5 search-outer-container">
        <div class="d-flex flex-column flex-md-row align-items-center justify-content-center w-100 search-inner-wrapper">
            <form action="" method="GET" class="search-form flex-grow-1 w-100 <?= $is_filtered ? 'me-md-3' : ''; ?>">
                <div class="input-group search-input-group">
                    <input type="text" name="search_box" id="search-input"
                           value="<?= htmlspecialchars($search_query); ?>"
                           class="form-control"
                           placeholder="Search by Order ID, User ID, or Name..." autofocus>
                    
                    <?php if ($current_page > 1): ?>
                        <input type="hidden" name="page" value="<?= $current_page; ?>">
                    <?php endif; ?>
                    
                    <button type="submit" name="search_btn" class="input-group-text btn-primary" title="Search Orders">
                        <i class="fas fa-search me-1"></i> Search
                    </button>
                </div>
            </form>

            <?php if ($is_filtered): ?>
            <a href="admin_orders.php" class="btn btn-danger mt-3 mt-md-0 ms-md-3 btn-reset-compact" title="Reset Search">
                <i class="fas fa-undo"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($message) && is_array($message)): ?>
        <?php foreach ($message as $msg): ?>
            <div class="alert alert-info alert-dismissible fade show mb-3 shadow-sm">
                <i class="fas fa-info-circle me-2"></i><?= htmlspecialchars($msg); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="row g-4 order-card-container">
        <?php if ($select_orders->rowCount() > 0): ?>
            <?php while ($order = $select_orders->fetch(PDO::FETCH_ASSOC)): ?>
                
                <?php include 'order_card.php'; ?> 
            
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center p-5 bg-white shadow rounded w-100">
                <?php if ($is_filtered): ?>
                    <i class="fas fa-filter me-2"></i> No active orders found matching your search query.
                <?php else: ?>
                    <i class="fas fa-info-circle me-2"></i> No active orders placed yet.
                <?php endif; ?>
            </p>
        <?php endif; ?>
    </div>

    <?php if ($total_pages > 1): ?>
    <nav aria-label="Order Page Navigation" class="d-flex justify-content-center mt-4 mb-5">
        <ul class="pagination pagination-lg shadow-sm">
            
            <li class="page-item <?= ($current_page <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?= get_pagination_url($current_page - 1, $search_query); ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>

            <?php 
            $start_page = max(1, $current_page - 2);
            $end_page = min($total_pages, $current_page + 2);
            
            if ($start_page > 1) {
                echo '<li class="page-item"><a class="page-link" href="' . get_pagination_url(1, $search_query) . '">1</a></li>';
                if ($start_page > 2) {
                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }

            for ($i = $start_page; $i <= $end_page; $i++): 
            ?>
            <li class="page-item <?= ($i == $current_page) ? 'active' : ''; ?>">
                <a class="page-link" href="<?= get_pagination_url($i, $search_query); ?>">
                    <?= $i; ?>
                </a>
            </li>
            <?php endfor; ?>
            
            <?php
            if ($end_page < $total_pages) {
                if ($end_page < $total_pages - 1) {
                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
                echo '<li class="page-item"><a class="page-link" href="' . get_pagination_url($total_pages, $search_query) . '">' . $total_pages . '</a></li>';
            }
            ?>
            
            <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                <a class="page-link" href="<?= get_pagination_url($current_page + 1, $search_query); ?>" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/admin_script.js"></script>
</body>
</html>
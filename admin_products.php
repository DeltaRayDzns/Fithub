<?php
@include 'config.php';
session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
    header('location:login.php');
    exit;
}

if(isset($_POST['add_product'])){
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $price = filter_var($_POST['price'], FILTER_SANITIZE_STRING);
    $category = filter_var($_POST['category'], FILTER_SANITIZE_STRING);
    $details = filter_var($_POST['details'], FILTER_SANITIZE_STRING);

    $image = filter_var($_FILES['image']['name'], FILTER_SANITIZE_STRING);
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = 'uploaded_img/'.$image;

    $select_products = $conn->prepare("SELECT * FROM `products` WHERE name = ?");
    $select_products->execute([$name]);

    if($select_products->rowCount() > 0){
        $message[] = 'Product name already exists!';
    } else {
        $insert_products = $conn->prepare("INSERT INTO `products` (name, category, details, price, image) VALUES (?, ?, ?, ?, ?)");
        $insert_products->execute([$name, $category, $details, $price, $image]);

        if($insert_products){
            if($image_size > 2000000){
                $message[] = 'Image size is too large!';
            } else {
                move_uploaded_file($image_tmp_name, $image_folder);
                $message[] = 'New product added!';
            }
        }
    }
}

if(isset($_GET['delete'])){
    $delete_id = $_GET['delete'];

    $select_delete_image = $conn->prepare("SELECT image FROM `products` WHERE id = ?");
    $select_delete_image->execute([$delete_id]);
    $fetch_delete_image = $select_delete_image->fetch(PDO::FETCH_ASSOC);
    if (isset($fetch_delete_image['image'])) {
        unlink('uploaded_img/'.$fetch_delete_image['image']);
    }

    $delete_products = $conn->prepare("DELETE FROM `products` WHERE id = ?");
    $delete_products->execute([$delete_id]);

    $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE pid = ?");
    $delete_wishlist->execute([$delete_id]);

    $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE pid = ?");
    $delete_cart->execute([$delete_id]);

    header('location:admin_products.php');
    exit;
}

// --- Pagination & Filtering Setup (Using Named Parameters for Safety) ---
$limit = 8; // Number of products per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit; 

$search_query = isset($_GET['search_box']) ? htmlspecialchars($_GET['search_box']) : '';
$selected_category = isset($_GET['category']) ? htmlspecialchars($_GET['category']) : 'all';

// Use arrays to build the WHERE clause and parameters using NAMED placeholders
$where_clauses = [];
$bind_params = [];

$select_categories = $conn->prepare("SELECT DISTINCT category FROM `products` ORDER BY category ASC");
$select_categories->execute();
$categories = $select_categories->fetchAll(PDO::FETCH_COLUMN);

if (!empty($selected_category) && $selected_category !== 'all') {
    $where_clauses[] = "category = :category_filter";
    $bind_params[':category_filter'] = $selected_category;
}

if (!empty($search_query)) {
    $search_term = '%' . $search_query . '%';
    $where_clauses[] = "(name LIKE :search_name OR category LIKE :search_cat)";
    $bind_params[':search_name'] = $search_term;
    $bind_params[':search_cat'] = $search_term;
}

$where_clause = '';
if (!empty($where_clauses)) {
    $where_clause = " WHERE " . implode(' AND ', $where_clauses);
}

$is_filtered = !empty($search_query) || (!empty($selected_category) && $selected_category !== 'all');

// 1. Get total number of filtered records for pagination
$count_sql = "SELECT COUNT(*) FROM `products`" . $where_clause;
$count_stmt = $conn->prepare($count_sql);
$count_stmt->execute($bind_params); // Execute with only filter parameters
$total_records = $count_stmt->fetchColumn();

$total_pages = ceil($total_records / $limit);

// 2. Query for the current page's products
$sql = "SELECT * FROM `products`" . $where_clause . " ORDER BY id DESC LIMIT :start, :limit";
$show_products = $conn->prepare($sql);

// Bind the LIMIT parameters explicitly as integers to avoid SQL syntax error (1064)
$show_products->bindParam(':start', $start, PDO::PARAM_INT);
$show_products->bindParam(':limit', $limit, PDO::PARAM_INT);

// Bind the filter/search parameters
foreach ($bind_params as $key => &$value) {
    $show_products->bindParam($key, $value);
}

// Execute the final statement once
$show_products->execute(); 

// Category Counts (for radio buttons - using filter params)
$category_counts = [];
$total_count_stmt = $conn->query("SELECT COUNT(*) FROM `products`");
$category_counts['all'] = $total_count_stmt ? $total_count_stmt->fetchColumn() : 0;
foreach($categories as $cat) {
    $cat_count_stmt = $conn->prepare("SELECT COUNT(*) FROM `products` WHERE category = ?");
    $cat_count_stmt->execute([$cat]);
    $category_counts[$cat] = $cat_count_stmt->fetchColumn();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Products Management</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
<link rel="stylesheet" href="css/admin_style.css">

<style>
/* (Your existing styles here) */
.main-content {
    padding-top: 20px;
}

.title {
    color: 1F2937;
    font-weight: 600;
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
    transition: background-color 0.2s ease;
}
.btn-reset-compact:hover {
    background-color: #dc3545; 
}

.search-input-group .form-control {
    border-radius: 2rem 0 0 2rem !important; 
    height: 48px; 
    border-right: none;
    padding: 0.75rem 1.5rem; 
    border-color: #dee2e6;
}
.search-input-group .input-group-text {
    background-color: #007bff;
    color: white;
    border-radius: 0 2rem 2rem 0 !important; 
    border: 1px solid #007bff;
    padding: 0 1.5rem;
    height: 48px;
    font-weight: bold;
}

.category-filter-container {
    margin-top: 1rem;
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
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 1px solid #007bff;
    color: #007bff;
    background-color: transparent;
    text-align: center;
    text-transform: uppercase; 
}
.category-radio-group input:checked + .category-radio-label {
    background-color: #007bff; 
    color: #fff; 
    border-color: #007bff;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    pointer-events: none;
}
.category-radio-label:hover {
    background-color: #e9f3ff;
}

.card-img-top {
    border-bottom: 1px solid #eee;
}

/* Pagination Styling */
.pagination-container {
    padding: 20px 0;
}
</style>
</head>
<body>

<?php include 'admin_header.php'; ?>

<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-danger border-3 rounded-lg shadow-lg">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteConfirmModalLabel"><i class="fas fa-exclamation-triangle me-2"></i> Confirm Deletion</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        Are you sure you want to permanently delete this product? This action cannot be undone and will remove it from all users' carts and wishlists.
      </div>
      <div class="modal-footer justify-content-between">
        <a href="#" id="modalDeleteLink" class="btn btn-danger fw-bold">Delete Product</a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>

<div class="main-content container-fluid">

    <?php 
    if (isset($message) && is_array($message)) { 
        foreach ($message as $msg) {
            $alert_class = (strpos($msg, 'already exists') !== false || strpos($msg, 'too large') !== false) ? 'alert-warning' : 'alert-success';
            $icon_class = (strpos($msg, 'already exists') !== false || strpos($msg, 'too large') !== false) ? 'fas fa-exclamation-triangle' : 'fas fa-check-circle';

            echo '<div class="alert ' . $alert_class . ' alert-dismissible fade show" role="alert">
                      <i class="' . $icon_class . ' me-2"></i>' . htmlspecialchars($msg) . '
                      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
        }
    }
    ?>

    <h1 class="title pb-3 mb-4 border-bottom text-center"><i class="fas fa-box-open me-2"></i> Add New Product</h1>
    <div class="row mb-5 justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <div class="card shadow-lg border-0">
                <div class="card-body p-4 p-md-5">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="name" class="form-control" id="productName" required placeholder="Product Name">
                                    <label for="productName">Product Name</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <select name="category" class="form-select py-3" required>
                                    <option value="" selected disabled>Select Category</option>
                                    <option value="vegetables">Vegetables</option>
                                    <option value="fruits">Fruits</option>
                                    <option value="meat">Meat</option>
                                    <option value="fish">Fish</option>
                                    <option value="chocolate">Chocolate</option>
                                    <option value="bread">Bread</option>
                                    <option value="dairy">Dairy</option>
                                    <option value="drinks">Drinks</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="number" min="0" name="price" class="form-control" id="productPrice" required placeholder="Product Price">
                                    <label for="productPrice">Product Price</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <input type="file" name="image" required class="form-control pt-3" accept="image/jpg, image/jpeg, image/png">
                            </div>
                            <div class="col-12">
                                <div class="form-floating">
                                    <textarea name="details" class="form-control" id="productDetails" required placeholder="Product Details" style="height: 100px;"></textarea>
                                    <label for="productDetails">Product Details</label>
                                </div>
                            </div>
                            <div class="col-12 pt-3">
                                <button type="submit" class="btn btn-primary w-100 py-2" name="add_product">
                                    <i class="fas fa-plus me-2"></i> Add New Product
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <h1 class="title mt-5 pt-4 border-top text-center"><i class="fas fa-list-alt me-2"></i> Products Added</h1>
    <div class="search-container d-flex flex-column flex-md-row align-items-center justify-content-center">
    
        <form action="" method="GET" class="search-form <?= $is_filtered ? 'me-md-3' : ''; ?>">
            <div class="input-group search-input-group">
                <input type="text" name="search_box" id="search-input" value="<?= htmlspecialchars($search_query); ?>" 
                       class="form-control" placeholder="Search by product name or category..." autofocus>
                
                <?php if (!empty($selected_category) && $selected_category !== 'all'): ?>
                    <input type="hidden" name="category" value="<?= htmlspecialchars($selected_category); ?>">
                <?php endif; ?>
                
                <button type="submit" name="search_btn" class="input-group-text" title="Search Products">
                    <i class="fas fa-search me-1"></i> Search
                </button>
            </div>
        </form>

        <?php if ($is_filtered || $page > 1): ?>
        <a href="admin_products.php" class="btn btn-danger mt-3 mt-md-0 ms-md-3 btn-reset-compact" 
           title="Reset All Filters">
            <i class="fas fa-undo"></i>
        </a>
        <?php endif; ?>
    </div>
    
    <div class="category-filter-container text-center mb-5">
        <h5 class="mb-2 text-secondary">Filter by Category:</h5>
        <form action="" method="GET" id="category-filter-form" class="d-flex justify-content-center flex-wrap">
            <input type="hidden" name="search_box" value="<?= htmlspecialchars($search_query); ?>">
            
            <div class="category-radio-group">
                <?php
                    $cats_to_display = array_merge(['all'], $categories);

                    foreach($cats_to_display as $cat): 
                        
                        $is_all = ($cat === 'all');
                        $display_name = $is_all ? 'All' : htmlspecialchars($cat);
                        $filter_value = $is_all ? 'all' : $cat;
                        
                        $current_count = $category_counts[$filter_value] ?? 0;
                        
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

    <div class="row g-4 mb-5">
        <?php
        if($show_products->rowCount() > 0){
            while($fetch_products = $show_products->fetch(PDO::FETCH_ASSOC)){
        ?>
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
            <div class="card shadow-sm h-100 d-flex flex-column overflow-hidden position-relative">
                <img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="<?= $fetch_products['name']; ?>" 
                     class="card-img-top" style="height: 200px; object-fit: cover;">
                <span class="badge bg-success position-absolute top-0 start-0 m-2 p-2 fs-6 shadow-sm">
                    ₱<?= $fetch_products['price']; ?>/-
                </span>
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title text-truncate mb-1"><?= $fetch_products['name']; ?></h5>
                    <p class="small text-muted mb-3">Category: <span class="fw-bold text-primary"><?= ucfirst($fetch_products['category']); ?></span></p>
                    <p class="card-text small text-secondary mb-3 overflow-hidden" style="height: 3rem;">
                        <?= $fetch_products['details']; ?>
                    </p>
                    <div class="d-flex gap-2 mt-auto pt-3 border-top">
                       <a href="admin_update_product.php?update=<?= $fetch_products['id']; ?>" 
                           class="btn btn-warning btn-sm" id="btn-update">
                            <i class="fas fa-edit me-1"></i> Update
                       </a>
                       <a href="#" 
                           class="btn btn-danger btn-sm btn-delete-product"
                           data-delete-url="admin_products.php?delete=<?= $fetch_products['id']; ?>">
                            <i class="fas fa-trash me-1"></i> Delete
                       </a>
                        </div>
                </div>
            </div>
        </div>
        <?php
            }
        } elseif ($is_filtered) {
            echo '<div class="col-12">
                      <div class="alert alert-warning text-center mt-3" role="alert">
                          <i class="fas fa-exclamation-triangle me-2"></i> No products found matching your current filters.
                      </div>
                    </div>';
        } else {
            echo '<div class="col-12">
                      <div class="card shadow-sm p-4 text-center">
                          <p class="empty mb-0">No products added yet!</p>
                      </div>
                    </div>';
        }
        ?>
    </div>
    
    <?php if ($total_pages > 1): ?>
        <div class="pagination-container d-flex justify-content-center">
            <nav>
                <ul class="pagination shadow-sm rounded-pill overflow-hidden">
                    
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?= max(1, $page - 1); ?>&search_box=<?= htmlspecialchars($search_query); ?>&category=<?= htmlspecialchars($selected_category); ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <?php
                    $start_loop = max(1, $page - 2);
                    $end_loop = min($total_pages, $page + 2);
                    
                    if ($page < 3) $end_loop = min($total_pages, 5);
                    if ($page > $total_pages - 2) $start_loop = max(1, $total_pages - 4);
                    
                    if ($start_loop > 1) {
                        echo '<li class="page-item"><a class="page-link" href="?page=1&search_box=' . htmlspecialchars($search_query) . '&category=' . htmlspecialchars($selected_category) . '">1</a></li>';
                        if ($start_loop > 2) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                    }
                    
                    for ($i = $start_loop; $i <= $end_loop; $i++):
                    ?>
                        <li class="page-item <?= ($page == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?= $i; ?>&search_box=<?= htmlspecialchars($search_query); ?>&category=<?= htmlspecialchars($selected_category); ?>">
                                <?= $i; ?>
                            </a>
                        </li>
                    <?php 
                    endfor; 
                    
                    if ($end_loop < $total_pages) {
                        if ($end_loop < $total_pages - 1) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                        echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '&search_box=' . htmlspecialchars($search_query) . '&category=' . htmlspecialchars($selected_category) . '">' . $total_pages . '</a></li>';
                    }
                    ?>
                    
                    <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?= min($total_pages, $page + 1); ?>&search_box=<?= htmlspecialchars($search_query); ?>&category=<?= htmlspecialchars($selected_category); ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    <?php elseif ($is_filtered && $total_records > 0): ?>
        <p class="text-center text-muted mt-2 mb-5">
            Showing **<?= $total_records; ?>** matching products.
        </p>
    <?php endif; ?>

</div>

<script src="js/admin_script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    const deleteButtons = document.querySelectorAll('.btn-delete-product');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const deleteUrl = this.getAttribute('data-delete-url');
            
            const modalLink = document.getElementById('modalDeleteLink');
            modalLink.href = deleteUrl;
            
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            deleteModal.show();
        });
    });
});
</script>
</body>
</html>
<?php
@include 'config.php';
session_start();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

$message = [];

/* -------------------------
   Wishlist & Cart Handling
   ------------------------- */
if (isset($_POST['add_to_wishlist']) || isset($_POST['add_to_cart'])) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        $response = ['status' => 'error', 'message' => ''];

        if (isset($_POST['add_to_wishlist'])) {
            if ($user_id) {
                $pid = filter_var($_POST['pid'], FILTER_SANITIZE_STRING);
                $p_name = filter_var($_POST['p_name'], FILTER_SANITIZE_STRING);
                $p_price = filter_var($_POST['p_price'], FILTER_SANITIZE_STRING);
                $p_image = filter_var($_POST['p_image'], FILTER_SANITIZE_STRING);

                $check_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE name = ? AND user_id = ?");
                $check_wishlist->execute([$p_name, $user_id]);

                $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE name = ? AND user_id = ?");
                $check_cart->execute([$p_name, $user_id]);

                if ($check_wishlist->rowCount() > 0) {
                    $response['message'] = 'Already added to wishlist!';
                } elseif ($check_cart->rowCount() > 0) {
                    $response['message'] = 'Already added to cart!';
                } else {
                    $insert_wishlist = $conn->prepare("INSERT INTO `wishlist` (user_id, pid, name, price, image) VALUES (?, ?, ?, ?, ?)");
                    if ($insert_wishlist->execute([$user_id, $pid, $p_name, $p_price, $p_image])) {
                        $response['status'] = 'success';
                        $response['message'] = 'Added to wishlist!';
                    } else {
                        $response['message'] = 'Could not add to wishlist due to a database error.';
                    }
                }
            } else {
                $response['message'] = 'Please log in to add items to your wishlist.';
            }
        } elseif (isset($_POST['add_to_cart'])) {
            if ($user_id) {
                $pid = filter_var($_POST['pid'], FILTER_SANITIZE_STRING);
                $p_name = filter_var($_POST['p_name'], FILTER_SANITIZE_STRING);
                $p_price = filter_var($_POST['p_price'], FILTER_SANITIZE_STRING);
                $p_image = filter_var($_POST['p_image'], FILTER_SANITIZE_STRING);
                $p_qty = filter_var($_POST['p_qty'], FILTER_SANITIZE_STRING);

                $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE name = ? AND user_id = ?");
                $check_cart->execute([$p_name, $user_id]);

                if ($check_cart->rowCount() > 0) {
                    $response['message'] = 'Already added to cart!';
                } else {
                    $check_stock = $conn->prepare("SELECT stock FROM `products` WHERE id = ?");
                    $check_stock->execute([$pid]);
                    $product_data = $check_stock->fetch(PDO::FETCH_ASSOC);

                    if ($product_data && $product_data['stock'] <= 0) {
                        $response['message'] = 'Sorry, this product is out of stock!';
                        echo json_encode($response);
                        exit;
                    }

                    if ($product_data && $p_qty > $product_data['stock']) {
                        $response['message'] = 'You cannot order more than the available stock (' . $product_data['stock'] . ').';
                        echo json_encode($response);
                        exit;
                    }

                    $check_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE name = ? AND user_id = ?");
                    $check_wishlist->execute([$p_name, $user_id]);

                    if ($check_wishlist->rowCount() > 0) {
                        $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE name = ? AND user_id = ?");
                        $delete_wishlist->execute([$p_name, $user_id]);
                    }

                    $insert_cart = $conn->prepare("INSERT INTO `cart` (user_id, pid, name, price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)");
                    if ($insert_cart->execute([$user_id, $pid, $p_name, $p_price, $p_qty, $p_image])) {
                        $response['status'] = 'success';
                        $response['message'] = 'Added to cart!';
                    } else {
                        $response['message'] = 'Could not add to cart due to a database error.';
                    }
                }
            } else {
                $response['message'] = 'Please log in to add items to your cart.';
            }
        }

        if ($user_id) {
            $new_cart_count = $conn->query("SELECT COUNT(*) FROM `cart` WHERE user_id = $user_id")->fetchColumn();
            $new_wishlist_count = $conn->query("SELECT COUNT(*) FROM `wishlist` WHERE user_id = $user_id")->fetchColumn();
            $response['new_cart_count'] = (int)$new_cart_count;
            $response['new_wishlist_count'] = (int)$new_wishlist_count;
        } else {
            $response['new_cart_count'] = 0;
            $response['new_wishlist_count'] = 0;
        }

        echo json_encode($response);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    
    <style>
        .pagination {
            margin: 3rem 0;
        }
        .pagination .page-link {
            color: #1B80CC;
            border: 1px solid #dee2e6;
            padding: 0.5rem 0.75rem;
            margin: 0 0.2rem;
            border-radius: 0.25rem;
        }
        .pagination .page-link:hover {
            background-color: #1B80CC;
            color: white;
            border-color: #1B80CC;
        }
        .pagination .page-item.active .page-link {
            background-color: #1B80CC;
            border-color: #1B80CC;
            color: white;
        }
        .pagination .page-item.disabled .page-link {
            color: #6c757d;
            pointer-events: none;
            background-color: #fff;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<?php
if (isset($message) && is_array($message)) {
    foreach ($message as $msg) {
        echo '<div class="alert alert-info alert-dismissible fade show fixed-top mt-5" role="alert" id="php-alert">' . $msg . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    }
}
?>
<div id="ajax-message-container"></div>

<section class="p-category text-center">
    <?php
    $categories = ['fruits', 'vegetables', 'fish', 'meat', 'chocolate', 'dairies', 'bread', 'drinks'];
    $current_category = isset($_GET['category']) ? $_GET['category'] : '';
    foreach ($categories as $cat) {
        $active = ($current_category === $cat) ? 'active' : '';
        echo "<a href='?category=" . urlencode($cat) . "' class='$active'>" . strtoupper($cat) . "</a>";
    }
    ?>
</section>

<section class="products">
    <h1 class="title">Products</h1>

    <form method="GET" class="text-center mb-4">
        <input type="hidden" name="category" value="<?= isset($_GET['category']) ? $_GET['category'] : '' ?>">
        <input type="hidden" name="page" value="<?= isset($_GET['page']) ? (int)$_GET['page'] : 1 ?>">
        <label for="sort_by" style="font-size: 1.5rem; font-weight: bold; margin-right: 10px;">Sort By:</label>
        <select name="sort_by" id="sort_by" onchange="this.form.submit()" 
                style="font-size: 1.2rem; padding: 10px 20px; border-radius: 10px; border: 2px solid #1B80CC;">
            <option value="">Default</option>
            <option value="price_asc" <?= (isset($_GET['sort_by']) && $_GET['sort_by'] == 'price_asc') ? 'selected' : '' ?>>Price: Low to High</option>
            <option value="price_desc" <?= (isset($_GET['sort_by']) && $_GET['sort_by'] == 'price_desc') ? 'selected' : '' ?>>Price: High to Low</option>
            <option value="stock_asc" <?= (isset($_GET['sort_by']) && $_GET['sort_by'] == 'stock_asc') ? 'selected' : '' ?>>Stock: Low to High</option>
            <option value="stock_desc" <?= (isset($_GET['sort_by']) && $_GET['sort_by'] == 'stock_desc') ? 'selected' : '' ?>>Stock: High to Low</option>
        </select>
    </form>

    <div class="box-container">

    <?php
    // Pagination setup
    $products_per_page = 21;
    $current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $offset = ($current_page - 1) * $products_per_page;

    $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : '';
    $order_by = "ORDER BY (stock = 0), id DESC"; 

    switch ($sort_by) {
        case 'price_asc':
            $order_by = "ORDER BY price ASC";
            break;
        case 'price_desc':
            $order_by = "ORDER BY price DESC";
            break;
        case 'stock_asc':
            $order_by = "ORDER BY stock ASC";
            break;
        case 'stock_desc':
            $order_by = "ORDER BY stock DESC";
            break;
    }

    // Count total products
    if (isset($_GET['category']) && !empty($_GET['category'])) {
        $category = $_GET['category'];
        if (in_array($category, $categories)) {
            $count_products = $conn->prepare("SELECT COUNT(*) FROM `products` WHERE category = ?");
            $count_products->execute([$category]);
        } else {
            $count_products = $conn->prepare("SELECT COUNT(*) FROM `products`");
            $count_products->execute();
        }
    } else {
        $count_products = $conn->prepare("SELECT COUNT(*) FROM `products`");
        $count_products->execute();
    }
    
    $total_products = $count_products->fetchColumn();
    $total_pages = ceil($total_products / $products_per_page);

    // Fetch products with limit
    if (isset($_GET['category']) && !empty($_GET['category'])) {
        $category = $_GET['category'];
        if (in_array($category, $categories)) {
            $select_products = $conn->prepare("SELECT * FROM `products` WHERE category = ? $order_by LIMIT $products_per_page OFFSET $offset");
            $select_products->execute([$category]);
        } else {
            $select_products = $conn->prepare("SELECT * FROM `products` $order_by LIMIT $products_per_page OFFSET $offset");
            $select_products->execute();
        }
    } else {
        $select_products = $conn->prepare("SELECT * FROM `products` $order_by LIMIT $products_per_page OFFSET $offset");
        $select_products->execute();
    }

    // Fetch all ratings in one query
    $ratings_data = $conn->query("
        SELECT product_id, ROUND(AVG(rating), 1) AS avg_rating, COUNT(*) AS total_reviews
        FROM reviews
        GROUP BY product_id
    ")->fetchAll(PDO::FETCH_UNIQUE);

    if ($select_products->rowCount() > 0) {
        while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {

            $product_id = $fetch_products['id'];
            $rating_info = $ratings_data[$product_id] ?? ['avg_rating' => 0, 'total_reviews' => 0];
            $avg_rating = $rating_info['avg_rating'];
            $total_reviews = $rating_info['total_reviews'];

            // Build star icons
            $filled = floor($avg_rating);
            $half = ($avg_rating - $filled >= 0.5);
            $empty = 5 - $filled - ($half ? 1 : 0);
            $stars_html = str_repeat('<i class="fas fa-star text-warning"></i>', $filled);
            if ($half) $stars_html .= '<i class="fas fa-star-half-alt text-warning"></i>';
            $stars_html .= str_repeat('<i class="far fa-star text-warning"></i>', $empty);
            ?>
            
            <form class="box product-form" method="post">
                <div class="price">₱<span><?= htmlspecialchars($fetch_products['price']); ?></span>/-</div>
                <a href="view_page.php?pid=<?= (int)$fetch_products['id']; ?>" class="fas fa-eye"></a>
                <img src="uploaded_img/<?= htmlspecialchars($fetch_products['image']); ?>" alt="">
                <div class="name"><?= htmlspecialchars($fetch_products['name']); ?></div>

                <!-- Rating display -->
                <div class="rating mb-2" style="font-size: 1.2rem;">
                    <?= $stars_html ?>
                    <span style="color:#555; font-size:1rem;">(<?= $avg_rating ?> / 5)</span>
                    <span style="color:#777; font-size:0.9rem;">• <?= $total_reviews ?> rating<?= $total_reviews != 1 ? 's' : '' ?></span>
                </div>

                <input type="hidden" name="pid" value="<?= (int)$fetch_products['id']; ?>">
                <input type="hidden" name="p_name" value="<?= htmlspecialchars($fetch_products['name']); ?>">
                <input type="hidden" name="p_price" value="<?= htmlspecialchars($fetch_products['price']); ?>">
                <input type="hidden" name="p_image" value="<?= htmlspecialchars($fetch_products['image']); ?>">

                <?php if ($fetch_products['stock'] > 0) { ?>
                    <div class="stock" style="font-size:20px;">Stock: <?= (int)$fetch_products['stock']; ?></div>
                    <input type="number" min="1" max="<?= (int)$fetch_products['stock']; ?>" value="1" name="p_qty" class="qty">
                    <input type="submit" value="Add to Wishlist" class="products-button wishlist" name="add_to_wishlist" data-action="wishlist">
                    <input type="submit" value="Add to Cart" class="products-button cart" name="add_to_cart" data-action="cart">
                <?php } else { ?>
                    <div class="stock" style="font-size:20px; color:#d00; font-weight:bold;">Out of Stock</div>
                    <input type="number" min="1" value="1" name="p_qty" class="qty" disabled style="opacity:0.6; cursor:not-allowed;">
                    <button class="products-button wishlist" disabled style="background-color:#999; color:#fff; opacity:0.6;">Add to Wishlist</button>
                    <button class="products-button cart" disabled style="background-color:#999; color:#fff; opacity:0.6;">Add to Cart</button>
                <?php } ?>
            </form>
            <?php
        }
    } else {
        echo '<p class="empty">No products found!</p>';
    }
    ?>
    </div>
    
    <?php if ($total_pages > 1): ?>
    <!-- Pagination -->
    <nav aria-label="Product pagination">
        <ul class="pagination justify-content-center">
            <?php
            // Build query string
            $query_params = $_GET;
            unset($query_params['page']);
            $query_string = http_build_query($query_params);
            $query_string = $query_string ? '&' . $query_string : '';
            ?>
            
            <!-- Previous Button -->
            <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= max(1, $current_page - 1) . $query_string ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            
            <?php
            // Show page numbers
            $start_page = max(1, $current_page - 2);
            $end_page = min($total_pages, $current_page + 2);
            
            if ($start_page > 1): ?>
                <li class="page-item"><a class="page-link" href="?page=1<?= $query_string ?>">1</a></li>
                <?php if ($start_page > 2): ?>
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                <?php endif;
            endif;
            
            for ($i = $start_page; $i <= $end_page; $i++): ?>
                <li class="page-item <?= ($i == $current_page) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i . $query_string ?>"><?= $i ?></a>
                </li>
            <?php endfor;
            
            if ($end_page < $total_pages): 
                if ($end_page < $total_pages - 1): ?>
                    <li class="page-item disabled"><span class="page-link">...</span></li>
                <?php endif; ?>
                <li class="page-item"><a class="page-link" href="?page=<?= $total_pages . $query_string ?>"><?= $total_pages ?></a></li>
            <?php endif; ?>
            
            <!-- Next Button -->
            <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= min($total_pages, $current_page + 1) . $query_string ?>" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
    
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    function displayMessage(message, type = 'info') {
        if (typeof window.displayMessage === 'function') {
            window.displayMessage(message, type);
        } else {
            $('#ajax-message-container').html(
                '<div class="alert alert-' + (type === 'success' ? 'success' : 'info') + ' alert-dismissible fade show fixed-top mt-5" role="alert">' +
                message +
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                '</div>'
            );
            setTimeout(function() { $('.alert').alert('close'); }, 5000);
        }
    }

    $('.product-form').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var formData = $form.serializeArray();
        var submitButtonName = e.originalEvent.submitter?.name || '';

        if (submitButtonName === '') return;
        formData.push({name: submitButtonName, value: '1'});
        $('#php-alert').alert('close');

        $.ajax({
            url: window.location.href.split('?')[0],
            type: 'POST',
            data: $.param(formData),
            dataType: 'json',
            success: function(response) {
                if (response.message) displayMessage(response.message, response.status);
                if (typeof window.updateHeaderCounts === 'function') {
                    window.updateHeaderCounts(response.new_cart_count, response.new_wishlist_count);
                }
            },
            error: function(xhr, status, error) {
                displayMessage('An error occurred: ' + status + ' - ' + error, 'danger');
                console.error("AJAX Error:", status, error);
            }
        });
    });
});
</script>
</body>
</html>
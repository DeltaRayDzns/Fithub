<?php
@include 'config.php';
session_start();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$message = [];

/* -------------------------
   Add to Wishlist handling
   ------------------------- */
if (isset($_POST['add_to_wishlist'])) {
    if ($user_id) {
        $pid = filter_var($_POST['pid'], FILTER_SANITIZE_STRING);
        $p_name = filter_var($_POST['p_name'], FILTER_SANITIZE_STRING);
        $p_price = filter_var($_POST['p_price'], FILTER_SANITIZE_STRING);
        $p_image = filter_var($_POST['p_image'], FILTER_SANITIZE_STRING);

        $check_stock = $conn->prepare("SELECT stock FROM `products` WHERE id = ?");
        $check_stock->execute([$pid]);
        $product_data = $check_stock->fetch(PDO::FETCH_ASSOC);

        if ($product_data && $product_data['stock'] <= 0) {
            $message[] = 'Sorry, this product is out of stock!';
        } else {
            $check_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE name = ? AND user_id = ?");
            $check_wishlist->execute([$p_name, $user_id]);

            $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE name = ? AND user_id = ?");
            $check_cart->execute([$p_name, $user_id]);

            if ($check_wishlist->rowCount() > 0) {
                $message[] = 'Already added to wishlist!';
            } elseif ($check_cart->rowCount() > 0) {
                $message[] = 'Already added to cart!';
            } else {
                $insert_wishlist = $conn->prepare("INSERT INTO `wishlist` (user_id, pid, name, price, image) VALUES (?, ?, ?, ?, ?)");
                if ($insert_wishlist->execute([$user_id, $pid, $p_name, $p_price, $p_image])) {
                    $message[] = 'Added to wishlist!';
                } else {
                    $message[] = 'Could not add to wishlist due to a database error.';
                }
            }
        }
    } else {
        $message[] = 'Please log in to add items to your wishlist.';
    }
}

/* -------------------------
   Add to Cart handling
   ------------------------- */
if (isset($_POST['add_to_cart'])) {
    if ($user_id) {
        $pid = filter_var($_POST['pid'], FILTER_SANITIZE_STRING);
        $p_name = filter_var($_POST['p_name'], FILTER_SANITIZE_STRING);
        $p_price = filter_var($_POST['p_price'], FILTER_SANITIZE_STRING);
        $p_image = filter_var($_POST['p_image'], FILTER_SANITIZE_STRING);
        $p_qty = isset($_POST['p_qty']) ? (int)filter_var($_POST['p_qty'], FILTER_SANITIZE_NUMBER_INT) : 1;
        if ($p_qty < 1) $p_qty = 1;

        $check_stock = $conn->prepare("SELECT stock FROM `products` WHERE id = ?");
        $check_stock->execute([$pid]);
        $product_data = $check_stock->fetch(PDO::FETCH_ASSOC);

        if ($product_data && $product_data['stock'] <= 0) {
            $message[] = 'Sorry, this product is out of stock!';
        } elseif ($product_data && $p_qty > (int)$product_data['stock']) {
            $message[] = 'You cannot order more than the available stock (' . (int)$product_data['stock'] . ').';
        } else {
            $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE name = ? AND user_id = ?");
            $check_cart->execute([$p_name, $user_id]);

            if ($check_cart->rowCount() > 0) {
                $message[] = 'Already added to cart!';
            } else {
                $check_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE name = ? AND user_id = ?");
                $check_wishlist->execute([$p_name, $user_id]);

                if ($check_wishlist->rowCount() > 0) {
                    $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE name = ? AND user_id = ?");
                    $delete_wishlist->execute([$p_name, $user_id]);
                }

                $insert_cart = $conn->prepare("INSERT INTO `cart` (user_id, pid, name, price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)");
                if ($insert_cart->execute([$user_id, $pid, $p_name, $p_price, $p_qty, $p_image])) {
                    $message[] = 'Added to cart!';
                } else {
                    $message[] = 'Could not add to cart due to a database error.';
                }
            }
        }
    } else {
        $message[] = 'Please log in to add items to your cart.';
    }
}

/* -------------------------
   Submit Review handling
   ------------------------- */
if (isset($_POST['submit_review'])) {
    if ($user_id) {
        $pid = filter_var($_POST['pid'], FILTER_SANITIZE_STRING);
        $rating = (int)$_POST['rating'];
        $review_text = substr(trim($_POST['review_text']), 0, 500);

        // Check if the user has purchased this product before allowing a review
        $check_order = $conn->prepare("
            SELECT * FROM `orders` 
            WHERE user_id = ? 
            AND payment_status = 'completed' 
            AND total_products LIKE ?
        ");
        $check_order->execute([$user_id, '%' . $_POST['p_name'] . '%']);

        if ($check_order->rowCount() == 0) {
            $message[] = 'You can only review products you have purchased.';
        } else {
            $review_image = null;
            if (!empty($_FILES['review_image']['name'])) {
                $review_image = time() . '_' . basename($_FILES['review_image']['name']);
                $target = 'review_image/' . $review_image;
                move_uploaded_file($_FILES['review_image']['tmp_name'], $target);
            }

            if ($rating < 1 || $rating > 5) {
                $message[] = 'Invalid rating!';
            } else {
                $insert_review = $conn->prepare("
                    INSERT INTO `reviews` (user_id, product_id, rating, review_text, image)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $insert_review->execute([$user_id, $pid, $rating, $review_text, $review_image]);
                $message[] = 'Review added successfully!';
            }
        }
    } else {
        $message[] = 'Please log in to post a review.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quick View</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'header.php'; ?>

<section class="quick-view">
<?php
$pid = $_GET['pid'];
$select_products = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
$select_products->execute([$pid]);
if ($select_products->rowCount() > 0) {
    while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
?>
<form action="" class="box" method="POST" enctype="multipart/form-data">
    <div class="price">₱<span><?= $fetch_products['price']; ?></span>/-</div>
    <img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="">
    <div class="name"><?= $fetch_products['name']; ?></div>
    <div class="details"><?= $fetch_products['details']; ?></div>
    <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
    <input type="hidden" name="p_name" value="<?= $fetch_products['name']; ?>">
    <input type="hidden" name="p_price" value="<?= $fetch_products['price']; ?>">
    <input type="hidden" name="p_image" value="<?= $fetch_products['image']; ?>">

    <?php if ($fetch_products['stock'] > 0) { ?>
        <div class="stock" style="font-size:20px;">Stock: <?= $fetch_products['stock']; ?></div>
        <input type="number" min="1" max="<?= $fetch_products['stock']; ?>" value="1" name="p_qty" class="qty">
        <div class="button-group">
            <input type="submit" value="Add to Wishlist" class="products-button wishlist" name="add_to_wishlist">
            <input type="submit" value="Add to Cart" class="products-button cart" name="add_to_cart">
        </div>
    <?php } else { ?>
        <div class="stock" style="font-size:20px; color:#d00; font-weight:bold;">Out of Stock</div>
        <div class="button-group">
            <button class="products-button wishlist" disabled>Out of Stock</button>
        </div>
    <?php } ?>

    <div class="back-btn-container">
        <a href="shop.php" class="products-button back">Back to Shop</a>
    </div>
</form>

<div class="reviews-section mt-5 p-3 rounded shadow-sm" style="background:#fff;">
<?php
// Calculate average rating & total reviews
$get_avg = $conn->prepare("
    SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_reviews 
    FROM `reviews` 
    WHERE product_id = ?
");
$get_avg->execute([$fetch_products['id']]);
$rating_data = $get_avg->fetch(PDO::FETCH_ASSOC);
$avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0;
$total_reviews = $rating_data['total_reviews'];
?>
<h2 class="text-center mb-4">
    Product Reviews 
    <?php if ($total_reviews > 0): ?>
        <span class="text-warning ms-2">
            <?= str_repeat('★', floor($avg_rating)); ?><?= str_repeat('☆', 5 - floor($avg_rating)); ?>
        </span>
        <small class="text-muted">(<?= $avg_rating; ?>/5 based on <?= $total_reviews; ?> reviews)</small>
    <?php endif; ?>
</h2>

<?php
// Check if user ordered this product
$hasOrdered = false;
if ($user_id) {
    $check_order = $conn->prepare("
        SELECT * FROM `orders` 
        WHERE user_id = ? 
        AND payment_status = 'completed' 
        AND total_products LIKE ?
    ");
    $check_order->execute([$user_id, '%' . $fetch_products['name'] . '%']);
    $hasOrdered = $check_order->rowCount() > 0;
}
?>

<?php if ($user_id && $hasOrdered): ?>
<form action="" method="POST" enctype="multipart/form-data" class="mb-4">
    <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
    <input type="hidden" name="p_name" value="<?= $fetch_products['name']; ?>">

    <div class="mb-3">
        <label for="rating" class="form-label">Your Rating</label>
        <select name="rating" id="rating" class="form-select" required>
            <option value="">Select rating</option>
            <option value="5">★★★★★ (5)</option>
            <option value="4">★★★★☆ (4)</option>
            <option value="3">★★★☆☆ (3)</option>
            <option value="2">★★☆☆☆ (2)</option>
            <option value="1">★☆☆☆☆ (1)</option>
        </select>
    </div>

    <div class="mb-3">
        <label for="review_text" class="form-label">Your Review</label>
        <textarea name="review_text" id="review_text" class="form-control" rows="3" maxlength="500" placeholder="Write your review (max 500 characters)" required></textarea>
    </div>

    <div class="mb-3">
        <label for="review_image" class="form-label">Upload Image (optional)</label>
        <input type="file" name="review_image" id="review_image" class="form-control" accept="image/*">
    </div>

    <button type="submit" name="submit_review" class="btn btn-success w-100">Submit Review</button>
</form>
<?php elseif ($user_id && !$hasOrdered): ?>
<p class="text-center text-muted">You can only review this product after purchasing it.</p>
<?php else: ?>
<p class="text-center text-muted">Please <a href="login.php">log in</a> to write a review.</p>
<?php endif; ?>

<?php
$get_reviews = $conn->prepare("
    SELECT r.*, u.name 
    FROM `reviews` r
    JOIN `users` u ON r.user_id = u.id
    WHERE r.product_id = ?
    ORDER BY r.created_at DESC
");
$get_reviews->execute([$fetch_products['id']]);
if ($get_reviews->rowCount() > 0) {
    while ($review = $get_reviews->fetch(PDO::FETCH_ASSOC)) {
        $short_review = strlen($review['review_text']) > 200 ? substr($review['review_text'], 0, 200) . '...' : $review['review_text'];
?>
<div class="card mb-3 border-0 shadow-sm">
    <div class="card-body">
        <h6 class="mb-1"><?= htmlspecialchars($review['name']); ?> 
            <span class="text-warning">
                <?= str_repeat('★', $review['rating']); ?>
                <?= str_repeat('☆', 5 - $review['rating']); ?>
            </span>
        </h6>
        <small class="text-muted"><?= date('M d, Y', strtotime($review['created_at'])); ?></small>
        <p class="mt-2"><?= htmlspecialchars($short_review); ?></p>
        <?php if ($review['image']): ?>
            <img src="review_image/<?= htmlspecialchars($review['image']); ?>" class="img-fluid rounded mt-2" style="max-width:150px;">
        <?php endif; ?>
    </div>
</div>
<?php
    }
} else {
    echo '<p class="text-muted">No reviews yet. Be the first to review this product!</p>';
}
?>
</div>
<?php
    }
} else {
    echo '<p class="empty">No products found!</p>';
}
?>
</section>

<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>

<?php

@include 'config.php';

session_start();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

$message = [];

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

                $check_wishlist_numbers = $conn->prepare("SELECT * FROM `wishlist` WHERE name = ? AND user_id = ?");
                $check_wishlist_numbers->execute([$p_name, $user_id]);

                $check_cart_numbers = $conn->prepare("SELECT * FROM `cart` WHERE name = ? AND user_id = ?");
                $check_cart_numbers->execute([$p_name, $user_id]);

                if ($check_wishlist_numbers->rowCount() > 0) {
                    $response['message'] = 'already added to wishlist!';
                } elseif ($check_cart_numbers->rowCount() > 0) {
                    $response['message'] = 'already added to cart!';
                } else {
                    $insert_wishlist = $conn->prepare("INSERT INTO `wishlist`(user_id, pid, name, price, image) VALUES(?,?,?,?,?)");
                    if ($insert_wishlist->execute([$user_id, $pid, $p_name, $p_price, $p_image])) {
                        $response['status'] = 'success';
                        $response['message'] = 'added to wishlist!';
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

                $check_cart_numbers = $conn->prepare("SELECT * FROM `cart` WHERE name = ? AND user_id = ?");
                $check_cart_numbers->execute([$p_name, $user_id]);

                if ($check_cart_numbers->rowCount() > 0) {
                    $response['message'] = 'already added to cart!';
                } else {
                    // Stock validation
                    $check_stock = $conn->prepare("SELECT stock FROM `products` WHERE id = ?");
                    $check_stock->execute([$pid]);
                    $product_data = $check_stock->fetch(PDO::FETCH_ASSOC);

                    if ($product_data && $product_data['stock'] <= 0) {
                        $message[] = 'Sorry, this product is out of stock!';
                        header("location: shop.php");
                        exit;
                    }

                    if ($product_data && $p_qty > $product_data['stock']) {
                        $message[] = 'You cannot order more than the available stock (' . $product_data['stock'] . ').';
                        header("location: shop.php");
                        exit;
                    }

                    $check_wishlist_numbers = $conn->prepare("SELECT * FROM `wishlist` WHERE name = ? AND user_id = ?");
                    $check_wishlist_numbers->execute([$p_name, $user_id]);

                    if ($check_wishlist_numbers->rowCount() > 0) {
                        $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE name = ? AND user_id = ?");
                        $delete_wishlist->execute([$p_name, $user_id]);
                    }

                    $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
                    if ($insert_cart->execute([$user_id, $pid, $p_name, $p_price, $p_qty, $p_image])) {
                        $response['status'] = 'success';
                        $response['message'] = 'added to cart!';
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
    <title>Home Page</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

</head>
<body>
    
<?php include 'header.php'; ?>

<?php
if (isset($message)) {
   foreach ($message as $msg) {
     echo '<div class="alert alert-info alert-dismissible fade show fixed-top mt-5" role="alert" id="php-alert">' . htmlspecialchars($msg) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
   }
}
?>
<div id="ajax-message-container"></div>

<div class="home-bg">
    <section class="home">
        <div class="content">
            <span>Less time shopping, more time living.</span>
            <h3>Nourish Your Body, Elevate Your Life with Our Quality Products.</h3>
            <a href="about.php" class="btn">about us</a>
        </div>
    </section>
</div>

<section class="home-category">
    <h1 class="title">CATEGORIES</h1>
    <div class="box-container">

        <div class="box">
            <img src="https://png.pngtree.com/png-vector/20240807/ourmid/pngtree-juicy-fruits-and-vitamins-natural-organic-fruits-png-image_13146415.png" alt="">
            <h3>FRUITS</h3>
            <a href="shop.php?category=fruits" class="btn">fruits</a>
        </div>

        <div class="box">
            <img src="https://png.pngtree.com/png-vector/20240413/ourmid/pngtree-a-piece-of-meat-that-is-cut-in-half-png-image_11955762.png" alt="">
            <h3>MEAT</h3>
            <a href="shop.php?category=meat" class="btn">meat</a>
        </div>

        <div class="box">
            <img src="https://static.vecteezy.com/system/resources/previews/022/984/730/non_2x/vegetable-transparent-free-png.png" alt="">
            <h3>VEGETABLES</h3>
            <a href="shop.php?category=vegetables" class="btn">vegetables</a>
        </div>

        <div class="box">
            <img src="https://png.pngtree.com/png-clipart/20211116/original/pngtree-rohu-carp-fish-png-png-image_6940208.png" alt="">
            <h3>FISH</h3>
            <a href="shop.php?category=fish" class="btn">fish</a>
        </div>

        <div class="box">
            <img src="https://static.vecteezy.com/system/resources/thumbnails/046/710/013/small_2x/pile-of-chocolate-bars-isolated-on-transparent-background-png.png" alt="">
            <h3>CHOCOLATE</h3>
            <a href="shop.php?category=chocolate" class="btn">chocolate</a>
        </div>

        <div class="box">
            <img src="https://static.vecteezy.com/system/resources/previews/050/590/571/non_2x/a-variety-of-dairy-products-including-milk-cheese-and-butter-free-png.png" alt="">
            <h3>DAIRIES</h3>
            <a href="shop.php?category=dairies" class="btn">dairies</a>
        </div>

        <div class="box">
            <img src="https://png.pngtree.com/png-vector/20240128/ourmid/pngtree-bakery-bread-milky-plain-white-bread-png-image_11503244.png" alt="">
            <h3>BREAD</h3>
            <a href="shop.php?category=bread" class="btn">bread</a>
        </div>

        <div class="box">
            <img src="https://static.vecteezy.com/system/resources/thumbnails/024/850/489/small_2x/orange-juice-glass-transparent-background-png.png" alt="">
            <h3>DRINKS</h3>
            <a href="shop.php?category=drinks" class="btn">drinks</a>
        </div>

    </div>
</section>

<section class="products">
    <h1 class="title">LATEST PRODUCTS</h1>
    <div class="box-container">

    <?php
        $select_products = $conn->prepare("SELECT * FROM `products` ORDER BY CASE WHEN stock = 0 THEN 1 ELSE 0 END, id DESC LIMIT 6");
        $select_products->execute();

        // Fetch average rating + total reviews
        $ratings = $conn->query("
            SELECT product_id, ROUND(AVG(rating),1) AS avg_rating, COUNT(*) AS total_reviews 
            FROM reviews 
            GROUP BY product_id
        ")->fetchAll(PDO::FETCH_UNIQUE);

        if($select_products->rowCount() > 0){
           while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){ 
                $pid = $fetch_products['id'];
                $avg_rating = $ratings[$pid]['avg_rating'] ?? 0;
                $total_reviews = $ratings[$pid]['total_reviews'] ?? 0;

                $filled = floor($avg_rating);
                $half = ($avg_rating - $filled >= 0.5);
                $empty = 5 - $filled - ($half ? 1 : 0);
                $stars = str_repeat('<i class="fas fa-star text-warning"></i>', $filled);
                if ($half) $stars .= '<i class="fas fa-star-half-alt text-warning"></i>';
                $stars .= str_repeat('<i class="far fa-star text-warning"></i>', $empty);
    ?>
    <form class="box product-form"> 
      <div class="price">₱<span><?= $fetch_products['price']; ?></span>/-</div>
      <a href="view_page.php?pid=<?= $fetch_products['id']; ?>" class="fas fa-eye"></a>
      <img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="">
      <div class="name"><?= $fetch_products['name']; ?></div>

      <!-- Rating Display -->
      <div class="rating mb-2" style="font-size: 1.1rem;">
          <?= $stars ?> 
          <span style="color:#555; font-size:1rem;">(<?= $avg_rating ?> / 5)</span>
          <span style="color:#777; font-size:0.9rem;">• <?= $total_reviews ?> review<?= $total_reviews != 1 ? 's' : '' ?></span>
      </div>

      <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
      <input type="hidden" name="p_name" value="<?= $fetch_products['name']; ?>">
      <input type="hidden" name="p_price" value="<?= $fetch_products['price']; ?>">
      <input type="hidden" name="p_image" value="<?= $fetch_products['image']; ?>">

      <?php if ($fetch_products['stock'] > 0): ?>
          <div class="stock" style="font-size:20px;">Stock: <?= $fetch_products['stock']; ?></div>
          <input type="number" min="1" max="<?= $fetch_products['stock']; ?>" value="1" name="p_qty" class="qty">
          <input type="submit" value="add to wishlist" class="products-button wishlist" name="add_to_wishlist">
          <input type="submit" value="add to cart" class="products-button cart" name="add_to_cart">
      <?php else: ?>
          <div class="stock text-danger fw-bold" style="font-size:20px;">Out of Stock</div>
          <button type="button" class="products-button wishlist" disabled style="opacity:0.5; cursor:not-allowed;">add to wishlist</button>
          <button type="button" class="products-button cart" disabled style="opacity:0.5; cursor:not-allowed;">add to cart</button>
      <?php endif; ?>
    </form>
    <?php
        }
    }else{
        echo '<p class="empty">no products added yet!</p>';
    }
    ?>

    </div>
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
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);
        }
    }

    $('.product-form').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        var formData = $form.serializeArray();
        var submitButtonName = e.originalEvent.submitter?.name || '';
        
        formData.push({name: submitButtonName, value: '1'});
        $('#php-alert').alert('close');

        $.ajax({
            url: window.location.href.split('?')[0],
            type: 'POST',
            data: $.param(formData),
            dataType: 'json',
            success: function(response) {
                if (response.message) {
                    displayMessage(response.message, response.status);
                }
                if (response.new_cart_count !== undefined || response.new_wishlist_count !== undefined) {
                    if (typeof window.updateHeaderCounts === 'function') {
                        window.updateHeaderCounts(response.new_cart_count, response.new_wishlist_count);
                    }
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

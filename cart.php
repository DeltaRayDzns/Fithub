<?php

@include 'config.php';

session_start();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if(isset($_GET['delete']) && $user_id){
   $delete_id = $_GET['delete'];
   $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
   $delete_cart_item->execute([$delete_id]);
   header('location:cart.php');
   exit;
}

if(isset($_GET['delete_all']) && $user_id){
   $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
   $delete_cart_item->execute([$user_id]);
   header('location:cart.php');
   exit;
}

if(isset($_POST['update_qty'])){
   if($user_id){
      $cart_id = $_POST['cart_id'];
      $p_qty = $_POST['p_qty'];
      $p_qty = filter_var($p_qty, FILTER_SANITIZE_STRING);

      
      $get_stock = $conn->prepare("
         SELECT products.stock 
         FROM cart 
         JOIN products ON cart.pid = products.id 
         WHERE cart.id = ?
      ");
      $get_stock->execute([$cart_id]);
      $product_data = $get_stock->fetch(PDO::FETCH_ASSOC);

      if ($product_data) {
         $available_stock = (int)$product_data['stock'];

         if ($p_qty > $available_stock) {
            $message[] = "You cannot set a quantity higher than available stock ($available_stock).";
         } else {
            $update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ?");
            $update_qty->execute([$p_qty, $cart_id]);
            $message[] = 'Cart quantity updated';
         }
      } else {
         $message[] = 'Product not found or invalid cart item.';
      }
   } else {
      $message[] = 'Please log in to update your cart';
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Shopping Cart</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'header.php'; ?>

<section class="shopping-cart">

   <h1 class="title">Products Added</h1>

   <div class="box-container">

   <?php
      $grand_total = 0;

      if($user_id){
         $select_cart = $conn->prepare("
            SELECT cart.*, products.stock 
            FROM cart 
            JOIN products ON cart.pid = products.id 
            WHERE cart.user_id = ?
         ");
         $select_cart->execute([$user_id]);
      } else {
         $select_cart = null;
      }

      if($select_cart && $select_cart->rowCount() > 0){
         while($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)){ 
   ?>
   <form action="" method="POST" class="box">
      <a href="cart.php?delete=<?= $fetch_cart['id']; ?>" class="fas fa-times" onclick="return confirm('Delete this from cart?');"></a>
      <a href="view_page.php?pid=<?= $fetch_cart['pid']; ?>" class="fas fa-eye"></a>
      <img src="uploaded_img/<?= $fetch_cart['image']; ?>" alt="">
      <div class="name"><?= $fetch_cart['name']; ?></div>
      <div class="price">₱<?= $fetch_cart['price']; ?>/-</div>
      <div class="stock" style="font-size:20px">Stock: <?= $fetch_cart['stock']; ?></div>
      <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
      <div class="flex-btn">

         <input type="number" min="1" max="<?= $fetch_cart['stock']; ?>" value="<?= $fetch_cart['quantity']; ?>" class="qty" name="p_qty">
         <input type="submit" value="Update" name="update_qty" class="option-btn">
      </div>
      <div class="sub-total"> Sub Total : <span>₱<?= $sub_total = ($fetch_cart['price'] * $fetch_cart['quantity']); ?>/-</span> </div>
   </form>
   <?php
      $grand_total += $sub_total;
      }
   } elseif($user_id) {
      echo '<p class="empty">Your cart is empty</p>';
   } else {
      echo '<p class="empty">Please log in to view your cart</p>';
   }
   ?>
   </div>

   <div class="cart-total">
      <p>Grand Total : <span>₱<?= $grand_total; ?>/-</span></p>
      <a href="shop.php" class="option-btn">Continue Shopping</a>
      <a href="cart.php?delete_all" class="delete-btn <?= ($grand_total > 1)?'':'disabled'; ?>">Delete All</a>
      <a href="checkout.php" class="btn <?= ($grand_total > 1)?'':'disabled'; ?>">Proceed to Checkout</a>
   </div>

</section>

<?php include 'footer.php'; ?>
<script src="js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

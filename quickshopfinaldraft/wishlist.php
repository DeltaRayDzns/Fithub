<?php

@include 'config.php';

session_start();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if(isset($_POST['add_to_cart'])){

   if(!$user_id){
      $message[] = 'Please log in to add items to cart';
   } else {
      $pid = $_POST['pid'];
      $pid = filter_var($pid, FILTER_SANITIZE_STRING);
      $p_name = $_POST['p_name'];
      $p_name = filter_var($p_name, FILTER_SANITIZE_STRING);
      $p_price = $_POST['p_price'];
      $p_price = filter_var($p_price, FILTER_SANITIZE_STRING);
      $p_image = $_POST['p_image'];
      $p_image = filter_var($p_image, FILTER_SANITIZE_STRING);
      $p_qty = $_POST['p_qty'];
      $p_qty = filter_var($p_qty, FILTER_SANITIZE_STRING);

      $check_stock = $conn->prepare("SELECT stock FROM `products` WHERE id = ?");
      $check_stock->execute([$pid]);
      $product = $check_stock->fetch(PDO::FETCH_ASSOC);

      if($product && $p_qty > $product['stock']){
         $message[] = 'Not enough stock available!';
      } else {
         $check_cart_numbers = $conn->prepare("SELECT * FROM `cart` WHERE name = ? AND user_id = ?");
         $check_cart_numbers->execute([$p_name, $user_id]);

         if($check_cart_numbers->rowCount() > 0){
            $message[] = 'Already added to cart!';
         }else{

            $check_wishlist_numbers = $conn->prepare("SELECT * FROM `wishlist` WHERE name = ? AND user_id = ?");
            $check_wishlist_numbers->execute([$p_name, $user_id]);

            if($check_wishlist_numbers->rowCount() > 0){
               $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE name = ? AND user_id = ?");
               $delete_wishlist->execute([$p_name, $user_id]);
            }

            $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
            $insert_cart->execute([$user_id, $pid, $p_name, $p_price, $p_qty, $p_image]);
            $message[] = 'Added to cart!';
         }
      }
   }
}

if(isset($_GET['delete']) && $user_id){
   $delete_id = $_GET['delete'];
   $delete_wishlist_item = $conn->prepare("DELETE FROM `wishlist` WHERE id = ?");
   $delete_wishlist_item->execute([$delete_id]);
   header('location:wishlist.php');
   exit;
}

if(isset($_GET['delete_all']) && $user_id){
   $delete_wishlist_item = $conn->prepare("DELETE FROM `wishlist` WHERE user_id = ?");
   $delete_wishlist_item->execute([$user_id]);
   header('location:wishlist.php');
   exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Wishlist</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
   
<?php include 'header.php'; ?>

<section class="wishlist">

   <h1 class="title">Products Added</h1>

   <div class="box-container">

   <?php
      $grand_total = 0;

      if($user_id){
         $select_wishlist = $conn->prepare("
            SELECT wishlist.*, products.stock 
            FROM wishlist 
            JOIN products ON wishlist.pid = products.id 
            WHERE wishlist.user_id = ?
         ");
         $select_wishlist->execute([$user_id]);
      } else {
         $select_wishlist = null;
      }

      if($select_wishlist && $select_wishlist->rowCount() > 0){
         while($fetch_wishlist = $select_wishlist->fetch(PDO::FETCH_ASSOC)){ 
   ?>
   <form action="" method="POST" class="box">
      <a href="wishlist.php?delete=<?= $fetch_wishlist['id']; ?>" class="fas fa-times" onclick="return confirm('Delete this from wishlist?');"></a>
      <a href="view_page.php?pid=<?= $fetch_wishlist['pid']; ?>" class="fas fa-eye"></a>
      <img src="uploaded_img/<?= $fetch_wishlist['image']; ?>" alt="">
      <div class="name"><?= $fetch_wishlist['name']; ?></div>
      <div class="price">₱<?= $fetch_wishlist['price']; ?>/-</div>
      <div class="stock" style="font-size: 20px;">Stock: <?= $fetch_wishlist['stock']; ?></div>
      <input type="number" min="1" max="<?= $fetch_wishlist['stock']; ?>" value="1" class="qty" name="p_qty">
      <input type="hidden" name="pid" value="<?= $fetch_wishlist['pid']; ?>">
      <input type="hidden" name="p_name" value="<?= $fetch_wishlist['name']; ?>">
      <input type="hidden" name="p_price" value="<?= $fetch_wishlist['price']; ?>">
      <input type="hidden" name="p_image" value="<?= $fetch_wishlist['image']; ?>">
      <input type="submit" value="Add to Cart" name="add_to_cart" class="btn <?= ($fetch_wishlist['stock'] < 1)?'disabled':''; ?>">
   </form>
   <?php
      $grand_total += $fetch_wishlist['price'];
      }
   } elseif($user_id) {
      echo '<p class="empty">Your wishlist is empty</p>';
   } else {
      echo '<p class="empty">Please log in to view your wishlist</p>';
   }
   ?>
   </div>

   <div class="wishlist-total">
      <p>Grand Total : <span>₱<?= $grand_total; ?>/-</span></p>
      <a href="shop.php" class="option-btn">Continue Shopping</a>
      <a href="wishlist.php?delete_all" class="delete-btn <?= ($grand_total > 1)?'':'disabled';

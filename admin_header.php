<?php
if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="alert alert-info alert-dismissible fade show" role="alert">
         '.$message.'
         <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      ';
   }
}
?>

<button class="sidebar-toggle" aria-controls="sidebar" aria-expanded="true">
    <i class="fas fa-bars"></i>
</button>

<aside class="sidebar" id="sidebar">

    <div class="sidebar-logo">
        <a href="admin_page.php">
            <img src="images/logo.png" alt="logo">
            <h1>Quick<span class="shoplogospan">Shop</span>Admin</h1>
        </a>
    </div>

    <ul class="sidebar-nav">
        <li><a href="admin_page.php"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="admin_products.php"><i class="fas fa-box"></i> Products</a></li>
        <li><a href="admin_stocks.php"><i class="fas fa-warehouse"></i> Stocks</a></li>
        <li><a href="admin_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
        <li><a href="admin_users.php"><i class="fas fa-users"></i> Users</a></li>
        <li><a href="admin_contacts.php"><i class="fas fa-envelope"></i> Messages</a></li>
    </ul>

    <div class="sidebar-profile">
        <?php
          $select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
          $select_profile->execute([$admin_id]);
          $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
        ?>
        <div class="profile-dropdown">
            <div class="profile-toggle">
                <img src="uploaded_img/<?= $fetch_profile['image']; ?>" alt="profile">
                <span><?= htmlspecialchars($fetch_profile['name']); ?></span>
            </div>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="admin_update_profile.php">Update Profile</a></li>
                <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>

</aside>

<div class="main-content">

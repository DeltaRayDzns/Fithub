<?php
$current_page = basename($_SERVER['PHP_SELF']);

if (isset($message)) {
    foreach ($message as $msg) {
        echo '
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            ' . htmlspecialchars($msg) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        ';
    }
}
?>

<style>
    .navbar-nav .nav-link {
        transition: font-weight 0.2s, color 0.2s;
    }
    .navbar-nav .nav-link:hover,
    .navbar-nav .nav-link:focus,
    .navbar-nav .nav-link.active {
        font-weight: bold;
        color: #3638D9 !important; 
    }
</style>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container-fluid">

        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="images/logo.png" alt="logo" style="height:60px;" class="me-2">
            <h1>Quick<span class="shoplogospan">Shop</span></h1>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#qsNavbar" aria-controls="qsNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="qsNavbar">

            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link <?= ($current_page == 'index.php') ? 'active' : ''; ?>" href="index.php">HOME</a></li>
                <li class="nav-item"><a class="nav-link <?= ($current_page == 'shop.php') ? 'active' : ''; ?>" href="shop.php">SHOP</a></li>
                <li class="nav-item"><a class="nav-link <?= ($current_page == 'orders.php') ? 'active' : ''; ?>" href="orders.php">ORDERS</a></li>
                <li class="nav-item"><a class="nav-link <?= ($current_page == 'about.php') ? 'active' : ''; ?>" href="about.php">ABOUT</a></li>
                <li class="nav-item"><a class="nav-link <?= ($current_page == 'contact.php') ? 'active' : ''; ?>" href="contact.php">CONTACT</a></li>
            </ul>

            <div class="d-flex align-items-center nav-icons">
                <a href="search_page.php" class="nav-link px-2">
                    <i class="fas fa-search"></i>
                </a>

                <?php
                $count_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
                $count_cart_items->execute([$user_id]);

                $count_wishlist_items = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ?");
                $count_wishlist_items->execute([$user_id]);
                ?>

                <a href="wishlist.php" class="nav-link position-relative px-2">
                    <i class="fas fa-heart"></i>
                    <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle">
                        <?= $count_wishlist_items->rowCount(); ?>
                    </span>
                </a>

                <a href="cart.php" class="nav-link position-relative px-2">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle">
                        <?= $count_cart_items->rowCount(); ?>
                    </span>
                </a>

                <div class="dropdown ms-3">
                    <?php
                    if (isset($user_id)) {
                        $select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
                        $select_profile->execute([$user_id]);
                        $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
                    }
                    ?>

                    <?php if (!empty($fetch_profile)) : ?>

                        <a class="d-flex align-items-center text-decoration-none dropdown-toggle" href="#" role="button" id="qsProfileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="uploaded_img/<?= htmlspecialchars($fetch_profile['image']); ?>" alt="profile" class="rounded-circle me-2" style="width:36px; height:36px; object-fit:cover;">
                            <span class="d-none d-lg-inline"><?= htmlspecialchars($fetch_profile['name']); ?></span>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="qsProfileDropdown">
                            <li><a class="dropdown-item" href="user_profile_update.php">Update Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                        </ul>

                    <?php else : ?>

                        <a class="d-flex align-items-center text-decoration-none dropdown-toggle" href="#" role="button" id="qsGuestDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle fa-2x text-primary me-2"></i>
                            <span class="d-none d-lg-inline">Account</span>
                        </a>

                    <ul class="dropdown-menu dropdown-menu-end text-center" aria-labelledby="qsGuestDropdown">
                        <li class="px-3 py-2">
                            <span class="text-muted d-block mb-2">Don’t have an account?</span>
                            <a href="login.php" class="guest-btn login-btn w-100 mb-2">Log In</a>
                            <a href="register.php" class="guest-btn signup-btn w-100">Sign Up</a>
                        </li>
                    </ul>

                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    window.updateHeaderCounts = function(cartCount, wishlistCount) {
        const cartBadge = document.querySelector('.fa-shopping-cart')
            ?.closest('a')
            ?.querySelector('.badge');
        const wishlistBadge = document.querySelector('.fa-heart')
            ?.closest('a')
            ?.querySelector('.badge');

        if (cartBadge && typeof cartCount !== 'undefined') {
            cartBadge.textContent = cartCount;
        }
        if (wishlistBadge && typeof wishlistCount !== 'undefined') {
            wishlistBadge.textContent = wishlistCount;
        }
    };
</script>
</nav>
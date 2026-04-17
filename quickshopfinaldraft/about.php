<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'] ?? null; 

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>about</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'header.php'; ?>

<section class="about">

   <div class="row">

      <div class="box"> 
         <img src="images/aboutuspic.png" alt="">
         <h3>ABOUT US</h3>
         <p>At QuickShop, we believe grocery shopping should be simple, convenient, and reliable. Founded in 2017, QuickShop began as a small neighborhood store and has grown into a trusted online grocery platform serving communities across the Philippines. We provide customers with a seamless shopping experience by combining the ease of online browsing with the assurance of fresh, quality products delivered straight to their door. Whether it’s everyday essentials, fresh produce, or household needs, QuickShop is here to save you time and make life easier.
         </p>
      </div>
      
      <div class="box">
         <img src="images/aboutuspic2.png" alt="">
         <h3>WHAT DO WE HAVE?</h3>
         <p>At QuickShop, we focus on providing the essentials that matter most. Our selection includes fresh meat, crisp vegetables, premium fish, and seasonal fruits. Everything is carefully chosen to ensure quality and freshness with every order. With these everyday staples, QuickShop makes it easier for you to prepare delicious and healthy meals for your family, without the hassle of traditional grocery shopping.</p>
      </div>

      <a href="shop.php" style="text-align: center; width: 350px;" class="btn">our shop</a>

   </div>

</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php

@include 'config.php';
session_start();

if(isset($_POST['submit'])){

    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password_input = $_POST['pass']; 

    $sql = "SELECT * FROM `users` WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if($row){
        $stored_hash = $row['password'];

        if (strlen($stored_hash) === 32 && !str_starts_with($stored_hash, '$')) {
            

            if (md5($password_input) === $stored_hash) {
                

                $new_hashed_pass = password_hash($password_input, PASSWORD_DEFAULT);

                $update = $conn->prepare("UPDATE `users` SET password = ? WHERE id = ?");
                $update->execute([$new_hashed_pass, $row['id']]);

                if($row['user_type'] == 'admin'){
                    $_SESSION['admin_id'] = $row['id'];
                    header('location:admin_page.php');
                } else { 
                    $_SESSION['user_id'] = $row['id'];
                    header('location:index.php');
                }
                exit; 
                
            } else {
                $message[] = 'Incorrect email or password!';
            }
        
        } else {

            if (password_verify($password_input, $stored_hash)) {
                
                if($row['user_type'] == 'admin'){
                    $_SESSION['admin_id'] = $row['id'];
                    header('location:admin_page.php');
                } else { 
                    $_SESSION['user_id'] = $row['id'];
                    header('location:index.php');
                }
                exit; 

            } else {
                $message[] = 'Incorrect email or password!';
            }
        }
    } else {
        $message[] = 'Incorrect email or password!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Login</title>

   <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600&display=swap" rel="stylesheet">

   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">



</head>
<body class="d-flex align-items-center justify-content-center vh-100" style="background-color: #C4DFF5; font-family: 'Rubik', sans-serif; ">

   <div class="container">
      <div class="row justify-content-center">
         <div class="col-md-5">
            <?php if(isset($message)): ?>
               <?php foreach($message as $msg): ?>
                  <div class="alert alert-warning alert-dismissible fade show" role="alert">
                     <i class="fas fa-exclamation-circle me-2"></i> 
                     <?= $msg; ?>
                     <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>
               <?php endforeach; ?>
            <?php endif; ?>

            <div class="card shadow-lg border-0 rounded-4">
               <div class="card-body p-4">
                  <h3 class="text-center mb-4">Login</h3>
                  <form action="" method="POST">
                     <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" required>
                     </div>
                     <div class="mb-3">
                        <label for="pass" class="form-label">Password</label>
                        <input type="password" name="pass" id="pass" class="form-control" placeholder="Enter your password" required>
                     </div>
                     <div class="d-grid">
                        <button type="submit" name="submit" class="btn btn-primary" style="border-radius:20px; height:50px; background-color: #1B80CC; ">Login Now</button>
                     </div>
                  </form>
                  <p class="text-center mt-3">
                     Don't have an account? <a href="register.php" class="text-decoration-none">Register now</a>
                  </p>
               </div>
            </div>
         </div>
      </div>
   </div>

   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

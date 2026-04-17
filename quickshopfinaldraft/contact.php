<?php

@include 'config.php';

session_start();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

$is_logged_in = !empty($user_id);


if(isset($_POST['send'])){
    
    if (!$is_logged_in) {
        $message[] = 'You must be logged in to send a message!';
    } else {

        $name = $_POST['name'];
        $name = filter_var($name, FILTER_SANITIZE_STRING);
        $email = $_POST['email'];
        $email = filter_var($email, FILTER_SANITIZE_STRING);
        $number = $_POST['number'];
        $number = filter_var($number, FILTER_SANITIZE_STRING);
        $msg = $_POST['msg'];
        $msg = filter_var($msg, FILTER_SANITIZE_STRING);

        $select_message = $conn->prepare("SELECT * FROM `message` WHERE name = ? AND email = ? AND number = ? AND message = ?");
        $select_message->execute([$name, $email, $number, $msg]);

        if($select_message->rowCount() > 0){
            $message[] = 'already sent message!';
        }else{

            $insert_message = $conn->prepare("INSERT INTO `message`(user_id, name, email, number, message) VALUES(?,?,?,?,?)");
            $insert_message->execute([$user_id, $name, $email, $number, $msg]);

            $message[] = 'sent message successfully!';

        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>contact</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="css/style.css">
    <style>
        .contact form {
            position: relative;
        }
        .contact form.disabled-form {
            pointer-events: none;
            opacity: 0.5;
            user-select: none;
        }
        .contact .clickable-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.8); 
            z-index: 10;
            border-radius: .5rem;
            text-decoration: none; 
            pointer-events: auto;
        }
        .contact .login-prompt {
            background: #3638D9;
            color: #fff;
            padding: 1.5rem 3rem;
            border-radius: .75rem; 
            font-size: 1.5rem;
            font-weight: 600;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
            text-align: center;
            transition: transform 0.2s;
        }
        .contact .clickable-overlay:hover .login-prompt {
            transform: scale(1.03);
            background: #2b2eaf; 
        }
    </style>
</head>
<body>
    
<?php include 'header.php'; ?>

<?php
if(isset($message)){
    foreach($message as $msg){
        echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> 
                ' . htmlspecialchars($msg) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
    }
}
?>

<section class="contact">


    <form action="" method="POST" class="<?= $is_logged_in ? '' : 'disabled-form'; ?>">
        
        <?php if (!$is_logged_in): ?>
            <a href="login.php" class="clickable-overlay">
                <div class="login-prompt">
                    Please login to send a message
                </div>
            </a>
        <?php endif; ?>
        
        <input type="text" name="name" class="box" required placeholder="Enter your name">
        <input type="email" name="email" class="box" required placeholder="Enter your email">
        <input type="number" name="number" min="0" class="box" required placeholder="Enter your number">
        <textarea name="msg" class="box" required placeholder="Enter your message" cols="30" rows="10"></textarea>
        <input type="submit" value="send message" class="btn" name="send">
    </form>

</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

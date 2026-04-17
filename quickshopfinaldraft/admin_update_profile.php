<?php
@include 'config.php';
session_start();

$admin_id = $_SESSION['admin_id'] ?? null;

if (!$admin_id) {
    header('location:login.php');
    exit;
}

$message = [];

if (isset($_POST['update_profile'])) {

    $select_admin = $conn->prepare("SELECT password FROM `users` WHERE id = ?");
    $select_admin->execute([$admin_id]);
    $current_pass = $select_admin->fetch(PDO::FETCH_ASSOC)['password'];

    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);

    $update_profile = $conn->prepare("UPDATE `users` SET name = ?, email = ? WHERE id = ?");
    $update_profile->execute([$name, $email, $admin_id]);
    $message[] = 'Profile details updated!';

    $image = $_FILES['image']['name'] ?? '';
    $image = filter_var($image, FILTER_SANITIZE_STRING);
    $image_size = $_FILES['image']['size'] ?? 0;
    $image_tmp_name = $_FILES['image']['tmp_name'] ?? '';
    $image_folder = 'uploaded_img/'.$image;
    $old_image = $_POST['old_image'] ?? '';

    if (!empty($image)) {
        if ($image_size > 2000000) {
            $message[] = 'Image size is too large! Max 2MB allowed.';
        } else {
            $update_image = $conn->prepare("UPDATE `users` SET image = ? WHERE id = ?");
            if ($update_image->execute([$image, $admin_id])) {
                move_uploaded_file($image_tmp_name, $image_folder);
                if (!empty($old_image) && file_exists('uploaded_img/'.$old_image)) {
                    unlink('uploaded_img/'.$old_image);
                }
                $message[] = 'Image updated successfully!';
            }
        }
    }

    $old_pass_input = md5($_POST['update_pass'] ?? '');
    $new_pass = md5($_POST['new_pass'] ?? '');
    $confirm_pass = md5($_POST['confirm_pass'] ?? '');

    if (!empty($_POST['update_pass']) || !empty($_POST['new_pass']) || !empty($_POST['confirm_pass'])) {
        if ($old_pass_input != $current_pass) {
            $message[] = 'Old password not matched!';
        } elseif ($new_pass != $confirm_pass) {
            $message[] = 'Confirm password not matched!';
        } elseif (empty($_POST['new_pass'])) {
            $message[] = 'New password cannot be empty!';
        } else {
            $update_pass_query = $conn->prepare("UPDATE `users` SET password = ? WHERE id = ?");
            $update_pass_query->execute([$confirm_pass, $admin_id]);
            $message[] = 'Password updated successfully!';
        }
    }
}

$select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
$select_profile->execute([$admin_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Update Admin Profile</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
<link rel="stylesheet" href="css/admin_styles.css">

<style>
.profile-container { max-width: 900px; margin: 0 auto; border-radius: 1rem; }
.profile-container form { background-color: #fff; border-radius: 1rem; box-shadow: 0 0 10px rgba(0,0,0,0.1); padding: 2rem; }
.profile-image-box { border-radius: 50%; width: 150px; height: 150px; object-fit: cover; margin-bottom: 1rem; border: 5px solid #0d6efd; }
</style>
</head>
<body>

<div class="main-content">
    <section class="update-profile container-fluid mt-4">
        <h1 class="title text-center pb-3 mb-5 border-bottom">UPDATE ADMIN PROFILE</h1>

        <?php if (!empty($message)) {
            foreach ($message as $msg) {
                echo '<div class="alert alert-info alert-dismissible fade show profile-container" role="alert">
                        <i class="fas fa-info-circle me-2"></i>' . htmlspecialchars($msg) . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>';
            }
        } ?>

        <div class="profile-container bg-white p-4 shadow-lg">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="row g-4">
                    <div class="col-12 text-center mb-4">
                        <img src="uploaded_img/<?= $fetch_profile['image']; ?>" alt="Profile Picture" class="profile-image-box">
                        <input type="hidden" name="old_image" value="<?= $fetch_profile['image']; ?>">
                    </div>

                    <div class="col-md-6">
                        <h4 class="mb-4 text-primary"><i class="fas fa-user-edit me-2"></i> Personal Details</h4>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Username</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($fetch_profile['name']); ?>" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email Address</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($fetch_profile['email']); ?>" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Update Profile Picture</label>
                            <input type="file" name="image" accept="image/jpg, image/jpeg, image/png" class="form-control">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h4 class="mb-4 text-warning"><i class="fas fa-lock me-2"></i> Password Update</h4>
                        <input type="hidden" name="old_pass" value="<?= $fetch_profile['password']; ?>">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Old Password</label>
                            <input type="password" name="update_pass" placeholder="Enter previous password" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">New Password</label>
                            <input type="password" name="new_pass" placeholder="Enter new password" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Confirm New Password</label>
                            <input type="password" name="confirm_pass" placeholder="Confirm new password" class="form-control">
                        </div>
                    </div>

                    <div class="col-12 mt-4">
                        <div class="d-flex justify-content-end gap-3">
                            <a href="admin_page.php" class="btn btn-secondary px-4 py-2">
                                <i class="fas fa-arrow-left me-2"></i> Go Back
                            </a>
                            <button type="submit" class="btn btn-primary px-4 py-2" name="update_profile">
                                <i class="fas fa-user-edit me-2"></i> Update Profile
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

<script src="js/admin_script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

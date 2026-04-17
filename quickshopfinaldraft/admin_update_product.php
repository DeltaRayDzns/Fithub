<?php
@include 'config.php';
session_start();

$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)) {
    header('location:login.php');
    exit;
}

$message = []; // Initialize as an empty array

if (isset($_POST['update_product'])) {
    $pid = $_POST['pid'];
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    // Use FILTER_VALIDATE_FLOAT for cleaner validation, though FILTER_SANITIZE_NUMBER_FLOAT works
    $price = filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION); 
    $category = filter_var($_POST['category'], FILTER_SANITIZE_STRING);
    $details = filter_var($_POST['details'], FILTER_SANITIZE_STRING);

    $image = $_FILES['image']['name'] ?? '';
    $image = filter_var($image, FILTER_SANITIZE_STRING);
    $image_size = $_FILES['image']['size'] ?? 0;
    $image_tmp_name = $_FILES['image']['tmp_name'] ?? '';
    $image_folder = 'uploaded_img/' . $image;
    $old_image = $_POST['old_image'];

    // 1. Update text fields (name, category, details, price)
    $update_product = $conn->prepare("UPDATE `products` SET name = ?, category = ?, details = ?, price = ? WHERE id = ?");
    if ($update_product->execute([$name, $category, $details, $price, $pid])) {
        $message[] = 'Product details updated successfully!';
    } else {
         $message[] = 'Error updating product details.';
    }

    // 2. Handle image update (if a new image was uploaded)
    if (!empty($image)) {
        if ($image_size > 2000000) {
            $message[] = 'Image size is too large! Max 2MB allowed.';
        } else {
            $update_image = $conn->prepare("UPDATE `products` SET image = ? WHERE id = ?");
            if ($update_image->execute([$image, $pid])) {
                move_uploaded_file($image_tmp_name, $image_folder);
                // Only attempt to unlink if the old image name exists and is not the new image name
                if (!empty($old_image) && $old_image != $image && file_exists('uploaded_img/' . $old_image)) {
                     unlink('uploaded_img/' . $old_image);
                }
                $message[] = 'Image updated successfully!';
            } else {
                $message[] = 'Error updating image in database.';
            }
        }
    }
}

$update_id = $_GET['update'] ?? 0;
$select_products = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
$select_products->execute([$update_id]);
$fetch_products = $select_products->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Update Product</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
<link rel="stylesheet" href="css/admin_style.css">

<style>
body { 
    background-color: #f8f9fa; /* Lighter background */
    font-family: 'Inter', sans-serif;
}

.update-form-container {
    max-width: 900px;
    margin: 2rem auto;
    background-color: #fff;
    padding: 2.5rem;
    border-radius: 16px; /* Increased rounding */
    box-shadow: 0 10px 30px rgba(0,0,0,0.1); /* Deeper shadow */
}

h1 {
    color: #4c5270; /* Deep slate color for title */
    font-weight: 700;
}

.product-image-preview {
    width: 100%;
    /* Use aspect ratio for modern, responsive sizing */
    aspect-ratio: 1 / 1; 
    max-height: 350px;
    object-fit: contain; /* Use 'contain' to ensure full image visibility */
    border-radius: 12px;
    border: 2px solid #e9ecef; /* Subtle border */
    padding: 10px;
    background-color: #f4f6f9;
}

.form-label { 
    font-weight: 600; 
    color: #343a40;
}
.form-control, .form-select { 
    border-radius: 10px; 
    font-size: 1rem; 
    padding: 0.75rem 1rem;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);
}

.btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
    transition: all 0.3s ease;
}
.btn-primary:hover {
    background-color: #0b5ed7;
    border-color: #0a58ca;
    transform: translateY(-1px);
}
.btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
}
.btn-secondary:hover {
    background-color: #5c636a;
    border-color: #565e64;
}

.btn { 
    border-radius: 10px; 
    font-size: 1rem;
    padding: 0.6rem 1.5rem;
    font-weight: 600;
}

.btn-container { justify-content: flex-end; gap: 1rem; }

.alert {
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

/* Mobile adjustments */
@media (max-width: 767.98px) {
    .update-form-container {
        padding: 1.5rem;
        margin: 1rem auto;
    }
    .product-image-preview { 
        aspect-ratio: 4/3; 
        max-height: 300px;
    }
    .btn-container { 
        flex-direction: column; 
        align-items: stretch;
    }
    .btn { 
        width: 100%;
    }
}
</style>
</head>
<body>

<?php include 'admin_header.php'; ?>

<section class="container mt-4">

<h1 class="text-center mb-5">Update Product Details</h1>

<?php 
// PHP FIX: Added is_array check to prevent 'foreach() argument must be of type array|object, string given' warning
if (is_array($message) && !empty($message)) {
    foreach ($message as $msg) {
        echo '<div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
                <i class="fas fa-info-circle me-2"></i>' . htmlspecialchars($msg) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
    }
}
?>

<?php if ($fetch_products): ?>
<div class="update-form-container">
<form action="" method="post" enctype="multipart/form-data">
<input type="hidden" name="old_image" value="<?= $fetch_products['image']; ?>">
<input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">

<div class="row g-5">
    <!-- Image & File Upload Column -->
    <div class="col-lg-5 text-center">
        <h5 class="mb-3 text-muted">Current Product Image</h5>
        <img src="uploaded_img/<?= $fetch_products['image']; ?>" onerror="this.onerror=null;this.src='https://placehold.co/400x400/CCCCCC/333333?text=Image+Not+Found';" alt="<?= $fetch_products['name']; ?>" class="product-image-preview">
        
        <div class="mt-4">
            <label for="productImage" class="form-label text-start w-100">Change Image (Max 2MB)</label>
            <input type="file" name="image" id="productImage" class="form-control" accept="image/jpg, image/jpeg, image/png">
        </div>
    </div>

    <!-- Product Details Column -->
    <div class="col-lg-7">
        <h5 class="mb-4 text-muted">Product Information (ID: #<?= $fetch_products['id']; ?>)</h5>
        
        <div class="mb-3">
            <label for="productName" class="form-label">Product Name</label>
            <input type="text" name="name" id="productName" class="form-control" required value="<?= htmlspecialchars($fetch_products['name']); ?>">
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="productPrice" class="form-label">Price (₱)</label>
                <input type="number" name="price" id="productPrice" class="form-control" min="0" step="0.01" required value="<?= htmlspecialchars($fetch_products['price']); ?>">
            </div>

            <div class="col-md-6 mb-3">
                <label for="productCategory" class="form-label">Category</label>
                <select name="category" id="productCategory" class="form-select" required>
                    <?php 
                    // Defined categories
                    $categories = ['vegetables','fruits','meat','fish','grains','dairy'];
                    $current_category = strtolower($fetch_products['category']);
                    
                    // Always show current category selected
                    echo '<option value="'.htmlspecialchars($current_category).'" selected>'.ucfirst($current_category).' (Current)</option>';
                    
                    // Show other options
                    foreach ($categories as $cat) {
                        if ($cat !== $current_category) {
                            echo '<option value="'.$cat.'">'.ucfirst($cat).'</option>';
                        }
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label for="productDetails" class="form-label">Product Details</label>
            <textarea name="details" id="productDetails" class="form-control" rows="5" required><?= htmlspecialchars($fetch_products['details']); ?></textarea>
        </div>
        
        <div class="d-flex btn-container mt-4">
            <a href="admin_products.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Go Back</a>
            <button type="submit" name="update_product" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Product</button>
        </div>
    </div>
</div>
</form>
</div>
<?php else: ?>
<p class="text-center alert alert-danger mt-5">
    <i class="fas fa-exclamation-triangle me-2"></i>No product found with ID: **<?= htmlspecialchars($update_id); ?>**
</p>
<?php endif; ?>

</section>
<script src="js/admin_script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

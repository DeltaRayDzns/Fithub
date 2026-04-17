<?php
@include 'config.php';
session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
   exit;
}

$select_user = $conn->prepare("SELECT name, number, email, address FROM `users` WHERE id = ?");
$select_user->execute([$user_id]);
$fetch_user = $select_user->fetch(PDO::FETCH_ASSOC);

$saved_name = $fetch_user['name'] ?? '';
$saved_number = $fetch_user['number'] ?? '';
$saved_email = $fetch_user['email'] ?? '';

$saved_address = $fetch_user['address'] ?? '';
$address_parts = explode(', ', $saved_address);

$saved_region = $address_parts[0] ?? '';
$saved_province = $address_parts[1] ?? '';
$saved_city_municipality = $address_parts[2] ?? '';
$saved_barangay = $address_parts[3] ?? '';
$saved_street_house = $address_parts[4] ?? '';

$message = [];

$discount_type = $_POST['discount_type'] ?? ($_GET['discount_type'] ?? 'None');


if(isset($_POST['order'])){

    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
    $method = filter_var($_POST['method'], FILTER_SANITIZE_STRING);
    $discount_type = filter_var($_POST['discount_type'], FILTER_SANITIZE_STRING);

    $region = filter_var($_POST['region'], FILTER_SANITIZE_STRING);
    $province = filter_var($_POST['province'], FILTER_SANITIZE_STRING);
    $city_municipality = filter_var($_POST['city_municipality'], FILTER_SANITIZE_STRING);
    $barangay = filter_var($_POST['barangay'], FILTER_SANITIZE_STRING);
    $street_house = filter_var($_POST['street_house'], FILTER_SANITIZE_STRING);

    $address = $region . ', ' . $province . ', ' . $city_municipality . ', ' . $barangay . ', ' . $street_house;

    $cart_original_total = 0;
    $cart_products = [];

    $cart_query = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
    $cart_query->execute([$user_id]);

    $insufficient_stock = false;

    if($cart_query->rowCount() > 0){
        while($cart_item = $cart_query->fetch(PDO::FETCH_ASSOC)){

            $product_check = $conn->prepare("SELECT stock FROM `products` WHERE id = ?");
            $product_check->execute([$cart_item['pid']]);
            $product = $product_check->fetch(PDO::FETCH_ASSOC);

            if(!$product || $product['stock'] < $cart_item['quantity']){
                $message[] = "Not enough stock for " . $cart_item['name'] . ". Available: " . ($product['stock'] ?? 0);
                $insufficient_stock = true;
            }

            $cart_products[] = $cart_item['name'].' ( '.$cart_item['quantity'].' )';
            $sub_total = ($cart_item['price'] * $cart_item['quantity']);
            $cart_original_total += $sub_total;
        }
    }

    $total_products = implode(', ', $cart_products);

    $cart_total_for_db = $cart_original_total;
    
    $image_folder = null;
    if($method == "GCash" && isset($_FILES['image']['name']) && $_FILES['image']['name'] != ''){
       $image_name = time() . '_gcash_' . basename($_FILES['image']['name']); 
       $image_folder = 'gcash/' . $image_name; 
       move_uploaded_file($_FILES['image']['tmp_name'], $image_folder);
    }
    
    $image_folder_id = null;
    $required_id_upload = ($discount_type == "PWD" || $discount_type == "Senior");
    if($required_id_upload && isset($_FILES['id_image']['name']) && $_FILES['id_image']['name'] != ''){
       $image_name_id = time() . '_id_' . basename($_FILES['id_image']['name']); 
       $image_folder_id = 'id_uploads/' . $image_name_id; 
       move_uploaded_file($_FILES['id_image']['tmp_name'], $image_folder_id);
    }


    $placed_on = date('Y-m-d H:i:s');

    if($cart_total_for_db == 0){
        $message[] = 'Your cart is empty';
    }elseif($insufficient_stock){
        $message[] = 'Some items do not have enough stock. Please update your cart.';
    }elseif($required_id_upload && !$image_folder_id){
        $message[] = 'Please upload a valid PWD or Senior ID image to avail the discount.';
    }else{
            
        $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price, placed_on, image, discount_type, id_image_proof) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)");
        $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $cart_total_for_db, $placed_on, $image_folder, $discount_type, $image_folder_id]);

        $cart_query = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
        $cart_query->execute([$user_id]);
        while($cart_item = $cart_query->fetch(PDO::FETCH_ASSOC)){
            $update_stock = $conn->prepare("UPDATE `products` SET stock = stock - ? WHERE id = ?");
            $update_stock->execute([$cart_item['quantity'], $cart_item['pid']]);
        }

        $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
        $delete_cart->execute([$user_id]);

        $message[] = 'Order placed successfully!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .checkout-orders input[readonly],
        .checkout-orders input[readonly]:focus {
            background-color: #f5f5f5 !important;
            color: #777;
            cursor: not-allowed;
            border-color: #ddd;
        }
        .receipt-container {
            padding: 20px;
        }
        .receipt-box {
            border: 1px solid #ddd;
            padding: 20px;
            width: 1000px;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin: 0 auto;
        }
        .receipt-title {
            text-align: center;
            color: #3638D9;
            border-bottom: 2px solid #3638D9;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .receipt-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px dashed #eee;
        }
        .receipt-sub-row {
            padding: 2px 0 2px 20px;
            font-size: 1.2em;
            color: #555;
        }
        .receipt-row .item-name {
            font-weight: bold;
            flex-basis: 50%;
        }
        .receipt-row .item-qty {
            flex-basis: 10%;
        }
        .receipt-row .item-price, .receipt-row .item-total {
            flex-basis: 20%;
            text-align: right;
        }
        .receipt-row-total {
            border-bottom: 2px solid #ddd;
            padding: 5px 0 10px 0;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
        }
        .receipt-footer {
            padding: 20px 0 0;
            margin-top: 15px;
        }
        .summary-line {
            text-align: right;
        }
        .summary-line strong {
            font-weight: 500;
            font-size: 0.9em;
        }
        .summary-line span {
            font-size: 1.1em;
            text-align: center;
        }
        .summary-line.border-top {
            border-top: 1px solid #ddd !important;
            padding-top: 1rem;
            padding-bottom: 0.5rem;
        }
        .summary-line.border-top-dashed {
            color: #e74c3c;
            border-top: 1px dashed #ccc;
            margin-top: 5px;
            padding-top: 0.75rem;
            padding-bottom: 0.5rem;
        }
        .summary-line.border-top-dashed strong {
            color: #e74c3c;
        }
        .summary-grand-total {
            border-top: 3px double #3638D9;
            border-bottom: 3px double #3638D9;
            font-size: 1.4em;
            background: #f0f8ff;
            border-radius: 4px;
            text-align: right;
            padding: 1rem;
            margin-top: 1rem;
        }
        .summary-grand-total strong {
            color: #3638D9;
            font-size: 1em;
        }
        .summary-grand-total span {
            color: #3638D9;
            font-size: 1.2em;
            text-align: center;
        }
        
        .gcash-qr-container {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-top: 15px;
            border: 2px dashed #1B80CC;
        }
        .gcash-qr-container h4 {
            color: #1B80CC;
            margin-bottom: 15px;
            font-weight: bold;
        }
        .gcash-qr-container img {
            max-width: 300px;
            width: 100%;
            height: auto;
            border: 3px solid #1B80CC;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .gcash-qr-container p {
            margin-top: 15px;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    
<?php include 'header.php'; ?>

<?php
if (isset($message) && is_array($message)) {
    foreach ($message as $msg) {
        echo '<script>
            Swal.fire({
                title: "' . (strpos($msg, 'successfully') !== false ? 'Success!' : 'Error!') . '",
                text: "' . htmlspecialchars($msg) . '",
                icon: "' . (strpos($msg, 'successfully') !== false ? 'success' : 'error') . '",
                confirmButtonColor: "' . (strpos($msg, 'successfully') !== false ? '#1B80CC' : '#e74c3c') . '",
                customClass: {
                    popup: "rounded-4 shadow-lg",
                    title: "fw-bold"
                }
            });
        </script>';
    }
}
?>

<section class="display-orders receipt-container">
    <div class="receipt-box">
        <h3 class="receipt-title">Order Summary</h3>
        <div class="receipt-items">
            <?php
                $cart_grand_total = 0;
                $vat_total_sum = 0;
                $vat_exclusive_total_sum = 0;
                $discount_total = 0;
                $is_discount_avail = ($discount_type == 'PWD' || $discount_type == 'Senior');

                $select_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
                $select_cart_items->execute([$user_id]);
                if($select_cart_items->rowCount() > 0){
                   while($fetch_cart_items = $select_cart_items->fetch(PDO::FETCH_ASSOC)){
                      $price_with_vat = $fetch_cart_items['price'];
                      $quantity = $fetch_cart_items['quantity'];
                      
                      $vat_exclusive_price = $price_with_vat / 1.12;
                      $vat_amount = $price_with_vat - $vat_exclusive_price;
                      
                      $total_original_price = $price_with_vat * $quantity;
                      $total_vat_exclusive_item = $vat_exclusive_price * $quantity;
                      $total_vat_item = $vat_amount * $quantity;

                      $item_discount_amount = 0;
                      if ($is_discount_avail) {
                          $item_discount_amount = $total_original_price * 0.20;
                      }
                      
                      $total_item_price = $total_original_price - $item_discount_amount;


                      $cart_grand_total += $total_item_price;
                      $vat_exclusive_total_sum += $total_vat_exclusive_item;
                      $vat_total_sum += $total_vat_item;
                      $discount_total += $item_discount_amount;
            ?>
           <div class="receipt-row">
                <span class="item-name"><?= $fetch_cart_items['name']; ?></span>
                <span class="item-qty">x<?= $quantity; ?></span>
                <span class="item-price">₱<?= number_format($price_with_vat, 2); ?></span>
                <span class="item-total">₱<?= number_format($total_original_price, 2); ?></span>
            </div>
            <div class="receipt-row receipt-sub-row">
                <span class="item-name" style="font-weight: normal;">Price (VAT Ex.):</span>
                <span class="item-total">₱<?= number_format($total_vat_exclusive_item, 2); ?></span>
            </div>
            <div class="receipt-row receipt-sub-row">
                <span class="item-name" style="font-weight: normal;">VAT (12%):</span>
                <span class="item-total">₱<?= number_format($total_vat_item, 2); ?></span>
            </div>
            <?php if ($is_discount_avail): ?>
            <div class="receipt-row receipt-sub-row" style="color: red; font-weight: bold; border-bottom: 1px dashed #ccc; padding-bottom: 5px;">
                <span class="item-name" style="font-weight: bold; flex-basis: 80%;"><?= $discount_type; ?> Discount (20% on Total):</span>
                <span class="item-total" style="flex-basis: 20%; text-align: right;">-₱<?= number_format($item_discount_amount, 2); ?></span>
            </div>
            <div class="receipt-row-total">
                <span class="item-name" style="padding-left: 20px;">Final Item Total:</span>
                <span class="item-total">₱<?= number_format($total_item_price, 2); ?></span>
            </div>
            <?php endif; ?>
            <?php
                }
                }else{
                    echo '<p class="empty" style="text-align: center; padding: 20px; color: #777;">Your cart is empty!</p>';
                }
                
                $vat_exempt_total = $vat_exclusive_total_sum;
            ?>
        </div>
<div class="receipt-footer">
             
             <div class="summary-line border-top">
                 <strong class="text-muted d-block mb-1">Subtotal (VAT Exclusive):</strong> 
                 <span class="fw-bold d-block">₱<?= number_format($vat_exclusive_total_sum, 2); ?></span>
             </div>
             
             <div class="summary-line py-2">
                 <strong class="text-muted d-block mb-1">VAT (12%):</strong> 
                 <span class="fw-bold d-block">₱<?= number_format($vat_total_sum, 2); ?></span>
             </div>
             
             <?php if ($is_discount_avail && $discount_total > 0): ?>
             <div class="summary-line border-top-dashed">
                 <strong class="d-block mb-1">Total <?= $discount_type; ?> Discount (20%):</strong> 
                 <span class="fw-bolder d-block">-₱<?= number_format($discount_total, 2); ?></span>
             </div>
             <?php endif; ?>

             <div class="summary-grand-total">
                 <strong class="d-block mb-1">FINAL GRAND TOTAL:</strong> 
                 <span class="fw-bolder d-block">₱<?= number_format($cart_grand_total, 2); ?></span>
             </div>
          </div>
    </div>
</section>


<section class="checkout-orders">
    <form action="" method="POST" id="checkout-form" enctype="multipart/form-data">

        <h3>Place your order</h3>

        <div class="flex">
            <div class="inputBox">
                <span>Your name :<span style="color: red;">*</span></span>
                <input type="text" name="name" placeholder="Enter your name" class="box" required
                        value="<?= htmlspecialchars($saved_name); ?>" readonly>
            </div>
            <div class="inputBox">
                <span>Your number :<span style="color: red;">*</span></span>
                <input type="number" name="number" placeholder="Enter your number" class="box" required
                        value="<?= htmlspecialchars($saved_number); ?>" readonly>
            </div>
            <div class="inputBox">
                <span>Your email :<span style="color: red;">*</span></span>
                <input type="email" name="email" placeholder="Enter your email" class="box" required
                        value="<?= htmlspecialchars($saved_email); ?>" readonly>
            </div>
            
            <div class="inputBox">
                <span>Region:<span style="color: red;">*</span></span>
                <input type="text" name="region" placeholder="Enter your region" class="box" required
                        value="<?= htmlspecialchars($saved_region); ?>" readonly>
            </div>
            <div class="inputBox">
                <span>Province:<span style="color: red;">*</span></span>
                <input type="text" name="province" placeholder="Enter your province" class="box" required
                        value="<?= htmlspecialchars($saved_province); ?>" readonly>
            </div>
            <div class="inputBox">
                <span>City/Municipality:<span style="color: red;">*</span></span>
                <input type="text" name="city_municipality" placeholder="Enter your city/municipality" class="box" required
                        value="<?= htmlspecialchars($saved_city_municipality); ?>" readonly>
            </div>
            <div class="inputBox">
                <span>Barangay:<span style="color: red;">*</span></span>
                <input type="text" name="barangay" placeholder="Enter your barangay" class="box" required
                        value="<?= htmlspecialchars($saved_barangay); ?>" readonly>
            </div>
            <div class="inputBox">
                <span>Street/House No.:<span style="color: red;">*</span></span>
                <input type="text" name="street_house" placeholder="Enter your street/house no." class="box" required
                        value="<?= htmlspecialchars($saved_street_house); ?>" readonly>
            </div>
            
            <div class="inputBox">
                <span>Payment method :<span style="color: red;">*</span></span>
                <select name="method" id="payment-method" class="box" required>
                    <option value="cash on delivery">Cash On Delivery</option>
                    <option value="GCash">GCash</option>
                </select>
            </div>

            <div class="inputBox" id="gcash-qr-display" style="display:none; grid-column: 1 / -1;">
                <div class="gcash-qr-container">
                    <h4><i class="fas fa-qrcode"></i> Scan to Pay via GCash</h4>
                    <img src="images/gcashqrimage.jpg" alt="GCash QR Code">
                    <p><i class="fas fa-info-circle"></i> Scan this QR code using your GCash app, then upload the screenshot with reference number below.</p>
                </div>
            </div>
            
            <div class="inputBox" id="gcash-upload" style="display:none;">
                <span>GCash Screenshot (Reference Number):<span style="color: red;">*</span></span>
                <input type="file" name="image" class="box" accept="image/jpg, image/jpeg, image/png"> 
            </div>
            
            <div class="inputBox">
                <span>Avail Discount :<span style="color: red;">*</span></span>
                <select name="discount_type" id="discount-type" class="box" required>
                    <option value="None" <?= ($discount_type == 'None') ? 'selected' : ''; ?>>None</option>
                    <option value="PWD" <?= ($discount_type == 'PWD') ? 'selected' : ''; ?>>PWD Discount</option>
                    <option value="Senior" <?= ($discount_type == 'Senior') ? 'selected' : ''; ?>>Senior Citizen Discount</option>
                </select>
            </div>
            <div class="inputBox" id="id-upload" style="display:none;">
                <span>Upload PWD/Senior ID Proof:<span style="color: red;">*</span></span>
                <input type="file" name="id_image" class="box" accept="image/jpg, image/jpeg, image/png">
            </div>
        </div>

        <input type="submit" name="order" class="btn <?= ($cart_grand_total > 0)?'':'disabled'; ?>" value="Place Order">

    </form>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function(){
    
    const gcashInput = $("#gcash-upload input[name='image']");
    const idInput = $("#id-upload input[name='id_image']");
    const form = $("#checkout-form");

    function handleConditionalFields() {
        if($("#payment-method").val() === "GCash"){
            $("#gcash-qr-display").slideDown(); // Show QR code
            $("#gcash-upload").slideDown();
            gcashInput.prop("required", true);
        } else {
            $("#gcash-qr-display").slideUp(); // Hide QR code
            $("#gcash-upload").slideUp();
            gcashInput.prop("required", false);
            gcashInput.val(''); 
        }
        
        const selectedDiscount = $("#discount-type").val();
        if(selectedDiscount === "PWD" || selectedDiscount === "Senior"){
            $("#id-upload").slideDown();

            idInput.prop("required", true);
        } else {
            $("#id-upload").slideUp();
            idInput.prop("required", false);
            idInput.val(''); 
        }
    }
    
    handleConditionalFields();

    $("#payment-method").change(handleConditionalFields);
    

    $("#discount-type").change(function(){
        const selectedDiscount = $(this).val();

        handleConditionalFields();

        $.ajax({
            url: 'calculate_discount.php',
            type: 'POST',
            data: { discount_type: selectedDiscount },
            dataType: 'json',
            success: function(response) {
                if (response.error) {
                    console.error(response.error);
                    return;
                }

                $(".summary-line.border-top span").text('₱' + response.vat_exclusive_total_sum);
                $(".summary-line.py-2 span").text('₱' + response.vat_total_sum);

                if (selectedDiscount === "PWD" || selectedDiscount === "Senior") {
                    if ($(".receipt-items .receipt-row-total").length > 0) {
                    }

                    if ($(".summary-line.border-top-dashed").length === 0) {
                        $(".summary-grand-total").before(`
                            <div class="summary-line border-top-dashed">
                                <strong class="d-block mb-1">Total ${selectedDiscount} Discount (20%):</strong> 
                                <span class="fw-bolder d-block">-₱${response.discount_total}</span>
                            </div>
                        `);
                    } else {
                        $(".summary-line.border-top-dashed strong").text(`Total ${selectedDiscount} Discount (20%):`);
                        $(".summary-line.border-top-dashed span").text('-₱' + response.discount_total);
                    }
                } else {


                    $(".summary-line.border-top-dashed").remove();
                }

                $(".summary-grand-total span").text('₱' + response.cart_grand_total);
                

            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", error);
            }
        });
    });



    form.on('submit', function(e) {
        const submitButton = $("input[name='order']");

        if (submitButton.hasClass('disabled')) {
            e.preventDefault();
            return;
        }

        e.preventDefault(); 


        const currentTotalText = $(".summary-grand-total span").text().replace('₱', '').trim();

        Swal.fire({
            title: 'Confirm Your Order',
            text: `Are you sure you want to place this order? The total amount is ${$(".summary-grand-total span").text()}`,
            icon: 'question',
            showCancelButton: true,
            background: '#fff0fa',
            color: '#5a2d82',
            confirmButtonColor: '#1B80CC',
            cancelButtonColor: '#e74c3c',
            confirmButtonText: 'Yes, place my order',
            cancelButtonText: 'Cancel',
            customClass: {
                popup: 'rounded-4 shadow-lg',
                title: 'fw-bold',
                confirmButton: 'px-4 py-2 rounded-3',
                cancelButton: 'px-4 py-2 rounded-3'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'order',
                    value: 'Place Order' 
                }).appendTo(form);

                form.off('submit').submit(); 
            }
        });
    });
});
</script>

</body>
</html>
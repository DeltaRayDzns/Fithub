<?php
@include 'config.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$discount_type = $_POST['discount_type'] ?? 'None';

$cart_grand_total = 0;
$vat_total_sum = 0;
$vat_exclusive_total_sum = 0;
$discount_total = 0;
$is_discount_avail = ($discount_type == 'PWD' || $discount_type == 'Senior');

$select_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
$select_cart_items->execute([$user_id]);

if ($select_cart_items->rowCount() > 0) {
    while ($fetch_cart_items = $select_cart_items->fetch(PDO::FETCH_ASSOC)) {
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
    }
}

$response = [
    'vat_exclusive_total_sum' => number_format($vat_exclusive_total_sum, 2),
    'vat_total_sum' => number_format($vat_total_sum, 2),
    'discount_total' => number_format($discount_total, 2),
    'cart_grand_total' => number_format($cart_grand_total, 2),
    'discount_type' => $discount_type,
];

header('Content-Type: application/json');
echo json_encode($response);

<?php
session_start();
require 'db.php';

$orc_id = $_SESSION['ORCID_Id'];
$genome_id = $_POST['Genome_Id'] ?? null;

if (!$genome_id) {
    header('Location: cart.php');
    exit;
}

// First check available quantity from SUPPLIES table
$available_sql = "SELECT su.Number_of_Genomes as available_quantity 
                  FROM SUPPLIES su 
                  WHERE su.Genome_Id = ?";
$available_stmt = $conn->prepare($available_sql);
$available_stmt->bind_param("s", $genome_id);
$available_stmt->execute();
$available_result = $available_stmt->get_result();

if ($available_result->num_rows === 0) {
    // Item not found in supplies
    $_SESSION['quantity_message'] = "Item not available.";
    $_SESSION['quantity_message_type'] = 'error';
    header('Location: cart.php');
    exit;
}

$available_data = $available_result->fetch_assoc();
$available_quantity = (int)$available_data['available_quantity'];

// Check if item is already in cart and get current cart quantity
$check_sql = "SELECT Number_of_Genome FROM SHOPPING_CART WHERE ORCID_Id = ? AND Genome_Id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ss", $orc_id, $genome_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

$current_cart_quantity = 0;
if ($check_result->num_rows > 0) {
    $cart_data = $check_result->fetch_assoc();
    $current_cart_quantity = (int)$cart_data['Number_of_Genome'];
}

// Check if we can add more (cart quantity < available quantity)
if ($current_cart_quantity < $available_quantity) {
    if ($current_cart_quantity > 0) {
        // Item exists in cart, update quantity
        $update_sql = "UPDATE SHOPPING_CART SET Number_of_Genome = Number_of_Genome + 1 
                      WHERE ORCID_Id = ? AND Genome_Id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ss", $orc_id, $genome_id);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
        // Item not in cart, insert new record
        $insert_sql = "INSERT INTO SHOPPING_CART (ORCID_Id, Genome_Id, Number_of_Genome) 
                       VALUES (?, ?, 1)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ss", $orc_id, $genome_id);
        $insert_stmt->execute();
        $insert_stmt->close();
    }
    
    // Decrement available quantity in SUPPLIES
    $decrement_sql = "UPDATE SUPPLIES SET Number_of_Genomes = Number_of_Genomes - 1 WHERE Genome_Id = ?";
    $decrement_stmt = $conn->prepare($decrement_sql);
    $decrement_stmt->bind_param("s", $genome_id);
    $decrement_stmt->execute();
    $decrement_stmt->close();
    
} else {
    // Cannot add more - maximum quantity reached
    $_SESSION['quantity_message'] = "Cannot add more items. Maximum available quantity reached.";
    $_SESSION['quantity_message_type'] = 'error';
}

$check_stmt->close();
$available_stmt->close();
$conn->close();

header('Location: cart.php');
exit;
?>

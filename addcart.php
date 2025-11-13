<?php
session_start();
require 'db.php';

if (!isset($_SESSION['ORCID_Id'])) {
    echo '<!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv="refresh" content="3;url=login.html">
        <title>Login Failed</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; margin-top: 50px; background-color: #00aee7; }
            .message { color: black; font-size: 1.5em; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="message">You must be logged-in to add items to your cart. Redirecting to login page...</div>
    </body>
    </html>';
    exit;
}

$orc_id = $_SESSION['ORCID_Id'];
$genome_id = $_POST['Genome_Id'] ?? null;

if (!$genome_id) {
    die("Invalid request: Genome ID missing.");
}

$check_sql = "SELECT Number_of_Genome FROM SHOPPING_CART WHERE ORCID_Id = ? AND Genome_Id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ss", $orc_id, $genome_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    $update_sql = "UPDATE SHOPPING_CART SET Number_of_Genome = Number_of_Genome + 1 
                   WHERE ORCID_Id = ? AND Genome_Id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ss", $orc_id, $genome_id);
    $update_stmt->execute();
    $update_stmt->close();

} else {

    $insert_sql = "INSERT INTO SHOPPING_CART (ORCID_Id, Genome_Id, Number_of_Genome) VALUES (?, ?, 1)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("ss", $orc_id, $genome_id);
    $insert_stmt->execute();
    $insert_stmt->close();
}

$check_stmt->close();

$decrement_sql = "UPDATE SUPPLIES SET Number_of_Genomes = Number_of_Genomes - 1 WHERE Genome_Id = ?";
$decrement_stmt = $conn->prepare($decrement_sql);
$decrement_stmt->bind_param("s", $genome_id);
$decrement_stmt->execute();
$decrement_stmt->close();

$conn->close();

echo '<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="refresh" content="3;url=cart.php">
    <title>Added to cart!</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin-top: 50px; background-color: #00aee7; }
        .message { color: black; font-size: 1.5em; font-weight: bold; }
    </style>
</head>
<body>
    <div class="message">Item added to cart! Redirecting to shopping cart page...</div>
</body>
</html>';
exit;
?>

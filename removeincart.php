<?php
session_start();
require 'db.php';

$orc_id = $_SESSION['ORCID_Id'];
$genome_id = $_POST['Genome_Id'];

// Check current quantity in cart
$stmt = $conn->prepare("SELECT Number_of_Genome FROM SHOPPING_CART 
                        WHERE ORCID_Id = ? AND Genome_Id = ?");
$stmt->bind_param("ss", $orc_id, $genome_id); // Changed "si" to "ss" since Genome_Id is string
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Item not in cart, redirect
    header("Location: cart.php");
    exit;
}

$row = $result->fetch_assoc();
$qty = (int)$row['Number_of_Genome'];

if ($qty > 1) {
    // Decrease quantity by 1
    $stmt = $conn->prepare("UPDATE SHOPPING_CART 
                            SET Number_of_Genome = Number_of_Genome - 1 
                            WHERE ORCID_Id = ? AND Genome_Id = ?");
    $stmt->bind_param("ss", $orc_id, $genome_id); // Changed "si" to "ss"
    $stmt->execute();
} else {
    // Remove item completely
    $stmt = $conn->prepare("DELETE FROM SHOPPING_CART 
                            WHERE ORCID_Id = ? AND Genome_Id = ?");
    $stmt->bind_param("ss", $orc_id, $genome_id); // Changed "si" to "ss"
    $stmt->execute();
}

// Increment available quantity in SUPPLIES
$stmt = $conn->prepare("UPDATE SUPPLIES 
                        SET Number_of_Genomes = Number_of_Genomes + 1 
                        WHERE Genome_Id = ?");
$stmt->bind_param("s", $genome_id); // Changed "i" to "s" since Genome_Id is string
$stmt->execute();

$conn->close();

header("Location: cart.php");
exit;
?>

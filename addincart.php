<?php
session_start();
require 'db.php';


$orc_id = $_SESSION['ORCID_Id'];
$genome_id = $_POST['Genome_Id'] ?? null;

$check_sql = "SELECT Number_of_Genome FROM SHOPPING_CART WHERE ORCID_Id = ? AND Genome_Id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ss", $orc_id, $genome_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();


$update_sql = "UPDATE SHOPPING_CART SET Number_of_Genome = Number_of_Genome + 1 
                WHERE ORCID_Id = ? AND Genome_Id = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("ss", $orc_id, $genome_id);
$update_stmt->execute();
$update_stmt->close();

$check_stmt->close();

$decrement_sql = "UPDATE SUPPLIES SET Number_of_Genomes = Number_of_Genomes - 1 WHERE Genome_Id = ?";
$decrement_stmt = $conn->prepare($decrement_sql);
$decrement_stmt->bind_param("s", $genome_id);
$decrement_stmt->execute();
$decrement_stmt->close();

$conn->close();

header('Location: cart.php');
exit;
?>

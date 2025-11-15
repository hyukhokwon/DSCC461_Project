<?php
session_start();
require 'db.php';

$orc_id = $_SESSION['ORCID_Id'];
$genome_id = $_POST['Genome_Id'];

$stmt = $conn->prepare("SELECT Number_of_Genome FROM SHOPPING_CART 
                        WHERE ORCID_Id = ? AND Genome_Id = ?");

$stmt->bind_param("si", $orc_id, $genome_id);
$stmt->execute();
$result = $stmt->get_result();

$row = $result->fetch_assoc();

$qty = $row['Number_of_Genome'];

if ($qty>1) {
    $stmt = $conn->prepare("UPDATE SHOPPING_CART 
                            SET Number_of_Genome = Number_of_Genome - 1
                            WHERE ORCID_Id = ? AND Genome_Id = ?");

    $stmt->bind_param("si", $orc_id, $genome_id);
    $stmt->execute();
} elseif ($qty === 1) {

    $stmt = $conn->prepare("DELETE FROM SHOPPING_CART 
                            WHERE ORCID_Id = ? AND Genome_Id = ?");

    $stmt->bind_param("si", $orc_id, $genome_id);
    $stmt->execute();
}


$stmt = $conn->prepare("UPDATE SUPPLIES 
                        SET Number_of_Genomes = Number_of_Genomes + 1 
                        WHERE Genome_Id = ?");

$stmt->bind_param("i", $genome_id);
$stmt->execute();

header("Location: cart.php");
exit;
?>

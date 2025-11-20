<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_name = trim($_POST['company_name']);
    $genome_id = intval($_POST['genome_id']);
    $number_of_genomes = intval($_POST['number_of_genomes']);
    
    // Validate inputs
    if (empty($company_name) || $genome_id <= 0 || $number_of_genomes <= 0) {
        die("Invalid input data. Please check your entries.");
    }
    
    // First, check if the company exists in the SELLER table (this is the master list of valid companies)
    $check_company_sql = "SELECT Company_Name FROM SELLER WHERE Company_Name = ?";
    $check_company_stmt = $conn->prepare($check_company_sql);
    $check_company_stmt->bind_param("s", $company_name);
    $check_company_stmt->execute();
    $company_result = $check_company_stmt->get_result();
    
    if ($company_result->num_rows === 0) {
        // Company doesn't exist in SELLER table
        header("Location: index.php?message=company_not_found");
        exit();
    }
    
    // Company exists in SELLER table, now check if the specific company+genome combination exists in SUPPLIES
    $check_sql = "SELECT * FROM SUPPLIES WHERE Company_Name = ? AND Genome_Id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $company_name, $genome_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing entry in SUPPLIES
        $update_sql = "UPDATE SUPPLIES SET Number_of_Genomes = Number_of_Genomes + ? WHERE Company_Name = ? AND Genome_Id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("isi", $number_of_genomes, $company_name, $genome_id);
        
        if ($update_stmt->execute()) {
            header("Location: index.php?message=supply_updated");
        } else {
            echo "Error updating supply: " . $conn->error;
        }
        $update_stmt->close();
    } else {
        // Insert new entry into SUPPLIES for existing company
        $insert_sql = "INSERT INTO SUPPLIES (Company_Name, Genome_Id, Number_of_Genomes) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("sii", $company_name, $genome_id, $number_of_genomes);
        
        if ($insert_stmt->execute()) {
            header("Location: index.php?message=supply_added");
        } else {
            echo "Error adding supply: " . $conn->error;
        }
        $insert_stmt->close();
    }
    
    $check_company_stmt->close();
    $check_stmt->close();
    $conn->close();
} else {
    header("Location: sell_form.php");
}
?>

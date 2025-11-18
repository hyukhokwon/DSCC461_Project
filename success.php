<?php
session_start();
require 'db.php';

$orc_id = $_SESSION['ORCID_Id'] ?? null;


 if ($orc_id) {
     $sql = "DELETE FROM SHOPPING_CART WHERE ORCID_Id = ?";
     $stmt = $conn->prepare($sql);
     $stmt->bind_param("s", $orc_id);
     $stmt->execute();
    
     unset($_SESSION['cart_items']);
 }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Success!</title>
    <link rel="stylesheet" href="stylesheet.css">

    <!-- Redirect after 3 seconds -->
    <meta http-equiv="refresh" content="3;url=index.php">
    
</head>
<body class="landing-body"> 

    <div class="success-container">
        <h1 class="success-message">Success!</h1>
        <p style="color: black; font-size: 1.2em;">
            Your payment is complete. Redirecting you to the home page...
        </p>
    </div>

</body>
</html>

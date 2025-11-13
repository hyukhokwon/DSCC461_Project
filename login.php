<?php
session_start();
require 'db.php';

$orc_id = $_POST['ORCID_Id'] ?? '';
$password = $_POST['Password'] ?? '';

$sql = "SELECT * FROM USERS WHERE ORCID_Id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $orc_id);
$stmt->execute();
$result = $stmt->get_result();
$valid_login = true;


if ($user = $result->fetch_assoc()) {
    if (password_verify($password, $user['Password'])) {
        $_SESSION['ORCID_Id'] = $user['ORCID_Id'];
        $_SESSION['First_Name'] = $user['First_Name'];
        $_SESSION['Last_Name'] = $user['Last_Name'];
        header("Location: index.php");
        exit;
    } else {
        $valid_login = false;
    }
} else {
    $valid_login = false;
}

if (!$valid_login) {

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
        <div class="message">Incorrect login details. Redirecting to login page...</div>
    </body>
    </html>';
    exit;
}
<?php
session_start();
require 'db.php'; 

$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$orc_id = trim($_POST['orc_id'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$qualification = trim($_POST['qualification'] ?? '');
$affiliation = trim($_POST['affiliation'] ?? '');

$errors = [];

if (!$first_name || !preg_match("/^[a-zA-Z-' ]+$/", $first_name)) {
    $errors[] = "First name must only contain letters, spaces, hyphens, or apostrophes.";
}
if (!$last_name || !preg_match("/^[a-zA-Z-' ]+$/", $last_name)) {
    $errors[] = "Last name must only contain letters, spaces, hyphens, or apostrophes.";
}

if (!$orc_id || !preg_match("/^\d{4}-\d{4}-\d{4}-\d{4}$/", $orc_id)) {
    $errors[] = "ORC ID must be in the format 0000-0000-0000-0000.";
}

if (strlen($password) < 6) {
    $errors[] = "Password must be at least 6 characters long.";
}

if ($password !== $confirm_password) { 
    $errors[] = "Passwords do not match.";
}

if (!$qualification) { 
    $errors[] = "Qualification is required.";
}

if (!$affiliation) { 
    $errors[] = "Affiliation is required.";
}

if (!empty($errors)) {
    foreach ($errors as $error) {
        echo "<!DOCTYPE html>
        <html>
        <head>
            <meta http-equiv='refresh' content='3;url=signup.html'>
            <title>Signup Failed</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; margin-top: 50px; background-color: #00aee7; }
                .message { color: black; font-size: 1.5em; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='message'> $error Redirecting to signup page...</div>
        </body>
        </html>";
        exit;
    }
}

$check_sql = "SELECT 1 FROM USERS WHERE ORCID_Id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("s", $orc_id);
$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows > 0) {
            echo "<!DOCTYPE html>
                <html>
                <head>
                    <meta http-equiv='refresh' content='3;url=signup.html'>
                    <title>Signup Failed</title>
                    <style>
                        body { font-family: Arial, sans-serif; text-align: center; margin-top: 50px; background-color: #00aee7; }
                        .message { color: black; font-size: 1.5em; font-weight: bold; }
                    </style>
                </head>
                <body>
                    <div class='message'> ORC ID already exists. Redirecting to signup page...</div>
                </body>
                </html>";
    $check_stmt->close();
    exit;
}
$check_stmt->close();

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO USERS (ORCID_Id, First_Name, Last_Name, Degree, Affiliation, Password)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param(
    "ssssss",
    $orc_id,
    $first_name,
    $last_name,
    $qualification,
    $affiliation,
    $hashed_password
);

if ($stmt->execute()) {
    echo "<!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv='refresh' content='3;url=login.html'>
        <title>Sign Up Successful!</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; margin-top: 50px; background-color: #00aee7; }
            .message { color: black; font-size: 1.5em; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='message'> Sign-up successful! Redirecting to login page...</div>
    </body>
    </html>";
}


$stmt->close();
$conn->close();

?>

<?php
$host = "localhost";
$user = "frohan";
$pass = "PP6JeWpS";
$db   = "hkwon17_1";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
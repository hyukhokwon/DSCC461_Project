<?php
require 'db.php';

// Set proper character encoding
header('Content-Type: text/html; charset=utf-8');
mysqli_set_charset($conn, "utf8");

$company_name = $_GET['company'] ?? '';

if (empty($company_name)) {
    echo '<p class="no-ratings">No seller specified</p>';
    exit;
}

// Get the ratings
$sql = "SELECT Comments FROM RATING WHERE Company_Name = ? ORDER BY Rating_Id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $company_name);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($rating = $result->fetch_assoc()) {
        $comment = $rating['Comments'];
        // Don't trim - display exactly as in database
        echo '<div class="rating-item">';
        echo '<div class="rating-comment">' . htmlspecialchars($comment, ENT_QUOTES, 'UTF-8') . '</div>';
        echo '</div>';
    }
} else {
    echo '<p class="no-ratings">No ratings found for this seller</p>';
}

$stmt->close();
$conn->close();
?>

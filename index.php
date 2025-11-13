<?php
session_start();
$logged_in = false;
$user_name = '';

if (isset($_SESSION['First_Name'])) {
    $logged_in = true;
    $user_name = $_SESSION['First_Name'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>G-Mart - Genome Marketplace</title>
    <link rel="stylesheet" href="stylesheet.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>

    <div class="nav">
        <?php if ($logged_in): ?>
            <p style="font-weight: bold;">Hi, <?php echo htmlspecialchars($user_name); ?>!</p>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="signup.html">Sign Up</a> or <a href="login.html">Log-In</a>
        <?php endif; ?>
    </div>

    <div class="center-container">
        <div class="logo"><i class="fa-sharp fa-solid fa-dna"></i> G-Mart</div>
        
        <form class="search-form" action="listings.php" method="get">
            <input type="text" name="query" placeholder="Search by assembly name...">
            <button type="submit"><i class="fa-sharp fa-solid fa-magnifying-glass"></i></button>
        </form>
    </div>

</body>
</html>
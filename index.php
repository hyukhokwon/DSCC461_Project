<?php
session_start();
$logged_in = false;
$user_name = '';
$user_role = '';

if (isset($_SESSION['First_Name'])) {
    $logged_in = true;
    $user_name = $_SESSION['First_Name'];
    $user_role = $_SESSION['role'] ?? '';
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
            <!-- Role display - automatically determined from database -->
            <?php if ($user_role === 'admin'): ?>
                <p style="margin-top: 5px;">
                    <a href="admin_dashboard.php" style="color: #ffeb3b; text-decoration: underline; font-weight: bold;">
                        <i class="fa-sharp fa-solid fa-user-shield"></i> Admin Dashboard
                    </a>
                </p>
            <?php elseif (!empty($user_role)): ?>
                <p style="margin-top: 5px; color: #ccc; font-size: 0.9em;">
                    Role: <?php echo htmlspecialchars($user_role); ?>
                </p>
            <?php endif; ?>
            
            <!-- NEW: Shopping Cart Link for Logged-in Users -->
            <p style="margin-top: 10px;">
                <a href="cart.php" style="color: white; text-decoration: underline; font-weight: bold;">
                    <i class="fa-sharp fa-solid fa-cart-shopping"></i> View Shopping Cart
                </a>
            </p>
            
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

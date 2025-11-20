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

// Check for success messages
$message = '';
if (isset($_GET['message'])) {
    if ($_GET['message'] === 'supply_added') {
        $message = 'Your genomes have been successfully listed on the marketplace!';
    } elseif ($_GET['message'] === 'supply_updated') {
        $message = 'Your supply has been successfully updated!';
    } elseif ($_GET['message'] === 'company_not_found') {
        $message = 'Error: Company name not found in our system. Please check your company name.';
    }
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

            <!-- Shopping Cart Link -->
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
        <?php if ($message): ?>
            <div class="success-message" style="background-color: <?php echo strpos($message, 'Error') !== false ? '#ff4444' : '#4CAF50'; ?>; color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="logo"><i class="fa-sharp fa-solid fa-dna"></i> G-Mart</div>

        <!-- SELL ON MARKETPLACE BUTTON - Available to everyone -->
        <div style="margin: 20px 0;">
            <a href="sell_form.php" style="display: inline-block; background-color: #4CAF50; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 1.2em; border: 3px solid black;">
                <i class="fa-sharp fa-solid fa-store"></i> Sell On Marketplace
            </a>
        </div>

        <form class="search-form" action="listings.php" method="get">
            <input type="text" name="query" placeholder="Search by assembly name...">
            <button type="submit"><i class="fa-sharp fa-solid fa-magnifying-glass"></i></button>
        </form>
    </div>

</body>
</html>

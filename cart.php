<?php
session_start();
require 'db.php';

const TAX = 0.08;
$logged_in = false;


$orc_id = $_SESSION['ORCID_Id'] ?? null;
if (!$orc_id) {
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
        <div class="message">You must be logged-in to view shopping cart. Redirecting to login page...</div>
    </body>
    </html>';
    exit;
} else {
    $user_name = '';
    $logged_in = true;
    $user_name = $_SESSION['First_Name'];
}

$sql = "SELECT s.Genome_Id, s.Number_of_Genome, a.Assembly_Name, a.Price
        FROM SHOPPING_CART s
        JOIN ASSEMBLY_STATISTICS a ON s.Genome_Id = a.Genome_Id
        WHERE s.ORCID_Id = ?";


$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $orc_id);
$stmt->execute();
$result = $stmt->get_result();

$_SESSION['cart_items'] = [];
while ($row = $result->fetch_assoc()) {
    $_SESSION['cart_items'][] = $row;
}

$cart_items = $_SESSION['cart_items'] 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopping Cart - G-Mart</title>
    <link rel="stylesheet" href="stylesheet.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>

    <header class="shopping-header">
        <a href="index.php" class="header-logo">
            <span> <i class="fa-sharp fa-solid fa-dna"></i> </span>
            <span> G-Mart </span>
        </a>

        <div class='user-info'>
            Hi, <?php echo htmlspecialchars($user_name); ?>!
            <br>
            <br>
            <a href="logout.php" style="color:black; text-decoration: underline;">Logout</a>
        </div>
    </header>


    <div class="cart-container">
        
        <h2 class="cart-title"><i class="fa-sharp fa-solid fa-cart-shopping"> </i> Shopping Cart</h2>

        <?php if (!empty($cart_items)): ?>
            
            <table class="cart-table">
                <thead>
                    <tr>
                        <th class="col-remove"></th>
                        <th class="col-qty">Qty.</th>
                        <th class="col-item">Item</th>
                        <th class="col-price">Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $subtotal = 0; 
                    foreach ($cart_items as $item):
                        $item_total = $item['Price'] * $item['Number_of_Genome'];
                        $subtotal += $item_total;
                    ?>
                    <tr>
                        <td>
                            <a href="" class="col-remove">Remove</a>
                        </td>
                        <td class="col-qty">
                            <span class="qty-box"><?php echo (int)$item['Number_of_Genome']; ?></span>
                        </td>
                        <td class="col-item"><?php echo htmlspecialchars($item['Assembly_Name']); ?></td>
                        <td class="col-price">$<?php echo number_format($item['Price']*$item['Number_of_Genome'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php
            $tax = $subtotal * TAX;
            $grand_total = $subtotal + $tax;
            ?>

            <div class="totals-section">
                <div>Total: <span class="total-value">$<?php echo number_format($subtotal, 2); ?></span></div>
                <div>Tax: <span class="total-value">$<?php echo number_format($tax, 2); ?></span></div>
                <div class="final-total">
                    Total after-tax: <span class="total-value">$<?php echo number_format($grand_total, 2); ?></span>
                </div>
            </div>

            <form action="checkout.php" method="post">
                <button type="submit" class='buttons'>CHECKOUT</button>
            </form>

        <?php else: ?>
            <p> Your shopping cart is empty.</p>
        <?php endif; ?>

    </div> 

</body>
</html>
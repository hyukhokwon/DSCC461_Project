<?php
session_start();
require 'db.php';

const TAX = 0.08;
$logged_in = false;

$orc_id = $_SESSION['ORCID_Id'] ?? null;
if (!$orc_id) {
    header("refresh:3;url=login.php");
    die("You must be logged in to view the cart.");
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
    <title>Checkout - G-Mart</title>
    <link rel="stylesheet" href="stylesheet.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>

    <header class="shopping-header">
        <a href="index.php" class="header-logo">
            <span><i class="fa-sharp fa-solid fa-dna"></i></span>
            <span>G-Mart</span>
        </a>

        <div class='user-info'>
            Hi, <?php echo htmlspecialchars($user_name); ?>!
            <br>
            <br>
            <a href="logout.php" style="color:black; text-decoration: underline;">Logout</a>
        </div>
    </header>

    <div class="cart-container">
        
        <h2 class="cart-title">Checkout</h2>

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
                            <form action="removecart.php" method="POST" style="display:inline;">
                                <input type="hidden" name="Genome_Id" value="<?php echo htmlspecialchars($item['Genome_Id']); ?>">
                                <button type="submit" class="button-remove">Remove</button>
                            </form>
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

            <form action="success.php" method="post">
                <button type="submit" class='buttons'>PAY</button>
            </form>

        <?php else: ?>
            <p>Your shopping cart is empty. Unable to proceed to checkout</p>
        <?php endif; ?>

    </div> 

</body>
</html>
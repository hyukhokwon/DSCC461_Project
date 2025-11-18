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

// Get cart items with available quantity information
$sql = "SELECT 
            s.Genome_Id, 
            s.Number_of_Genome, 
            a.Assembly_Name, 
            a.Price,
            su.Number_of_Genomes as Available_Quantity
        FROM SHOPPING_CART s
        JOIN ASSEMBLY_STATISTICS a ON s.Genome_Id = a.Genome_Id
        JOIN SUPPLIES su ON s.Genome_Id = su.Genome_Id
        WHERE s.ORCID_Id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $orc_id);
$stmt->execute();
$result = $stmt->get_result();

$_SESSION['cart_items'] = [];
$cart_items = [];

while ($row = $result->fetch_assoc()) {
    $_SESSION['cart_items'][] = $row;
    $cart_items[] = $row;
}

// Check for quantity limit messages
$quantity_message = $_SESSION['quantity_message'] ?? '';
$quantity_message_type = $_SESSION['quantity_message_type'] ?? '';
unset($_SESSION['quantity_message']);
unset($_SESSION['quantity_message_type']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopping Cart - G-Mart</title>
    <link rel="stylesheet" href="stylesheet.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        .quantity-warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-weight: bold;
        }
        
        .quantity-error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-weight: bold;
        }
        
        .max-quantity {
            color: #e74c3c;
            font-size: 0.8em;
            margin-top: 5px;
            font-weight: bold;
        }
        
        .available-quantity {
            color: #27ae60;
            font-size: 0.8em;
            margin-top: 5px;
        }
        
        .qty-btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        
        .qty-btn:disabled:hover {
            background-color: #ccc;
        }
        
        .stock-info {
            font-size: 0.8em;
            color: #666;
            margin-top: 5px;
        }
    </style>
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

        <?php if ($quantity_message): ?>
            <div class="<?php echo $quantity_message_type === 'error' ? 'quantity-error' : 'quantity-warning'; ?>">
                <?php echo htmlspecialchars($quantity_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($cart_items)): ?>

            <table class="cart-table">
                <thead>
                    <tr>
                        <th class="col-remove"></th>
                        <th class="col-qty">Qty.</th>
                        <th>ID</th>
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
                        $available_quantity = (int)$item['Available_Quantity'];
                        $cart_quantity = (int)$item['Number_of_Genome'];
                        $can_increase = $cart_quantity < $available_quantity;
                    ?>
                        <tr>
                            <td>
                                <form action="removecart.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="Genome_Id" value="<?php echo htmlspecialchars($item['Genome_Id']); ?>">
                                    <button type="submit" class="button-remove">Remove</button>
                                </form>
                            </td>
                            <td class="qty-controls">
                                <form action="removeincart.php" method="POST" class="qty-form">
                                    <input type="hidden" name="Genome_Id" value="<?= $item['Genome_Id'] ?>">
                                    <button type="submit" class="qty-btn">−</button>
                                </form>

                                <span class="qty-box"><?= $item['Number_of_Genome']; ?></span>

                                <form action="addincart.php" method="POST" class="qty-form">
                                    <input type="hidden" name="Genome_Id" value="<?= $item['Genome_Id'] ?>">
                                    <button type="submit" class="qty-btn" <?= !$can_increase ? 'disabled' : '' ?>>
                                        +
                                    </button>
                                </form>
                                
                                <div class="stock-info">
                                    Available: <?= $available_quantity ?>
                                    <?php if (!$can_increase): ?>
                                        <div class="max-quantity">Maximum quantity reached</div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class='col-id'><?php echo htmlspecialchars($item['Genome_Id']); ?></td>
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

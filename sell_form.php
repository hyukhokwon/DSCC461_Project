<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell on Marketplace - G-Mart</title>
    <link rel="stylesheet" href="stylesheet.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>

    <div class="nav">
        <a href="index.php">Back to Home</a>
        <?php if (isset($_SESSION['First_Name'])): ?>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="signup.html">Sign Up</a> or <a href="login.html">Log-In</a>
        <?php endif; ?>
    </div>

    <div class="center-container">
        <div class="logo"><i class="fa-sharp fa-solid fa-dna"></i> Sell on G-Mart</div>
        
        <form class="sell-form" action="process_sell.php" method="post">
            <div class="form-group">
                <label for="company_name">Company Name:</label>
                <input type="text" id="company_name" name="company_name" required placeholder="Enter your registered company name">
                <small style="color: #666; font-size: 0.9em;">Must be an existing company in our system</small>
            </div>
            
            <div class="form-group">
                <label for="genome_id">Genome ID:</label>
                <input type="number" id="genome_id" name="genome_id" required placeholder="Enter genome ID" min="1">
            </div>
            
            <div class="form-group">
                <label for="number_of_genomes">Number of Genomes:</label>
                <input type="number" id="number_of_genomes" name="number_of_genomes" required placeholder="Enter quantity" min="1">
            </div>
            
            <button type="submit" class="submit-btn">
                <i class="fa-sharp fa-solid fa-upload"></i> List on Marketplace
            </button>
        </form>
    </div>

</body>
</html>

<?php
session_start();

require 'db.php';

$search_query = $_GET['query'] ?? ''; 

if (empty($search_query)) {
    header("Location: index.php");
    exit;
}

$sql = "SELECT 
            a.Genome_Id, a.Assembly_Name, a.Level, a.Chromosomes, 
            a.Scaffolds, a.Contigs, a.GC_Percent, a.Release_Date, a.Price,
            su.Company_Name, su.Number_of_Genomes
        FROM ASSEMBLY_STATISTICS a
        JOIN SUPPLIES su ON a.Genome_Id = su.Genome_Id
        JOIN SELLER se ON su.Company_Name = se.Company_Name";

$limit = 100;

if (isset($_GET['page'])) {
    $page = (int)$_GET['page'];
} else {
    $page = 1;
}
$offset = ($page-1)*$limit;


if (!empty($search_query)) {
    $sql .= " WHERE (Assembly_Name LIKE ?)";
} 

$search_term = '%' . $search_query . '%'; 
$sql .=  " LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param('sii', $search_term, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Genome Listings - G-Mart</title>
    <link rel="stylesheet" href="stylesheet.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

</head>
<body>



    <header class="listings-header">
        <a href="index.php" class="header-logo">
            <i class="fa-sharp fa-solid fa-dna"></i>
            <span class="logo-text">G-Mart</span>
        </a>
        <form action="listings.php" method="get" class="search-form">
            <input type="text" name="query" placeholder="Search by assembly name..." value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit"><i class="fa-sharp fa-solid fa-magnifying-glass"></i></button>
        </form>
        <div class="header-cart-icon">
            <a href="cart.php"><i class="fa-sharp fa-solid fa-cart-shopping"></i></a>
        </div>
    </header>

    <div class="listings-container">

        <?php if ($result->num_rows > 0): ?>
            <?php while ($genome = $result->fetch_assoc()): ?>
            <div class="listing-item">

                <h2>Genome ID: <?php echo htmlspecialchars($genome['Genome_Id']); ?></h2>
                
                <div class="genome-details">
                    <div>
                        <span>Assembly Name:</span> 
                        <?php echo htmlspecialchars($genome['Assembly_Name']); ?>
                    </div>

                    <div>
                        <span>Assembly Level:</span> 
                        <?php echo htmlspecialchars($genome['Level']); ?>
                    </div>
                    
                    <div>
                        <span>Chromosomes:</span> 
                        <?php echo $genome['Chromosomes']; ?>
                    </div>

                    <div>
                        <span>Scaffolds:</span> 
                        <?php echo $genome['Scaffolds']; ?>
                    </div>

                    <div>
                        <span>Contigs:</span> 
                        <?php echo $genome['Contigs']; ?>
                    </div>
                    
                    <div>
                        <span>GC Percent:</span> 
                        <?php  echo $genome['GC_Percent'] . '%'; ?>
                    </div>

                    <div>
                        <span>Assembly release date:</span> 
                        <?php echo $genome['Release_Date']; ?>
                    </div>

                    <div>
                        <span>Seller:</span> 
                        <strong><?php echo htmlspecialchars($genome['Company_Name']); ?></strong>
                    </div>

                    <div>
                        <span>Quantity Available:</span> 
                        <?php echo (int)$genome['Number_of_Genomes']; ?>
                    </div>
                </div>
                
                <div class="listing-actions">

                    <div class="price">
                        $<?php echo number_format($genome['Price'], 2); ?>
                    </div>

                    
                    <form action="addcart.php" method="POST" style="display:inline;">
                        <input type="hidden" name="Genome_Id" value="<?php echo htmlspecialchars($genome['Genome_Id']); ?>">
                        <button type="submit" class="buttons">Add to cart</button>
                    </form>

                    <a class='seller-ratings'>
                        View Seller Ratings
                    </a>

                </div>
            </div>

            <?php endwhile; ?>
        <?php else: ?>
            <p>
                No genomes found
                <?php
                    if (!empty($search_query)) {
                        echo ' matching "' . htmlspecialchars($search_query) . '"';
                    }?>.
            </p>              
        <?php endif; ?>

        <div style="display: flex; justify-content: space-between; margin-top: 30px;">

            <?php if ($page > 1): ?>
                <a href="listings.php?query=<?php echo urlencode($search_query); ?>&page=<?php echo $page - 1; ?>">
                    ← Previous
                </a>
            <?php else: ?>
                <span></span>
            <?php endif; ?>

            <?php if ($result->num_rows === $limit): ?>
                <a href="listings.php?query=<?php echo urlencode($search_query); ?>&page=<?php echo $page + 1; ?>">
                    Next →
                </a>
            <?php endif; ?>

        </div>

    </div>

</body>
</html>
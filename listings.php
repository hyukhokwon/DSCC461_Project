<?php
session_start();

require 'db.php';

$search_query = $_GET['query'] ?? '';
$level_filter = $_GET['level'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$min_gc = $_GET['min_gc'] ?? '';
$max_gc = $_GET['max_gc'] ?? '';
$seller_filter = $_GET['seller'] ?? '';

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
        JOIN SELLER se ON su.Company_Name = se.Company_Name
        WHERE 1=1";

$params = [];
$types = '';

if (!empty($search_query)) {
    $sql .= " AND (a.Assembly_Name LIKE ?)";
    $params[] = '%' . $search_query . '%';
    $types .= 's';
}

if (!empty($level_filter)) {
    $sql .= " AND a.Level = ?";
    $params[] = $level_filter;
    $types .= 's';
}

if (!empty($min_price) && is_numeric($min_price)) {
    $sql .= " AND a.Price >= ?";
    $params[] = $min_price;
    $types .= 'd';
}

if (!empty($max_price) && is_numeric($max_price)) {
    $sql .= " AND a.Price <= ?";
    $params[] = $max_price;
    $types .= 'd';
}

if (!empty($min_gc) && is_numeric($min_gc)) {
    $sql .= " AND a.GC_Percent >= ?";
    $params[] = $min_gc;
    $types .= 'd';
}

if (!empty($max_gc) && is_numeric($max_gc)) {
    $sql .= " AND a.GC_Percent <= ?";
    $params[] = $max_gc;
    $types .= 'd';
}

if (!empty($seller_filter)) {
    $sql .= " AND su.Company_Name = ?";
    $params[] = $seller_filter;
    $types .= 's';
}

$limit = 100;

if (isset($_GET['page'])) {
    $page = (int)$_GET['page'];
} else {
    $page = 1;
}
$offset = ($page-1)*$limit;

$sql .= " LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("SQL Error: " . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param('ii', $limit, $offset);
}

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
    <style>
        /* Modal styles for seller ratings */
        .ratings-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 12px;
            width: 80%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .close-modal {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close-modal:hover {
            color: black;
        }

        .rating-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
            margin-bottom: 10px;
        }

        .rating-comment {
            font-style: italic;
            color: #555;
        }

        .no-ratings {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
        }

        /* Out of stock styles */
        .out-of-stock {
            background-color: #ffebee;
            border: 2px solid #f44336;
        }

        .stock-warning {
            color: #d32f2f;
            font-weight: bold;
            margin: 10px 0;
            padding: 8px;
            background-color: #ffcdd2;
            border-radius: 4px;
            text-align: center;
        }

        .disabled-button {
            background-color: #9e9e9e !important;
            cursor: not-allowed !important;
            opacity: 0.6;
        }

        .disabled-button:hover {
            background-color: #9e9e9e !important;
        }

        .quantity-low {
            color: #ff9800;
            font-weight: bold;
        }

        .quantity-zero {
            color: #f44336;
            font-weight: bold;
        }
    </style>
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

    <div class="filters-container">
        <form method="get" class="filter-form">
            <input type="hidden" name="query" value="<?php echo htmlspecialchars($search_query); ?>">

            <div class="filter-group">
                <label>Assembly Level:</label>
                <select name="level">
                    <option value="">All Levels</option>
                    <option value="Chromosome" <?php echo $level_filter=='Chromosome'?'selected':''; ?>>Chromosome</option>
                    <option value="Scaffold" <?php echo $level_filter=='Scaffold'?'selected':''; ?>>Scaffold</option>
                    <option value="Contig" <?php echo $level_filter=='Contig'?'selected':''; ?>>Contig</option>
                    <option value="Complete" <?php echo $level_filter=='Complete'?'selected':''; ?>>Complete</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Price Range:</label>
                <input type="number" name="min_price" placeholder="Min $" value="<?php echo htmlspecialchars($min_price); ?>" step="0.01" min="0">
                <input type="number" name="max_price" placeholder="Max $" value="<?php echo htmlspecialchars($max_price); ?>" step="0.01" min="0">
            </div>

            <div class="filter-group">
                <label>GC % Range:</label>
                <input type="number" name="min_gc" placeholder="Min %" value="<?php echo htmlspecialchars($min_gc); ?>" min="0" max="100">
                <input type="number" name="max_gc" placeholder="Max %" value="<?php echo htmlspecialchars($max_gc); ?>" min="0" max="100">
            </div>

            <button type="submit" class="filter-button">Apply Filters</button>
            <a href="listings.php?query=<?php echo urlencode($search_query); ?>" class="clear-filters">Clear Filters</a>
        </form>
    </div>

    <div class="listings-container">

        <?php if ($result->num_rows > 0): ?>
            <?php while ($genome = $result->fetch_assoc()): ?>
            <?php 
            $quantity_available = (int)$genome['Number_of_Genomes'];
            $is_out_of_stock = $quantity_available === 0;
            $is_low_stock = $quantity_available > 0 && $quantity_available <= 5;
            ?>
            
            <div class="listing-item <?php echo $is_out_of_stock ? 'out-of-stock' : ''; ?>">

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
                        <span class="<?php echo $is_out_of_stock ? 'quantity-zero' : ($is_low_stock ? 'quantity-low' : ''); ?>">
                            <?php echo $quantity_available; ?>
                            <?php if ($is_out_of_stock): ?>
                                (Out of Stock)
                            <?php elseif ($is_low_stock): ?>
                                (Low Stock)
                            <?php endif; ?>
                        </span>
                    </div>
                </div>

                <div class="listing-actions">

                    <div class="price">
                        $<?php echo number_format($genome['Price'], 2); ?>
                    </div>

                    <?php if ($is_out_of_stock): ?>
                        <!-- Out of Stock - Show disabled button and message -->
                        <div class="stock-warning">
                            <i class="fa-solid fa-triangle-exclamation"></i> Not enough quantity
                        </div>
                        <button type="button" class="buttons disabled-button" disabled>
                            Out of Stock
                        </button>
                    <?php else: ?>
                        <!-- In Stock - Show normal add to cart button -->
                        <form action="addcart.php" method="POST" style="display:inline;">
                            <input type="hidden" name="Genome_Id" value="<?php echo htmlspecialchars($genome['Genome_Id']); ?>">
                            <button type="submit" class="buttons">Add to cart</button>
                        </form>
                    <?php endif; ?>

                    <!-- MODIFIED: Clickable Seller Ratings -->
                    <a href="#" class="seller-ratings" onclick="showSellerRatings('<?php echo htmlspecialchars($genome['Company_Name']); ?>')">
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
                <a href="listings.php?query=<?php echo urlencode($search_query); ?>&level=<?php echo urlencode($level_filter); ?>&min_price=<?php echo urlencode($min_price); ?>&max_price=<?php echo urlencode($max_price); ?>&min_gc=<?php echo urlencode($min_gc); ?>&max_gc=<?php echo urlencode($max_gc); ?>&page=<?php echo $page - 1; ?>">
                    ← Previous
                </a>
            <?php else: ?>
                <span></span>
            <?php endif; ?>

            <?php if ($result->num_rows === $limit): ?>
                <a href="listings.php?query=<?php echo urlencode($search_query); ?>&level=<?php echo urlencode($level_filter); ?>&min_price=<?php echo urlencode($min_price); ?>&max_price=<?php echo urlencode($max_price); ?>&min_gc=<?php echo urlencode($min_gc); ?>&max_gc=<?php echo urlencode($max_gc); ?>&page=<?php echo $page + 1; ?>">
                    Next →
                </a>
            <?php endif; ?>

        </div>

    </div>

    <!-- Seller Ratings Modal -->
    <div id="ratingsModal" class="ratings-modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeSellerRatings()">&times;</span>
            <h3 id="modalSellerName"></h3>
            <div id="ratingsList"></div>
        </div>
    </div>

    <script>
        function showSellerRatings(companyName) {
            // Show loading
            document.getElementById('modalSellerName').textContent = 'Ratings for ' + companyName;
            document.getElementById('ratingsList').innerHTML = '<p>Loading ratings...</p>';
            document.getElementById('ratingsModal').style.display = 'block';

            // Fetch ratings via AJAX
            fetch('get_seller_ratings.php?company=' + encodeURIComponent(companyName))
                .then(response => response.text())
                .then(data => {
                    document.getElementById('ratingsList').innerHTML = data;
                })
                .catch(error => {
                    document.getElementById('ratingsList').innerHTML = '<p class="no-ratings">Error loading ratings</p>';
                });
        }

        function closeSellerRatings() {
            document.getElementById('ratingsModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('ratingsModal');
            if (event.target == modal) {
                closeSellerRatings();
            }
        }

        // Prevent form submission for out of stock items
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.disabled-button').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    alert('Not enough quantity - This item is out of stock.');
                });
            });
        });
    </script>

</body>
</html>

<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['First_Name']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: index.php");
    exit;
}

require 'db.php';

// TEMPORARY DEBUG - Check database connection and tables
echo "<!-- Debug: Database connected successfully -->";
$table_check = $conn->query("SHOW TABLES");
$table_count = $table_check->num_rows;
echo "<!-- Debug: Found $table_count tables in database -->";

// Function to get first 5 rows from any table
function getTablePreview($conn, $tableName) {
    echo "<!-- Debug: Processing table: $tableName -->";
    $sql = "SELECT * FROM $tableName LIMIT 5";
    $result = $conn->query($sql);
    
    // Debug query result
    if ($result === false) {
        echo "<!-- Debug: Query failed for $tableName: " . $conn->error . " -->";
    } else {
        echo "<!-- Debug: Query successful for $tableName, rows: " . $result->num_rows . " -->";
    }
    
    $data = [
        'columns' => [],
        'rows' => []
    ];
    
    if ($result && $result->num_rows > 0) {
        // Get column names
        while ($field = $result->fetch_field()) {
            $data['columns'][] = $field->name;
        }
        
        // Reset pointer and get rows
        $result->data_seek(0);
        while ($row = $result->fetch_assoc()) {
            $data['rows'][] = $row;
        }
    }
    
    return $data;
}

// Get preview data for all tables
$tables = ['ASSEMBLY_STATISTICS', 'GENOME_TYPE', 'RATING', 'SELLER', 'SHOPPING_CART', 'SUPPLIES', 'USERS'];
$tableData = [];

foreach ($tables as $table) {
    $tableData[$table] = getTablePreview($conn, $table);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - G-Mart</title>
    <link rel="stylesheet" href="stylesheet.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }

        .admin-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid white;
        }

        .admin-title {
            font-size: 2.5em;
            color: white;
            font-weight: bold;
        }

        .back-link {
            color: white;
            text-decoration: underline;
            font-size: 1.1em;
        }

        .table-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .table-title {
            font-size: 1.5em;
            color: #00aee7;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #00aee7;
            font-weight: bold;
        }

        .preview-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9em;
        }

        .preview-table th {
            background: #00aee7;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }

        .preview-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #ddd;
            color: black;
        }

        .preview-table tr:nth-child(even) {
            background: #f8f9fa;
        }

        .no-data {
            color: #666;
            font-style: italic;
            text-align: center;
            padding: 20px;
        }

        .row-count {
            color: #00aee7;
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <div class="admin-container">
        <div class="admin-header">
            <div class="admin-title">
                <i class="fa-sharp fa-solid fa-user-shield"></i> Admin Dashboard
            </div>
            <a href="index.php" class="back-link">← Back to Home</a>
        </div>

        <p style="color: white; margin-bottom: 20px;">
            Welcome, <?php echo htmlspecialchars($_SESSION['First_Name']); ?>!
            Below are previews of all database tables.
        </p>

        <?php foreach ($tableData as $tableName => $data): ?>
        <div class="table-section">
            <div class="table-title">
                <?php echo htmlspecialchars($tableName); ?>
                <span class="row-count">(Showing <?php echo count($data['rows']); ?> rows)</span>
            </div>

            <?php if (!empty($data['rows'])): ?>
                <table class="preview-table">
                    <thead>
                        <tr>
                            <?php foreach ($data['columns'] as $column): ?>
                                <th><?php echo htmlspecialchars($column); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['rows'] as $row): ?>
                            <tr>
                                <?php foreach ($row as $value): ?>
                                    <td><?php echo htmlspecialchars($value ?? 'NULL'); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-data">No data found in this table</p>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

</body>
</html>

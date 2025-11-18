<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['First_Name']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: index.php");
    exit;
}

require 'db.php';

// Initialize variables
$message = '';
$message_type = ''; // 'success' or 'error'
$selected_table = $_POST['table'] ?? 'USERS';
$action = $_POST['action'] ?? 'view';

// Get all table names
$tables_result = $conn->query("SHOW TABLES");
$tables = [];
while ($row = $tables_result->fetch_array()) {
    $tables[] = $row[0];
}

// Process form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $table = $_POST['table'];
        $action = $_POST['action'];
        
        switch ($action) {
            case 'add':
                $columns = [];
                $values = [];
                $placeholders = [];
                
                foreach ($_POST as $key => $value) {
                    if (strpos($key, 'field_') === 0 && !empty($value)) {
                        $column = substr($key, 6); // Remove 'field_' prefix
                        $columns[] = $column;
                        $values[] = $value;
                        $placeholders[] = '?';
                    }
                }
                
                if (!empty($columns)) {
                    $sql = "INSERT INTO $table (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param(str_repeat('s', count($values)), ...$values);
                    $stmt->execute();
                    $message = "Record added successfully!";
                    $message_type = 'success';
                }
                break;
                
            case 'update':
                $updates = [];
                $values = [];
                $where_conditions = [];
                $where_values = [];
                
                foreach ($_POST as $key => $value) {
                    if (strpos($key, 'field_') === 0) {
                        $column = substr($key, 6);
                        // Check if this is a where condition (primary key field)
                        if (isset($_POST['where_field']) && in_array($column, $_POST['where_field'])) {
                            $where_conditions[] = "$column = ?";
                            $where_values[] = $value;
                        } else {
                            $updates[] = "$column = ?";
                            $values[] = $value;
                        }
                    }
                }
                
                if (!empty($updates) && !empty($where_conditions)) {
                    $sql = "UPDATE $table SET " . implode(', ', $updates) . " WHERE " . implode(' AND ', $where_conditions);
                    $stmt = $conn->prepare($sql);
                    $all_values = array_merge($values, $where_values);
                    $stmt->bind_param(str_repeat('s', count($all_values)), ...$all_values);
                    $stmt->execute();
                    $message = "Record updated successfully!";
                    $message_type = 'success';
                }
                break;
                
            case 'delete':
                $where_conditions = [];
                $values = [];
                
                foreach ($_POST as $key => $value) {
                    if (strpos($key, 'field_') === 0 && !empty($value)) {
                        $column = substr($key, 6);
                        $where_conditions[] = "$column = ?";
                        $values[] = $value;
                    }
                }
                
                if (!empty($where_conditions)) {
                    $sql = "DELETE FROM $table WHERE " . implode(' AND ', $where_conditions);
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param(str_repeat('s', count($values)), ...$values);
                    $stmt->execute();
                    $message = "Record deleted successfully!";
                    $message_type = 'success';
                }
                break;
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = 'error';
    }
}

// Get table structure and sample data
function getTableInfo($conn, $tableName) {
    $info = [
        'columns' => [],
        'sample_data' => []
    ];
    
    // Get column information
    $result = $conn->query("DESCRIBE $tableName");
    while ($row = $result->fetch_assoc()) {
        $info['columns'][] = $row;
    }
    
    // Get sample data (first 10 rows)
    $result = $conn->query("SELECT * FROM $tableName LIMIT 10");
    while ($row = $result->fetch_assoc()) {
        $info['sample_data'][] = $row;
    }
    
    return $info;
}

$table_info = getTableInfo($conn, $selected_table);
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
            justify-content: space-between;
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

        .crud-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .section-title {
            font-size: 1.5em;
            color: #00aee7;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #00aee7;
            font-weight: bold;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            align-items: end;
        }

        .form-field {
            flex: 1;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333; /* Fixed: Dark color for better readability */
        }

        input[type="text"], input[type="number"], input[type="email"], input[type="password"], select {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            color: #333; /* Fixed: Dark text color */
            background: white; /* Fixed: Ensure white background */
        }

        input:focus, select:focus {
            outline: none;
            border-color: #00aee7;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
            margin-right: 10px;
            color: white; /* Fixed: Ensure button text is visible */
        }

        .btn-primary {
            background: #00aee7;
        }

        .btn-primary:hover {
            background: #008cba;
        }

        .btn-danger {
            background: #e74c3c;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        .btn-success {
            background: #27ae60;
        }

        .btn-success:hover {
            background: #219a52;
        }

        .btn-warning {
            background: #f39c12;
            color: white;
        }

        .btn-warning:hover {
            background: #e67e22;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .message {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: bold;
            color: #333; /* Fixed: Dark text color */
        }

        .message.success {
            background: #e8f5e8;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }

        .message.error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }

        .sample-data {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            color: #333; /* Fixed: Dark text color */
        }

        .sample-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9em;
            margin-top: 10px;
            color: #333; /* Fixed: Dark text color */
        }

        .sample-table th {
            background: #00aee7;
            color: white;
            padding: 10px;
            text-align: left;
        }

        .sample-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #ddd;
            color: #333; /* Fixed: Dark text color */
            background: white;
        }

        .sample-table tr:hover td {
            background: #e3f2fd;
            cursor: pointer;
        }

        .field-note {
            font-size: 0.8em;
            color: #666;
            margin-top: 3px;
        }

        .where-checkbox {
            margin-top: 5px;
        }

        .where-label {
            font-size: 0.8em;
            color: #e74c3c;
        }

        .action-tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 2px solid #00aee7;
        }

        .action-tab {
            padding: 12px 24px;
            background: #f8f9fa;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-weight: bold;
            color: #666;
            transition: all 0.3s;
        }

        .action-tab.active {
            background: white;
            color: #00aee7;
            border-bottom-color: #00aee7;
        }

        .action-tab:hover {
            color: #00aee7;
            background: #e3f2fd;
        }

        .action-form {
            display: none;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .action-form.active {
            display: block;
        }

        .form-description {
            color: #666;
            margin-bottom: 20px;
            font-style: italic;
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
            Use the form below to manage database records.
        </p>

        <!-- CRUD Operations Section -->
        <div class="crud-section">
            <div class="section-title">
                <i class="fa-solid fa-database"></i> Database Management
            </div>

            <?php if ($message): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Table Selection -->
            <div class="form-group">
                <label for="table" style="color: #333;">Select Table:</label>
                <form method="POST" id="tableForm" style="display: inline;">
                    <select name="table" id="table" onchange="document.getElementById('tableForm').submit()" style="color: #333;">
                        <?php foreach ($tables as $table): ?>
                            <option value="<?php echo $table; ?>" <?php echo $selected_table === $table ? 'selected' : ''; ?>>
                                <?php echo $table; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                </form>
            </div>

            <!-- Action Tabs -->
            <div class="action-tabs">
                <button class="action-tab <?php echo $action === 'view' ? 'active' : ''; ?>" onclick="showAction('view')">
                    <i class="fa-solid fa-eye"></i> View Data
                </button>
                <button class="action-tab <?php echo $action === 'add' ? 'active' : ''; ?>" onclick="showAction('add')">
                    <i class="fa-solid fa-plus"></i> Add Record
                </button>
                <button class="action-tab <?php echo $action === 'update' ? 'active' : ''; ?>" onclick="showAction('update')">
                    <i class="fa-solid fa-pen-to-square"></i> Update Record
                </button>
                <button class="action-tab <?php echo $action === 'delete' ? 'active' : ''; ?>" onclick="showAction('delete')">
                    <i class="fa-solid fa-trash"></i> Delete Record
                </button>
            </div>

            <!-- View Data Form -->
            <div id="viewForm" class="action-form <?php echo $action === 'view' ? 'active' : ''; ?>">
                <h3 style="color: #333;">Viewing Data from <?php echo $selected_table; ?></h3>
                <p class="form-description">Click on any row in the table below to auto-fill the forms for update or delete operations.</p>
                
                <!-- Sample Data Preview -->
                <div class="sample-data">
                    <h4>Current Data in <?php echo $selected_table; ?>:</h4>
                    <?php if (!empty($table_info['sample_data'])): ?>
                        <table class="sample-table">
                            <thead>
                                <tr>
                                    <?php foreach ($table_info['columns'] as $column): ?>
                                        <th><?php echo $column['Field']; ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($table_info['sample_data'] as $row): ?>
                                    <tr>
                                        <?php foreach ($row as $value): ?>
                                            <td><?php echo htmlspecialchars($value ?? 'NULL'); ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <p style="font-size: 0.9em; color: #666; margin-top: 10px;">
                            Showing first 10 records. Click on any row to use its data in other forms.
                        </p>
                    <?php else: ?>
                        <p style="color: #666;">No data found in this table.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Add Record Form -->
            <form method="POST" id="addForm" class="action-form <?php echo $action === 'add' ? 'active' : ''; ?>">
                <input type="hidden" name="table" value="<?php echo $selected_table; ?>">
                <input type="hidden" name="action" value="add">
                
                <h3 style="color: #333;">Add New Record to <?php echo $selected_table; ?></h3>
                <p class="form-description">Fill in the fields below to add a new record. Required fields are marked with additional information.</p>
                
                <?php foreach ($table_info['columns'] as $column): ?>
                    <div class="form-row">
                        <div class="form-field">
                            <label for="add_field_<?php echo $column['Field']; ?>">
                                <?php echo $column['Field']; ?>
                                <span class="field-note">
                                    (<?php echo $column['Type']; ?> 
                                    <?php echo $column['Null'] === 'NO' ? ', REQUIRED' : ''; ?>
                                    <?php echo $column['Key'] === 'PRI' ? ', PRIMARY KEY' : ''; ?>)
                                </span>
                            </label>
                            <input type="text" 
                                   id="add_field_<?php echo $column['Field']; ?>" 
                                   name="field_<?php echo $column['Field']; ?>" 
                                   placeholder="Enter <?php echo strtolower($column['Field']); ?>"
                                   <?php if ($column['Null'] === 'NO'): ?>required<?php endif; ?>>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="action-buttons">
                    <button type="submit" class="btn btn-success">
                        <i class="fa-solid fa-plus"></i> Add Record
                    </button>
                    <button type="button" class="btn btn-warning" onclick="clearForm('addForm')">
                        <i class="fa-solid fa-eraser"></i> Clear Form
                    </button>
                </div>
            </form>

            <!-- Update Record Form -->
            <form method="POST" id="updateForm" class="action-form <?php echo $action === 'update' ? 'active' : ''; ?>">
                <input type="hidden" name="table" value="<?php echo $selected_table; ?>">
                <input type="hidden" name="action" value="update">
                
                <h3 style="color: #333;">Update Record in <?php echo $selected_table; ?></h3>
                <p class="form-description">Fill in ALL fields. Check "Use for WHERE" to specify which record(s) to update (typically primary keys).</p>
                
                <?php foreach ($table_info['columns'] as $column): ?>
                    <div class="form-row">
                        <div class="form-field" style="flex: 2;">
                            <label for="update_field_<?php echo $column['Field']; ?>">
                                <?php echo $column['Field']; ?>
                                <span class="field-note">
                                    (<?php echo $column['Type']; ?> 
                                    <?php echo $column['Key'] === 'PRI' ? ', PRIMARY KEY' : ''; ?>)
                                </span>
                            </label>
                            <input type="text" 
                                   id="update_field_<?php echo $column['Field']; ?>" 
                                   name="field_<?php echo $column['Field']; ?>" 
                                   placeholder="Enter <?php echo strtolower($column['Field']); ?>">
                        </div>
                        
                        <div class="form-field" style="flex: 0.5;">
                            <label class="where-label">
                                <input type="checkbox" 
                                       name="where_field[]" 
                                       value="<?php echo $column['Field']; ?>" 
                                       class="where-checkbox"
                                       <?php echo $column['Key'] === 'PRI' ? 'checked' : ''; ?>>
                                Use for WHERE
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-pen-to-square"></i> Update Record
                    </button>
                    <button type="button" class="btn btn-warning" onclick="clearForm('updateForm')">
                        <i class="fa-solid fa-eraser"></i> Clear Form
                    </button>
                </div>
            </form>

            <!-- Delete Record Form -->
            <form method="POST" id="deleteForm" class="action-form <?php echo $action === 'delete' ? 'active' : ''; ?>">
                <input type="hidden" name="table" value="<?php echo $selected_table; ?>">
                <input type="hidden" name="action" value="delete">
                
                <h3 style="color: #333;">Delete Record from <?php echo $selected_table; ?></h3>
                <p class="form-description">Fill in the fields that identify the record(s) you want to delete. Be careful - this action cannot be undone!</p>
                
                <?php foreach ($table_info['columns'] as $column): ?>
                    <div class="form-row">
                        <div class="form-field">
                            <label for="delete_field_<?php echo $column['Field']; ?>">
                                <?php echo $column['Field']; ?>
                                <span class="field-note">
                                    (<?php echo $column['Type']; ?> 
                                    <?php echo $column['Key'] === 'PRI' ? ', PRIMARY KEY' : ''; ?>)
                                </span>
                            </label>
                            <input type="text" 
                                   id="delete_field_<?php echo $column['Field']; ?>" 
                                   name="field_<?php echo $column['Field']; ?>" 
                                   placeholder="Enter <?php echo strtolower($column['Field']); ?> to identify records">
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="action-buttons">
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this record? This action cannot be undone.')">
                        <i class="fa-solid fa-trash"></i> Delete Record
                    </button>
                    <button type="button" class="btn btn-warning" onclick="clearForm('deleteForm')">
                        <i class="fa-solid fa-eraser"></i> Clear Form
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showAction(action) {
            // Hide all forms
            document.querySelectorAll('.action-form').forEach(form => {
                form.classList.remove('active');
            });
            
            // Show selected form
            document.getElementById(action + 'Form').classList.add('active');
            
            // Update active tab
            document.querySelectorAll('.action-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Update hidden action field in table form
            document.querySelector('input[name="action"]').value = action;
        }
        
        function clearForm(formId) {
            const form = document.getElementById(formId);
            const inputs = form.querySelectorAll('input[type="text"]');
            inputs.forEach(input => input.value = '');
        }
        
        // Auto-fill form with sample data when clicking on sample table rows
        document.addEventListener('DOMContentLoaded', function() {
            const sampleRows = document.querySelectorAll('.sample-table tbody tr');
            sampleRows.forEach(row => {
                row.addEventListener('click', function() {
                    const cells = this.querySelectorAll('td');
                    cells.forEach((cell, index) => {
                        const columnName = document.querySelectorAll('.sample-table th')[index].textContent;
                        
                        // Fill all forms with this data
                        const addInput = document.querySelector(`#addForm input[name="field_${columnName}"]`);
                        const updateInput = document.querySelector(`#updateForm input[name="field_${columnName}"]`);
                        const deleteInput = document.querySelector(`#deleteForm input[name="field_${columnName}"]`);
                        
                        if (addInput) addInput.value = cell.textContent;
                        if (updateInput) updateInput.value = cell.textContent;
                        if (deleteInput) deleteInput.value = cell.textContent;
                    });
                });
            });
        });
    </script>

</body>
</html>

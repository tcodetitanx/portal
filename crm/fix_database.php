<?php
session_start();

// Check if user is authenticated
if (!isset($_SESSION["authenticated"]) || $_SESSION["authenticated"] !== true) {
    header("Location: ../login.php");
    exit();
}

// Include database configuration
$conn = require_once('config/db_config.php');

// Include database helper functions
if (file_exists('includes/db_helpers.php')) {
    require_once('includes/db_helpers.php');
} else {
    // Define the functions if the file doesn't exist
    function columnExists($conn, $table, $column) {
        $sql = "SHOW COLUMNS FROM $table LIKE '$column'";
        $result = $conn->query($sql);
        return $result->num_rows > 0;
    }

    function tableExists($conn, $table) {
        $sql = "SHOW TABLES LIKE '$table'";
        $result = $conn->query($sql);
        return $result->num_rows > 0;
    }
}

// Array to store messages
$messages = [];

// Define the expected schema for each table
$expected_schema = [
    'contacts' => [
        ['name' => 'id', 'type' => 'INT(11)', 'null' => false, 'default' => null, 'extra' => 'AUTO_INCREMENT', 'key' => 'PRI'],
        ['name' => 'contact_type', 'type' => "ENUM('clients','prospects','closed')", 'null' => false],
        ['name' => 'name', 'type' => 'VARCHAR(255)', 'null' => false],
        ['name' => 'rep', 'type' => 'VARCHAR(255)', 'null' => true],
        ['name' => 'interest_level', 'type' => 'INT(11)', 'null' => true],
        ['name' => 'initial_contact_date', 'type' => 'DATE', 'null' => true],
        ['name' => 'obstacle', 'type' => 'TEXT', 'null' => true],
        ['name' => 'next_step', 'type' => 'TEXT', 'null' => true],
        ['name' => 'update_date', 'type' => 'DATE', 'null' => true],
        ['name' => 'call_back_date', 'type' => 'DATE', 'null' => true],
        ['name' => 'email', 'type' => 'VARCHAR(255)', 'null' => true],
        ['name' => 'phone_number', 'type' => 'VARCHAR(20)', 'null' => true],
        ['name' => 'address', 'type' => 'TEXT', 'null' => true],
        ['name' => 'city', 'type' => 'VARCHAR(100)', 'null' => true],
        ['name' => 'state', 'type' => 'VARCHAR(50)', 'null' => true],
        ['name' => 'zip', 'type' => 'VARCHAR(20)', 'null' => true],
        ['name' => 'loan_institution', 'type' => 'VARCHAR(100)', 'null' => true],
        ['name' => 'lender_id', 'type' => 'INT(11)', 'null' => true],
        ['name' => 'step', 'type' => 'INT(1)', 'null' => true, 'default' => '0'],
        ['name' => 'past_due_on_loan', 'type' => "ENUM('Y','N')", 'null' => true],
        ['name' => 'additional_notes', 'type' => 'TEXT', 'null' => true],
        ['name' => 'payment_date', 'type' => 'DATE', 'null' => true],
        ['name' => 'next_payment_date', 'type' => 'DATE', 'null' => true],
        ['name' => 'contract_amount', 'type' => 'DECIMAL(10,2)', 'null' => true],
        ['name' => 'first_noe', 'type' => 'DATE', 'null' => true],
        ['name' => 'final_noe', 'type' => 'DATE', 'null' => true],
        ['name' => 'court_date', 'type' => 'DATE', 'null' => true],
        ['name' => 'suit_filed', 'type' => 'DATE', 'null' => true],
        ['name' => 'status', 'type' => 'VARCHAR(100)', 'null' => true],
        ['name' => 'first_noe_tracking_number', 'type' => 'VARCHAR(50)', 'null' => true],
        ['name' => 'final_noe_tracking_number', 'type' => 'VARCHAR(50)', 'null' => true],
        ['name' => 'suit_filed_tracking_number', 'type' => 'VARCHAR(50)', 'null' => true],
        ['name' => 'first_noe_tracking_confirmed', 'type' => 'TINYINT(1)', 'null' => true, 'default' => '0'],
        ['name' => 'final_noe_tracking_confirmed', 'type' => 'TINYINT(1)', 'null' => true, 'default' => '0'],
        ['name' => 'suit_filed_tracking_confirmed', 'type' => 'TINYINT(1)', 'null' => true, 'default' => '0'],
        ['name' => 'created_at', 'type' => 'TIMESTAMP', 'null' => false, 'default' => 'CURRENT_TIMESTAMP'],
        ['name' => 'updated_at', 'type' => 'TIMESTAMP', 'null' => false, 'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP']
    ],
    'lenders' => [
        ['name' => 'id', 'type' => 'INT(11)', 'null' => false, 'default' => null, 'extra' => 'AUTO_INCREMENT', 'key' => 'PRI'],
        ['name' => 'lender_name', 'type' => 'VARCHAR(255)', 'null' => false],
        ['name' => 'phone_number', 'type' => 'VARCHAR(50)', 'null' => true],
        ['name' => 'address', 'type' => 'TEXT', 'null' => true],
        ['name' => 'street_address', 'type' => 'VARCHAR(255)', 'null' => true],
        ['name' => 'city', 'type' => 'VARCHAR(100)', 'null' => true],
        ['name' => 'state', 'type' => 'VARCHAR(50)', 'null' => true],
        ['name' => 'zip', 'type' => 'VARCHAR(20)', 'null' => true],
        ['name' => 'created_at', 'type' => 'TIMESTAMP', 'null' => false, 'default' => 'CURRENT_TIMESTAMP'],
        ['name' => 'updated_at', 'type' => 'TIMESTAMP', 'null' => false, 'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP']
    ],
    'documents' => [
        ['name' => 'id', 'type' => 'INT(11)', 'null' => false, 'default' => null, 'extra' => 'AUTO_INCREMENT', 'key' => 'PRI'],
        ['name' => 'contact_id', 'type' => 'INT(11)', 'null' => false],
        ['name' => 'document_type', 'type' => "ENUM('contract','first_noe','final_noe','lawsuit','other')", 'null' => false],
        ['name' => 'file_path', 'type' => 'VARCHAR(255)', 'null' => false],
        ['name' => 'created_at', 'type' => 'TIMESTAMP', 'null' => false, 'default' => 'CURRENT_TIMESTAMP']
    ],
    'state_statutes' => [
        ['name' => 'id', 'type' => 'INT(11)', 'null' => false, 'default' => null, 'extra' => 'AUTO_INCREMENT', 'key' => 'PRI'],
        ['name' => 'state_code', 'type' => 'VARCHAR(2)', 'null' => false],
        ['name' => 'state_name', 'type' => 'VARCHAR(50)', 'null' => false],
        ['name' => 'statute_text', 'type' => 'LONGTEXT', 'null' => false],
        ['name' => 'created_at', 'type' => 'TIMESTAMP', 'null' => false, 'default' => 'CURRENT_TIMESTAMP'],
        ['name' => 'updated_at', 'type' => 'TIMESTAMP', 'null' => false, 'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP']
    ],
    'mail_tracking' => [
        ['name' => 'id', 'type' => 'INT(11)', 'null' => false, 'default' => null, 'extra' => 'AUTO_INCREMENT', 'key' => 'PRI'],
        ['name' => 'contact_id', 'type' => 'INT(11)', 'null' => false],
        ['name' => 'tracking_number', 'type' => 'VARCHAR(50)', 'null' => false],
        ['name' => 'tracking_type', 'type' => "ENUM('first_noe','final_noe','suit_filed')", 'null' => false],
        ['name' => 'status', 'type' => 'VARCHAR(100)', 'null' => true],
        ['name' => 'status_details', 'type' => 'TEXT', 'null' => true],
        ['name' => 'delivery_date', 'type' => 'DATE', 'null' => true],
        ['name' => 'created_at', 'type' => 'TIMESTAMP', 'null' => false, 'default' => 'CURRENT_TIMESTAMP'],
        ['name' => 'updated_at', 'type' => 'TIMESTAMP', 'null' => false, 'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP']
    ]
];

// Check each table and its columns
foreach ($expected_schema as $table => $columns) {
    // Check if table exists
    if (!tableExists($conn, $table)) {
        $messages[] = "Table '$table' does not exist. Please run the database setup script.";
        continue;
    }

    // Get existing columns
    $sql = "SHOW COLUMNS FROM `$table`";
    $result = $conn->query($sql);
    $existing_columns = [];
    while ($row = $result->fetch_assoc()) {
        $existing_columns[] = $row['Field'];
    }

    // Check each column
    foreach ($columns as $column) {
        if (!in_array($column['name'], $existing_columns)) {
            // Build the column definition
            $column_def = $column['name'] . ' ' . $column['type'];

            if (isset($column['null']) && $column['null'] === false) {
                $column_def .= ' NOT NULL';
            }

            if (isset($column['default'])) {
                if ($column['default'] === null) {
                    if (isset($column['null']) && $column['null'] === true) {
                        $column_def .= ' DEFAULT NULL';
                    }
                } else {
                    $column_def .= ' DEFAULT ' . $column['default'];
                }
            }

            if (isset($column['extra'])) {
                $column_def .= ' ' . $column['extra'];
            }

            // Determine where to add the column
            $after_clause = '';
            $index = array_search($column['name'], array_column($columns, 'name'));
            if ($index > 0) {
                $prev_column = $columns[$index - 1]['name'];
                $after_clause = " AFTER `$prev_column`";
            }

            // Add the column
            $sql = "ALTER TABLE `$table` ADD COLUMN $column_def$after_clause";
            if ($conn->query($sql)) {
                $messages[] = "Added '{$column['name']}' column to $table table.";
            } else {
                $messages[] = "Error adding '{$column['name']}' column to $table table: " . $conn->error;
            }

            // Add primary key if needed
            if (isset($column['key']) && $column['key'] === 'PRI') {
                $sql = "ALTER TABLE `$table` ADD PRIMARY KEY (`{$column['name']}`)";
                if ($conn->query($sql)) {
                    $messages[] = "Added primary key on '{$column['name']}' to $table table.";
                } else {
                    // Primary key might already exist, so this error is not critical
                    $messages[] = "Note: Could not add primary key on '{$column['name']}' to $table table. It might already exist.";
                }
            }
        }
    }
}

// Force-add specific columns that are causing issues
$critical_columns = [
    ['table' => 'contacts', 'name' => 'suit_filed', 'type' => 'DATE NULL', 'after' => 'court_date'],
    ['table' => 'contacts', 'name' => 'suit_filed_tracking_number', 'type' => 'VARCHAR(50) NULL', 'after' => 'final_noe_tracking_number'],
    ['table' => 'contacts', 'name' => 'suit_filed_tracking_confirmed', 'type' => 'TINYINT(1) DEFAULT 0', 'after' => 'final_noe_tracking_confirmed'],
    ['table' => 'contacts', 'name' => 'lender_id', 'type' => 'INT(11) NULL', 'after' => 'loan_institution']
];

foreach ($critical_columns as $column) {
    if (!columnExists($conn, $column['table'], $column['name'])) {
        $sql = "ALTER TABLE `{$column['table']}` ADD COLUMN `{$column['name']}` {$column['type']} AFTER `{$column['after']}`";
        if ($conn->query($sql)) {
            $messages[] = "Force-added '{$column['name']}' column to {$column['table']} table.";
        } else {
            $messages[] = "Error force-adding '{$column['name']}' column to {$column['table']} table: " . $conn->error;
        }
    }
}

// Add foreign key constraints if needed
if (tableExists($conn, 'contacts') && tableExists($conn, 'lenders') && columnExists($conn, 'contacts', 'lender_id')) {
    // Check if foreign key exists
    $sql = "SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'contacts'
            AND COLUMN_NAME = 'lender_id'
            AND REFERENCED_TABLE_NAME = 'lenders'";
    $result = $conn->query($sql);

    if ($result->num_rows === 0) {
        // Add foreign key constraint
        $sql = "ALTER TABLE contacts ADD FOREIGN KEY (lender_id) REFERENCES lenders(id) ON DELETE SET NULL";
        if ($conn->query($sql)) {
            $messages[] = "Added foreign key constraint on contacts.lender_id referencing lenders.id.";
        } else {
            $messages[] = "Error adding foreign key constraint: " . $conn->error;
        }
    }
}

// Create directories if they don't exist
$directories = ['../documents', '../contracts', 'images'];
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0777, true)) {
            $messages[] = "Created directory: $dir";
        } else {
            $messages[] = "Error creating directory: $dir";
        }
    }
}

// Create copyView.png if it doesn't exist
if (!file_exists('images/copyView.png')) {
    // Create a simple text file as a fallback
    file_put_contents('images/copyView.txt', 'COPY VIEW');
    $messages[] = "Created copyView.txt file in images directory.";
}

// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Database - CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Database Fix Utility</h1>

        <?php if (count($messages) > 0): ?>
            <div class="alert alert-info">
                <h4>Results:</h4>
                <ul>
                    <?php foreach ($messages as $message): ?>
                        <li><?php echo htmlspecialchars($message); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <div class="alert alert-success">
                <p>No database fixes were needed. Your database schema is up to date.</p>
            </div>
        <?php endif; ?>

        <p>This utility checks for and adds missing columns that are required for the CRM to function properly.</p>

        <div class="mt-4">
            <a href="index.php" class="btn btn-primary">Back to CRM</a>
        </div>
    </div>
</body>
</html>

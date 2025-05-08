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
}

// Get a sample contact for testing
$sql = "SELECT * FROM contacts LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    echo "No contacts found for testing. Please add a contact first.";
    exit();
}

$contact = $result->fetch_assoc();
$contact_id = $contact['id'];

// Test the dynamic update function
$columnValues = [
    'name' => $contact['name'] . ' (Updated)',
    'email' => $contact['email'],
    'phone_number' => $contact['phone_number'],
    'additional_notes' => 'Updated via test script at ' . date('Y-m-d H:i:s')
];

$whereClause = "id = ?";
$whereParams = [$contact_id];

// Test the update
$success = false;
$error_message = '';

try {
    if (function_exists('executeDynamicUpdate')) {
        // Use the helper function if available
        $success = executeDynamicUpdate($conn, 'contacts', $columnValues, $whereClause, $whereParams);
    } else {
        // Manual implementation
        $setClauses = [];
        $paramTypes = '';
        $paramValues = [];
        
        foreach ($columnValues as $column => $value) {
            if (columnExists($conn, 'contacts', $column)) {
                $setClauses[] = "`$column` = ?";
                
                // Determine parameter type
                if (is_int($value)) {
                    $paramTypes .= 'i';
                } elseif (is_float($value)) {
                    $paramTypes .= 'd';
                } elseif (is_null($value)) {
                    $paramTypes .= 's';
                    $value = null;
                } else {
                    $paramTypes .= 's';
                }
                
                $paramValues[] = $value;
            }
        }
        
        // Add WHERE clause parameter type
        $paramTypes .= 'i';
        
        // Build the query
        $query = "UPDATE `contacts` SET " . implode(', ', $setClauses) . " WHERE $whereClause";
        
        // Prepare the statement
        $stmt = $conn->prepare($query);
        if ($stmt) {
            // Create references for bind_param
            $params = array();
            $params[] = &$paramTypes;
            
            // Add references to column values
            foreach ($paramValues as $key => $value) {
                $paramValues[$key] = $value; // Ensure the value is set
                $params[] = &$paramValues[$key]; // Add a reference to this element
            }
            
            // Add references to WHERE params
            foreach ($whereParams as $key => $value) {
                $whereParams[$key] = $value; // Ensure the value is set
                $params[] = &$whereParams[$key]; // Add a reference to this element
            }
            
            // Call bind_param with the array of references
            call_user_func_array(array($stmt, 'bind_param'), $params);
            
            // Execute the statement
            $success = $stmt->execute();
            $stmt->close();
        } else {
            $error_message = "Failed to prepare statement: " . $conn->error;
        }
    }
} catch (Exception $e) {
    $error_message = "Exception: " . $e->getMessage();
}

// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Update - CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Test Update Function</h1>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <h4>Update Successful!</h4>
                <p>The contact "<?php echo htmlspecialchars($contact['name']); ?>" (ID: <?php echo $contact_id; ?>) was successfully updated.</p>
                <p>The name was changed to "<?php echo htmlspecialchars($columnValues['name']); ?>".</p>
                <p>Additional notes were updated to: "<?php echo htmlspecialchars($columnValues['additional_notes']); ?>"</p>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">
                <h4>Update Failed</h4>
                <p>The update operation failed.</p>
                <?php if (!empty($error_message)): ?>
                    <p>Error: <?php echo htmlspecialchars($error_message); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="mt-4">
            <a href="index.php" class="btn btn-primary">Back to CRM</a>
        </div>
    </div>
</body>
</html>

<?php
session_start();

// Check if user is authenticated
if (!isset($_SESSION["authenticated"]) || $_SESSION["authenticated"] !== true) {
    header("Location: ../login.php");
    exit();
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: debug_update.php");
    exit();
}

// Include database configuration
$conn = require_once('config/db_config.php');

// Sanitize and validate input
function sanitizeInput($conn, $input) {
    return $conn->real_escape_string(trim($input));
}

// Get form data
$id = isset($_POST['id']) && is_numeric($_POST['id']) ? intval($_POST['id']) : 0;
$contact_type = sanitizeInput($conn, $_POST['contact_type']);
$name = sanitizeInput($conn, $_POST['name']);
$additional_notes = sanitizeInput($conn, $_POST['additional_notes']);

// Validate contact ID
if ($id <= 0) {
    $_SESSION['error'] = "Invalid contact ID";
    header("Location: debug_update.php");
    exit();
}

// Validate contact type
if (!in_array($contact_type, ['clients', 'prospects', 'closed'])) {
    $_SESSION['error'] = "Invalid contact type";
    header("Location: debug_update.php");
    exit();
}

// Simple update query with minimal fields
$sql = "UPDATE contacts SET additional_notes = ? WHERE id = ?";
$stmt = $conn->prepare($sql);

// Debug information
$debug_info = "SQL: $sql\n\n";
$debug_info .= "ID: $id\n\n";
$debug_info .= "Additional Notes: $additional_notes\n\n";

// Bind parameters
try {
    $stmt->bind_param("si", $additional_notes, $id);
    
    // Execute the statement
    if ($stmt->execute()) {
        $_SESSION['success'] = "Test update successful! The contact's additional notes have been updated.";
    } else {
        $_SESSION['error'] = "Error executing update: " . $conn->error . "\n\n" . $debug_info;
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error binding parameters: " . $e->getMessage() . "\n\n" . $debug_info;
}

// Close the database connection
mysqli_close($conn);

// Redirect back to the debug page
header("Location: debug_update.php");
exit();
?>

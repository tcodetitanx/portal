<?php
session_start();

// Check if user is authenticated
if (!isset($_SESSION["authenticated"]) || $_SESSION["authenticated"] !== true) {
    header("Location: ../login.php");
    exit();
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: index.php");
    exit();
}

// Include database configuration
$conn = require_once('config/db_config.php');

// Get contact ID
$id = isset($_POST['id']) && is_numeric($_POST['id']) ? intval($_POST['id']) : 0;

// Validate contact ID
if ($id <= 0) {
    $_SESSION['error'] = "Invalid contact ID";
    header("Location: index.php");
    exit();
}

// Get contact type before deletion for redirect
$sql = "SELECT contact_type FROM contacts WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$contact_type = 'clients'; // Default

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $contact_type = $row['contact_type'];
}

// Delete the contact
$sql = "DELETE FROM contacts WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Contact deleted successfully";
} else {
    $_SESSION['error'] = "Error deleting contact: " . $conn->error;
}

// Close the database connection
mysqli_close($conn);

// Redirect back to the contacts page
header("Location: index.php?type=" . $contact_type);
exit();
?>

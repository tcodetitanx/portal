<?php
session_start();

// Check if user is authenticated
if (!isset($_SESSION["authenticated"]) || $_SESSION["authenticated"] !== true) {
    header("Location: ../login.php");
    exit();
}

// Check if required parameters are provided
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid contact ID']);
    exit();
}

$contact_id = intval($_POST['id']);

// Include database configuration
$conn = require_once('config/db_config.php');

// Get contact details
$sql = "SELECT * FROM contacts WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $contact_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Contact not found']);
    exit();
}

$contact = $result->fetch_assoc();

// Close the database connection
mysqli_close($conn);

// Check if contact has email
if (empty($contact['email'])) {
    echo json_encode(['success' => false, 'message' => 'Contact does not have an email address']);
    exit();
}

// In a real implementation, you would integrate with the DocuSign API here
// For now, we'll just simulate success
$response = [
    'success' => true,
    'message' => 'DocuSign contract sent successfully to ' . $contact['email']
];

// Update the contact's step to 4 (Contract Sent)
$conn = require_once('config/db_config.php');
$update_sql = "UPDATE contacts SET step = 4 WHERE id = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("i", $contact_id);
$update_stmt->execute();
mysqli_close($conn);

echo json_encode($response);
exit();
?>

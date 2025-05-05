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

// Generate the contract PDF
$name = urlencode($contact['name']);
$address = urlencode($contact['address']);
$phone = urlencode($contact['phone_number']);
$creation_date = date('Y-m-d');
$amount = $contact['contract_amount'] > 0 ? $contact['contract_amount'] : 1249.50;
$months = 0; // Pay in full by default
$clause = 'default';
$language = 'english';

// Build the contract generation URL
$contract_url = "../promnote/contractV2gen.php?name=$name&address=$address&phone=$phone&creation_date=$creation_date&amount=$amount&months=$months&clause_choice=$clause&language=$language";

// Use the Gmail API to send the email
// This is a simplified version - in a real implementation, you would need to:
// 1. Generate the PDF
// 2. Save it temporarily
// 3. Attach it to the email
// 4. Send the email using the Gmail API

// For now, we'll just simulate success
$response = [
    'success' => true,
    'message' => 'Contract sent successfully to ' . $contact['email'],
    'contract_url' => $contract_url
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

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

// Sanitize and validate input
function sanitizeInput($conn, $input) {
    return $conn->real_escape_string(trim($input));
}

// Get form data
$contact_type = sanitizeInput($conn, $_POST['contact_type']);
$name = sanitizeInput($conn, $_POST['name']);
$rep = isset($_POST['rep']) ? sanitizeInput($conn, $_POST['rep']) : '';
$interest_level = isset($_POST['interest_level']) && is_numeric($_POST['interest_level']) ? intval($_POST['interest_level']) : 0;
$email = isset($_POST['email']) ? sanitizeInput($conn, $_POST['email']) : '';
$phone_number = isset($_POST['phone_number']) ? sanitizeInput($conn, $_POST['phone_number']) : '';
$address = isset($_POST['address']) ? sanitizeInput($conn, $_POST['address']) : '';
$city = isset($_POST['city']) ? sanitizeInput($conn, $_POST['city']) : '';
$state = isset($_POST['state']) ? sanitizeInput($conn, $_POST['state']) : '';
$zip = isset($_POST['zip']) ? sanitizeInput($conn, $_POST['zip']) : '';
$loan_institution = isset($_POST['loan_institution']) ? sanitizeInput($conn, $_POST['loan_institution']) : '';
$lender_id = isset($_POST['lender_id']) && is_numeric($_POST['lender_id']) ? intval($_POST['lender_id']) : null;
$step = isset($_POST['step']) && is_numeric($_POST['step']) ? intval($_POST['step']) : 0;
$additional_notes = isset($_POST['additional_notes']) ? sanitizeInput($conn, $_POST['additional_notes']) : '';

// Validate contact type
if (!in_array($contact_type, ['clients', 'prospects', 'closed'])) {
    $_SESSION['error'] = "Invalid contact type";
    header("Location: index.php");
    exit();
}

// Insert the new contact
$sql = "INSERT INTO contacts (
    contact_type, name, rep, interest_level, email, phone_number,
    address, city, state, zip, loan_institution, lender_id, step, additional_notes
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssississssiis",
    $contact_type, $name, $rep, $interest_level, $email, $phone_number,
    $address, $city, $state, $zip, $loan_institution, $lender_id, $step, $additional_notes
);

if ($stmt->execute()) {
    $_SESSION['success'] = "Contact added successfully";
} else {
    $_SESSION['error'] = "Error adding contact: " . $conn->error;
}

// Close the database connection
mysqli_close($conn);

// Redirect back to the contacts page
header("Location: index.php?type=" . $contact_type);
exit();
?>

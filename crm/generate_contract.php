<?php
session_start();

// Check if user is authenticated
if (!isset($_SESSION["authenticated"]) || $_SESSION["authenticated"] !== true) {
    header("Location: ../login.php");
    exit();
}

// Check if required parameters are provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid contact ID";
    exit();
}

$contact_id = intval($_GET['id']);
$amount = isset($_GET['amount']) && is_numeric($_GET['amount']) ? floatval($_GET['amount']) : 2499.00;
$months = isset($_GET['months']) && is_numeric($_GET['months']) ? intval($_GET['months']) : 0;
$language = isset($_GET['language']) ? $_GET['language'] : 'english';
$clause = isset($_GET['clause']) ? $_GET['clause'] : 'default';

// Ensure amount is never 0
if ($amount <= 0) {
    $amount = 2499.00;
}

// Restrict months to either 0 (pay in full) or 1
if ($months > 1) {
    $months = 1;
}

// Include database configuration
$conn = require_once('config/db_config.php');

// Get contact details
$sql = "SELECT * FROM contacts WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $contact_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Contact not found";
    exit();
}

$contact = $result->fetch_assoc();

// Get payment link for this contact based on amount
$payment_link_id = 0;

// First try to find a payment link with the exact amount using a direct query
$payment_link_sql = "SELECT id FROM payment_links WHERE contact_id = $contact_id AND amount = $amount LIMIT 1";
$payment_link_result = $conn->query($payment_link_sql);

if ($payment_link_result->num_rows > 0) {
    $payment_link = $payment_link_result->fetch_assoc();
    $payment_link_id = $payment_link['id'];
} else {
    // If no payment link exists for this amount, get the first payment link
    $payment_link_sql = "SELECT id FROM payment_links WHERE contact_id = $contact_id LIMIT 1";
    $payment_link_result = $conn->query($payment_link_sql);

    if ($payment_link_result->num_rows > 0) {
        $payment_link = $payment_link_result->fetch_assoc();
        $payment_link_id = $payment_link['id'];
    }
}

// Close the database connection
mysqli_close($conn);

// Prepare data for contract generation
$name = $contact['name'];
$address = $contact['address'];
$phone = $contact['phone_number'];
$creation_date = date('Y-m-d');

// Redirect to the contract generation page with the necessary parameters
$redirect_url = "../promnote/contractV2gen.php";
$params = [
    'name' => $name,
    'address' => $address,
    'phone' => $phone,
    'creation_date' => $creation_date,
    'amount' => $amount,
    'months' => $months,
    'clause_choice' => $clause,
    'language' => $language,
    'contact_id' => $contact_id,
    'payment_link_id' => $payment_link_id
];

// Build the query string
$query_string = http_build_query($params);

// Redirect to the contract generation page
header("Location: $redirect_url?$query_string");
exit();
?>

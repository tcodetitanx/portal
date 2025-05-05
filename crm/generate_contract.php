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
$amount = isset($_GET['amount']) && is_numeric($_GET['amount']) ? floatval($_GET['amount']) : 0;
$months = isset($_GET['months']) && is_numeric($_GET['months']) ? intval($_GET['months']) : 0;
$language = isset($_GET['language']) ? $_GET['language'] : 'english';
$clause = isset($_GET['clause']) ? $_GET['clause'] : 'default';

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
    'language' => $language
];

// Build the query string
$query_string = http_build_query($params);

// Redirect to the contract generation page
header("Location: $redirect_url?$query_string");
exit();
?>

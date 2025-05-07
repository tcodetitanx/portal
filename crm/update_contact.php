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
$id = isset($_POST['id']) && is_numeric($_POST['id']) ? intval($_POST['id']) : 0;
$contact_type = sanitizeInput($conn, $_POST['contact_type']);
$name = sanitizeInput($conn, $_POST['name']);
$rep = isset($_POST['rep']) ? sanitizeInput($conn, $_POST['rep']) : '';
$interest_level = isset($_POST['interest_level']) && is_numeric($_POST['interest_level']) ? intval($_POST['interest_level']) : 0;
$initial_contact_date = !empty($_POST['initial_contact_date']) ? sanitizeInput($conn, $_POST['initial_contact_date']) : null;
$obstacle = isset($_POST['obstacle']) ? sanitizeInput($conn, $_POST['obstacle']) : '';
$next_step = isset($_POST['next_step']) ? sanitizeInput($conn, $_POST['next_step']) : '';
$update_date = !empty($_POST['update_date']) ? sanitizeInput($conn, $_POST['update_date']) : null;
$call_back_date = !empty($_POST['call_back_date']) ? sanitizeInput($conn, $_POST['call_back_date']) : null;
$email = isset($_POST['email']) ? sanitizeInput($conn, $_POST['email']) : '';
$phone_number = isset($_POST['phone_number']) ? sanitizeInput($conn, $_POST['phone_number']) : '';
$address = isset($_POST['address']) ? sanitizeInput($conn, $_POST['address']) : '';
$city = isset($_POST['city']) ? sanitizeInput($conn, $_POST['city']) : '';
$state = isset($_POST['state']) ? sanitizeInput($conn, $_POST['state']) : '';
$zip = isset($_POST['zip']) ? sanitizeInput($conn, $_POST['zip']) : '';
$loan_institution = isset($_POST['loan_institution']) ? sanitizeInput($conn, $_POST['loan_institution']) : '';
$step = isset($_POST['step']) && is_numeric($_POST['step']) ? intval($_POST['step']) : 0;
$past_due_on_loan = isset($_POST['past_due_on_loan']) && $_POST['past_due_on_loan'] == 'Y' ? 'Y' : 'N';
$additional_notes = isset($_POST['additional_notes']) ? sanitizeInput($conn, $_POST['additional_notes']) : '';
$payment_date = !empty($_POST['payment_date']) ? sanitizeInput($conn, $_POST['payment_date']) : null;
$next_payment_date = !empty($_POST['next_payment_date']) ? sanitizeInput($conn, $_POST['next_payment_date']) : null;
$contract_amount = isset($_POST['contract_amount']) && is_numeric($_POST['contract_amount']) ? floatval($_POST['contract_amount']) : 0;
$first_noe = !empty($_POST['first_noe']) ? sanitizeInput($conn, $_POST['first_noe']) : null;
$final_noe = !empty($_POST['final_noe']) ? sanitizeInput($conn, $_POST['final_noe']) : null;
$court_date = !empty($_POST['court_date']) ? sanitizeInput($conn, $_POST['court_date']) : null;
$suit_filed = !empty($_POST['suit_filed']) ? sanitizeInput($conn, $_POST['suit_filed']) : null;
$status = isset($_POST['status']) ? sanitizeInput($conn, $_POST['status']) : '';

// Mail tracking fields
$first_noe_tracking_number = isset($_POST['first_noe_tracking_number']) ? sanitizeInput($conn, $_POST['first_noe_tracking_number']) : '';
$final_noe_tracking_number = isset($_POST['final_noe_tracking_number']) ? sanitizeInput($conn, $_POST['final_noe_tracking_number']) : '';
$suit_filed_tracking_number = isset($_POST['suit_filed_tracking_number']) ? sanitizeInput($conn, $_POST['suit_filed_tracking_number']) : '';
$first_noe_tracking_confirmed = isset($_POST['first_noe_tracking_confirmed']) && $_POST['first_noe_tracking_confirmed'] == '1' ? 1 : 0;
$final_noe_tracking_confirmed = isset($_POST['final_noe_tracking_confirmed']) && $_POST['final_noe_tracking_confirmed'] == '1' ? 1 : 0;
$suit_filed_tracking_confirmed = isset($_POST['suit_filed_tracking_confirmed']) && $_POST['suit_filed_tracking_confirmed'] == '1' ? 1 : 0;

// Validate contact ID
if ($id <= 0) {
    $_SESSION['error'] = "Invalid contact ID";
    header("Location: index.php");
    exit();
}

// Validate contact type
if (!in_array($contact_type, ['clients', 'prospects', 'closed'])) {
    $_SESSION['error'] = "Invalid contact type";
    header("Location: index.php");
    exit();
}

// Update the contact
$sql = "UPDATE contacts SET
    contact_type = ?,
    name = ?,
    rep = ?,
    interest_level = ?,
    initial_contact_date = ?,
    obstacle = ?,
    next_step = ?,
    update_date = ?,
    call_back_date = ?,
    email = ?,
    phone_number = ?,
    address = ?,
    city = ?,
    state = ?,
    zip = ?,
    loan_institution = ?,
    step = ?,
    past_due_on_loan = ?,
    additional_notes = ?,
    payment_date = ?,
    next_payment_date = ?,
    contract_amount = ?,
    first_noe = ?,
    final_noe = ?,
    court_date = ?,
    suit_filed = ?,
    status = ?,
    first_noe_tracking_number = ?,
    final_noe_tracking_number = ?,
    suit_filed_tracking_number = ?,
    first_noe_tracking_confirmed = ?,
    final_noe_tracking_confirmed = ?,
    suit_filed_tracking_confirmed = ?
    WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssississssssssisssssdsssssssssiiiii",
    $contact_type,
    $name,
    $rep,
    $interest_level,
    $initial_contact_date,
    $obstacle,
    $next_step,
    $update_date,
    $call_back_date,
    $email,
    $phone_number,
    $address,
    $city,
    $state,
    $zip,
    $loan_institution,
    $step,
    $past_due_on_loan,
    $additional_notes,
    $payment_date,
    $next_payment_date,
    $contract_amount,
    $first_noe,
    $final_noe,
    $court_date,
    $suit_filed,
    $status,
    $first_noe_tracking_number,
    $final_noe_tracking_number,
    $suit_filed_tracking_number,
    $first_noe_tracking_confirmed,
    $final_noe_tracking_confirmed,
    $suit_filed_tracking_confirmed,
    $id
);

if (!$stmt->execute()) {
    $_SESSION['error'] = "Error updating contact: " . $conn->error;
    header("Location: index.php?type=" . $contact_type);
    exit();
}

// Handle payment links
if (isset($_POST['payment_link_id']) && is_array($_POST['payment_link_id'])) {
    $payment_link_ids = $_POST['payment_link_id'];
    $payment_amounts = $_POST['payment_amount'];
    $payment_urls = $_POST['payment_url'];
    $payment_pay_in_fulls = $_POST['payment_pay_in_full'];

    for ($i = 0; $i < count($payment_link_ids); $i++) {
        $link_id = $payment_link_ids[$i];
        $amount = floatval($payment_amounts[$i]);
        $url = sanitizeInput($conn, $payment_urls[$i]);
        $pay_in_full = $payment_pay_in_fulls[$i] == 'Y' ? 'Y' : 'N';

        if ($link_id == 'new') {
            // Insert new payment link
            $insert_sql = "INSERT INTO payment_links (contact_id, amount, url, pay_in_full) VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("idss", $id, $amount, $url, $pay_in_full);
            $insert_stmt->execute();
        } else {
            // Update existing payment link
            $update_sql = "UPDATE payment_links SET amount = ?, url = ?, pay_in_full = ? WHERE id = ? AND contact_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("dssii", $amount, $url, $pay_in_full, $link_id, $id);
            $update_stmt->execute();
        }
    }
}

$_SESSION['success'] = "Contact updated successfully";

// Close the database connection
mysqli_close($conn);

// Redirect back to the contacts page
header("Location: index.php?type=" . $contact_type);
exit();
?>

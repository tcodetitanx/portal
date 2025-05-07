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

// Check if contact has email
if (empty($contact['email'])) {
    echo json_encode(['success' => false, 'message' => 'Contact does not have an email address']);
    mysqli_close($conn);
    exit();
}

// Get payment link for this contact
$payment_link_sql = "SELECT * FROM payment_links WHERE contact_id = ? LIMIT 1";
$payment_link_stmt = $conn->prepare($payment_link_sql);
$payment_link_stmt->bind_param("i", $contact_id);
$payment_link_stmt->execute();
$payment_link_result = $payment_link_stmt->get_result();

if ($payment_link_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'No payment link found for this contact']);
    mysqli_close($conn);
    exit();
}

$payment_link = $payment_link_result->fetch_assoc();

// Get contract for this contact
$contract_sql = "SELECT * FROM contracts WHERE contact_id = ? ORDER BY created_at DESC LIMIT 1";
$contract_stmt = $conn->prepare($contract_sql);
$contract_stmt->bind_param("i", $contact_id);
$contract_stmt->execute();
$contract_result = $contract_stmt->get_result();

// If no contract exists, generate one
if ($contract_result->num_rows === 0) {
    // Generate the contract PDF
    $name = urlencode($contact['name']);
    $address = urlencode($contact['address']);
    $phone = urlencode($contact['phone_number']);
    $creation_date = date('Y-m-d');
    $amount = $contact['contract_amount'] > 0 ? $contact['contract_amount'] : 2499.00;
    $months = 0; // Pay in full by default
    $clause = 'default';
    $language = 'english';

    // Build the contract generation URL and call it
    $contract_url = "../promnote/contractV2gen.php?name=$name&address=$address&phone=$phone&creation_date=$creation_date&amount=$amount&months=$months&clause_choice=$clause&language=$language&contact_id=$contact_id&payment_link_id=" . $payment_link['id'];

    // We need to actually generate the contract by making a request to the URL
    $contract_content = file_get_contents($contract_url);

    // Check if contract was generated
    $contract_sql = "SELECT * FROM contracts WHERE contact_id = ? ORDER BY created_at DESC LIMIT 1";
    $contract_stmt = $conn->prepare($contract_sql);
    $contract_stmt->bind_param("i", $contact_id);
    $contract_stmt->execute();
    $contract_result = $contract_stmt->get_result();

    if ($contract_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Failed to generate contract. Please try again.']);
        mysqli_close($conn);
        exit();
    }
}

$contract = $contract_result->fetch_assoc();
$contract_file_path = '../contracts/' . $contract['file_path'];

if (!file_exists($contract_file_path)) {
    echo json_encode(['success' => false, 'message' => 'Contract file not found. Please regenerate the contract.']);
    mysqli_close($conn);
    exit();
}

// Get first name
$name_parts = explode(' ', $contact['name']);
$first_name = $name_parts[0];

// Email template
$subject = "Bull Axiom Retainer and Payment Link";
$message = "
$first_name,

Thank you for reaching out to us regarding your loan contract. After a thorough review by our contract review specialist, William, we are confident that we can assist you. Your lender appears to have failed to provide a fully itemized Truth in Lending Disclosure, which may constitute a violation of the Truth in Lending Act (TILA), 15 U.S.C. § 1601 et seq.

You will soon receive a contract which includes a 90-Day money back guarantee from Bull Axiom outlining our agreement to proceed. Please keep an eye on your inbox, and be sure to check your spam or junk folder in case it gets filtered there.

To move forward, please complete your payment at the following link and sign the contract:

{$payment_link['url']}

Once payment is received, we will immediately send a Notice of Error to your lender to formally challenge their violations. If you have any questions, feel free to reach out.

Best regards,
Bull Axiom LLC
";

// Send email with attachment
$to = $contact['email'];
$from = "contracts@bullaxiom.com";

// Boundary for multipart/mixed
$boundary = md5(time());

// Headers for multipart/mixed
$headers = "MIME-Version: 1.0\r\n";
$headers .= "From: Bull Axiom <contracts@bullaxiom.com>\r\n";
$headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

// Email body
$body = "--$boundary\r\n";
$body .= "Content-Type: text/plain; charset=ISO-8859-1\r\n";
$body .= "Content-Transfer-Encoding: base64\r\n\r\n";
$body .= chunk_split(base64_encode($message));

// Attachment
$file_content = file_get_contents($contract_file_path);
$body .= "--$boundary\r\n";
$body .= "Content-Type: application/pdf; name=\"contract.pdf\"\r\n";
$body .= "Content-Disposition: attachment; filename=\"contract.pdf\"\r\n";
$body .= "Content-Transfer-Encoding: base64\r\n\r\n";
$body .= chunk_split(base64_encode($file_content));
$body .= "--$boundary--";

// Send email
$mail_sent = mail($to, $subject, $body, $headers);

// Update the contact's step to 4 (Contract Sent)
$update_sql = "UPDATE contacts SET step = 4 WHERE id = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("i", $contact_id);
$update_stmt->execute();
mysqli_close($conn);

if ($mail_sent) {
    $response = [
        'success' => true,
        'message' => 'Contract sent successfully to ' . $contact['email']
    ];
} else {
    $response = [
        'success' => false,
        'message' => 'Failed to send email. Please try again.'
    ];
}

echo json_encode($response);
exit();
?>

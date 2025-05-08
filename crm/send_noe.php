<?php
session_start();

// Check if user is authenticated
if (!isset($_SESSION["authenticated"]) || $_SESSION["authenticated"] !== true) {
    header("Location: ../login.php");
    exit();
}

// Check if required parameters are provided
if (!isset($_POST['id']) || !is_numeric($_POST['id']) || !isset($_POST['type']) || !in_array($_POST['type'], ['first', 'final'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

$contact_id = intval($_POST['id']);
$noe_type = $_POST['type']; // 'first' or 'final'

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
    exit();
}

// Get the most recent NOE document
$document_type = ($noe_type == 'first') ? 'first_noe' : 'final_noe';
$doc_sql = "SELECT * FROM documents WHERE contact_id = ? AND document_type = ? ORDER BY created_at DESC LIMIT 1";
$doc_stmt = $conn->prepare($doc_sql);
$doc_stmt->bind_param("is", $contact_id, $document_type);
$doc_stmt->execute();
$doc_result = $doc_stmt->get_result();

if ($doc_result->num_rows === 0) {
    // Generate the NOE if it doesn't exist
    $noe_url = "generate_noe.php?id=$contact_id&type=$noe_type";

    // We need to actually generate the NOE by making a request to the URL
    ob_start();
    include($noe_url);
    ob_end_clean();

    // Get the newly created document
    $doc_stmt->execute();
    $doc_result = $doc_stmt->get_result();

    if ($doc_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Failed to generate NOE document. Please try again.']);
        exit();
    }
}

$document = $doc_result->fetch_assoc();
$document_path = '../documents/' . $document['file_path'];
$copy_document_path = '../documents/' . str_replace('.pdf', '_copy_view.pdf', $document['file_path']);

// If copy view document doesn't exist, use the original path
if (!file_exists($copy_document_path)) {
    $copy_document_path = $document_path;
}

// Prepare email content
$subject = ($noe_type == 'first') ? 'Notice of Error - Bull Axiom' : 'Final Notice of Error - Bull Axiom';
$from_email = "notices@bullaxiom.com";
$to_email = $contact['email'];

$message = "
Dear " . $contact['name'] . ",

Attached is a " . ($noe_type == 'first' ? 'Notice of Error' : 'Final Notice of Error') . " that has been sent to your lender on your behalf.

This document outlines the legal issues we've identified with your loan and the demands we're making to the lender. Please review it carefully and keep it for your records.

If you have any questions about this document or the process, please don't hesitate to contact us.

Best regards,
Bull Axiom LLC
";

// Send email with attachment
$boundary = md5(time());
$headers = "From: Bull Axiom <$from_email>\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";

$body = "--$boundary\r\n";
$body .= "Content-Type: text/plain; charset=ISO-8859-1\r\n";
$body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
$body .= $message . "\r\n";

// Attachment
if (file_exists($copy_document_path)) {
    $file_content = file_get_contents($copy_document_path);
    $body .= "--$boundary\r\n";
    $body .= "Content-Type: application/pdf; name=\"" . ($noe_type == 'first' ? 'Notice_of_Error.pdf' : 'Final_Notice_of_Error.pdf') . "\"\r\n";
    $body .= "Content-Disposition: attachment; filename=\"" . ($noe_type == 'first' ? 'Notice_of_Error.pdf' : 'Final_Notice_of_Error.pdf') . "\"\r\n";
    $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $body .= chunk_split(base64_encode($file_content));
} else {
    // If the file doesn't exist, add a note in the email
    $body .= "\r\n\r\nNOTE: The document could not be attached. Please contact support for assistance.\r\n";
}
$body .= "--$boundary--";

// Send email
$mail_sent = mail($to_email, $subject, $body, $headers);

if ($mail_sent) {
    // Update the contact's NOE date in the database if not already set
    $update_field = ($noe_type == 'first') ? 'first_noe' : 'final_noe';
    if (empty($contact[$update_field])) {
        $update_sql = "UPDATE contacts SET $update_field = CURDATE() WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $contact_id);
        $update_stmt->execute();
    }

    echo json_encode(['success' => true, 'message' => ($noe_type == 'first' ? 'Notice of Error' : 'Final Notice of Error') . ' sent successfully to ' . $contact['email']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send email. Please try again.']);
}

// Close the database connection
mysqli_close($conn);
exit();
?>

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

// Function to check if a column exists in a table
function columnExists($conn, $table, $column) {
    $sql = "SHOW COLUMNS FROM $table LIKE '$column'";
    $result = $conn->query($sql);
    return $result && $result->num_rows > 0;
}

// Get form data
$id = isset($_POST['id']) && is_numeric($_POST['id']) ? intval($_POST['id']) : 0;
$contact_type = sanitizeInput($conn, $_POST['contact_type']);

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

// Get all columns from the contacts table
$sql = "SHOW COLUMNS FROM contacts";
$result = $conn->query($sql);
$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
}

// Remove id from columns as we don't update it
$columns = array_diff($columns, ['id', 'created_at', 'updated_at']);

// Build the update data array
$updateData = [];
$updateTypes = "";
$updateValues = [];

// Process each column
foreach ($columns as $column) {
    // Skip columns that aren't in the form
    if (!isset($_POST[$column]) && $column != 'past_due_on_loan') {
        continue;
    }
    
    // Handle special cases
    switch ($column) {
        case 'contact_type':
            $updateData[$column] = $contact_type;
            $updateTypes .= "s";
            $updateValues[] = $contact_type;
            break;
            
        case 'interest_level':
        case 'step':
            $value = isset($_POST[$column]) && is_numeric($_POST[$column]) ? intval($_POST[$column]) : 0;
            $updateData[$column] = $value;
            $updateTypes .= "i";
            $updateValues[] = $value;
            break;
            
        case 'contract_amount':
            $value = isset($_POST[$column]) && is_numeric($_POST[$column]) ? floatval($_POST[$column]) : 0;
            $updateData[$column] = $value;
            $updateTypes .= "d";
            $updateValues[] = $value;
            break;
            
        case 'past_due_on_loan':
            $value = isset($_POST[$column]) && $_POST[$column] == 'Y' ? 'Y' : 'N';
            $updateData[$column] = $value;
            $updateTypes .= "s";
            $updateValues[] = $value;
            break;
            
        case 'lender_id':
            $value = isset($_POST[$column]) && is_numeric($_POST[$column]) ? intval($_POST[$column]) : null;
            $updateData[$column] = $value;
            $updateTypes .= "i";
            $updateValues[] = $value;
            break;
            
        case 'first_noe_tracking_confirmed':
        case 'final_noe_tracking_confirmed':
        case 'suit_filed_tracking_confirmed':
            $value = isset($_POST[$column]) && $_POST[$column] == '1' ? 1 : 0;
            $updateData[$column] = $value;
            $updateTypes .= "i";
            $updateValues[] = $value;
            break;
            
        case 'initial_contact_date':
        case 'update_date':
        case 'call_back_date':
        case 'payment_date':
        case 'next_payment_date':
        case 'first_noe':
        case 'final_noe':
        case 'court_date':
        case 'suit_filed':
            $value = !empty($_POST[$column]) ? sanitizeInput($conn, $_POST[$column]) : null;
            $updateData[$column] = $value;
            $updateTypes .= "s";
            $updateValues[] = $value;
            break;
            
        default:
            // Handle all other string fields
            $value = isset($_POST[$column]) ? sanitizeInput($conn, $_POST[$column]) : '';
            $updateData[$column] = $value;
            $updateTypes .= "s";
            $updateValues[] = $value;
            break;
    }
}

// Build the SQL query
$setClauses = [];
foreach ($updateData as $column => $value) {
    $setClauses[] = "`$column` = ?";
}

$sql = "UPDATE contacts SET " . implode(', ', $setClauses) . " WHERE id = ?";

// Add the ID parameter
$updateTypes .= "i";
$updateValues[] = $id;

// Debug information
$debug_info = "SQL: $sql\n\n";
$debug_info .= "Parameter types: $updateTypes\n\n";
$debug_info .= "Parameter count: " . count($updateValues) . "\n\n";
$debug_info .= "Placeholders in SQL: " . substr_count($sql, '?') . "\n\n";
$debug_info .= "Columns being updated: " . implode(', ', array_keys($updateData)) . "\n\n";

// Check if parameter count matches placeholder count
if (count($updateValues) != substr_count($sql, '?')) {
    $_SESSION['error'] = "Parameter count mismatch. Please contact support.\n\n" . $debug_info;
    header("Location: debug_update.php");
    exit();
}

// Prepare the statement
$stmt = $conn->prepare($sql);
if (!$stmt) {
    $_SESSION['error'] = "Error preparing statement: " . $conn->error . "\n\n" . $debug_info;
    header("Location: index.php?type=" . $contact_type);
    exit();
}

// Create references for bind_param
$params = [];
$params[] = &$updateTypes;

// Add references to values
foreach ($updateValues as $key => $value) {
    $updateValues[$key] = $value; // Ensure the value is set
    $params[] = &$updateValues[$key]; // Add a reference to this element
}

// Call bind_param with the array of references
try {
    call_user_func_array([$stmt, 'bind_param'], $params);
} catch (Exception $e) {
    $_SESSION['error'] = "Error binding parameters: " . $e->getMessage() . "\n\n" . $debug_info;
    header("Location: debug_update.php");
    exit();
}

// Execute the statement
if (!$stmt->execute()) {
    $_SESSION['error'] = "Error updating contact: " . $conn->error . "\n\n" . $debug_info;
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

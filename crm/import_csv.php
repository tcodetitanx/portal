<?php
session_start();

// Check if user is authenticated
if (!isset($_SESSION["authenticated"]) || $_SESSION["authenticated"] !== true) {
    header("Location: ../login.php");
    exit();
}

// Include database configuration
$conn = require_once('config/db_config.php');

// Function to parse date in various formats
function parseDate($dateStr) {
    if (empty($dateStr) || $dateStr == 'PAID' || strtolower($dateStr) == 'n/a') {
        return null;
    }
    
    // Try different date formats
    $formats = ['m/d/Y', 'd/m/Y', 'Y-m-d'];
    
    foreach ($formats as $format) {
        $date = DateTime::createFromFormat($format, $dateStr);
        if ($date !== false) {
            return $date->format('Y-m-d');
        }
    }
    
    return null;
}

// Function to import clients CSV
function importClientsCSV($conn) {
    $file = fopen('csvs/clients.csv', 'r');
    
    // Skip header row
    fgetcsv($file);
    
    $count = 0;
    
    while (($data = fgetcsv($file)) !== FALSE) {
        // Check if we have enough data
        if (count($data) < 10) {
            continue;
        }
        
        $name = $conn->real_escape_string($data[0]);
        $rep = $conn->real_escape_string($data[1]);
        $email = $conn->real_escape_string($data[2]);
        $phone = $conn->real_escape_string($data[3]);
        $address = $conn->real_escape_string($data[4]);
        $state = $conn->real_escape_string($data[5]);
        $payment_date = parseDate($data[6]);
        $next_payment_date = parseDate($data[7]);
        $contract_amount = is_numeric(str_replace(',', '', $data[8])) ? str_replace(',', '', $data[8]) : 0;
        $first_noe = parseDate($data[10]);
        $final_noe = parseDate($data[12]);
        
        // Check if contact already exists
        $checkSql = "SELECT id FROM contacts WHERE name = ? AND email = ? AND contact_type = 'clients'";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ss", $name, $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            // Update existing contact
            $row = $checkResult->fetch_assoc();
            $contactId = $row['id'];
            
            $updateSql = "UPDATE contacts SET 
                rep = ?,
                phone_number = ?,
                address = ?,
                state = ?,
                payment_date = ?,
                next_payment_date = ?,
                contract_amount = ?,
                first_noe = ?,
                final_noe = ?
                WHERE id = ?";
                
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("ssssssdssi", 
                $rep, $phone, $address, $state, $payment_date, $next_payment_date, 
                $contract_amount, $first_noe, $final_noe, $contactId);
            
            if ($updateStmt->execute()) {
                $count++;
            }
        } else {
            // Insert new contact
            $insertSql = "INSERT INTO contacts (
                contact_type, name, rep, email, phone_number, address, state,
                payment_date, next_payment_date, contract_amount, first_noe, final_noe
            ) VALUES (
                'clients', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )";
            
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("ssssssssdss", 
                $name, $rep, $email, $phone, $address, $state,
                $payment_date, $next_payment_date, $contract_amount, $first_noe, $final_noe);
            
            if ($insertStmt->execute()) {
                $count++;
            }
        }
    }
    
    fclose($file);
    return $count;
}

// Function to import prospects CSV
function importProspectsCSV($conn) {
    $file = fopen('csvs/prospects.csv', 'r');
    
    // Skip header row
    fgetcsv($file);
    
    $count = 0;
    
    while (($data = fgetcsv($file)) !== FALSE) {
        // Skip empty rows
        if (empty($data[0])) {
            continue;
        }
        
        // Check if we have enough data
        if (count($data) < 8) {
            continue;
        }
        
        $name = $conn->real_escape_string($data[0]);
        $rep = $conn->real_escape_string($data[1]);
        $interest_level = is_numeric($data[2]) ? $data[2] : 0;
        $initial_contact_date = parseDate($data[3]);
        $obstacle = $conn->real_escape_string($data[4]);
        $update_date = parseDate($data[5]);
        $call_back_date = parseDate($data[6]);
        $email = $conn->real_escape_string($data[7]);
        $phone = isset($data[8]) ? $conn->real_escape_string($data[8]) : '';
        $address = isset($data[9]) ? $conn->real_escape_string($data[9]) : '';
        $loan_institution = isset($data[10]) ? $conn->real_escape_string($data[10]) : '';
        
        // Determine step based on Y/N fields
        $step = 0;
        if (isset($data[11]) && $data[11] == 'Y') $step = 1; // Loan Docs Requested
        if (isset($data[14]) && $data[14] == 'Y') $step = 2; // Loan Docs Received
        if (isset($data[15]) && $data[15] == 'Y') $step = 3; // Contract Generated
        if (isset($data[16]) && $data[16] == 'Y') $step = 4; // Contract Sent
        
        $past_due = isset($data[12]) && $data[12] == 'Y' ? 'Y' : 'N';
        $additional_notes = isset($data[19]) ? $conn->real_escape_string($data[19]) : '';
        
        // Check if contact already exists
        $checkSql = "SELECT id FROM contacts WHERE name = ? AND contact_type = 'prospects'";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $name);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            // Update existing contact
            $row = $checkResult->fetch_assoc();
            $contactId = $row['id'];
            
            $updateSql = "UPDATE contacts SET 
                rep = ?,
                interest_level = ?,
                initial_contact_date = ?,
                obstacle = ?,
                update_date = ?,
                call_back_date = ?,
                email = ?,
                phone_number = ?,
                address = ?,
                loan_institution = ?,
                step = ?,
                past_due_on_loan = ?,
                additional_notes = ?
                WHERE id = ?";
                
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("sisssssssssssi", 
                $rep, $interest_level, $initial_contact_date, $obstacle, $update_date, $call_back_date,
                $email, $phone, $address, $loan_institution, $step, $past_due, $additional_notes, $contactId);
            
            if ($updateStmt->execute()) {
                $count++;
            }
        } else {
            // Insert new contact
            $insertSql = "INSERT INTO contacts (
                contact_type, name, rep, interest_level, initial_contact_date, obstacle,
                update_date, call_back_date, email, phone_number, address, loan_institution,
                step, past_due_on_loan, additional_notes
            ) VALUES (
                'prospects', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )";
            
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("ssissssssssiss", 
                $name, $rep, $interest_level, $initial_contact_date, $obstacle,
                $update_date, $call_back_date, $email, $phone, $address, $loan_institution,
                $step, $past_due, $additional_notes);
            
            if ($insertStmt->execute()) {
                $count++;
            }
        }
    }
    
    fclose($file);
    return $count;
}

// Function to import closed CSV
function importClosedCSV($conn) {
    $file = fopen('csvs/closed.csv', 'r');
    
    // Skip header row
    fgetcsv($file);
    
    $count = 0;
    
    while (($data = fgetcsv($file)) !== FALSE) {
        // Skip empty rows
        if (empty($data[0])) {
            continue;
        }
        
        // Check if we have enough data
        if (count($data) < 5) {
            continue;
        }
        
        $name = $conn->real_escape_string($data[0]);
        $rep = $conn->real_escape_string($data[1]);
        $interest_level = is_numeric($data[2]) ? $data[2] : 0;
        $initial_contact_date = parseDate($data[4]);
        $obstacle = $conn->real_escape_string($data[5]);
        $update_date = parseDate($data[6]);
        $call_back_date = parseDate($data[7]);
        $email = isset($data[8]) ? $conn->real_escape_string($data[8]) : '';
        $phone = isset($data[9]) ? $conn->real_escape_string($data[9]) : '';
        $address = isset($data[10]) ? $conn->real_escape_string($data[10]) : '';
        $loan_institution = isset($data[11]) ? $conn->real_escape_string($data[11]) : '';
        $additional_notes = isset($data[20]) ? $conn->real_escape_string($data[20]) : '';
        
        // Check if contact already exists
        $checkSql = "SELECT id FROM contacts WHERE name = ? AND contact_type = 'closed'";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $name);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            // Update existing contact
            $row = $checkResult->fetch_assoc();
            $contactId = $row['id'];
            
            $updateSql = "UPDATE contacts SET 
                rep = ?,
                interest_level = ?,
                initial_contact_date = ?,
                obstacle = ?,
                update_date = ?,
                call_back_date = ?,
                email = ?,
                phone_number = ?,
                address = ?,
                loan_institution = ?,
                additional_notes = ?
                WHERE id = ?";
                
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("sisssssssssi", 
                $rep, $interest_level, $initial_contact_date, $obstacle, $update_date, $call_back_date,
                $email, $phone, $address, $loan_institution, $additional_notes, $contactId);
            
            if ($updateStmt->execute()) {
                $count++;
            }
        } else {
            // Insert new contact
            $insertSql = "INSERT INTO contacts (
                contact_type, name, rep, interest_level, initial_contact_date, obstacle,
                update_date, call_back_date, email, phone_number, address, loan_institution,
                additional_notes
            ) VALUES (
                'closed', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )";
            
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("ssisssssssss", 
                $name, $rep, $interest_level, $initial_contact_date, $obstacle,
                $update_date, $call_back_date, $email, $phone, $address, $loan_institution,
                $additional_notes);
            
            if ($insertStmt->execute()) {
                $count++;
            }
        }
    }
    
    fclose($file);
    return $count;
}

// Process the import
$clientsCount = importClientsCSV($conn);
$prospectsCount = importProspectsCSV($conn);
$closedCount = importClosedCSV($conn);

// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSV Import</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4>CSV Import Results</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-success">
                            <p>Successfully imported:</p>
                            <ul>
                                <li><?php echo $clientsCount; ?> clients</li>
                                <li><?php echo $prospectsCount; ?> prospects</li>
                                <li><?php echo $closedCount; ?> closed contacts</li>
                            </ul>
                        </div>
                        <a href="index.php" class="btn btn-primary">Go to CRM Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

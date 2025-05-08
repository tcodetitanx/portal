<?php
session_start();

// Check if user is authenticated
if (!isset($_SESSION["authenticated"]) || $_SESSION["authenticated"] !== true) {
    header("Location: ../login.php");
    exit();
}

// Include database configuration
$conn = require_once('config/db_config.php');

// Get a sample contact for testing
$sql = "SELECT * FROM contacts LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    echo "No contacts found for testing. Please add a contact first.";
    exit();
}

$contact = $result->fetch_assoc();
$contact_id = $contact['id'];

// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test New Buttons - CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Test New Buttons</h1>
        <p>This page allows you to test the new buttons added to the CRM.</p>
        <p>Using contact: <strong><?php echo htmlspecialchars($contact['name']); ?></strong> (ID: <?php echo $contact_id; ?>)</p>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5>Legal Actions</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <button type="button" class="btn btn-warning" id="send_first_noe_btn">Send NOE</button>
                    <p class="mt-2">This button will generate a Notice of Error PDF and send it to the contact's email.</p>
                </div>
                
                <div class="mb-3">
                    <button type="button" class="btn btn-danger" id="send_final_noe_btn">Send Final NOE</button>
                    <p class="mt-2">This button will generate a Final Notice of Error PDF with state-specific statutes and send it to the contact's email.</p>
                </div>
                
                <div class="mb-3">
                    <button type="button" class="btn btn-dark" id="send_lawsuit_btn">Send Law Suit</button>
                    <p class="mt-2">This button will generate a lawsuit document (currently a placeholder).</p>
                </div>
                
                <div class="mb-3">
                    <a href="view_documents.php?id=<?php echo $contact_id; ?>" class="btn btn-secondary" target="_blank">View Documents</a>
                    <p class="mt-2">This button will open a page showing all documents associated with the contact.</p>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5>Lenders Management</h5>
            </div>
            <div class="card-body">
                <a href="lenders.php" class="btn btn-info" target="_blank">Manage Lenders</a>
                <p class="mt-2">This button will open the lenders management page where you can add, edit, and delete lenders.</p>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5>State Statutes Management</h5>
            </div>
            <div class="card-body">
                <a href="state_statutes.php" class="btn btn-primary" target="_blank">Manage State Statutes</a>
                <p class="mt-2">This button will open the state statutes management page where you can add, edit, and delete state-specific statutes for Final NOE.</p>
            </div>
        </div>
        
        <div class="mt-4">
            <a href="index.php" class="btn btn-secondary">Back to CRM</a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Handle send first NOE button
            $('#send_first_noe_btn').click(function() {
                const contactId = <?php echo $contact_id; ?>;
                
                // Confirm before sending
                if (confirm('Are you sure you want to generate and send a Notice of Error to this contact?')) {
                    // First, open the NOE in a new window for preview
                    window.open(`generate_noe.php?id=${contactId}&type=first`, '_blank');
                    
                    // Then send the email
                    $.ajax({
                        url: 'send_noe.php',
                        type: 'POST',
                        data: { 
                            id: contactId,
                            type: 'first'
                        },
                        success: function(response) {
                            try {
                                const result = JSON.parse(response);
                                if (result.success) {
                                    alert(result.message);
                                } else {
                                    alert('Error: ' + result.message);
                                }
                            } catch (e) {
                                alert('Error parsing response: ' + response);
                            }
                        },
                        error: function() {
                            alert('Error sending Notice of Error. Please try again.');
                        }
                    });
                }
            });
            
            // Handle send final NOE button
            $('#send_final_noe_btn').click(function() {
                const contactId = <?php echo $contact_id; ?>;
                
                // Confirm before sending
                if (confirm('Are you sure you want to generate and send a Final Notice of Error to this contact?')) {
                    // First, open the NOE in a new window for preview
                    window.open(`generate_noe.php?id=${contactId}&type=final`, '_blank');
                    
                    // Then send the email
                    $.ajax({
                        url: 'send_noe.php',
                        type: 'POST',
                        data: { 
                            id: contactId,
                            type: 'final'
                        },
                        success: function(response) {
                            try {
                                const result = JSON.parse(response);
                                if (result.success) {
                                    alert(result.message);
                                } else {
                                    alert('Error: ' + result.message);
                                }
                            } catch (e) {
                                alert('Error parsing response: ' + response);
                            }
                        },
                        error: function() {
                            alert('Error sending Final Notice of Error. Please try again.');
                        }
                    });
                }
            });
            
            // Handle send lawsuit button
            $('#send_lawsuit_btn').click(function() {
                const contactId = <?php echo $contact_id; ?>;
                
                // Confirm before sending
                if (confirm('Are you sure you want to generate a lawsuit document for this contact?')) {
                    // Open the lawsuit document in a new window
                    window.open(`generate_lawsuit.php?id=${contactId}`, '_blank');
                }
            });
        });
    </script>
</body>
</html>

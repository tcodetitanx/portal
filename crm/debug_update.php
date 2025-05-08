<?php
session_start();

// Check if user is authenticated
if (!isset($_SESSION["authenticated"]) || $_SESSION["authenticated"] !== true) {
    header("Location: ../login.php");
    exit();
}

// Include database configuration
$conn = require_once('config/db_config.php');

// Function to check if a column exists in a table
function columnExists($conn, $table, $column) {
    $sql = "SHOW COLUMNS FROM $table LIKE '$column'";
    $result = $conn->query($sql);
    return $result->num_rows > 0;
}

// Get all columns from the contacts table
$sql = "SHOW COLUMNS FROM contacts";
$result = $conn->query($sql);
$columns = [];
$column_details = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
    $column_details[] = $row;
}

// Check specific columns
$has_suit_filed = in_array('suit_filed', $columns);
$has_suit_filed_tracking_number = in_array('suit_filed_tracking_number', $columns);
$has_suit_filed_tracking_confirmed = in_array('suit_filed_tracking_confirmed', $columns);
$has_lender_id = in_array('lender_id', $columns);

// Get a sample contact for testing
$sample_contact = null;
$sql = "SELECT * FROM contacts LIMIT 1";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $sample_contact = $result->fetch_assoc();
}

// Check for any errors in the session
$error_message = isset($_SESSION['error']) ? $_SESSION['error'] : '';
if (!empty($error_message)) {
    unset($_SESSION['error']);
}

// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Update - CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .debug-info {
            font-family: monospace;
            white-space: pre-wrap;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>Debug Update Function</h1>

        <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger">
            <h4>Error Detected</h4>
            <pre><?php echo htmlspecialchars($error_message); ?></pre>
        </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header">
                <h5>Database Column Check</h5>
            </div>
            <div class="card-body">
                <p>This page checks the database structure and helps debug the update function.</p>

                <h6>Columns in the contacts table:</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Field</th>
                                <th>Type</th>
                                <th>Null</th>
                                <th>Key</th>
                                <th>Default</th>
                                <th>Extra</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($column_details as $column): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($column['Field']); ?></td>
                                    <td><?php echo htmlspecialchars($column['Type']); ?></td>
                                    <td><?php echo htmlspecialchars($column['Null']); ?></td>
                                    <td><?php echo htmlspecialchars($column['Key']); ?></td>
                                    <td><?php echo htmlspecialchars($column['Default'] ?? 'NULL'); ?></td>
                                    <td><?php echo htmlspecialchars($column['Extra']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <h6>Special Column Checks:</h6>
                <ul>
                    <li>suit_filed exists: <?php echo $has_suit_filed ? 'Yes' : 'No'; ?></li>
                    <li>suit_filed_tracking_number exists: <?php echo $has_suit_filed_tracking_number ? 'Yes' : 'No'; ?></li>
                    <li>suit_filed_tracking_confirmed exists: <?php echo $has_suit_filed_tracking_confirmed ? 'Yes' : 'No'; ?></li>
                    <li>lender_id exists: <?php echo $has_lender_id ? 'Yes' : 'No'; ?></li>
                </ul>
            </div>
        </div>

        <?php if ($sample_contact): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5>Sample Contact Data</h5>
            </div>
            <div class="card-body">
                <p>This is a sample contact from the database to help debug field values:</p>

                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Field</th>
                                <th>Value</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sample_contact as $field => $value): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($field); ?></td>
                                    <td><?php echo htmlspecialchars($value ?? 'NULL'); ?></td>
                                    <td><?php echo gettype($value); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header">
                <h5>SQL Query Simulation</h5>
            </div>
            <div class="card-body">
                <p>This section simulates the SQL query that would be generated for an update operation.</p>

                <h6>SQL Query:</h6>
                <div class="debug-info">
UPDATE contacts SET
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
<?php if ($has_lender_id): ?>
    lender_id = ?,
<?php endif; ?>
    step = ?,
    past_due_on_loan = ?,
    additional_notes = ?,
    payment_date = ?,
    next_payment_date = ?,
    contract_amount = ?,
    first_noe = ?,
    final_noe = ?,
    court_date = ?,
<?php if ($has_suit_filed): ?>
    suit_filed = ?,
<?php endif; ?>
    status = ?,
    first_noe_tracking_number = ?,
    final_noe_tracking_number = ?,
<?php if ($has_suit_filed_tracking_number): ?>
    suit_filed_tracking_number = ?,
<?php endif; ?>
    first_noe_tracking_confirmed = ?,
    final_noe_tracking_confirmed = ?
<?php if ($has_suit_filed_tracking_confirmed): ?>,
    suit_filed_tracking_confirmed = ?
<?php endif; ?>
WHERE id = ?
                </div>

                <h6>Parameter Types String:</h6>
                <div class="debug-info">
sssississssssssi<?php if ($has_lender_id) echo 'i'; ?>isssssdsss<?php if ($has_suit_filed) echo 's'; ?>sss<?php if ($has_suit_filed_tracking_number) echo 's'; ?>ii<?php if ($has_suit_filed_tracking_confirmed) echo 'i'; ?>i
                </div>

                <h6>Parameter Count:</h6>
                <div class="debug-info">
Base parameters: 16
<?php if ($has_lender_id): ?>lender_id: +1<?php endif; ?>

Additional base parameters: +10
<?php if ($has_suit_filed): ?>suit_filed: +1<?php endif; ?>

Status and tracking numbers: +3
<?php if ($has_suit_filed_tracking_number): ?>suit_filed_tracking_number: +1<?php endif; ?>

Tracking confirmations: +2
<?php if ($has_suit_filed_tracking_confirmed): ?>suit_filed_tracking_confirmed: +1<?php endif; ?>

ID parameter: +1

Total parameters: <?php
    $count = 16 + 10 + 3 + 2 + 1;
    if ($has_lender_id) $count++;
    if ($has_suit_filed) $count++;
    if ($has_suit_filed_tracking_number) $count++;
    if ($has_suit_filed_tracking_confirmed) $count++;
    echo $count;
?>

Total placeholders in SQL: <?php
    $placeholders = 16 + 10 + 3 + 2 + 1;
    if ($has_lender_id) $placeholders++;
    if ($has_suit_filed) $placeholders++;
    if ($has_suit_filed_tracking_number) $placeholders++;
    if ($has_suit_filed_tracking_confirmed) $placeholders++;
    echo $placeholders;
?>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5>Test Update Function</h5>
            </div>
            <div class="card-body">
                <p>This section allows you to test the update function with a sample contact.</p>

                <?php if ($sample_contact): ?>
                <form action="test_update_function.php" method="post">
                    <input type="hidden" name="id" value="<?php echo $sample_contact['id']; ?>">
                    <input type="hidden" name="contact_type" value="<?php echo $sample_contact['contact_type']; ?>">
                    <input type="hidden" name="name" value="<?php echo htmlspecialchars($sample_contact['name']); ?>">
                    <input type="hidden" name="additional_notes" value="Test update at <?php echo date('Y-m-d H:i:s'); ?>">

                    <div class="alert alert-warning">
                        <p><strong>Warning:</strong> This will update the sample contact with test data. The contact's additional notes will be updated with a timestamp.</p>
                    </div>

                    <button type="submit" class="btn btn-primary">Test Update Function</button>
                </form>
                <?php else: ?>
                <div class="alert alert-info">
                    <p>No sample contact found. Please add a contact to the database first.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-4">
            <a href="fix_database.php" class="btn btn-warning me-2">Run Database Fix</a>
            <a href="index.php" class="btn btn-primary">Back to CRM</a>
        </div>
    </div>
</body>
</html>

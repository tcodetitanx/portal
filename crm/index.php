<?php
session_start();

// Check if user is authenticated
if (!isset($_SESSION["authenticated"]) || $_SESSION["authenticated"] !== true) {
    header("Location: ../login.php");
    exit();
}

// Include database configuration
$conn = require_once('config/db_config.php');

// Get the current contact type (default to clients)
$contact_type = isset($_GET['type']) ? $_GET['type'] : 'clients';

// Validate contact type
if (!in_array($contact_type, ['clients', 'prospects', 'closed'])) {
    $contact_type = 'clients';
}

// Get sorting parameters
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'name';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'ASC';

// Validate sort column against allowed columns
$allowed_columns = ['name', 'rep', 'interest_level', 'initial_contact_date', 'update_date', 
                   'call_back_date', 'email', 'phone_number', 'address', 'city', 'state', 
                   'loan_institution', 'step', 'payment_date', 'next_payment_date', 
                   'contract_amount', 'first_noe', 'final_noe', 'court_date', 'status'];

if (!in_array($sort_column, $allowed_columns)) {
    $sort_column = 'name';
}

// Validate sort order
if (!in_array($sort_order, ['ASC', 'DESC'])) {
    $sort_order = 'ASC';
}

// Build the query based on contact type
$sql = "SELECT * FROM contacts WHERE contact_type = ? ORDER BY $sort_column $sort_order";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $contact_type);
$stmt->execute();
$result = $stmt->get_result();

// Get all reps for dropdown
$reps_sql = "SELECT * FROM reps ORDER BY name";
$reps_result = $conn->query($reps_sql);
$reps = [];
while ($rep = $reps_result->fetch_assoc()) {
    $reps[] = $rep;
}

// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .table-responsive {
            overflow-x: auto;
        }
        .sort-icon {
            margin-left: 5px;
        }
        .step-indicator {
            display: inline-block;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: #ccc;
            margin-right: 5px;
        }
        .step-active {
            background-color: #28a745;
        }
        .date-warning {
            background-color: #dc3545;
            color: white;
        }
        .date-success {
            background-color: #28a745;
            color: white;
        }
        .edit-icon {
            cursor: pointer;
            color: #007bff;
        }
        .modal-xl {
            max-width: 90%;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row mb-4">
            <div class="col-md-6">
                <h1>CRM System</h1>
            </div>
            <div class="col-md-6 text-end">
                <a href="../portal.php" class="btn btn-secondary">Back to Portal</a>
                <a href="import_csv.php" class="btn btn-success">Import CSV Data</a>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addContactModal">
                    Add New Contact
                </button>
            </div>
        </div>

        <!-- Contact Type Tabs -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link <?php echo $contact_type == 'clients' ? 'active' : ''; ?>" href="?type=clients">
                    Clients
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $contact_type == 'prospects' ? 'active' : ''; ?>" href="?type=prospects">
                    Prospects
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $contact_type == 'closed' ? 'active' : ''; ?>" href="?type=closed">
                    Closed
                </a>
            </li>
        </ul>

        <!-- Contacts Table -->
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>
                            <a href="?type=<?php echo $contact_type; ?>&sort=name&order=<?php echo $sort_column == 'name' && $sort_order == 'ASC' ? 'DESC' : 'ASC'; ?>">
                                Name
                                <?php if ($sort_column == 'name'): ?>
                                    <i class="fas fa-sort-<?php echo $sort_order == 'ASC' ? 'up' : 'down'; ?> sort-icon"></i>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th>
                            <a href="?type=<?php echo $contact_type; ?>&sort=rep&order=<?php echo $sort_column == 'rep' && $sort_order == 'ASC' ? 'DESC' : 'ASC'; ?>">
                                Rep
                                <?php if ($sort_column == 'rep'): ?>
                                    <i class="fas fa-sort-<?php echo $sort_order == 'ASC' ? 'up' : 'down'; ?> sort-icon"></i>
                                <?php endif; ?>
                            </a>
                        </th>
                        <?php if ($contact_type != 'clients'): ?>
                        <th>
                            <a href="?type=<?php echo $contact_type; ?>&sort=interest_level&order=<?php echo $sort_column == 'interest_level' && $sort_order == 'ASC' ? 'DESC' : 'ASC'; ?>">
                                Interest
                                <?php if ($sort_column == 'interest_level'): ?>
                                    <i class="fas fa-sort-<?php echo $sort_order == 'ASC' ? 'up' : 'down'; ?> sort-icon"></i>
                                <?php endif; ?>
                            </a>
                        </th>
                        <?php endif; ?>
                        <th>
                            <a href="?type=<?php echo $contact_type; ?>&sort=phone_number&order=<?php echo $sort_column == 'phone_number' && $sort_order == 'ASC' ? 'DESC' : 'ASC'; ?>">
                                Phone
                                <?php if ($sort_column == 'phone_number'): ?>
                                    <i class="fas fa-sort-<?php echo $sort_order == 'ASC' ? 'up' : 'down'; ?> sort-icon"></i>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th>
                            <a href="?type=<?php echo $contact_type; ?>&sort=email&order=<?php echo $sort_column == 'email' && $sort_order == 'ASC' ? 'DESC' : 'ASC'; ?>">
                                Email
                                <?php if ($sort_column == 'email'): ?>
                                    <i class="fas fa-sort-<?php echo $sort_order == 'ASC' ? 'up' : 'down'; ?> sort-icon"></i>
                                <?php endif; ?>
                            </a>
                        </th>
                        <?php if ($contact_type == 'clients'): ?>
                        <th>
                            <a href="?type=<?php echo $contact_type; ?>&sort=payment_date&order=<?php echo $sort_column == 'payment_date' && $sort_order == 'ASC' ? 'DESC' : 'ASC'; ?>">
                                Payment Date
                                <?php if ($sort_column == 'payment_date'): ?>
                                    <i class="fas fa-sort-<?php echo $sort_order == 'ASC' ? 'up' : 'down'; ?> sort-icon"></i>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th>
                            <a href="?type=<?php echo $contact_type; ?>&sort=next_payment_date&order=<?php echo $sort_column == 'next_payment_date' && $sort_order == 'ASC' ? 'DESC' : 'ASC'; ?>">
                                Next Payment
                                <?php if ($sort_column == 'next_payment_date'): ?>
                                    <i class="fas fa-sort-<?php echo $sort_order == 'ASC' ? 'up' : 'down'; ?> sort-icon"></i>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th>
                            <a href="?type=<?php echo $contact_type; ?>&sort=first_noe&order=<?php echo $sort_column == 'first_noe' && $sort_order == 'ASC' ? 'DESC' : 'ASC'; ?>">
                                First NOE
                                <?php if ($sort_column == 'first_noe'): ?>
                                    <i class="fas fa-sort-<?php echo $sort_order == 'ASC' ? 'up' : 'down'; ?> sort-icon"></i>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th>
                            <a href="?type=<?php echo $contact_type; ?>&sort=final_noe&order=<?php echo $sort_column == 'final_noe' && $sort_order == 'ASC' ? 'DESC' : 'ASC'; ?>">
                                Final NOE
                                <?php if ($sort_column == 'final_noe'): ?>
                                    <i class="fas fa-sort-<?php echo $sort_order == 'ASC' ? 'up' : 'down'; ?> sort-icon"></i>
                                <?php endif; ?>
                            </a>
                        </th>
                        <?php else: ?>
                        <th>
                            <a href="?type=<?php echo $contact_type; ?>&sort=initial_contact_date&order=<?php echo $sort_column == 'initial_contact_date' && $sort_order == 'ASC' ? 'DESC' : 'ASC'; ?>">
                                Initial Contact
                                <?php if ($sort_column == 'initial_contact_date'): ?>
                                    <i class="fas fa-sort-<?php echo $sort_order == 'ASC' ? 'up' : 'down'; ?> sort-icon"></i>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th>
                            <a href="?type=<?php echo $contact_type; ?>&sort=call_back_date&order=<?php echo $sort_column == 'call_back_date' && $sort_order == 'ASC' ? 'DESC' : 'ASC'; ?>">
                                Call Back Date
                                <?php if ($sort_column == 'call_back_date'): ?>
                                    <i class="fas fa-sort-<?php echo $sort_order == 'ASC' ? 'up' : 'down'; ?> sort-icon"></i>
                                <?php endif; ?>
                            </a>
                        </th>
                        <?php if ($contact_type == 'prospects'): ?>
                        <th>Steps</th>
                        <?php endif; ?>
                        <?php endif; ?>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['rep']) . "</td>";
                            
                            if ($contact_type != 'clients') {
                                echo "<td>" . htmlspecialchars($row['interest_level']) . "</td>";
                            }
                            
                            echo "<td>" . htmlspecialchars($row['phone_number']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                            
                            if ($contact_type == 'clients') {
                                echo "<td>" . ($row['payment_date'] ? htmlspecialchars($row['payment_date']) : '') . "</td>";
                                echo "<td>" . ($row['next_payment_date'] ? htmlspecialchars($row['next_payment_date']) : '') . "</td>";
                                
                                // First NOE with color coding
                                $first_noe_class = '';
                                if ($row['first_noe']) {
                                    $first_noe_date = new DateTime($row['first_noe']);
                                    $today = new DateTime();
                                    $diff = $today->diff($first_noe_date);
                                    $days_diff = $diff->days;
                                    
                                    if ($today < $first_noe_date || $days_diff <= 20) {
                                        $first_noe_class = 'date-warning';
                                    } else {
                                        $first_noe_class = 'date-success';
                                    }
                                }
                                echo "<td class='" . $first_noe_class . "'>" . ($row['first_noe'] ? htmlspecialchars($row['first_noe']) : '') . "</td>";
                                
                                // Final NOE with color coding
                                $final_noe_class = '';
                                if ($row['final_noe']) {
                                    $final_noe_date = new DateTime($row['final_noe']);
                                    $today = new DateTime();
                                    $diff = $today->diff($final_noe_date);
                                    $days_diff = $diff->days;
                                    
                                    if ($today < $final_noe_date || $days_diff <= 20) {
                                        $final_noe_class = 'date-warning';
                                    } else {
                                        $final_noe_class = 'date-success';
                                    }
                                }
                                echo "<td class='" . $final_noe_class . "'>" . ($row['final_noe'] ? htmlspecialchars($row['final_noe']) : '') . "</td>";
                            } else {
                                echo "<td>" . ($row['initial_contact_date'] ? htmlspecialchars($row['initial_contact_date']) : '') . "</td>";
                                echo "<td>" . ($row['call_back_date'] ? htmlspecialchars($row['call_back_date']) : '') . "</td>";
                                
                                if ($contact_type == 'prospects') {
                                    echo "<td>";
                                    // Step indicators
                                    echo "<div class='step-indicator " . ($row['step'] >= 1 ? 'step-active' : '') . "' title='Loan Docs Requested'></div>";
                                    echo "<div class='step-indicator " . ($row['step'] >= 2 ? 'step-active' : '') . "' title='Loan Docs Received'></div>";
                                    echo "<div class='step-indicator " . ($row['step'] >= 3 ? 'step-active' : '') . "' title='Contract Generated'></div>";
                                    echo "<div class='step-indicator " . ($row['step'] >= 4 ? 'step-active' : '') . "' title='Contract Sent'></div>";
                                    echo "</td>";
                                }
                            }
                            
                            echo "<td><i class='fas fa-pen edit-icon' data-bs-toggle='modal' data-bs-target='#editContactModal' data-id='" . $row['id'] . "'></i></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='10' class='text-center'>No contacts found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Contact Modal -->
    <div class="modal fade" id="addContactModal" tabindex="-1" aria-labelledby="addContactModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addContactModalLabel">Add New Contact</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addContactForm" action="save_contact.php" method="post">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="contact_type" class="form-label">Contact Type</label>
                                <select class="form-select" id="contact_type" name="contact_type" required>
                                    <option value="clients">Client</option>
                                    <option value="prospects">Prospect</option>
                                    <option value="closed">Closed</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="rep" class="form-label">Representative</label>
                                <select class="form-select" id="rep" name="rep">
                                    <option value="">Select Rep</option>
                                    <?php foreach ($reps as $rep): ?>
                                        <option value="<?php echo htmlspecialchars($rep['name']); ?>"><?php echo htmlspecialchars($rep['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="phone_number" class="form-label">Phone Number</label>
                                <input type="text" class="form-control" id="phone_number" name="phone_number">
                            </div>
                            <div class="col-md-6">
                                <label for="interest_level" class="form-label">Interest Level (0-10)</label>
                                <input type="number" class="form-control" id="interest_level" name="interest_level" min="0" max="10">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="address" name="address">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city">
                            </div>
                            <div class="col-md-4">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control" id="state" name="state">
                            </div>
                            <div class="col-md-4">
                                <label for="zip" class="form-label">ZIP</label>
                                <input type="text" class="form-control" id="zip" name="zip">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="loan_institution" class="form-label">Loan Institution</label>
                                <input type="text" class="form-control" id="loan_institution" name="loan_institution">
                            </div>
                            <div class="col-md-6">
                                <label for="step" class="form-label">Step</label>
                                <select class="form-select" id="step" name="step">
                                    <option value="0">None</option>
                                    <option value="1">Loan Docs Requested</option>
                                    <option value="2">Loan Docs Received</option>
                                    <option value="3">Contract Generated</option>
                                    <option value="4">Contract Sent</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="additional_notes" class="form-label">Additional Notes</label>
                                <textarea class="form-control" id="additional_notes" name="additional_notes" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Contact</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Contact Modal (will be populated via AJAX) -->
    <div class="modal fade" id="editContactModal" tabindex="-1" aria-labelledby="editContactModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editContactModalLabel">Edit Contact</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Content will be loaded via AJAX -->
                    <div id="editContactContent">Loading...</div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Handle edit icon click
            $('.edit-icon').click(function() {
                const contactId = $(this).data('id');
                $('#editContactContent').html('Loading...');
                
                // Load contact details via AJAX
                $.ajax({
                    url: 'get_contact.php',
                    type: 'GET',
                    data: { id: contactId },
                    success: function(response) {
                        $('#editContactContent').html(response);
                    },
                    error: function() {
                        $('#editContactContent').html('Error loading contact details. Please try again.');
                    }
                });
            });
        });
    </script>
</body>
</html>

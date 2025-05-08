<?php
session_start();

// Check if user is authenticated
if (!isset($_SESSION["authenticated"]) || $_SESSION["authenticated"] !== true) {
    header("Location: ../login.php");
    exit();
}

// Include database configuration
$conn = require_once('config/db_config.php');

// Handle form submission for adding/editing state statutes
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        // Sanitize input
        function sanitizeInput($conn, $input) {
            return $conn->real_escape_string(trim($input));
        }

        if ($_POST['action'] == 'add' || $_POST['action'] == 'edit') {
            // Add or edit state statute
            $state_code = sanitizeInput($conn, $_POST['state_code']);
            $state_name = sanitizeInput($conn, $_POST['state_name']);
            $statute_text = sanitizeInput($conn, $_POST['statute_text']);

            if ($_POST['action'] == 'add') {
                $sql = "INSERT INTO state_statutes (state_code, state_name, statute_text) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sss", $state_code, $state_name, $statute_text);
            } else {
                $id = intval($_POST['id']);
                $sql = "UPDATE state_statutes SET state_code = ?, state_name = ?, statute_text = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssi", $state_code, $state_name, $statute_text, $id);
            }

            if ($stmt->execute()) {
                $_SESSION['success'] = "State statute " . ($_POST['action'] == 'add' ? "added" : "updated") . " successfully";
            } else {
                $_SESSION['error'] = "Error " . ($_POST['action'] == 'add' ? "adding" : "updating") . " state statute: " . $conn->error;
            }
        } elseif ($_POST['action'] == 'delete') {
            // Delete state statute
            $id = intval($_POST['id']);
            $sql = "DELETE FROM state_statutes WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                $_SESSION['success'] = "State statute deleted successfully";
            } else {
                $_SESSION['error'] = "Error deleting state statute: " . $conn->error;
            }
        }
    }

    // Redirect to refresh the page
    header("Location: state_statutes.php");
    exit();
}

// Get all state statutes
$sql = "SELECT * FROM state_statutes ORDER BY state_name";
$result = $conn->query($sql);
$statutes = [];
while ($statute = $result->fetch_assoc()) {
    $statutes[] = $statute;
}

// Close the database connection
mysqli_close($conn);

// US States array for dropdown
$us_states = [
    'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas', 'CA' => 'California',
    'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware', 'FL' => 'Florida', 'GA' => 'Georgia',
    'HI' => 'Hawaii', 'ID' => 'Idaho', 'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa',
    'KS' => 'Kansas', 'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland',
    'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi', 'MO' => 'Missouri',
    'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada', 'NH' => 'New Hampshire', 'NJ' => 'New Jersey',
    'NM' => 'New Mexico', 'NY' => 'New York', 'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio',
    'OK' => 'Oklahoma', 'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina',
    'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah', 'VT' => 'Vermont',
    'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia', 'WI' => 'Wisconsin', 'WY' => 'Wyoming',
    'DC' => 'District of Columbia'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>State Statutes Management - CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .container {
            max-width: 1200px;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .statute-text {
            max-height: 100px;
            overflow-y: auto;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>State Statutes Management</h1>
            <a href="index.php" class="btn btn-secondary">Back to CRM</a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Add New State Statute</h5>
            </div>
            <div class="card-body">
                <form action="state_statutes.php" method="post">
                    <input type="hidden" name="action" value="add">
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label for="state_code" class="form-label">State Code</label>
                            <select class="form-select" id="state_code" name="state_code" required>
                                <option value="">Select State</option>
                                <?php foreach ($us_states as $code => $name): ?>
                                    <option value="<?php echo $code; ?>"><?php echo $code . ' - ' . $name; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="state_name" class="form-label">State Name</label>
                            <input type="text" class="form-control" id="state_name" name="state_name" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="statute_text" class="form-label">Statute Text</label>
                            <textarea class="form-control" id="statute_text" name="statute_text" rows="10" required></textarea>
                            <div class="form-text">Enter the state-specific statute text for Final NOE. This will be included in the Final NOE document.</div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Add State Statute</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">State Statutes List</h5>
            </div>
            <div class="card-body">
                <?php if (count($statutes) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>State Code</th>
                                    <th>State Name</th>
                                    <th>Statute Text</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($statutes as $statute): ?>
                                    <tr>
                                        <td><?php echo $statute['id']; ?></td>
                                        <td><?php echo htmlspecialchars($statute['state_code']); ?></td>
                                        <td><?php echo htmlspecialchars($statute['state_name']); ?></td>
                                        <td>
                                            <div class="statute-text"><?php echo nl2br(htmlspecialchars($statute['statute_text'])); ?></div>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary edit-statute-btn" 
                                                data-id="<?php echo $statute['id']; ?>"
                                                data-code="<?php echo htmlspecialchars($statute['state_code']); ?>"
                                                data-name="<?php echo htmlspecialchars($statute['state_name']); ?>"
                                                data-text="<?php echo htmlspecialchars($statute['statute_text']); ?>">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-statute-btn" 
                                                data-id="<?php echo $statute['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($statute['state_name']); ?>">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center">No state statutes found. Add your first state statute using the form above.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Edit State Statute Modal -->
    <div class="modal fade" id="editStatuteModal" tabindex="-1" aria-labelledby="editStatuteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editStatuteModalLabel">Edit State Statute</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editStatuteForm" action="state_statutes.php" method="post">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="row mb-3">
                            <div class="col-md-2">
                                <label for="edit_state_code" class="form-label">State Code</label>
                                <select class="form-select" id="edit_state_code" name="state_code" required>
                                    <option value="">Select State</option>
                                    <?php foreach ($us_states as $code => $name): ?>
                                        <option value="<?php echo $code; ?>"><?php echo $code . ' - ' . $name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="edit_state_name" class="form-label">State Name</label>
                                <input type="text" class="form-control" id="edit_state_name" name="state_name" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="edit_statute_text" class="form-label">Statute Text</label>
                                <textarea class="form-control" id="edit_statute_text" name="statute_text" rows="15" required></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="editStatuteForm" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete State Statute Modal -->
    <div class="modal fade" id="deleteStatuteModal" tabindex="-1" aria-labelledby="deleteStatuteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteStatuteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the statute for <strong id="delete_statute_name"></strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteStatuteForm" action="state_statutes.php" method="post">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_id">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Auto-fill state name when state code is selected
            $('#state_code').change(function() {
                const stateCode = $(this).val();
                const stateName = $('#state_code option:selected').text().split(' - ')[1];
                $('#state_name').val(stateName);
            });
            
            // Handle edit statute button
            $('.edit-statute-btn').click(function() {
                const id = $(this).data('id');
                const code = $(this).data('code');
                const name = $(this).data('name');
                const text = $(this).data('text');
                
                $('#edit_id').val(id);
                $('#edit_state_code').val(code);
                $('#edit_state_name').val(name);
                $('#edit_statute_text').val(text);
                
                $('#editStatuteModal').modal('show');
            });
            
            // Handle delete statute button
            $('.delete-statute-btn').click(function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                
                $('#delete_id').val(id);
                $('#delete_statute_name').text(name);
                
                $('#deleteStatuteModal').modal('show');
            });
            
            // Auto-fill state name when state code is selected in edit form
            $('#edit_state_code').change(function() {
                const stateCode = $(this).val();
                const stateName = $('#edit_state_code option:selected').text().split(' - ')[1];
                $('#edit_state_name').val(stateName);
            });
        });
    </script>
</body>
</html>

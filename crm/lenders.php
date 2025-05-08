<?php
session_start();

// Check if user is authenticated
if (!isset($_SESSION["authenticated"]) || $_SESSION["authenticated"] !== true) {
    header("Location: ../login.php");
    exit();
}

// Include database configuration
$conn = require_once('config/db_config.php');

// Handle form submission for adding/editing lenders
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        // Sanitize input
        function sanitizeInput($conn, $input) {
            return $conn->real_escape_string(trim($input));
        }

        if ($_POST['action'] == 'add') {
            // Add new lender
            $lender_name = sanitizeInput($conn, $_POST['lender_name']);
            $phone_number = sanitizeInput($conn, $_POST['phone_number']);
            $street_address = sanitizeInput($conn, $_POST['street_address']);
            $city = sanitizeInput($conn, $_POST['city']);
            $state = sanitizeInput($conn, $_POST['state']);
            $zip = sanitizeInput($conn, $_POST['zip']);

            $sql = "INSERT INTO lenders (lender_name, phone_number, street_address, city, state, zip) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $lender_name, $phone_number, $street_address, $city, $state, $zip);

            if ($stmt->execute()) {
                $_SESSION['success'] = "Lender added successfully";
            } else {
                $_SESSION['error'] = "Error adding lender: " . $conn->error;
            }
        } elseif ($_POST['action'] == 'edit') {
            // Edit existing lender
            $id = intval($_POST['id']);
            $lender_name = sanitizeInput($conn, $_POST['lender_name']);
            $phone_number = sanitizeInput($conn, $_POST['phone_number']);
            $street_address = sanitizeInput($conn, $_POST['street_address']);
            $city = sanitizeInput($conn, $_POST['city']);
            $state = sanitizeInput($conn, $_POST['state']);
            $zip = sanitizeInput($conn, $_POST['zip']);

            $sql = "UPDATE lenders SET lender_name = ?, phone_number = ?, street_address = ?, city = ?, state = ?, zip = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssi", $lender_name, $phone_number, $street_address, $city, $state, $zip, $id);

            if ($stmt->execute()) {
                $_SESSION['success'] = "Lender updated successfully";
            } else {
                $_SESSION['error'] = "Error updating lender: " . $conn->error;
            }
        } elseif ($_POST['action'] == 'delete') {
            // Delete lender
            $id = intval($_POST['id']);

            // First, update any contacts that use this lender to set lender_id to NULL
            $sql = "UPDATE contacts SET lender_id = NULL WHERE lender_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();

            // Then delete the lender
            $sql = "DELETE FROM lenders WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                $_SESSION['success'] = "Lender deleted successfully";
            } else {
                $_SESSION['error'] = "Error deleting lender: " . $conn->error;
            }
        }
    }

    // Redirect to refresh the page
    header("Location: lenders.php");
    exit();
}

// Get all lenders
$sql = "SELECT * FROM lenders ORDER BY lender_name";
$result = $conn->query($sql);
$lenders = [];
while ($lender = $result->fetch_assoc()) {
    $lenders[] = $lender;
}

// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lenders Management - CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .container {
            max-width: 1200px;
        }
        .table th, .table td {
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Lenders Management</h1>
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
                <h5 class="mb-0">Add New Lender</h5>
            </div>
            <div class="card-body">
                <form action="lenders.php" method="post">
                    <input type="hidden" name="action" value="add">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="lender_name" class="form-label">Lender Name</label>
                            <input type="text" class="form-control" id="lender_name" name="lender_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="phone_number" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="phone_number" name="phone_number">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="street_address" class="form-label">Street Address</label>
                            <input type="text" class="form-control" id="street_address" name="street_address">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-5">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city">
                        </div>
                        <div class="col-md-3">
                            <label for="state" class="form-label">State</label>
                            <input type="text" class="form-control" id="state" name="state">
                        </div>
                        <div class="col-md-4">
                            <label for="zip" class="form-label">ZIP Code</label>
                            <input type="text" class="form-control" id="zip" name="zip">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Add Lender</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Lenders List</h5>
            </div>
            <div class="card-body">
                <?php if (count($lenders) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Lender Name</th>
                                    <th>Phone Number</th>
                                    <th>Street Address</th>
                                    <th>City</th>
                                    <th>State</th>
                                    <th>ZIP</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lenders as $lender): ?>
                                    <tr>
                                        <td><?php echo $lender['id']; ?></td>
                                        <td><?php echo htmlspecialchars($lender['lender_name']); ?></td>
                                        <td><?php echo htmlspecialchars($lender['phone_number']); ?></td>
                                        <td><?php echo htmlspecialchars($lender['street_address']); ?></td>
                                        <td><?php echo htmlspecialchars($lender['city']); ?></td>
                                        <td><?php echo htmlspecialchars($lender['state']); ?></td>
                                        <td><?php echo htmlspecialchars($lender['zip']); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary edit-lender-btn"
                                                data-id="<?php echo $lender['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($lender['lender_name']); ?>"
                                                data-phone="<?php echo htmlspecialchars($lender['phone_number']); ?>"
                                                data-street-address="<?php echo htmlspecialchars($lender['street_address']); ?>"
                                                data-city="<?php echo htmlspecialchars($lender['city']); ?>"
                                                data-state="<?php echo htmlspecialchars($lender['state']); ?>"
                                                data-zip="<?php echo htmlspecialchars($lender['zip']); ?>">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-lender-btn"
                                                data-id="<?php echo $lender['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($lender['lender_name']); ?>">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center">No lenders found. Add your first lender using the form above.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Edit Lender Modal -->
    <div class="modal fade" id="editLenderModal" tabindex="-1" aria-labelledby="editLenderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editLenderModalLabel">Edit Lender</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editLenderForm" action="lenders.php" method="post">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label for="edit_lender_name" class="form-label">Lender Name</label>
                            <input type="text" class="form-control" id="edit_lender_name" name="lender_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_phone_number" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="edit_phone_number" name="phone_number">
                        </div>
                        <div class="mb-3">
                            <label for="edit_street_address" class="form-label">Street Address</label>
                            <input type="text" class="form-control" id="edit_street_address" name="street_address">
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-5">
                                <label for="edit_city" class="form-label">City</label>
                                <input type="text" class="form-control" id="edit_city" name="city">
                            </div>
                            <div class="col-md-3">
                                <label for="edit_state" class="form-label">State</label>
                                <input type="text" class="form-control" id="edit_state" name="state">
                            </div>
                            <div class="col-md-4">
                                <label for="edit_zip" class="form-label">ZIP Code</label>
                                <input type="text" class="form-control" id="edit_zip" name="zip">
                            </div>
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="editLenderForm" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Lender Modal -->
    <div class="modal fade" id="deleteLenderModal" tabindex="-1" aria-labelledby="deleteLenderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteLenderModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the lender <strong id="delete_lender_name"></strong>?</p>
                    <p class="text-danger">This action cannot be undone. Any contacts associated with this lender will have their lender reference removed.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteLenderForm" action="lenders.php" method="post">
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
            // Handle edit lender button
            $('.edit-lender-btn').click(function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const phone = $(this).data('phone');
                const streetAddress = $(this).data('street-address');
                const city = $(this).data('city');
                const state = $(this).data('state');
                const zip = $(this).data('zip');

                $('#edit_id').val(id);
                $('#edit_lender_name').val(name);
                $('#edit_phone_number').val(phone);
                $('#edit_street_address').val(streetAddress);
                $('#edit_city').val(city);
                $('#edit_state').val(state);
                $('#edit_zip').val(zip);

                $('#editLenderModal').modal('show');
            });

            // Handle delete lender button
            $('.delete-lender-btn').click(function() {
                const id = $(this).data('id');
                const name = $(this).data('name');

                $('#delete_id').val(id);
                $('#delete_lender_name').text(name);

                $('#deleteLenderModal').modal('show');
            });
        });
    </script>
</body>
</html>

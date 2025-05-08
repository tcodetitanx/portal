<?php
session_start();

// Check if user is authenticated
if (!isset($_SESSION["authenticated"]) || $_SESSION["authenticated"] !== true) {
    header("Location: ../login.php");
    exit();
}

// Check if contact ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid contact ID";
    exit();
}

$contact_id = intval($_GET['id']);

// Include database configuration
$conn = require_once('config/db_config.php');

// Get contact details
$sql = "SELECT * FROM contacts WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $contact_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Contact not found";
    exit();
}

$contact = $result->fetch_assoc();

// Get all documents for this contact
$docs_sql = "SELECT * FROM documents WHERE contact_id = ? ORDER BY created_at DESC";
$docs_stmt = $conn->prepare($docs_sql);
$docs_stmt->bind_param("i", $contact_id);
$docs_stmt->execute();
$docs_result = $docs_stmt->get_result();
$documents = [];
while ($doc = $docs_result->fetch_assoc()) {
    $documents[] = $doc;
}

// Get contracts for this contact
$contracts_sql = "SELECT * FROM contracts WHERE contact_id = ? ORDER BY created_at DESC";
$contracts_stmt = $conn->prepare($contracts_sql);
$contracts_stmt->bind_param("i", $contact_id);
$contracts_stmt->execute();
$contracts_result = $contracts_stmt->get_result();
while ($contract = $contracts_result->fetch_assoc()) {
    $documents[] = [
        'id' => 'contract_' . $contract['id'],
        'document_type' => 'contract',
        'file_path' => $contract['file_path'],
        'created_at' => $contract['created_at']
    ];
}

// Sort all documents by created_at
usort($documents, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

// Handle document deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_document'])) {
    $document_id = intval($_POST['document_id']);
    $document_type = $_POST['document_type'];
    
    if ($document_type === 'contract') {
        // Extract the actual contract ID from the format "contract_X"
        $contract_id = intval(str_replace('contract_', '', $document_id));
        
        // Get the file path before deleting
        $file_sql = "SELECT file_path FROM contracts WHERE id = ?";
        $file_stmt = $conn->prepare($file_sql);
        $file_stmt->bind_param("i", $contract_id);
        $file_stmt->execute();
        $file_result = $file_stmt->get_result();
        
        if ($file_result->num_rows > 0) {
            $file_row = $file_result->fetch_assoc();
            $file_path = '../contracts/' . $file_row['file_path'];
            
            // Delete the file if it exists
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            
            // Delete from database
            $delete_sql = "DELETE FROM contracts WHERE id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("i", $contract_id);
            $delete_stmt->execute();
        }
    } else {
        // Get the file path before deleting
        $file_sql = "SELECT file_path FROM documents WHERE id = ?";
        $file_stmt = $conn->prepare($file_sql);
        $file_stmt->bind_param("i", $document_id);
        $file_stmt->execute();
        $file_result = $file_stmt->get_result();
        
        if ($file_result->num_rows > 0) {
            $file_row = $file_result->fetch_assoc();
            $file_path = '../documents/' . $file_row['file_path'];
            
            // Delete the file if it exists
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            
            // Also delete any copy_view version
            $copy_file_path = '../documents/' . str_replace('.pdf', '_copy_view.pdf', $file_row['file_path']);
            if (file_exists($copy_file_path)) {
                unlink($copy_file_path);
            }
            
            // Delete from database
            $delete_sql = "DELETE FROM documents WHERE id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("i", $document_id);
            $delete_stmt->execute();
        }
    }
    
    // Redirect to refresh the page
    header("Location: view_documents.php?id=$contact_id");
    exit();
}

// Close the database connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documents for <?php echo htmlspecialchars($contact['name']); ?> - CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .document-card {
            transition: transform 0.2s;
        }
        .document-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .document-icon {
            font-size: 3rem;
            color: #dc3545;
        }
        .document-type {
            text-transform: capitalize;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Documents for <?php echo htmlspecialchars($contact['name']); ?></h1>
            <div>
                <a href="#" class="btn btn-primary" onclick="window.history.back(); return false;">Back</a>
                <a href="index.php" class="btn btn-secondary">Back to CRM</a>
            </div>
        </div>

        <?php if (count($documents) > 0): ?>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php foreach ($documents as $document): ?>
                    <div class="col">
                        <div class="card h-100 document-card">
                            <div class="card-body text-center">
                                <i class="bi bi-file-earmark-pdf document-icon"></i>
                                <h5 class="card-title document-type">
                                    <?php 
                                    $doc_type = str_replace('_', ' ', $document['document_type']);
                                    echo htmlspecialchars(ucfirst($doc_type)); 
                                    ?>
                                </h5>
                                <p class="card-text">
                                    Created: <?php echo date('M j, Y', strtotime($document['created_at'])); ?>
                                </p>
                                <div class="d-flex justify-content-center">
                                    <?php if ($document['document_type'] === 'contract'): ?>
                                        <a href="../contracts/<?php echo htmlspecialchars($document['file_path']); ?>" class="btn btn-primary me-2" target="_blank">View</a>
                                        <a href="../contracts/<?php echo htmlspecialchars($document['file_path']); ?>" class="btn btn-success me-2" download>Download</a>
                                    <?php else: ?>
                                        <a href="../documents/<?php echo htmlspecialchars($document['file_path']); ?>" class="btn btn-primary me-2" target="_blank">View</a>
                                        <a href="../documents/<?php echo htmlspecialchars($document['file_path']); ?>" class="btn btn-success me-2" download>Download</a>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteDocumentModal" 
                                        data-document-id="<?php echo $document['id']; ?>"
                                        data-document-type="<?php echo $document['document_type']; ?>"
                                        data-document-name="<?php echo htmlspecialchars(ucfirst($doc_type)); ?>">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                No documents found for this contact.
            </div>
        <?php endif; ?>
    </div>

    <!-- Delete Document Modal -->
    <div class="modal fade" id="deleteDocumentModal" tabindex="-1" aria-labelledby="deleteDocumentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteDocumentModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this <span id="document-type-text"></span> document?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="view_documents.php?id=<?php echo $contact_id; ?>" method="post">
                        <input type="hidden" name="document_id" id="delete_document_id">
                        <input type="hidden" name="document_type" id="delete_document_type">
                        <button type="submit" name="delete_document" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#deleteDocumentModal').on('show.bs.modal', function (event) {
                const button = $(event.relatedTarget);
                const documentId = button.data('document-id');
                const documentType = button.data('document-type');
                const documentName = button.data('document-name');
                
                $('#delete_document_id').val(documentId);
                $('#delete_document_type').val(documentType);
                $('#document-type-text').text(documentName);
            });
        });
    </script>
</body>
</html>

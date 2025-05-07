<?php
session_start();

// Check if user is authenticated
if (!isset($_SESSION["authenticated"]) || $_SESSION["authenticated"] !== true) {
    header("Location: ../login.php");
    exit();
}

// Check if ID is provided
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

// Get payment links for this contact
$payment_links_sql = "SELECT * FROM payment_links WHERE contact_id = ?";
$payment_links_stmt = $conn->prepare($payment_links_sql);
$payment_links_stmt->bind_param("i", $contact_id);
$payment_links_stmt->execute();
$payment_links_result = $payment_links_stmt->get_result();
$payment_links = [];
while ($link = $payment_links_result->fetch_assoc()) {
    $payment_links[] = $link;
}

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

<form id="editContactForm" action="update_contact.php" method="post">
    <input type="hidden" name="id" value="<?php echo $contact['id']; ?>">

    <ul class="nav nav-tabs mb-3" id="contactTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab" aria-controls="details" aria-selected="true">Contact Details</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="dates-tab" data-bs-toggle="tab" data-bs-target="#dates" type="button" role="tab" aria-controls="dates" aria-selected="false">Dates & Status</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="payment-tab" data-bs-toggle="tab" data-bs-target="#payment" type="button" role="tab" aria-controls="payment" aria-selected="false">Payment Info</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="mail-tracking-tab" data-bs-toggle="tab" data-bs-target="#mail-tracking" type="button" role="tab" aria-controls="mail-tracking" aria-selected="false">Mail Tracking</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes" type="button" role="tab" aria-controls="notes" aria-selected="false">Notes</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="actions-tab" data-bs-toggle="tab" data-bs-target="#actions" type="button" role="tab" aria-controls="actions" aria-selected="false">Actions</button>
        </li>
    </ul>

    <div class="tab-content" id="contactTabsContent">
        <!-- Contact Details Tab -->
        <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="edit_contact_type" class="form-label">Contact Type</label>
                    <select class="form-select" id="edit_contact_type" name="contact_type" required>
                        <option value="clients" <?php echo $contact['contact_type'] == 'clients' ? 'selected' : ''; ?>>Client</option>
                        <option value="prospects" <?php echo $contact['contact_type'] == 'prospects' ? 'selected' : ''; ?>>Prospect</option>
                        <option value="closed" <?php echo $contact['contact_type'] == 'closed' ? 'selected' : ''; ?>>Closed</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="edit_rep" class="form-label">Representative</label>
                    <select class="form-select" id="edit_rep" name="rep">
                        <option value="">Select Rep</option>
                        <?php foreach ($reps as $rep): ?>
                            <option value="<?php echo htmlspecialchars($rep['name']); ?>" <?php echo $contact['rep'] == $rep['name'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($rep['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="edit_interest_level" class="form-label">Interest Level (0-10)</label>
                    <input type="number" class="form-control" id="edit_interest_level" name="interest_level" min="0" max="10" value="<?php echo htmlspecialchars($contact['interest_level']); ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="edit_name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="edit_name" name="name" value="<?php echo htmlspecialchars($contact['name']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="edit_email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="edit_email" name="email" value="<?php echo htmlspecialchars($contact['email']); ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="edit_phone_number" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="edit_phone_number" name="phone_number" value="<?php echo htmlspecialchars($contact['phone_number']); ?>">
                </div>
                <div class="col-md-6">
                    <label for="edit_loan_institution" class="form-label">Loan Institution</label>
                    <input type="text" class="form-control" id="edit_loan_institution" name="loan_institution" value="<?php echo htmlspecialchars($contact['loan_institution']); ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12">
                    <label for="edit_address" class="form-label">Address</label>
                    <input type="text" class="form-control" id="edit_address" name="address" value="<?php echo htmlspecialchars($contact['address']); ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="edit_city" class="form-label">City</label>
                    <input type="text" class="form-control" id="edit_city" name="city" value="<?php echo htmlspecialchars($contact['city']); ?>">
                </div>
                <div class="col-md-4">
                    <label for="edit_state" class="form-label">State</label>
                    <input type="text" class="form-control" id="edit_state" name="state" value="<?php echo htmlspecialchars($contact['state']); ?>">
                </div>
                <div class="col-md-4">
                    <label for="edit_zip" class="form-label">ZIP</label>
                    <input type="text" class="form-control" id="edit_zip" name="zip" value="<?php echo htmlspecialchars($contact['zip']); ?>">
                </div>
            </div>
        </div>

        <!-- Dates & Status Tab -->
        <div class="tab-pane fade" id="dates" role="tabpanel" aria-labelledby="dates-tab">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="edit_initial_contact_date" class="form-label">Initial Contact Date</label>
                    <input type="date" class="form-control" id="edit_initial_contact_date" name="initial_contact_date" value="<?php echo $contact['initial_contact_date']; ?>">
                </div>
                <div class="col-md-4">
                    <label for="edit_update_date" class="form-label">Update Date</label>
                    <input type="date" class="form-control" id="edit_update_date" name="update_date" value="<?php echo $contact['update_date']; ?>">
                </div>
                <div class="col-md-4">
                    <label for="edit_call_back_date" class="form-label">Call Back Date</label>
                    <input type="date" class="form-control" id="edit_call_back_date" name="call_back_date" value="<?php echo $contact['call_back_date']; ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="edit_first_noe" class="form-label">First NOE Date</label>
                    <input type="date" class="form-control" id="edit_first_noe" name="first_noe" value="<?php echo $contact['first_noe']; ?>">
                </div>
                <div class="col-md-3">
                    <label for="edit_final_noe" class="form-label">Final NOE Date</label>
                    <input type="date" class="form-control" id="edit_final_noe" name="final_noe" value="<?php echo $contact['final_noe']; ?>">
                </div>
                <div class="col-md-3">
                    <label for="edit_court_date" class="form-label">Court Date</label>
                    <input type="date" class="form-control" id="edit_court_date" name="court_date" value="<?php echo $contact['court_date']; ?>">
                </div>
                <div class="col-md-3">
                    <label for="edit_suit_filed" class="form-label">Suit Filed Date</label>
                    <input type="date" class="form-control" id="edit_suit_filed" name="suit_filed" value="<?php echo $contact['suit_filed']; ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="edit_step" class="form-label">Step</label>
                    <select class="form-select" id="edit_step" name="step">
                        <option value="0" <?php echo $contact['step'] == 0 ? 'selected' : ''; ?>>None</option>
                        <option value="1" <?php echo $contact['step'] == 1 ? 'selected' : ''; ?>>Loan Docs Requested</option>
                        <option value="2" <?php echo $contact['step'] == 2 ? 'selected' : ''; ?>>Loan Docs Received</option>
                        <option value="3" <?php echo $contact['step'] == 3 ? 'selected' : ''; ?>>Contract Generated</option>
                        <option value="4" <?php echo $contact['step'] == 4 ? 'selected' : ''; ?>>Contract Sent</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="edit_status" class="form-label">Status</label>
                    <input type="text" class="form-control" id="edit_status" name="status" value="<?php echo htmlspecialchars($contact['status']); ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="edit_obstacle" class="form-label">Obstacle</label>
                    <input type="text" class="form-control" id="edit_obstacle" name="obstacle" value="<?php echo htmlspecialchars($contact['obstacle']); ?>">
                </div>
                <div class="col-md-6">
                    <label for="edit_next_step" class="form-label">Next Step</label>
                    <input type="text" class="form-control" id="edit_next_step" name="next_step" value="<?php echo htmlspecialchars($contact['next_step']); ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="edit_past_due_on_loan" class="form-label">Past Due on Loan</label>
                    <select class="form-select" id="edit_past_due_on_loan" name="past_due_on_loan">
                        <option value="N" <?php echo $contact['past_due_on_loan'] == 'N' ? 'selected' : ''; ?>>No</option>
                        <option value="Y" <?php echo $contact['past_due_on_loan'] == 'Y' ? 'selected' : ''; ?>>Yes</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Payment Info Tab -->
        <div class="tab-pane fade" id="payment" role="tabpanel" aria-labelledby="payment-tab">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="edit_payment_date" class="form-label">Payment Date</label>
                    <input type="date" class="form-control" id="edit_payment_date" name="payment_date" value="<?php echo $contact['payment_date']; ?>">
                </div>
                <div class="col-md-4">
                    <label for="edit_next_payment_date" class="form-label">Next Payment Date</label>
                    <input type="date" class="form-control" id="edit_next_payment_date" name="next_payment_date" value="<?php echo $contact['next_payment_date']; ?>">
                </div>
                <div class="col-md-4">
                    <label for="edit_contract_amount" class="form-label">Contract Amount</label>
                    <input type="number" step="0.01" class="form-control" id="edit_contract_amount" name="contract_amount" value="<?php echo $contact['contract_amount']; ?>">
                </div>
            </div>

            <h5 class="mt-4">Payment Links</h5>
            <div id="payment_links_container">
                <?php if (count($payment_links) > 0): ?>
                    <?php foreach ($payment_links as $index => $link): ?>
                        <div class="row mb-3 payment-link-row">
                            <input type="hidden" name="payment_link_id[]" value="<?php echo $link['id']; ?>">
                            <div class="col-md-4">
                                <label for="payment_amount_<?php echo $index; ?>" class="form-label">Amount</label>
                                <input type="number" step="0.01" class="form-control" id="payment_amount_<?php echo $index; ?>" name="payment_amount[]" value="<?php echo $link['amount']; ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="payment_url_<?php echo $index; ?>" class="form-label">URL</label>
                                <input type="text" class="form-control" id="payment_url_<?php echo $index; ?>" name="payment_url[]" value="<?php echo htmlspecialchars($link['url']); ?>">
                            </div>
                            <div class="col-md-1">
                                <label for="payment_pay_in_full_<?php echo $index; ?>" class="form-label">Full</label>
                                <select class="form-select" id="payment_pay_in_full_<?php echo $index; ?>" name="payment_pay_in_full[]">
                                    <option value="N" <?php echo $link['pay_in_full'] == 'N' ? 'selected' : ''; ?>>No</option>
                                    <option value="Y" <?php echo $link['pay_in_full'] == 'Y' ? 'selected' : ''; ?>>Yes</option>
                                </select>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="button" class="btn btn-danger btn-sm remove-payment-link">Remove</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No payment links found.</p>
                <?php endif; ?>
            </div>

            <button type="button" class="btn btn-success btn-sm mt-2" id="add_payment_link">Add Payment Link</button>
        </div>

        <!-- Mail Tracking Tab -->
        <div class="tab-pane fade" id="mail-tracking" role="tabpanel" aria-labelledby="mail-tracking-tab">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="edit_first_noe_tracking_number" class="form-label">First NOE Tracking Number</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="edit_first_noe_tracking_number" name="first_noe_tracking_number" value="<?php echo htmlspecialchars($contact['first_noe_tracking_number']); ?>">
                        <button class="btn btn-outline-secondary track-mail-btn" type="button" data-tracking-field="first_noe_tracking_number">Track Mail</button>
                    </div>
                    <div class="form-text"><?php echo $contact['first_noe_tracking_confirmed'] ? 'Delivery confirmed' : 'Delivery not confirmed'; ?></div>
                </div>
                <div class="col-md-4">
                    <label for="edit_final_noe_tracking_number" class="form-label">Final NOE Tracking Number</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="edit_final_noe_tracking_number" name="final_noe_tracking_number" value="<?php echo htmlspecialchars($contact['final_noe_tracking_number']); ?>">
                        <button class="btn btn-outline-secondary track-mail-btn" type="button" data-tracking-field="final_noe_tracking_number">Track Mail</button>
                    </div>
                    <div class="form-text"><?php echo $contact['final_noe_tracking_confirmed'] ? 'Delivery confirmed' : 'Delivery not confirmed'; ?></div>
                </div>
                <div class="col-md-4">
                    <label for="edit_suit_filed_tracking_number" class="form-label">Suit Filed Tracking Number</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="edit_suit_filed_tracking_number" name="suit_filed_tracking_number" value="<?php echo htmlspecialchars($contact['suit_filed_tracking_number']); ?>">
                        <button class="btn btn-outline-secondary track-mail-btn" type="button" data-tracking-field="suit_filed_tracking_number">Track Mail</button>
                    </div>
                    <div class="form-text"><?php echo $contact['suit_filed_tracking_confirmed'] ? 'Delivery confirmed' : 'Delivery not confirmed'; ?></div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-12">
                    <h5>Tracking History</h5>
                    <div id="tracking-history">
                        <p>Select a tracking number and click "Track Mail" to view tracking history.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes Tab -->
        <div class="tab-pane fade" id="notes" role="tabpanel" aria-labelledby="notes-tab">
            <div class="row mb-3">
                <div class="col-md-12">
                    <label for="edit_additional_notes" class="form-label">Additional Notes</label>
                    <textarea class="form-control" id="edit_additional_notes" name="additional_notes" rows="10"><?php echo htmlspecialchars($contact['additional_notes']); ?></textarea>
                </div>
            </div>
        </div>

        <!-- Actions Tab -->
        <div class="tab-pane fade" id="actions" role="tabpanel" aria-labelledby="actions-tab">
            <div class="row mb-4">
                <div class="col-md-12">
                    <h5>Contract Actions</h5>
                    <button type="button" class="btn btn-primary me-2" id="update_contract_btn">Update Contract</button>
                    <button type="button" class="btn btn-success me-2" id="send_contract_btn">Send Contract</button>
                    <button type="button" class="btn btn-info" id="send_docusign_btn">Send Docusign</button>
                </div>
            </div>

            <!-- Contract Options (initially hidden) -->
            <div id="contract_options" class="d-none">
                <h5>Contract Options</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="contract_amount" class="form-label">Contract Amount</label>
                        <input type="number" step="0.01" class="form-control" id="contract_amount" name="contract_amount" value="<?php echo $contact['contract_amount'] > 0 ? $contact['contract_amount'] : 2499.00; ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="contract_months" class="form-label">Payment Option</label>
                        <select class="form-select" id="contract_months" name="contract_months">
                            <option value="0">Pay in Full</option>
                            <option value="1">1 Month</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="contract_language" class="form-label">Language</label>
                        <select class="form-select" id="contract_language" name="contract_language">
                            <option value="english">English</option>
                            <option value="spanish">Spanish</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="contract_clause" class="form-label">Clause Choice</label>
                        <select class="form-select" id="contract_clause" name="contract_clause">
                            <option value="default">Payment Help</option>
                            <option value="90-day Guarantee">90-day Guarantee</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <button type="button" class="btn btn-primary" id="generate_contract_btn">Generate Contract</button>
                        <button type="button" class="btn btn-secondary" id="cancel_contract_btn">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer mt-4">
        <button type="button" class="btn btn-danger me-auto" id="delete_contact_btn">Delete Contact</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </div>
</form>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this contact? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="delete_contact.php" method="post">
                    <input type="hidden" name="id" value="<?php echo $contact['id']; ?>">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Handle delete button
        $('#delete_contact_btn').click(function() {
            $('#deleteConfirmModal').modal('show');
        });

        // Handle update contract button
        $('#update_contract_btn').click(function() {
            $('#contract_options').removeClass('d-none');
        });

        // Handle cancel contract button
        $('#cancel_contract_btn').click(function() {
            $('#contract_options').addClass('d-none');
        });

        // Handle generate contract button
        $('#generate_contract_btn').click(function() {
            const contactId = $('input[name="id"]').val();
            const amount = $('#contract_amount').val();
            const months = $('#contract_months').val();
            const language = $('#contract_language').val();
            const clause = $('#contract_clause').val();

            // Open contract generation page in new window
            window.open(`generate_contract.php?id=${contactId}&amount=${amount}&months=${months}&language=${language}&clause=${clause}`, '_blank');

            // Hide contract options
            $('#contract_options').addClass('d-none');
        });

        // Handle send contract button
        $('#send_contract_btn').click(function() {
            const contactId = $('input[name="id"]').val();

            // Confirm before sending
            if (confirm('Are you sure you want to send the contract to this contact?')) {
                $.ajax({
                    url: 'send_contract.php',
                    type: 'POST',
                    data: { id: contactId },
                    success: function(response) {
                        alert('Contract sent successfully!');
                    },
                    error: function() {
                        alert('Error sending contract. Please try again.');
                    }
                });
            }
        });

        // Handle send docusign button
        $('#send_docusign_btn').click(function() {
            const contactId = $('input[name="id"]').val();

            // Confirm before sending
            if (confirm('Are you sure you want to send a Docusign contract to this contact?')) {
                $.ajax({
                    url: 'send_docusign.php',
                    type: 'POST',
                    data: { id: contactId },
                    success: function(response) {
                        alert('Docusign contract sent successfully!');
                    },
                    error: function() {
                        alert('Error sending Docusign contract. Please try again.');
                    }
                });
            }
        });

        // Handle add payment link button
        $('#add_payment_link').click(function() {
            const index = $('.payment-link-row').length;
            const newRow = `
                <div class="row mb-3 payment-link-row">
                    <input type="hidden" name="payment_link_id[]" value="new">
                    <div class="col-md-4">
                        <label for="payment_amount_${index}" class="form-label">Amount</label>
                        <input type="number" step="0.01" class="form-control" id="payment_amount_${index}" name="payment_amount[]" value="">
                    </div>
                    <div class="col-md-6">
                        <label for="payment_url_${index}" class="form-label">URL</label>
                        <input type="text" class="form-control" id="payment_url_${index}" name="payment_url[]" value="">
                    </div>
                    <div class="col-md-1">
                        <label for="payment_pay_in_full_${index}" class="form-label">Full</label>
                        <select class="form-select" id="payment_pay_in_full_${index}" name="payment_pay_in_full[]">
                            <option value="N" selected>No</option>
                            <option value="Y">Yes</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-sm remove-payment-link">Remove</button>
                    </div>
                </div>
            `;

            $('#payment_links_container').append(newRow);
        });

        // Handle remove payment link button (using event delegation)
        $(document).on('click', '.remove-payment-link', function() {
            $(this).closest('.payment-link-row').remove();
        });

        // Handle track mail button
        $('.track-mail-btn').click(function() {
            const trackingField = $(this).data('tracking-field');
            const trackingNumber = $('#edit_' + trackingField).val();
            const contactId = $('input[name="id"]').val();

            if (!trackingNumber) {
                alert('Please enter a tracking number first.');
                return;
            }

            // Show loading message
            $('#tracking-history').html('<p>Loading tracking information...</p>');

            // Make AJAX request to track mail
            $.ajax({
                url: 'track_mail.php',
                type: 'POST',
                data: {
                    contact_id: contactId,
                    tracking_number: trackingNumber,
                    tracking_type: trackingField.replace('_tracking_number', '')
                },
                success: function(response) {
                    try {
                        const data = JSON.parse(response);
                        if (data.success) {
                            // Display tracking history
                            let historyHtml = '<div class="alert alert-success">Tracking information retrieved successfully.</div>';
                            historyHtml += '<table class="table table-striped">';
                            historyHtml += '<thead><tr><th>Date</th><th>Status</th><th>Location</th></tr></thead>';
                            historyHtml += '<tbody>';

                            if (data.tracking_history && data.tracking_history.length > 0) {
                                data.tracking_history.forEach(function(entry) {
                                    historyHtml += `<tr>
                                        <td>${entry.date}</td>
                                        <td>${entry.status}</td>
                                        <td>${entry.location}</td>
                                    </tr>`;
                                });
                            } else {
                                historyHtml += '<tr><td colspan="3">No tracking history available.</td></tr>';
                            }

                            historyHtml += '</tbody></table>';
                            $('#tracking-history').html(historyHtml);

                            // Update delivery status if delivered
                            if (data.delivered) {
                                $(`#edit_${trackingField}`).closest('.col-md-4').find('.form-text').text('Delivery confirmed');
                            }
                        } else {
                            $('#tracking-history').html(`<div class="alert alert-danger">${data.message}</div>`);
                        }
                    } catch (e) {
                        $('#tracking-history').html('<div class="alert alert-danger">Error parsing response from server.</div>');
                    }
                },
                error: function() {
                    $('#tracking-history').html('<div class="alert alert-danger">Error tracking mail. Please try again.</div>');
                }
            });
        });
    });
</script>

<?php
session_start();

// Check if user is authenticated
if (!isset($_SESSION["authenticated"]) || $_SESSION["authenticated"] !== true) {
    header("Location: ../login.php");
    exit();
}

// Check if required parameters are provided
if (!isset($_POST['contact_id']) || !isset($_POST['tracking_number']) || !isset($_POST['tracking_type'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit();
}

// Include database configuration
$conn = require_once('config/db_config.php');

// Sanitize and validate input
function sanitizeInput($conn, $input) {
    return $conn->real_escape_string(trim($input));
}

$contact_id = intval($_POST['contact_id']);
$tracking_number = sanitizeInput($conn, $_POST['tracking_number']);
$tracking_type = sanitizeInput($conn, $_POST['tracking_type']);

// Validate tracking type
if (!in_array($tracking_type, ['first_noe', 'final_noe', 'suit_filed'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid tracking type']);
    exit();
}

// USPS API credentials
$usps_user_id = '9744R1BULLA93';

// Create XML request
$xml = '<?xml version="1.0" encoding="UTF-8" ?>
<TrackFieldRequest USERID="' . $usps_user_id . '">
    <TrackID ID="' . $tracking_number . '"></TrackID>
</TrackFieldRequest>';

// Set up cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://secure.shippingapis.com/ShippingAPI.dll?API=TrackV2&XML=' . urlencode($xml));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

// Execute the request
$response = curl_exec($ch);
curl_close($ch);

// Parse the response
$tracking_data = simplexml_load_string($response);

// Check for errors
if (isset($tracking_data->Error)) {
    echo json_encode(['success' => false, 'message' => (string)$tracking_data->Error->Description]);
    exit();
}

// Process tracking data
$tracking_history = [];
$delivered = false;
$delivery_date = null;

if (isset($tracking_data->TrackInfo->TrackDetail)) {
    foreach ($tracking_data->TrackInfo->TrackDetail as $detail) {
        $detail_text = (string)$detail;
        
        // Extract date and time from the detail text
        preg_match('/(\w+, \w+ \d+, \d+, \d+:\d+ [ap]m)/', $detail_text, $date_matches);
        $date = isset($date_matches[1]) ? $date_matches[1] : '';
        
        // Extract status and location
        $status_location = str_replace($date, '', $detail_text);
        $status_location = trim($status_location, ', ');
        
        // Split status and location
        $parts = explode(',', $status_location);
        $status = isset($parts[0]) ? trim($parts[0]) : '';
        $location = isset($parts[1]) ? trim($parts[1]) : '';
        
        // Check if delivered
        if (stripos($status, 'delivered') !== false) {
            $delivered = true;
            $delivery_date = date('Y-m-d', strtotime($date));
        }
        
        $tracking_history[] = [
            'date' => $date,
            'status' => $status,
            'location' => $location
        ];
    }
}

// Save tracking data to database
$sql = "INSERT INTO mail_tracking (contact_id, tracking_number, tracking_type, status, status_details, delivery_date) 
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$status = isset($tracking_data->TrackInfo->TrackSummary) ? (string)$tracking_data->TrackInfo->TrackSummary : '';
$status_details = json_encode($tracking_history);
$stmt->bind_param("isssss", $contact_id, $tracking_number, $tracking_type, $status, $status_details, $delivery_date);
$stmt->execute();

// Update contact if delivered
if ($delivered) {
    $tracking_field = $tracking_type . '_tracking_confirmed';
    $date_field = $tracking_type == 'first_noe' ? 'first_noe' : ($tracking_type == 'final_noe' ? 'final_noe' : 'suit_filed');
    
    $update_sql = "UPDATE contacts SET $tracking_field = 1, $date_field = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("si", $delivery_date, $contact_id);
    $update_stmt->execute();
}

// Close the database connection
mysqli_close($conn);

// Return tracking data
echo json_encode([
    'success' => true,
    'tracking_history' => $tracking_history,
    'delivered' => $delivered,
    'delivery_date' => $delivery_date
]);
?>

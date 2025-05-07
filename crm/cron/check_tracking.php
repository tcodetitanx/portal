<?php
// This script is meant to be run as a cron job to check the status of USPS tracking numbers

// Include database configuration
$conn = require_once('../config/db_config.php');

// Get all contacts with unconfirmed tracking numbers
$sql = "SELECT id, first_noe_tracking_number, final_noe_tracking_number, suit_filed_tracking_number 
        FROM contacts 
        WHERE 
            (first_noe_tracking_number != '' AND first_noe_tracking_confirmed = 0) OR 
            (final_noe_tracking_number != '' AND final_noe_tracking_confirmed = 0) OR 
            (suit_filed_tracking_number != '' AND suit_filed_tracking_confirmed = 0)";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    echo "No unconfirmed tracking numbers found.\n";
    mysqli_close($conn);
    exit();
}

// USPS API credentials
$usps_user_id = '9744R1BULLA93';

// Process each contact
while ($contact = $result->fetch_assoc()) {
    $contact_id = $contact['id'];
    
    // Check first NOE tracking number
    if (!empty($contact['first_noe_tracking_number'])) {
        checkTrackingNumber($conn, $usps_user_id, $contact_id, $contact['first_noe_tracking_number'], 'first_noe');
    }
    
    // Check final NOE tracking number
    if (!empty($contact['final_noe_tracking_number'])) {
        checkTrackingNumber($conn, $usps_user_id, $contact_id, $contact['final_noe_tracking_number'], 'final_noe');
    }
    
    // Check suit filed tracking number
    if (!empty($contact['suit_filed_tracking_number'])) {
        checkTrackingNumber($conn, $usps_user_id, $contact_id, $contact['suit_filed_tracking_number'], 'suit_filed');
    }
}

// Close the database connection
mysqli_close($conn);

// Function to check a tracking number
function checkTrackingNumber($conn, $usps_user_id, $contact_id, $tracking_number, $tracking_type) {
    echo "Checking $tracking_type tracking number $tracking_number for contact ID $contact_id...\n";
    
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
        echo "Error: " . $tracking_data->Error->Description . "\n";
        return;
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
        
        echo "Delivery confirmed for $tracking_type tracking number $tracking_number. Updated contact ID $contact_id.\n";
    } else {
        echo "No delivery confirmation yet for $tracking_type tracking number $tracking_number.\n";
    }
}
?>

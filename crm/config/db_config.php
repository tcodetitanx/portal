<?php
// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'portal_crm');

// Attempt to connect to MySQL database
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);

// Check connection
if (!$conn) {
    die("ERROR: Could not connect to MySQL. " . mysqli_connect_error());
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if (mysqli_query($conn, $sql)) {
    // Select the database
    mysqli_select_db($conn, DB_NAME);
} else {
    die("ERROR: Could not create database. " . mysqli_error($conn));
}

// Create tables if they don't exist
// Contacts table
$sql = "CREATE TABLE IF NOT EXISTS contacts (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    contact_type ENUM('clients', 'prospects', 'closed') NOT NULL,
    name VARCHAR(255) NOT NULL,
    rep VARCHAR(255),
    interest_level INT(11),
    initial_contact_date DATE,
    obstacle TEXT,
    next_step TEXT,
    update_date DATE,
    call_back_date DATE,
    email VARCHAR(255),
    phone_number VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(50),
    zip VARCHAR(20),
    loan_institution VARCHAR(100),
    step INT(1) DEFAULT 0,
    past_due_on_loan ENUM('Y', 'N'),
    additional_notes TEXT,
    payment_date DATE,
    next_payment_date DATE,
    contract_amount DECIMAL(10,2),
    first_noe DATE,
    final_noe DATE,
    court_date DATE,
    suit_filed DATE,
    status VARCHAR(100),
    first_noe_tracking_number VARCHAR(50),
    final_noe_tracking_number VARCHAR(50),
    suit_filed_tracking_number VARCHAR(50),
    first_noe_tracking_confirmed TINYINT(1) DEFAULT 0,
    final_noe_tracking_confirmed TINYINT(1) DEFAULT 0,
    suit_filed_tracking_confirmed TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (!mysqli_query($conn, $sql)) {
    die("ERROR: Could not create contacts table. " . mysqli_error($conn));
}

// Payment links table
$sql = "CREATE TABLE IF NOT EXISTS payment_links (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    contact_id INT(11) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    url VARCHAR(255) NOT NULL,
    pay_in_full ENUM('Y', 'N') DEFAULT 'N',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE
)";

if (!mysqli_query($conn, $sql)) {
    die("ERROR: Could not create payment_links table. " . mysqli_error($conn));
}

// Reps table
$sql = "CREATE TABLE IF NOT EXISTS reps (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    extension VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!mysqli_query($conn, $sql)) {
    die("ERROR: Could not create reps table. " . mysqli_error($conn));
}

// Insert default reps if table is empty
$sql = "SELECT COUNT(*) as count FROM reps";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

if ($row['count'] == 0) {
    $reps = [
        ['Daniel Taylor', 'daniel@example.com', '101'],
        ['Jessica Autrey', 'jessica@example.com', '102'],
        ['Jim Gibson', 'jim@example.com', '103'],
        ['Leslie Woodmansee', 'leslie@example.com', '104'],
        ['Monica Escobedo', 'monica@example.com', '105'],
        ['William Tavarez', 'william@example.com', '106']
    ];

    foreach ($reps as $rep) {
        $sql = "INSERT INTO reps (name, email, extension) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $rep[0], $rep[1], $rep[2]);
        mysqli_stmt_execute($stmt);
    }
}

// Contracts table
$sql = "CREATE TABLE IF NOT EXISTS contracts (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    contact_id INT(11) NOT NULL,
    payment_link_id INT(11) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE,
    FOREIGN KEY (payment_link_id) REFERENCES payment_links(id) ON DELETE CASCADE
)";

if (!mysqli_query($conn, $sql)) {
    die("ERROR: Could not create contracts table. " . mysqli_error($conn));
}

// Mail tracking table
$sql = "CREATE TABLE IF NOT EXISTS mail_tracking (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    contact_id INT(11) NOT NULL,
    tracking_number VARCHAR(50) NOT NULL,
    tracking_type ENUM('first_noe', 'final_noe', 'suit_filed') NOT NULL,
    status VARCHAR(100),
    status_details TEXT,
    delivery_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE CASCADE
)";

if (!mysqli_query($conn, $sql)) {
    die("ERROR: Could not create mail_tracking table. " . mysqli_error($conn));
}

// Return the connection
return $conn;
?>

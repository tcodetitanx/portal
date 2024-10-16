<?php
session_start();

if (!isset($_SESSION["authenticated"]) || $_SESSION["authenticated"] !== true) {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eviction Notice</title>
    <link rel="stylesheet" href="../assets/stylesLight.css">
</head>
<body>
    <div class="container">
        <h1>Eviction Notice</h1>
        <form id="form">
            <label for="issuer_name">Landlord Name:</label>
            <input type="text" id="issuer_name" name="issuer_name" required>

            <label for="issuer_address">Landlord Address:</label>
            <input type="text" id="issuer_address" name="issuer_address" required>

            <label for="issuer_phone">Landlord Phone:</label>
            <input type="text" id="issuer_phone" name="issuer_phone" required>

            <label for="name">Tenant Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="address">Tenant Address:</label>
            <input type="text" id="address" name="address" required>

            <label for="phone">Tenant Phone:</label>
            <input type="text" id="phone" name="phone" required>

            <label for="eviction_date">Eviction Date:</label>
            <input type="date" id="eviction_date" name="eviction_date" required>

            <label for="lease_date">Lease Date:</label>
            <input type="date" id="lease_date" name="lease_date" required>

            <label for="city">Tenant City:</label>
            <input type="text" id="city" name="city" required>

            <label for="state">Tenant State:</label>
            <input type="text" id="state" name="state" required>

            <label for="zip">Tenant Zip Code:</label>
            <input type="text" id="zip" name="zip" required>

            <label for="means_of_service">Means of Service:</label>
            <input type="text" id="means_of_service" name="means_of_service" required>

            <button type="button" onclick="generateUrl()">Generate URL</button>
        </form>

        <div id="generatedUrl"></div>
    </div>

    <script>
function generateUrl() {
    const form = document.getElementById('form');
    const formData = new FormData(form);

    let params = new URLSearchParams();
    formData.forEach((value, key) => {
        params.append(key, encodeURIComponent(value)); // Only encode values, not keys
    });

    const url = `view_eviction.php?${params.toString()}`;
    document.getElementById('generatedUrl').innerHTML = `<p>Generated URL: <a href="${url}" target="_blank">${url}</a></p>`;
}
</script>
</body>
</html>

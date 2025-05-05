<?php
session_start();

// Check if user is authenticated
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
    <title>Generate Statement</title>
    <link rel="stylesheet" href="../assets/stylesLight.css">
    <style>
        .transaction-row, .payment-row {
            display: grid;
            grid-template-columns: 1fr 3fr 1fr 1fr;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .transaction-header, .payment-header {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .payment-row {
            grid-template-columns: 1fr 1fr;
        }
        
        .section-title {
            margin-top: 20px;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        .add-button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 15px;
        }
        
        .remove-button {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Generate Statement</h1>
        <form id="statementForm" action="statementGen.php" method="POST">
            <div class="section-title">Basic Information</div>
            
            <label for="statement_date">Statement Date:</label>
            <input type="date" id="statement_date" name="statement_date" value="<?php echo date('Y-m-d'); ?>" required>
            
            <label for="account_number">Account Number:</label>
            <input type="text" id="account_number" name="account_number" required>
            
            <label for="recipient_name">Recipient Name:</label>
            <input type="text" id="recipient_name" name="recipient_name" required>
            
            <label for="recipient_address1">Address Line 1:</label>
            <input type="text" id="recipient_address1" name="recipient_address1" required>
            
            <label for="recipient_address2">Address Line 2:</label>
            <input type="text" id="recipient_address2" name="recipient_address2">
            
            <label for="account_info_name">Account Information Name:</label>
            <input type="text" id="account_info_name" name="account_info_name" required>
            
            <div class="section-title">Transaction Activity</div>
            
            <div id="transactions-container">
                <div class="transaction-header">
                    <div>Date</div>
                    <div>Description</div>
                    <div>Charges</div>
                    <div>Payments</div>
                </div>
                <div class="transaction-row">
                    <input type="date" name="transactions[0][date]" required>
                    <input type="text" name="transactions[0][description]" required>
                    <input type="number" step="0.01" name="transactions[0][charges]" value="0.00">
                    <input type="number" step="0.01" name="transactions[0][payments]" value="0.00">
                </div>
            </div>
            
            <button type="button" class="add-button" id="add-transaction">Add Transaction</button>
            
            <div class="section-title">Past Payments Breakdown</div>
            
            <div class="payment-header">
                <div>Category</div>
                <div>Last Statement</div>
                <div>Year to Date</div>
            </div>
            
            <div class="payment-row">
                <div>Principal</div>
                <input type="number" step="0.01" name="payments_breakdown[principal][last_statement]" value="0.00">
                <input type="number" step="0.01" name="payments_breakdown[principal][ytd]" value="0.00">
            </div>
            
            <div class="payment-row">
                <div>Interest</div>
                <input type="number" step="0.01" name="payments_breakdown[interest][last_statement]" value="0.00">
                <input type="number" step="0.01" name="payments_breakdown[interest][ytd]" value="0.00">
            </div>
            
            <div class="payment-row">
                <div>Escrow (Taxes and Insurance)</div>
                <input type="number" step="0.01" name="payments_breakdown[escrow][last_statement]" value="0.00">
                <input type="number" step="0.01" name="payments_breakdown[escrow][ytd]" value="0.00">
            </div>
            
            <div class="payment-row">
                <div>Other</div>
                <input type="number" step="0.01" name="payments_breakdown[other][last_statement]" value="0.00">
                <input type="number" step="0.01" name="payments_breakdown[other][ytd]" value="0.00">
            </div>
            
            <div class="payment-row">
                <div>Fees</div>
                <input type="number" step="0.01" name="payments_breakdown[fees][last_statement]" value="0.00">
                <input type="number" step="0.01" name="payments_breakdown[fees][ytd]" value="0.00">
            </div>
            
            <div class="payment-row">
                <div>Unapplied Funds</div>
                <input type="number" step="0.01" name="payments_breakdown[unapplied][last_statement]" value="0.00">
                <input type="number" step="0.01" name="payments_breakdown[unapplied][ytd]" value="0.00">
            </div>
            
            <div class="payment-row">
                <div><strong>Total</strong></div>
                <input type="number" step="0.01" name="payments_breakdown[total][last_statement]" value="0.00">
                <input type="number" step="0.01" name="payments_breakdown[total][ytd]" value="0.00">
            </div>
            
            <button type="submit">Generate Statement</button>
        </form>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let transactionCount = 1;
            
            // Add transaction row
            document.getElementById('add-transaction').addEventListener('click', function() {
                const container = document.getElementById('transactions-container');
                const newRow = document.createElement('div');
                newRow.className = 'transaction-row';
                newRow.innerHTML = `
                    <input type="date" name="transactions[${transactionCount}][date]" required>
                    <input type="text" name="transactions[${transactionCount}][description]" required>
                    <input type="number" step="0.01" name="transactions[${transactionCount}][charges]" value="0.00">
                    <input type="number" step="0.01" name="transactions[${transactionCount}][payments]" value="0.00">
                    <button type="button" class="remove-button">Remove</button>
                `;
                container.appendChild(newRow);
                transactionCount++;
                
                // Add event listener to the new remove button
                newRow.querySelector('.remove-button').addEventListener('click', function() {
                    container.removeChild(newRow);
                });
            });
            
            // Auto-calculate totals
            const paymentInputs = document.querySelectorAll('input[name^="payments_breakdown"]');
            paymentInputs.forEach(input => {
                if (!input.name.includes('total')) {
                    input.addEventListener('input', calculateTotals);
                }
            });
            
            function calculateTotals() {
                let lastStatementTotal = 0;
                let ytdTotal = 0;
                
                // Calculate last statement total
                document.querySelectorAll('input[name$="[last_statement]"]').forEach(input => {
                    if (!input.name.includes('total')) {
                        lastStatementTotal += parseFloat(input.value) || 0;
                    }
                });
                
                // Calculate YTD total
                document.querySelectorAll('input[name$="[ytd]"]').forEach(input => {
                    if (!input.name.includes('total')) {
                        ytdTotal += parseFloat(input.value) || 0;
                    }
                });
                
                // Update total fields
                document.querySelector('input[name="payments_breakdown[total][last_statement]"]').value = lastStatementTotal.toFixed(2);
                document.querySelector('input[name="payments_breakdown[total][ytd]"]').value = ytdTotal.toFixed(2);
            }
        });
    </script>
</body>
</html>

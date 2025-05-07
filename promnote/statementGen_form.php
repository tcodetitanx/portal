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
        .transaction-row {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            gap: 10px;
        }

        .transaction-header {
            display: flex;
            font-weight: bold;
            margin-bottom: 5px;
            gap: 10px;
        }

        .transaction-header div:nth-child(1) {
            width: 150px;
        }

        .transaction-header div:nth-child(2) {
            width: 300px;
        }

        .transaction-header div:nth-child(3),
        .transaction-header div:nth-child(4) {
            width: 100px;
        }

        .transaction-row input[type="date"] {
            width: 150px;
        }

        .transaction-row input[type="text"] {
            width: 300px;
        }

        .transaction-row input[type="number"] {
            width: 100px;
        }

        .payment-header {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .payment-row {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .payment-label {
            width: 150px;
            font-weight: normal;
        }

        .payment-input {
            width: 150px;
            margin-right: 20px;
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
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 15px;
            width: auto;
            display: inline-block;
        }

        .remove-button {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            width: auto;
        }

        button[type="submit"] {
            width: auto;
            padding: 10px 20px;
            display: inline-block;
        }

        /* Override the 100% width for inputs */
        input:not([type="radio"]), textarea {
            width: auto;
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
                <span style="display: inline-block; width: 150px;">Category</span>
                <span style="display: inline-block; width: 150px; margin-right: 20px;">Last Statement</span>
                <span style="display: inline-block; width: 150px;">Year to Date</span>
            </div>

            <div class="payment-row">
                <span class="payment-label">Principal</span>
                <input class="payment-input" type="number" step="0.01" name="payments_breakdown[principal][last_statement]" value="0.00">
                <input class="payment-input" type="number" step="0.01" name="payments_breakdown[principal][ytd]" value="0.00">
            </div>

            <div class="payment-row">
                <span class="payment-label">Interest</span>
                <input class="payment-input" type="number" step="0.01" name="payments_breakdown[interest][last_statement]" value="0.00">
                <input class="payment-input" type="number" step="0.01" name="payments_breakdown[interest][ytd]" value="0.00">
            </div>

            <div class="payment-row">
                <span class="payment-label">Other</span>
                <input class="payment-input" type="number" step="0.01" name="payments_breakdown[other][last_statement]" value="0.00">
                <input class="payment-input" type="number" step="0.01" name="payments_breakdown[other][ytd]" value="0.00">
            </div>

            <div class="payment-row">
                <span class="payment-label">Fees</span>
                <input class="payment-input" type="number" step="0.01" name="payments_breakdown[fees][last_statement]" value="0.00">
                <input class="payment-input" type="number" step="0.01" name="payments_breakdown[fees][ytd]" value="0.00">
            </div>

            <div class="payment-row">
                <span class="payment-label">Unapplied Funds</span>
                <input class="payment-input" type="number" step="0.01" name="payments_breakdown[unapplied][last_statement]" value="0.00">
                <input class="payment-input" type="number" step="0.01" name="payments_breakdown[unapplied][ytd]" value="0.00">
            </div>

            <div class="payment-row">
                <span class="payment-label"><strong>Total</strong></span>
                <input class="payment-input" type="number" step="0.01" name="payments_breakdown[total][last_statement]" value="0.00" readonly>
                <input class="payment-input" type="number" step="0.01" name="payments_breakdown[total][ytd]" value="0.00" readonly>
            </div>

            <div class="section-title">Delinquency Notice (Optional)</div>
            <textarea name="delinquency_notice" rows="4" style="width: 100%;" placeholder="Enter delinquency notice text here. If left empty, a blank box will be displayed in the statement."></textarea>

            <div style="display: flex; gap: 10px; margin-top: 15px;">
                <button type="submit">Generate Statement</button>
                <button type="button" id="insert-test-data" class="add-button">Insert Test Info</button>
            </div>
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

            // Calculate totals initially
            calculateTotals();

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

            // Insert Test Data button functionality
            document.getElementById('insert-test-data').addEventListener('click', function() {
                // Basic Information
                document.getElementById('statement_date').value = new Date().toISOString().split('T')[0]; // Today's date
                document.getElementById('account_number').value = 'ACCT-12345678';
                document.getElementById('recipient_name').value = 'John Q. Customer';
                document.getElementById('recipient_address1').value = '123 Main Street';
                document.getElementById('recipient_address2').value = 'Anytown, CA 90210';
                document.getElementById('account_info_name').value = 'John Q. Customer';

                // Clear existing transaction rows except the first one
                const container = document.getElementById('transactions-container');
                const rows = container.querySelectorAll('.transaction-row');
                for (let i = 1; i < rows.length; i++) {
                    container.removeChild(rows[i]);
                }

                // Set first transaction row data
                const firstRow = container.querySelector('.transaction-row');
                const inputs = firstRow.querySelectorAll('input');

                // Set date to 30 days ago
                const thirtyDaysAgo = new Date();
                thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
                inputs[0].value = thirtyDaysAgo.toISOString().split('T')[0];

                inputs[1].value = 'Previous Balance';
                inputs[2].value = '1250.00'; // Charges
                inputs[3].value = '0.00';    // Payments

                // Add more transaction rows
                const transactions = [
                    {
                        date: -25, // days ago
                        description: 'Payment Received - Thank You',
                        charges: '0.00',
                        payments: '250.00'
                    },
                    {
                        date: -15,
                        description: 'Late Fee',
                        charges: '35.00',
                        payments: '0.00'
                    },
                    {
                        date: -5,
                        description: 'Interest Charge',
                        charges: '22.50',
                        payments: '0.00'
                    }
                ];

                transactions.forEach(trans => {
                    // Create date
                    const transDate = new Date();
                    transDate.setDate(transDate.getDate() + trans.date);

                    // Add transaction
                    const newRow = document.createElement('div');
                    newRow.className = 'transaction-row';
                    newRow.innerHTML = `
                        <input type="date" name="transactions[${transactionCount}][date]" value="${transDate.toISOString().split('T')[0]}" required>
                        <input type="text" name="transactions[${transactionCount}][description]" value="${trans.description}" required>
                        <input type="number" step="0.01" name="transactions[${transactionCount}][charges]" value="${trans.charges}">
                        <input type="number" step="0.01" name="transactions[${transactionCount}][payments]" value="${trans.payments}">
                        <button type="button" class="remove-button">Remove</button>
                    `;
                    container.appendChild(newRow);

                    // Add event listener to the new remove button
                    newRow.querySelector('.remove-button').addEventListener('click', function() {
                        container.removeChild(newRow);
                    });

                    transactionCount++;
                });

                // Set payment breakdown values
                document.querySelector('input[name="payments_breakdown[principal][last_statement]"]').value = '200.00';
                document.querySelector('input[name="payments_breakdown[principal][ytd]"]').value = '750.00';

                document.querySelector('input[name="payments_breakdown[interest][last_statement]"]').value = '50.00';
                document.querySelector('input[name="payments_breakdown[interest][ytd]"]').value = '125.00';

                document.querySelector('input[name="payments_breakdown[other][last_statement]"]').value = '0.00';
                document.querySelector('input[name="payments_breakdown[other][ytd]"]').value = '35.00';

                document.querySelector('input[name="payments_breakdown[fees][last_statement]"]').value = '0.00';
                document.querySelector('input[name="payments_breakdown[fees][ytd]"]').value = '75.00';

                document.querySelector('input[name="payments_breakdown[unapplied][last_statement]"]').value = '0.00';
                document.querySelector('input[name="payments_breakdown[unapplied][ytd]"]').value = '25.00';

                // Recalculate totals
                calculateTotals();

                // Set delinquency notice
                document.querySelector('textarea[name="delinquency_notice"]').value = 'Your account is past due. Please make a payment of $1,057.50 by the end of the month to avoid additional fees and potential collection activity.';

                // Scroll to top of form
                window.scrollTo(0, 0);
            });
        });
    </script>
</body>
</html>

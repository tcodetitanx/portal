<!DOCTYPE html>
<html>
<head>
    <title>Lender Statement Generator</title>
    <script>
        let transactionCounter = 0;

        function addTransaction() {
            transactionCounter++;
            const container = document.getElementById('transactions-container');
            const newRow = document.createElement('div');
            newRow.id = 'transaction-row-' + transactionCounter;
            newRow.innerHTML = `
                <hr>
                <h4>Transaction Item ${transactionCounter}</h4>
                <label>Date:</label>
                <input type="date" name="transactions[${transactionCounter}][date]" required>
                <label>Description:</label>
                <input type="text" name="transactions[${transactionCounter}][description]" size="40" required>
                <label>Charges:</label>
                <input type="number" step="0.01" name="transactions[${transactionCounter}][charges]" required>
                <label>Payments:</label>
                <input type="number" step="0.01" name="transactions[${transactionCounter}][payments]" required>
                <button type="button" onclick="removeTransaction(${transactionCounter})">Remove</button>
            `;
            container.appendChild(newRow);
        }

        function removeTransaction(id) {
            const row = document.getElementById('transaction-row-' + id);
            if (row) {
                row.remove();
                // Optional: Renumber remaining items if needed, though backend can handle gaps.
            }
        }
    </script>
    <style>
        label { display: block; margin-top: 10px; }
        input[type=text], input[type=date], input[type=number] { margin-bottom: 5px; padding: 5px; }
        button { margin-top: 10px; }
        hr { margin: 15px 0; }
        .section { border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; }
        .section h3 { margin-top: 0; }
    </style>
</head>
<body>

    <h1>Generate Lender Statement PDF</h1>

    <form action="statementGen.php" mehod="post">

        <div class="section">
            <h3>Header Information</h3>
            <label for="statement_date">Statement Date:</label>
            <input type="date" id="statement_date" name="statement_date" required>

            <label for="account_number">Account Number:</label>
            <input type="text" id="account_number" name="account_number" value="PLL001" required>
        </div>

        <div class="section">
            <h3>Recipient Information</h3>
            <label for="recipient_name">Recipient Name:</label>
            <input type="text" id="recipient_name" name="recipient_name" value="Ambergris Investor LLC" required>

            <label for="recipient_address1">Address Line 1:</label>
            <input type="text" id="recipient_address1" name="recipient_address1" value="30 N Gould St Ste R" required>

            <label for="recipient_address2">City, State, Zip:</label>
            <input type="text" id="recipient_address2" name="recipient_address2" value="Sheridan, WY 82880" required>
        </div>

         <div class="section">
            <h3>Account Information</h3>
             <label for="account_info_name">Account Name:</label>
             <input type="text" id="account_info_name" name="account_info_name" value="Platinum Point Lots" required>
         </div>


        <div class="section">
            <h3>Transaction Activity</h3>
            <div id="transactions-container">
                </div>
            <button type="button" onclick="addTransaction()">Add Transaction Item</button>
            <p><small>Add at least one transaction.</small></p>
        </div>


        <div class="section">
            <h3>Past Payments Breakdown</h3>
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Paid Since Last Statement</th>
                        <th>Paid Year to Date</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Principal</td>
                        <td>$<input type="number" step="0.01" name="payments_breakdown[principal][last_statement]" required></td>
                        <td>$<input type="number" step="0.01" name="payments_breakdown[principal][ytd]" required></td>
                    </tr>
                    <tr>
                        <td>Interest</td>
                        <td>$<input type="number" step="0.01" name="payments_breakdown[interest][last_statement]" required></td>
                        <td>$<input type="number" step="0.01" name="payments_breakdown[interest][ytd]" required></td>
                    </tr>
                     <tr>
                        <td>Escrow (Taxes and Insurance)</td>
                        <td>$<input type="number" step="0.01" name="payments_breakdown[escrow][last_statement]" value="0.00" required></td>
                        <td>$<input type="number" step="0.01" name="payments_breakdown[escrow][ytd]" value="0.00" required></td>
                    </tr>
                     <tr>
                        <td>Other</td>
                        <td>$<input type="number" step="0.01" name="payments_breakdown[other][last_statement]" value="0.00" required></td>
                        <td>$<input type="number" step="0.01" name="payments_breakdown[other][ytd]" value="0.00" required></td>
                    </tr>
                     <tr>
                        <td>Fees</td>
                        <td>$<input type="number" step="0.01" name="payments_breakdown[fees][last_statement]" value="0.00" required></td>
                        <td>$<input type="number" step="0.01" name="payments_breakdown[fees][ytd]" value="0.00" required></td>
                    </tr>
                     <tr>
                        <td>Unapplied Funds</td>
                        <td>$<input type="number" step="0.01" name="payments_breakdown[unapplied][last_statement]" value="0.00" required></td>
                        <td>$<input type="number" step="0.01" name="payments_breakdown[unapplied][ytd]" value="0.00" required></td>
                    </tr>
                     <tr>
                        <td>Total</td>
                        <td>$<input type="number" step="0.01" name="payments_breakdown[total][last_statement]" required></td>
                        <td>$<input type="number" step="0.01" name="payments_breakdown[total][ytd]" required></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <button type="submit">Generate PDF</button>
    </form>

</body>
</html>
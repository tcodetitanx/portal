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
    <title>MLPA</title>
    <link rel="stylesheet" href="../assets/stylesLight.css">
</head>
<body>
    <div class="container">
        <h1>Master Loan Purchase Agreement</h1>
        <form id="form">
            <label for="date">Date:</label>
            <input type="text" id="date" name="date" value="<?php echo date('d/m/Y'); ?>" readonly required>

            <label for="buyername">Buyer Name:</label>
            <input type="text" id="buyername" name="buyername" required>

            <label for="buyertitle">Buyer Title:</label>
            <input type="text" id="buyertitle" name="buyertitle" required>

            <label for="purchaser">Purchaser:</label>
            <input type="text" id="purchaser" name="purchaser" required>

            <label for="loanvalue">Loan Value:</label>
            <input type="text" id="loanvalue" name="loanvalue" required>

            <label for="purchaseamount">Purchase Amount:</label>
            <input type="text" id="purchaseamount" name="purchaseamount" required>

            <label for="fico_score_650_min">FICO Score 650 Min:</label>
            <input type="text" id="fico_score_650_min" name="fico_score_650_min" required>

            <label for="fico_score_650_max">FICO Score 650 Max:</label>
            <input type="text" id="fico_score_650_max" name="fico_score_650_max" required>

            <label for="fico_score_680_min">FICO Score 680 Min:</label>
            <input type="text" id="fico_score_680_min" name="fico_score_680_min" required>

            <label for="fico_score_680_max">FICO Score 680 Max:</label>
            <input type="text" id="fico_score_680_max" name="fico_score_680_max" required>

            <label for="fico_score_700_min">FICO Score 700 Min:</label>
            <input type="text" id="fico_score_700_min" name="fico_score_700_min" required>

            <label for="fico_score_700_max">FICO Score 700 Max:</label>
            <input type="text" id="fico_score_700_max" name="fico_score_700_max" required>

            <label for="fico_score_760_min">FICO Score 760 Min:</label>
            <input type="text" id="fico_score_760_min" name="fico_score_760_min" required>

            <label for="fico_score_760_max">FICO Score 760 Max:</label>
            <input type="text" id="fico_score_760_max" name="fico_score_760_max" required>

            <label for="accountnumber">Account Number:</label>
            <input type="text" id="accountnumber" name="accountnumber" required>

            <label for="sellersloannumber">Seller's Loan Number:</label>
            <input type="text" id="sellersloannumber" name="sellersloannumber" required>

            <label for="unpaidprincipalbalance">Unpaid Principal Balance as of Cutoff Time:</label>
            <input type="text" id="unpaidprincipalbalance" name="unpaidprincipalbalance" required>

            <label for="loantype">Loan Type:</label>
            <input type="text" id="loantype" name="loantype" required>

            <label for="loancode">Loan Code:</label>
            <input type="text" id="loancode" name="loancode" required>

            <label for="originalficoscore">Original FICO Score:</label>
            <input type="text" id="originalficoscore" name="originalficoscore" required>

            <label for="originalficodate">Original FICO Date:</label>
            <input type="date" id="originalficodate" name="originalficodate" required>

            <label for="debttoincomeratio">Debt-to-Income Ratio:</label>
            <input type="text" id="debttoincomeratio" name="debttoincomeratio" required>

            <label for="annualhouseholdincome">Annual Household Income:</label>
            <input type="text" id="annualhouseholdincome" name="annualhouseholdincome" required>

            <label for="primarycity">Primary City:</label>
            <input type="text" id="primarycity" name="primarycity" required>

            <label for="primarystate">Primary State:</label>
            <input type="text" id="primarystate" name="primarystate" required>

            <label for="primaryzip">Primary Zip:</label>
            <input type="text" id="primaryzip" name="primaryzip" required>

            <label for="pbpc">Primary Borrower Property City:</label>
            <input type="text" id="pbpc" name="pbpc" required>

            <label for="installationcity">Installation City:</label>
            <input type="text" id="installationcity" name="installationcity" required>

            <label for="propertystate">Property State:</label>
            <input type="text" id="propertystate" name="propertystate" required>

            <label for="installationstate">Installation State:</label>
            <input type="text" id="installationstate" name="installationstate" required>

            <label for="propertyzipcode">Property Zip Code:</label>
            <input type="text" id="propertyzipcode" name="propertyzipcode" required>

            <label for="installationzip">Installation Zip:</label>
            <input type="text" id="installationzip" name="installationzip" required>

            <label for="contractoridentifier">Contractor Identifier:</label>
            <input type="text" id="contractoridentifier" name="contractoridentifier" required>

            <label for="ctsitp">Contractor that sold/installed the panels:</label>
            <input type="text" id="ctsitp" name="ctsitp" required>

            <label for="appdate">Application Date:</label>
            <input type="date" id="appdate" name="appdate" required>

            <label for="loandescription">Loan Description:</label>
            <input type="text" id="loandescription" name="loandescription" required>

            <label for="interestrate">Interest Rate:</label>
            <input type="text" id="interestrate" name="interestrate" required>

            <label for="originationdate">Origination Date:</label>
            <input type="date" id="originationdate" name="originationdate" required>

            <label for="maturitydate">Maturity Date:</label>
            <input type="date" id="maturitydate" name="maturitydate" required>

            <label for="cutofftime">Cutoff Time:</label>
            <input type="text" id="cutofftime" name="cutofftime" required>

            <label for="totalapprovedloanamount">Total Approved Loan Amount:</label>
            <input type="text" id="totalapprovedloanamount" name="totalapprovedloanamount" required>

            <label for="totalfundedtodate">Total Funded to Date:</label>
            <input type="text" id="totalfundedtodate" name="totalfundedtodate" required>

            <label for="totalprincipalpaidtodate">Total Principal Paid to Date:</label>
            <input type="text" id="totalprincipalpaidtodate" name="totalprincipalpaidtodate" required>

            <label for="totalinterestpaidtodate">Total Interest Paid to Date:</label>
            <input type="text" id="totalinterestpaidtodate" name="totalinterestpaidtodate" required>

            <label for="currentprincipalbalance">Current Principal Balance:</label>
            <input type="text" id="currentprincipalbalance" name="currentprincipalbalance" required>

            <label for="amountofsecondadvance">Amount of Second Advance:</label>
            <input type="text" id="amountofsecondadvance" name="amountofsecondadvance" required>

            <label for="currentaccruedinterest">Current Accrued Interest:</label>
            <input type="text" id="currentaccruedinterest" name="currentaccruedinterest" required>

            <label for="finalfundingdate">Final Funding Date:</label>
            <input type="date" id="finalfundingdate" name="finalfundingdate" required>

            <label for="monthlypipayment">Monthly P&I Payment:</label>
            <input type="text" id="monthlypipayment" name="monthlypipayment" required>

            <label for="paymentfrequency">Payment Frequency:</label>
            <input type="text" id="paymentfrequency" name="paymentfrequency" required>

            <label for="dateoflastpayment">Date of Last Payment:</label>
            <input type="date" id="dateoflastpayment" name="dateoflastpayment" required>

            <label for="dayspastdue">Days Past Due:</label>
            <input type="text" id="dayspastdue" name="dayspastdue" required>

            <label for="times30dayslate">Times 30 Days Late:</label>
            <input type="text" id="times30dayslate" name="times30dayslate" required>

            <label for="times60dayslate">Times 60 Days Late:</label>
            <input type="text" id="times60dayslate" name="times60dayslate" required>

            <label for="times90dayslate">Times 90 Days Late:</label>
            <input type="text" id="times90dayslate" name="times90dayslate" required>

            <label for="mostrecentficoscore">Most Recent FICO Score:</label>
            <input type="text" id="mostrecentficoscore" name="mostrecentficoscore" required>

            <label for="mostrecentfico">Most Recent FICO:</label>
            <input type="text" id="mostrecentfico" name="mostrecentfico" required>

            <label for="effectivedateoffico">Effective Date of Updated FICO Score:</label>
            <input type="date" id="effectivedateoffico" name="effectivedateoffico" required>

            <label for="promoperiodenddate">Promotional Period End Date:</label>
            <input type="text" id="promoperiodenddate" name="promoperiodenddate" required>

            <label for="purchasepricepercentage">Purchase Price Percentage:</label>
            <input type="text" id="purchasepricepercentage" name="purchasepricepercentage" required>

            <label for="initialpurchaseprice">Initial Purchase Price:</label>
            <input type="text" id="initialpurchaseprice" name="initialpurchaseprice" required>

            <button type="button" onclick="generatePDF()">Generate PDF</button>
        </form>
    </div>

    <script>
    function generatePDF() {
        const form = document.getElementById('form');
        const formData = new FormData(form);

        fetch('generate_pdf.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.ok) {
                return response.blob();
            } else {
                throw new Error('Failed to generate PDF');
            }
        })
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'loan_purchase_agreement.pdf';
            document.body.appendChild(a);
            a.click();
            a.remove();
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
    </script>
</body>
</html>

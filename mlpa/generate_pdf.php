<?php
require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $date = $_POST['date'] ?? "";
    $buyername = $_POST['buyername'] ?? "";
    $buyertitle = $_POST['buyertitle'] ?? "";
    $purchaser = $_POST['purchaser'] ?? "";
    $loanvalue = $_POST['loanvalue'] ?? "";
    $purchaseamount = $_POST['purchaseamount'] ?? "";
    $fico_score_650_min = $_POST['fico_score_650_min'] ?? "";
    $fico_score_650_max = $_POST['fico_score_650_max'] ?? "";
    $fico_score_680_min = $_POST['fico_score_680_min'] ?? "";
    $fico_score_680_max = $_POST['fico_score_680_max'] ?? "";
    $fico_score_700_min = $_POST['fico_score_700_min'] ?? "";
    $fico_score_700_max = $_POST['fico_score_700_max'] ?? "";
    $fico_score_760_min = $_POST['fico_score_760_min'] ?? "";
    $fico_score_760_max = $_POST['fico_score_760_max'] ?? "";
    $accountnumber = $_POST['accountnumber'] ?? "";
    $sellersloannumber = $_POST['sellersloannumber'] ?? "";
    $unpaidprincipalbalance = $_POST['unpaidprincipalbalance'] ?? "";
    $loantype = $_POST['loantype'] ?? "";
    $loancode = $_POST['loancode'] ?? "";
    $originalficoscore = $_POST['originalficoscore'] ?? "";
    $originalficodate = $_POST['originalficodate'] ?? "";
    $debttoincomeratio = $_POST['debttoincomeratio'] ?? "";
    $annualhouseholdincome = $_POST['annualhouseholdincome'] ?? "";
    $primarycity = $_POST['primarycity'] ?? "";
    $primarystate = $_POST['primarystate'] ?? "";
    $primaryzip = $_POST['primaryzip'] ?? "";
    $pbpc = $_POST['pbpc'] ?? "";
    $installationcity = $_POST['installationcity'] ?? "";
    $propertystate = $_POST['propertystate'] ?? "";
    $installationstate = $_POST['installationstate'] ?? "";
    $propertyzipcode = $_POST['propertyzipcode'] ?? "";
    $installationzip = $_POST['installationzip'] ?? "";
    $contractoridentifier = $_POST['contractoridentifier'] ?? "";
    $ctsitp = $_POST['ctsitp'] ?? "";
    $appdate = $_POST['appdate'] ?? "";
    $loandescription = $_POST['loandescription'] ?? "";
    $interestrate = $_POST['interestrate'] ?? "";
    $originationdate = $_POST['originationdate'] ?? "";
    $maturitydate = $_POST['maturitydate'] ?? "";
    $cutofftime = $_POST['cutofftime'] ?? "";
    $totalapprovedloanamount = $_POST['totalapprovedloanamount'] ?? "";
    $totalfundedtodate = $_POST['totalfundedtodate'] ?? "";
    $totalprincipalpaidtodate = $_POST['totalprincipalpaidtodate'] ?? "";
    $totalinterestpaidtodate = $_POST['totalinterestpaidtodate'] ?? "";
    $currentprincipalbalance = $_POST['currentprincipalbalance'] ?? "";
    $amountofsecondadvance = $_POST['amountofsecondadvance'] ?? "";
    $currentaccruedinterest = $_POST['currentaccruedinterest'] ?? "";
    $finalfundingdate = $_POST['finalfundingdate'] ?? "";
    $monthlypipayment = $_POST['monthlypipayment'] ?? "";
    $paymentfrequency = $_POST['paymentfrequency'] ?? "";
    $dateoflastpayment = $_POST['dateoflastpayment'] ?? "";
    $dayspastdue = $_POST['dayspastdue'] ?? "";
    $times30dayslate = $_POST['times30dayslate'] ?? "";
    $times60dayslate = $_POST['times60dayslate'] ?? "";
    $times90dayslate = $_POST['times90dayslate'] ?? "";
    $mostrecentficoscore = $_POST['mostrecentficoscore'] ?? "";
    $effectivedateoffico = $_POST['effectivedateoffico'] ?? "";
    $promoperiodenddate = $_POST['promoperiodenddate'] ?? "";
    $purchasepricepercentage = $_POST['purchasepricepercentage'] ?? "";
    $initialpurchaseprice = $_POST['initialpurchaseprice'] ?? "";

    // Load the HTML template
    $html_template = file_get_contents('htmlinput.html');

    // Replace placeholders with actual values from the form
    $replacements = [
        '**DATE**' => $date,
        '**BUYERNAME**' => $buyername,
        '**BUYERTITLE**' => $buyertitle,
        '**PURCHASER**' => $purchaser,
        '**LOANVALUE**' => $loanvalue,
        '**PURCHASEAMOUNT**' => $purchaseamount,
        '**FICO_SCORE_650_MIN**' => $fico_score_650_min,
        '**FICO_SCORE_650_MAX**' => $fico_score_650_max,
        '**FICO_SCORE_680_MIN**' => $fico_score_680_min,
        '**FICO_SCORE_680_MAX**' => $fico_score_680_max,
        '**FICO_SCORE_700_MIN**' => $fico_score_700_min,
        '**FICO_SCORE_700_MAX**' => $fico_score_700_max,
        '**FICO_SCORE_760_MIN**' => $fico_score_760_min,
        '**FICO_SCORE_760_MAX**' => $fico_score_760_max,
        '**ACCOUNTNUMBER**' => $accountnumber,
        '**SELLERSLOANNUMBER**' => $sellersloannumber,
        '**UNPAIDPRINCIPALBALANCE**' => $unpaidprincipalbalance,
        '**LOANTYPE**' => $loantype,
        '**LOANCODE**' => $loancode,
        '**ORIGINALFICOSCORE**' => $originalficoscore,
        '**ORIGINALFICODATE**' => $originalficodate,
        '**DEBTTOINCOMERATIO**' => $debttoincomeratio,
        '**ANNUALHOUSEHOLDINCOME**' => $annualhouseholdincome,
        '**PRIMARYCITY**' => $primarycity,
        '**PRIMARYSTATE**' => $primarystate,
        '**PRIMARYZIP**' => $primaryzip,
        '**PBPC**' => $pbpc,
        '**INSTALLATIONCITY**' => $installationcity,
        '**PROPERTYSTATE**' => $propertystate,
        '**INSTALLATIONSTATE**' => $installationstate,
        '**PROPERTYZIPCODE**' => $propertyzipcode,
        '**INSTALLATIONZIP**' => $installationzip,
        '**CONTRACTORIDENTIFIER**' => $contractoridentifier,
        '**CTSITP**' => $ctsitp,
        '**APPDATE**' => $appdate,
        '**LOANDESCRIPTION**' => $loandescription,
        '**INTERESTRATE**' => $interestrate,
        '**ORIGINATIONDATE**' => $originationdate,
        '**MATURITYDATE**' => $maturitydate,
        '**CUTOFFTIME**' => $cutofftime,
        '**TOTALAPPROVEDLOANAMOUNT**' => $totalapprovedloanamount,
        '**TOTALFUNDEDTODATE**' => $totalfundedtodate,
        '**TOTALPRINCIPALPAIDTODATE**' => $totalprincipalpaidtodate,
        '**TOTALINTERESTPAIDTODATE**' => $totalinterestpaidtodate,
        '**CURRENTPRINCIPALBALANCE**' => $currentprincipalbalance,
        '**AMOUNTOFSECONDADVANCE**' => $amountofsecondadvance,
        '**CURRENTACCRUEDINTEREST**' => $currentaccruedinterest,
        '**FINALFUNDINGDATE**' => $finalfundingdate,
        '**MONTHLYPIPAYMENT**' => $monthlypipayment,
        '**PAYMENTFREQUENCY**' => $paymentfrequency,
        '**DATEOFLASTPAYMENT**' => $dateoflastpayment,
        '**DAYSPASTDUE**' => $dayspastdue,
        '**TIMES30DAYSLATE**' => $times30dayslate,
        '**TIMES60DAYSLATE**' => $times60dayslate,
        '**TIMES90DAYSLATE**' => $times90dayslate,
        '**MOSTRECENTFICOSCORE**' => $mostrecentficoscore,
        '**EFFECTIVEDATEOFFICO**' => $effectivedateoffico,
        '**PROMOPERIODENDDATE**' => $promoperiodenddate,
        '**PURCHASEPRICEPERCENTAGE**' => $purchasepricepercentage,
        '**INITIALPURCHASEPRICE**' => $initialpurchaseprice,
    ];

    // Replace the placeholders in the HTML template
    foreach ($replacements as $placeholder => $value) {
        $html_template = str_replace($placeholder, $value, $html_template);
    }

    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Axiom Corp');
    $pdf->SetTitle('Master Loan Purchase Agreement');
    $pdf->SetSubject('Master Loan Purchase Agreement');
    $pdf->SetKeywords('Loan, Purchase, Agreement');

    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

    // Add a page
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('helvetica', '', 12);

    // Write the HTML content to the PDF
    $pdf->writeHTML($html_template, true, false, true, false, '');

    // Output the PDF as a download
    ob_end_clean(); // Clean the output buffer to avoid any warnings/errors
    $pdf->Output('loan_purchase_agreement.pdf', 'I');
}
?>

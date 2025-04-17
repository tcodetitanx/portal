<?php
require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');

// Create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Axiom Corp');
$pdf->SetTitle('Service Agreement');
$pdf->SetSubject('Solar Loan Dissolution Service Agreement');
$pdf->SetKeywords('Service Agreement, Solar Loan, Dissolution, Legal Document');

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 12);

// Get and sanitize data from POST parameters
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Get data from POST parameters
$name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : 'Client Name';
$address = isset($_POST['address']) ? sanitizeInput($_POST['address']) : 'Client Address';
$phone = isset($_POST['phone']) ? sanitizeInput($_POST['phone']) : 'Client Phone';
$creation_date = isset($_POST['creation_date']) ? sanitizeInput($_POST['creation_date']) : date('Y-m-d');
$amount = isset($_POST['amount']) && is_numeric($_POST['amount']) ? sanitizeInput($_POST['amount']) : 0;
$months = isset($_POST['months']) && is_numeric($_POST['months']) ? (int)sanitizeInput($_POST['months']) : 0;
$clause_choice = isset($_POST['clause_choice']) ? sanitizeInput($_POST['clause_choice']) : 'default';
$language = isset($_POST['language']) ? sanitizeInput($_POST['language']) : 'english';

// Define Spanish translations for clause text
$payment_help_en = '<h2>4. Payment Coverage</h2>
"Service Provider" agrees to cover the client\'s loan payments up to a maximum cumulative amount of 1,500. Payment will be made directly to the "Client\'s" behalf. This coverage is provided to alleviate financial strain on the "Client" while "Service Provider works towards "Client\'s" targeted resolution.
The "Service Provider\'s" obligation will cease once <br>
a. The date a resolution is reached or; <br>
b. Once the total amount of covered payments reaches the coverage cap of $1,500.';

$payment_help_es = '<h2>4. Cobertura de Pagos</h2>
El "Proveedor de Servicios" acuerda cubrir los pagos del préstamo del cliente hasta un monto acumulativo máximo de 1,500. El pago se realizará directamente en nombre del "Cliente". Esta cobertura se proporciona para aliviar la tensión financiera del "Cliente" mientras el "Proveedor de Servicios" trabaja hacia la resolución objetivo del "Cliente".
La obligación del "Proveedor de Servicios" cesará una vez que <br>
a. Se alcance una resolución o; <br>
b. Una vez que el monto total de los pagos cubiertos alcance el límite de cobertura de $1,500.';

$guarantee_en = '<h2>4. 90-Day Money-Back Guarantee</h2>
<ol type="a">
    <li>If, within 90 days from the date of this Agreement, Service Provider has not secured a resolution which outweighs the fee, the Client may request a refund of the Retainer Fee.</li>
    <li>To be eligible for the refund, Client must provide a written request to execute this clause no later than the 90th day following the execution of this Agreement.</li>
    <li>Upon receipt of such notice, Service Provider will issue a refund of the full $'. $amount . ' Retainer Fee or any payments made up to that point, whatever amount is smaller within 30 days, provided no acceptable resolution has been reached.</li>
    <li>This clause cannot be executed if the case is currently in litigation or if the case is on docket.</li>
</ol>';

$guarantee_es = '<h2>4. Garantía de Devolución de Dinero de 90 Días</h2>
<ol type="a">
    <li>Si, dentro de los 90 días a partir de la fecha de este Acuerdo, el Proveedor de Servicios no ha asegurado una resolución que supere la tarifa, el Cliente puede solicitar un reembolso de la Tarifa de Retención.</li>
    <li>Para ser elegible para el reembolso, el Cliente debe proporcionar una solicitud por escrito para ejecutar esta cláusula a más tardar el día 90 después de la ejecución de este Acuerdo.</li>
    <li>Al recibir dicha notificación, el Proveedor de Servicios emitirá un reembolso del total de $'. $amount . ' de la Tarifa de Retención o cualquier pago realizado hasta ese momento, cualquiera que sea la cantidad menor dentro de los 30 días, siempre que no se haya alcanzado una resolución aceptable.</li>
    <li>Esta cláusula no puede ejecutarse si el caso está actualmente en litigio o si el caso está en el expediente.</li>
</ol>';

$termination_en = '
<h2>9. Termination of Agreement</h2>
<p>This Agreement may be terminated by either party upon written notice:</p>
<ol type="a">
    <li>By the Client: If Client demonstrates that the services or outcomes are not consistent with the given plan within the 90-day period, as outlined in Section 4.</li>
    <li>By the Service Provider: If Client fails to provide necessary documentation or cooperate with the process.</li>
</ol>';

$termination_es = '
<h2>9. Terminación del Acuerdo</h2>
<p>Este Acuerdo puede ser terminado por cualquiera de las partes mediante notificación por escrito:</p>
<ol type="a">
    <li>Por el Cliente: Si el Cliente demuestra que los servicios o resultados no son consistentes con el plan dado dentro del período de 90 días, como se describe en la Sección 4.</li>
    <li>Por el Proveedor de Servicios: Si el Cliente no proporciona la documentación necesaria o no coopera con el proceso.</li>
</ol>';

// Clause logic with language support
if ($clause_choice === 'default' || $clause_choice === 'Payment Help') {
    $clause_text = ($language === 'spanish') ? $payment_help_es : $payment_help_en;
    $second_clause_text = "";
} else if ($clause_choice === '90-day Guarantee') {
    $clause_text = ($language === 'spanish') ? $guarantee_es : $guarantee_en;
    $second_clause_text = ($language === 'spanish') ? $termination_es : $termination_en;
}

// Format the creation date based on language
if ($language === 'spanish') {
    setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'es');
    $month_es = strftime('%B', strtotime($creation_date));
    $day_num = date('j', strtotime($creation_date));
    $year = date('Y', strtotime($creation_date));
    $agreement_date = "$day_num de $month_es de $year";
    setlocale(LC_TIME, '');
} else {
    $agreement_date = date('jS \d\a\y \of F, Y', strtotime($creation_date));
}

// Payment terms logic with language support
if ($amount > 0) {
    if ($months == 0) {
        if ($language === 'spanish') {
            $payment_description = "El monto total de \${$amount} se debe pagar al momento de la ejecución de este Acuerdo.";
        } else {
            $payment_description = "The full amount of \${$amount} is due upon execution of this Agreement.";
        }
        $remaining_balance = 0;
        $first_payment = number_format(round($amount, 2), 2);
    } else {
        $num_of_payments = $months + 1;
        $installment_amount = $amount / $num_of_payments;
        $first_payment = number_format(round($installment_amount, 2), 2);
        $remaining_balance = round($amount - $installment_amount, 2);

        if ($language === 'spanish') {
            $payment_description = "El primer pago de \${$first_payment} se debe realizar en la fecha de ejecución de este acuerdo. El saldo restante de \${$remaining_balance} se dividirá en {$months} pagos mensuales iguales de \${$first_payment} cada uno.";
        } else {
            $payment_description = "The first payment of \${$first_payment} is due on the date of execution of this agreement. The remaining balance of \${$remaining_balance} will be divided into {$months} equal monthly payments of \${$first_payment} each.";
        }
    }
} else {
    $first_payment = 0;
    $remaining_balance = 0;
    if ($language === 'spanish') {
        $payment_description = "Los términos de pago no están definidos.";
    } else {
        $payment_description = "Payment terms are not defined.";
    }
}

if ($clause_choice == 'Payment Help') {
    if ($language === 'spanish') {
        $part_5_insert = '<li>Proporcionar información precisa y oportuna sobre los pagos de su préstamo, incluidos los montos de pago, las fechas de vencimiento y la información de contacto del acreedor. El "Cliente" debe notificar al "Proveedor de Servicios" inmediatamente de cualquier cambio en los detalles de pago de su préstamo.</li>';
    } else {
        $part_5_insert = '<li>Provide accurate and timely information regarding their loan payments, including payment amounts, due dates, and creditor contact information. The "Client" must notify the "Service Provider" immediately of any changes to their loan payment details.</li>';
    }
} else {
    $part_5_insert = "";
}

// Define content based on language
if ($language === 'spanish') {
    // Spanish content
    $main_content = <<<EOD
<h1>Acuerdo de Servicio</h1>
<p>Este Acuerdo de Servicio ("Acuerdo") se celebra el {$agreement_date}, entre:</p>
<p><strong>Axiom Corp</strong><br>
1510 N State Street STE 300, Orem, UT 84057<br>
Teléfono: 844-402-9466<br>
("Proveedor de Servicios")</p>
<p>y</p>
<p><strong>{$name}</strong><br>
{$address}<br>
Teléfono: {$phone}<br>
("Cliente")</p>
<p>CONSIDERANDO QUE, el Cliente tiene un préstamo solar que desea disolver, y el Proveedor de Servicios tiene la experiencia y la capacidad para ayudar en este asunto;</p>
<p>CONSIDERANDO QUE, el Cliente acepta contratar al Proveedor de Servicios con el propósito de revisar la validez del contrato y reparar el crédito relacionado con la disolución del préstamo solar;</p>
<p>AHORA, POR LO TANTO, las partes acuerdan lo siguiente:</p>
<h2>1. Alcance de los Servicios</h2>
<p>El Proveedor de Servicios acepta proporcionar los siguientes servicios:</p>
<ol type="a">
    <li>Revisar el contrato de préstamo solar para determinar su validez y potencial de disolución.</li>
    <li>Ayudar al Cliente en los procedimientos de reparación de crédito, según sea necesario, para facilitar la disolución del préstamo.</li>
    <li>Ayudar a eliminar cualquier gravamen asociado con el préstamo/arrendamiento.</li>
    <li>Negociar términos de préstamo adecuados según las directivas del cliente.</li>
</ol>
<h2>2. Tarifa de Retención</h2>
<p>El Cliente acepta pagar al Proveedor de Servicios una tarifa de retención de \${$amount} ("Tarifa de Retención"). Esta tarifa cubre el costo de los servicios especificados en la Sección 1.</p>
<h2>3. Términos de Pago</h2>
<p>{$payment_description}</p><br>
{$clause_text}
<br><h2>5. Responsabilidades del Cliente</h2>
<p>El Cliente acepta:</p>
<ol type="a">
    <li>Proporcionar toda la documentación necesaria con respecto a su préstamo solar e historial crediticio.</li>
    <li>Cooperar con el Proveedor de Servicios para facilitar el proceso de disolución del préstamo.</li>
    {$part_5_insert}
</ol>
<h2>6. Sin Garantía de Resultado</h2>
<p>Si bien el Proveedor de Servicios utilizará su experiencia para ayudar a disolver el préstamo solar del Cliente, no se garantiza ningún resultado específico.</p>
<h2>7. Ley Aplicable</h2>
<p>Este Acuerdo se regirá e interpretará de acuerdo con las leyes del Estado de Utah.</p>
<h2>8. Acuerdo Completo</h2>
<p>Este Acuerdo constituye el entendimiento completo entre las partes y reemplaza todas las discusiones, acuerdos o entendimientos previos de cualquier tipo. Cualquier modificación a este Acuerdo debe hacerse por escrito y ser firmada por ambas partes.</p>
{$second_clause_text}
EOD;

    // Spanish signature content
    $signature_content = <<<EOD
<p>EN TESTIMONIO DE LO CUAL, las partes han ejecutado este Acuerdo de Servicio a partir del día y año escritos anteriormente.</p>
<p><strong>Axiom Corp</strong><br>
Nombre: Axiom Corp<br><br>
Firma:<br><br><br>
Fecha:</p>
<p><strong>Cliente</strong><br>
Nombre: {$name}<br><br>
Firma:<br><br><br>
Fecha:</p>
EOD;

} else {
    // English content (original)
    $main_content = <<<EOD
<h1>Service Agreement</h1>
<p>This Service Agreement ("Agreement") is made and entered into on this {$agreement_date}, by and between:</p>
<p><strong>Axiom Corp</strong><br>
1510 N State Street STE 300, Orem, UT 84057<br>
Phone: 844-402-9466<br>
("Service Provider")</p>
<p>and</p>
<p><strong>{$name}</strong><br>
{$address}<br>
Phone: {$phone}<br>
("Client")</p>
<p>WHEREAS, Client has a solar loan that they wish to dissolve, and Service Provider has the expertise and ability to assist in this matter;</p>
<p>WHEREAS, Client agrees to retain Service Provider for the purpose of contract validity review and credit repair related to the dissolution of the solar loan;</p>
<p>NOW, THEREFORE, the parties agree as follows:</p>
<h2>1. Scope of Services</h2>
<p>Service Provider agrees to provide the following services:</p>
<ol type="a">
    <li>Review the solar loan contract for validity and potential for dissolution.</li>
    <li>Assist Client in credit repair procedures, as needed, to facilitate loan dissolution.</li>
    <li>Aid in removing any liens associated with the loan/lease.</li>
    <li>Negotiate suitable loan terms per clients directive.</li>
</ol>
<h2>2. Retainer Fee</h2>
<p>Client agrees to pay Service Provider a retainer fee of \${$amount} ("Retainer Fee"). This fee covers the cost of the services specified in Section 1.</p>
<h2>3. Payment Terms</h2>
<p>{$payment_description}</p><br>
{$clause_text}
<br><h2>5. Client Responsibilities</h2>
<p>Client agrees to:</p>
<ol type="a">
    <li>Provide all necessary documentation regarding their solar loan and credit history.</li>
    <li>Cooperate with Service Provider to facilitate the loan dissolution process.</li>
    {$part_5_insert}
</ol>
<h2>6. No Guarantee of Outcome</h2>
<p>While Service Provider will use its expertise to assist in dissolving the Client's solar loan, no specific outcome is guaranteed.</p>
<h2>7. Governing Law</h2>
<p>This Agreement shall be governed by and construed in accordance with the laws of the State of Utah.</p>
<h2>8. Entire Agreement</h2>
<p>This Agreement constitutes the entire understanding between the parties and supersedes all prior discussions, agreements, or understandings of any kind. Any modifications to this Agreement must be made in writing and signed by both parties.</p>
{$second_clause_text}
EOD;

    // English signature content
    $signature_content = <<<EOD
<p>IN WITNESS WHEREOF, the parties hereto have executed this Service Agreement as of the day and year first above written.</p>
<p><strong>Axiom Corp</strong><br>
Name: Axiom Corp<br><br>
Signature:<br><br><br>
Date:</p>
<p><strong>Client</strong><br>
Name: {$name}<br><br>
Signature:<br><br><br>
Date:</p>
EOD;
}

// Output main content
$pdf->writeHTML($main_content, true, false, true, false, '');

// Check if there's enough space for the signature section
$signature_height_estimate = 100; // Estimate height in mm (adjust based on your content)
$current_y = $pdf->GetY();
$page_height = $pdf->getPageHeight();
$bottom_margin = PDF_MARGIN_BOTTOM;

if ($current_y + $signature_height_estimate > $page_height - $bottom_margin) {
    $pdf->AddPage(); // Add a new page if signature won't fit
}

// Output signature section with nobreak to keep it together
$pdf->writeHTML($signature_content, true, false, true, false, '');

// Close and output PDF document
$pdf->Output('contract.pdf', 'I');
?>
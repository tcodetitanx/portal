<?php
/**
 * PDF Helper Functions
 *
 * This file contains helper functions for PDF generation
 * that can be used across the application.
 */

// Require TCPDF library
require_once(__DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php');

/**
 * Create a new PDF document with standard settings
 *
 * @param string $title Document title
 * @param string $subject Document subject
 * @param string $author Document author (default: 'Bull Axiom LLC')
 * @param string $keywords Document keywords (default: '')
 * @return TCPDF The configured PDF object
 */
function createPDF($title, $subject, $author = 'Bull Axiom LLC', $keywords = '') {
    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($author);
    $pdf->SetTitle($title);
    $pdf->SetSubject($subject);
    $pdf->SetKeywords($keywords);

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

    return $pdf;
}

/**
 * Create a watermarked copy of a PDF
 *
 * @param string $content HTML content to write to the PDF
 * @param string $title Document title
 * @param string $subject Document subject
 * @param string $watermark_path Path to watermark image (optional)
 * @return TCPDF The watermarked PDF object
 */
function createWatermarkedPDF($content, $title, $subject, $watermark_path = null) {
    // Create a new PDF instance
    $pdf = createPDF($title . ' - Copy', $subject . ' - Copy');

    // Write the HTML content to the PDF
    $pdf->writeHTML($content, true, false, true, false, '');

    // Add watermark to each page
    if ($watermark_path && file_exists($watermark_path)) {
        // Use the image watermark
        $num_pages = $pdf->getNumPages();
        for ($i = 1; $i <= $num_pages; $i++) {
            $pdf->setPage($i);
            $pdf->Image($watermark_path, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0, false, false, false);
        }
    } else {
        // Create a simple text watermark
        $num_pages = $pdf->getNumPages();
        for ($i = 1; $i <= $num_pages; $i++) {
            $pdf->setPage($i);
            // Add a diagonal text watermark
            $pdf->SetFont('helvetica', 'B', 60);
            $pdf->SetTextColor(200, 200, 200);
            $pdf->StartTransform();
            $pdf->Rotate(45, 105, 148);
            $pdf->Text(105, 148, 'COPY VIEW');
            $pdf->StopTransform();
        }
    }

    return $pdf;
}

/**
 * Generate a unique filename for a PDF
 *
 * @param string $prefix Prefix for the filename
 * @param string $client_name Client name to include in the filename
 * @return string The generated filename
 */
function generatePDFFilename($prefix, $client_name) {
    $timestamp = date('YmdHis');
    return $prefix . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $client_name) . '_' . $timestamp . '.pdf';
}

/**
 * Save a PDF to the documents directory
 *
 * @param TCPDF $pdf The PDF object to save
 * @param string $filename The filename to save the PDF as
 * @param bool $create_dir Whether to create the documents directory if it doesn't exist
 * @return string The full path to the saved PDF
 */
function savePDF($pdf, $filename, $create_dir = true) {
    // Include database helper functions
    if (file_exists(__DIR__ . '/db_helpers.php')) {
        require_once(__DIR__ . '/db_helpers.php');
        $documents_dir = getDocumentsDirectory($create_dir);
    } else {
        // Fallback if helper functions are not available
        $documents_dir = realpath(__DIR__ . '/../../documents/');
        if ($documents_dir === false) {
            $parent_dir = realpath(__DIR__ . '/../..');
            $documents_dir = $parent_dir . DIRECTORY_SEPARATOR . 'documents';
        }

        // Ensure directory path ends with a directory separator
        if (substr($documents_dir, -1) !== DIRECTORY_SEPARATOR) {
            $documents_dir .= DIRECTORY_SEPARATOR;
        }

        // Create the directory if it doesn't exist
        if ($create_dir && !file_exists($documents_dir)) {
            $documents_parent_dir = dirname($documents_dir);
            if (!file_exists($documents_parent_dir)) {
                mkdir($documents_parent_dir, 0777, true);
            }
            mkdir($documents_dir, 0777, true);
        }
    }

    $filepath = $documents_dir . $filename;

    // Save the PDF
    $pdf->Output($filepath, 'F');

    return $filepath;
}

/**
 * Save document reference in the database
 *
 * @param int $contact_id Contact ID
 * @param string $document_type Document type
 * @param string $filename Filename (without path)
 * @return bool True if successful, false otherwise
 */
function saveDocumentReference($contact_id, $document_type, $filename) {
    try {
        // Create a direct database connection
        $conn = mysqli_connect('localhost', 'root', '', 'portal_crm');

        if (!$conn) {
            error_log("Database connection failed when saving document reference: " . mysqli_connect_error());
            return false;
        }

        $doc_sql = "INSERT INTO documents (contact_id, document_type, file_path) VALUES (?, ?, ?)";
        $doc_stmt = $conn->prepare($doc_sql);

        if ($doc_stmt) {
            $doc_stmt->bind_param("iss", $contact_id, $document_type, $filename);
            $result = $doc_stmt->execute();
            mysqli_close($conn);
            return $result;
        } else {
            error_log("Failed to prepare document insert statement: " . $conn->error);
            mysqli_close($conn);
        }
    } catch (Exception $e) {
        error_log("Error saving document reference: " . $e->getMessage());
    }

    return false;
}

/**
 * Update contact record with document date
 *
 * @param int $contact_id Contact ID
 * @param string $document_type Document type (first_noe, final_noe, lawsuit)
 * @return bool True if successful, false otherwise
 */
function updateContactDocumentDate($contact_id, $document_type) {
    try {
        // Create a direct database connection
        $conn = mysqli_connect('localhost', 'root', '', 'portal_crm');

        if (!$conn) {
            error_log("Database connection failed when updating contact: " . mysqli_connect_error());
            return false;
        }

        // Determine which field to update based on document type
        $field_name = '';
        switch ($document_type) {
            case 'first_noe':
                $field_name = 'first_noe';
                break;
            case 'final_noe':
                $field_name = 'final_noe';
                break;
            case 'lawsuit':
                $field_name = 'suit_filed';
                break;
            default:
                error_log("Invalid document type: $document_type");
                mysqli_close($conn);
                return false;
        }

        // Update the contact record
        $update_sql = "UPDATE contacts SET $field_name = CURDATE() WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);

        if ($update_stmt) {
            $update_stmt->bind_param("i", $contact_id);
            $result = $update_stmt->execute();
            mysqli_close($conn);
            return $result;
        } else {
            error_log("Failed to prepare contact update statement: " . $conn->error);
            mysqli_close($conn);
        }
    } catch (Exception $e) {
        error_log("Error updating contact document date: " . $e->getMessage());
    }

    return false;
}
?>

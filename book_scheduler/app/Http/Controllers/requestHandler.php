<?php
/*
 * Handles requests
 * */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use ZendPdf\PdfDocument;

class RequestHandler extends Controller
{
    public function getPdfTitle()
    {
        try {
            $pdfPath = "../../../pdf/My resume.pdf";
            $pdf = PdfDocument::load($pdfPath);

            // Get the PDF metadata and extract the title
            $metadata = $pdf->getMetadata();
            $title = $metadata->get("Title");

            return response()->json($title);
        } catch (Exception $e) {
            echo "An error occurred: " . $e->getMessage() . "\n";
        }
    }
}

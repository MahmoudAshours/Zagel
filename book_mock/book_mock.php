<?php
require "vendor/autoload.php";

use setasign\Fpdi\Fpdi;

$pdf = new \Clegginabox\PDFMerger\PDFMerger();
$pdfs = new Fpdi();

$ERR_CODE = 0;
$pdf_path = $argv[1];
$start_page = (int) $argv[2];
$factor = (int) $argv[3];
$end_page =  $start_page + $factor;

// check if limit pages is handled 
$pageCount = $pdfs->setSourceFile("{$pdf_path}");

if ($start_page < $pageCount) {
	$name = str_replace('.pdf', '', basename($pdf_path));
	$test = $name  . "_" . $start_page . "_" . $factor;
	mkdir($name);
	if ($end_page > $pageCount) {
		$end_page = $pageCount;
		try {
			$pdf->addPDF("$pdf_path", "$start_page - $end_page");
			$pdf->merge("file", $name . ".pdf", "P");
		} catch (InvalidArgumentException $e) {
			syslog(LOG_ALERT, "$e");
			$ERR_CODE = 3;
		}

		exec(
			"convert -alpha remove -alpha off -colorspace sRGB -antialias -filter Mitchell -density 300  $name.pdf $name/$test.png &"
		);
	}
} else {
	$ERR_CODE = 4;
}
exit($ERR_CODE);

<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Str;

use PhpOffice\PhpWord\IOFactory as PhpWordIOFactory;
use PhpOffice\PhpSpreadsheet\IOFactory as PhpSpreadsheetIOFactory;

class DocumentConverterService
{
    public static function convertToPdf($filePath)
    {
        // $filePath is the relative path in the 'public' disk or 'local' disk
        $absolutePath = Storage::disk('public')->path($filePath);
        $directory = dirname($absolutePath);
        
        $fileName = pathinfo($absolutePath, PATHINFO_FILENAME);
        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        $pdfFileName = $fileName . '.pdf';
        $pdfAbsolutePath = $directory . '/' . $pdfFileName;
        
        // If it already exists, return the path
        if (file_exists($pdfAbsolutePath)) {
            return str_replace(Storage::disk('public')->path(''), '', $pdfAbsolutePath);
        }

        try {
            if (in_array($extension, ['doc', 'docx', 'rtf', 'odt'])) {
                // Setup DomPDF for PhpWord
                \PhpOffice\PhpWord\Settings::setPdfRendererName(\PhpOffice\PhpWord\Settings::PDF_RENDERER_DOMPDF);
                \PhpOffice\PhpWord\Settings::setPdfRendererPath(base_path('vendor/dompdf/dompdf'));

                $phpWord = PhpWordIOFactory::load($absolutePath);
                $pdfWriter = PhpWordIOFactory::createWriter($phpWord, 'PDF');
                $pdfWriter->save($pdfAbsolutePath);

            } else {
                return null;
            }
        } catch (\Exception $e) {
            \Log::error('PHP Conversion failed: ' . $e->getMessage());
            return null; // Conversion failed
        }

        if (file_exists($pdfAbsolutePath)) {
            chmod($pdfAbsolutePath, 0644);
            return str_replace(Storage::disk('public')->path(''), '', $pdfAbsolutePath);
        }

        return null;
    }

    public static function convertToHtml($filePath)
    {
        // $filePath is the relative path in the 'public' disk or 'local' disk
        $absolutePath = Storage::disk('public')->path($filePath);
        $directory = dirname($absolutePath);
        
        $fileName = pathinfo($absolutePath, PATHINFO_FILENAME);
        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        $htmlFileName = $fileName . '.html';
        $htmlAbsolutePath = $directory . '/' . $htmlFileName;
        
        // If it already exists, return the path
        if (file_exists($htmlAbsolutePath)) {
            return str_replace(Storage::disk('public')->path(''), '', $htmlAbsolutePath);
        }

        try {
            if (in_array($extension, ['xls', 'xlsx', 'csv', 'ods'])) {
                $spreadsheet = PhpSpreadsheetIOFactory::load($absolutePath);
                
                $writer = PhpSpreadsheetIOFactory::createWriter($spreadsheet, 'Html');
                $writer->writeAllSheets();
                $writer->save($htmlAbsolutePath);

                // Inject custom CSS and JS for tabs and scrolling
                $htmlContent = file_get_contents($htmlAbsolutePath);
                $injection = <<<HTML
<style>
.navigation {
  list-style: none;
  padding: 0;
  margin: 0 0 10px 0;
  display: flex;
  background: #f3f4f6;
  border-bottom: 1px solid #d1d5db;
  overflow-x: auto;
}
.navigation li {
  margin-right: 2px;
}
.navigation li a {
  display: block;
  padding: 8px 16px;
  text-decoration: none;
  color: #374151;
  background: #e5e7eb;
  border: 1px solid #d1d5db;
  border-bottom: none;
  border-radius: 4px 4px 0 0;
  font-family: sans-serif;
  font-size: 14px;
}
.navigation li.active a {
  background: #fff;
  font-weight: bold;
  border-bottom: 1px solid #fff;
  margin-bottom: -1px;
}
body>div {
  display: none;
  overflow: auto;
  max-width: 100%;
  max-height: calc(100vh - 50px);
}
body>div.active {
  display: block;
}
/* Ensure table scrolls properly */
.gridlines, .gridlinesp {
  white-space: nowrap;
}
</style>
<script>
document.addEventListener("DOMContentLoaded", function() {
  var navLinks = document.querySelectorAll(".navigation a");
  var sheets = document.querySelectorAll("body > div");
  
  function showSheet(targetDiv, activeLink) {
    sheets.forEach(function(s) { s.classList.remove("active"); });
    navLinks.forEach(function(l) { l.parentElement.classList.remove("active"); });
    
    if (targetDiv) targetDiv.classList.add("active");
    if (activeLink) activeLink.parentElement.classList.add("active");
  }

  if (navLinks.length === 0) {
      if (sheets.length > 0) sheets[0].classList.add("active");
      return;
  }
  
  navLinks.forEach(function(link) {
    link.addEventListener("click", function(e) {
      e.preventDefault();
      var hash = this.getAttribute("href");
      var targetTable = document.querySelector(hash);
      if(targetTable) {
          showSheet(targetTable.closest('div'), this);
      }
    });
  });
  
  var firstHash = navLinks[0].getAttribute("href");
  var firstTable = document.querySelector(firstHash);
  if(firstTable) showSheet(firstTable.closest('div'), navLinks[0]);
});
</script>
</body>
HTML;
                $htmlContent = str_replace('</body>', $injection, $htmlContent);
                file_put_contents($htmlAbsolutePath, $htmlContent);

            } else {
                return null;
            }
        } catch (\Exception $e) {
            \Log::error('HTML Conversion failed: ' . $e->getMessage());
            return null; // Conversion failed
        }

        if (file_exists($htmlAbsolutePath)) {
            chmod($htmlAbsolutePath, 0644);
            return str_replace(Storage::disk('public')->path(''), '', $htmlAbsolutePath);
        }

        return null;
    }
}

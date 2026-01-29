<?php

namespace App\Services;

use Mpdf\Mpdf;

class PDFGenerator
{
    public function createPDF($html, $fileName = 'document.pdf', $format=true)
    {
        if($format) {
            $mpdf = new Mpdf([
                'default_font' => 'sans-serif',
                // Add more config here if needed
            
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_top' => 40,  // Reserve space for your header
        'margin_bottom' => 30, // Optional, reserve space for footer
                
            ]);
        }else {
            $mpdf = new Mpdf([
                'default_font' => 'sans-serif',
                // Add more config here if needed
            
                'mode' => 'utf-8',
                'format' => 'A4', 
            ]);
        }
       

        $mpdf->WriteHTML($html);
        return $mpdf->Output($fileName, 'I'); // 'I' = inline in browser, 'D' = download
    }
}
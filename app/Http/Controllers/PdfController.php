<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Codedge\Fpdf\Fpdf\Fpdf;
use DB;

class PdfController extends Controller
{
    private $fpdf;
    
    public function InsuredDNI($insured_id) {

    try {
      $insured = Insured::find($insured_id);
      $insured_dni = ImagesController::InsuredDNI($insured_id);

      $this->fpdf = new Fpdf('P', 'mm', array(974, 584));
      $this->fpdf->AddPage('H');
      $this->fpdf->Image($insured_dni['jpg'], 0, 0, 974);

      $title = 'Insured DIN ' . $insured->enrollment;
      $this->fpdf->SetTitle($title);

      $filename = public_path('..') . "/storage/app/private/documents/temp/" . $title . ".pdf";
      $this->fpdf->Output($filename, 'F');
      $pdf = file_get_contents($filename);
      $pdf64 = base64_encode($pdf);

      DocumentsManagerController::deleteDocument('temp', $title . ".pdf");
      DocumentsManagerController::deleteDocument('temp', $insured_dni['file_name']);

      $data = new \stdClass;
      $data->jpg64 = $insured_dni['jpg64'];
      $data->pdf64 = $pdf64;

      return response()->json([
        "success" => true,
        "message" => "Credencial creada correctamente",
        "data" => $data
      ], 200);

    } catch (\Throwable $th) {
      return response()->json([
        "success" => false,
        "message" => "ERR. " . $th
      ], 200);
    }
  }
}

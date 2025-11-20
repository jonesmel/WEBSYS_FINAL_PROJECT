<?php
use Dompdf\Dompdf;
use Dompdf\Options;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../models/ReferralModel.php';
require_once __DIR__ . '/../models/PatientModel.php';

class PDFHelper {

  public static function generateReferralPDF($referralId) {
    $ref = ReferralModel::getById($referralId);
    if (!$ref) exit('Referral not found');

    $patient = PatientModel::getById($ref['patient_id']);
    if (!$patient) exit('Patient not found');

    $html = self::renderReferralHTML($ref, $patient);

    $options = new Options();
    $options->set('isRemoteEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4');
    $dompdf->render();

    $dompdf->stream('Referral_'.$ref['referral_code'].'.pdf', ['Attachment' => false]);
    exit;
  }

  private static function renderReferralHTML($ref, $patient) {

    $safe = fn($x) => htmlspecialchars($x ?? '', ENT_QUOTES, 'UTF-8');

    return "
    <html>
    <head>
      <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align:center; font-size:16px; margin-bottom:15px; font-weight:bold; }
        .section { border:1px solid #000; padding:10px; margin-bottom:10px; }
        .label { font-weight:bold; }
      </style>
    </head>
    <body>

      <div class='header'>FORM 7 — TB Referral Form</div>

      <div class='section'>
        <p class='label'>Referral Code:</p> {$safe($ref['referral_code'])}

        <p class='label'>Patient Code:</p> {$safe($patient['patient_code'])}

        <p class='label'>TB Case Number:</p> {$safe($patient['tb_case_number'])}
      </div>

      <div class='section'>
        <p class='label'>Referring Barangay / Unit:</p> {$safe($ref['referring_unit'])}

        <p class='label'>Contact:</p>
        Tel: {$safe($ref['referring_tel'])}<br>
        Email: {$safe($ref['referring_email'])}

        <p class='label'>Barangay:</p> {$safe($ref['referring_unit'])}

        <p class='label'>Address:</p> {$safe($ref['referring_address'])}
      </div>

      <div class='section'>
        <p class='label'>Reason for Referral:</p> {$safe($ref['reason_for_referral'])}

        <p class='label'>Details:</p> {$safe($ref['details'])}
      </div>

      <div class='section'>
        <p class='label'>Receiving Barangay:</p> {$safe($ref['receiving_barangay'])}

        <p class='label'>Receiving Unit:</p> {$safe($ref['receiving_unit'])}

        <p class='label'>Receiving Officer:</p> {$safe($ref['receiving_officer'])}

        <p class='label'>Date Received:</p> {$safe($ref['date_received'])}

        <p class='label'>Action Taken:</p> {$safe($ref['action_taken'])}

        <p class='label'>Remarks:</p> {$safe($ref['remarks'])}
      </div>

    </body>
    </html>
    ";
  }
}
?>

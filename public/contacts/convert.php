<?php
require_once __DIR__.'/../../src/middleware/AuthMiddleware.php';
require_once __DIR__.'/../../src/models/ContactModel.php';
require_once __DIR__.'/../../src/models/PatientModel.php';
require_once __DIR__.'/../../src/models/LogModel.php';
AuthMiddleware::requireRole(['super_admin','health_worker']);

$id = $_GET['id'] ?? null;
if (!$id) die('Missing contact ID');

$contact = ContactModel::getById($id);
if (!$contact) die('Contact not found');

// Conversion handled inside controller normally, but if directly accessed:
$pdata = [
  'patient_code' => PatientModel::generatePatientCode(),
  'age' => $contact['age'],
  'sex' => $contact['sex'],
  'barangay' => $contact['barangay'] ?: 'Unknown',
  'contact_number' => $contact['contact_number'],
  'created_by' => $_SESSION['user']['user_id']
];

$pid = PatientModel::create($pdata);
ContactModel::markConverted($id, $pid);
LogModel::insertLog($_SESSION['user']['user_id'],'convert_contact','contacts',$id,json_encode($contact),json_encode(['converted_patient_id'=>$pid]),$_SERVER['REMOTE_ADDR'],$_SERVER['HTTP_USER_AGENT']);

header('Location: /?route=patient/view&id='.$pid);
exit;
?>
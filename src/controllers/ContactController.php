<?php
require_once __DIR__ . '/../models/ContactModel.php';
require_once __DIR__ . '/../models/PatientModel.php';
require_once __DIR__ . '/../models/LogModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/Flash.php';
require_once __DIR__ . '/../helpers/BarangayHelper.php';

class ContactController {
    public function list() {
        AuthMiddleware::requireRole(['super_admin', 'health_worker']);

        $user = $_SESSION['user'];

        $q = trim($_GET['q'] ?? '');
        $barangay = trim($_GET['barangay'] ?? '');

        if ($user['role'] === 'health_worker') {
            $rows = ContactModel::getByBarangayFiltered($user['barangay_assigned'], $q);
        } else {
            $rows = ContactModel::getAllFiltered($q, $barangay);
        }

        $barangays = BarangayHelper::getAll();
        include __DIR__ . '/../../public/contacts/list.php';
    }

    public function add() {
        AuthMiddleware::requireRole(['super_admin', 'health_worker']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $data['created_by'] = $_SESSION['user']['user_id'];

            $id = ContactModel::create($data);

            LogModel::insertLog(
                $_SESSION['user']['user_id'],
                'create',
                'contacts',
                $id,
                null,
                json_encode($data),
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            );

            Flash::set('success','Contact saved.');
            header("Location: /WEBSYS_FINAL_PROJECT/public/?route=contact/list");
            exit;
        }

        $user = $_SESSION['user'];

        if ($user['role'] === 'health_worker') {
            $patients = PatientModel::getAllByBarangay($user['barangay_assigned']);
        } else {
            $patients = PatientModel::getAll();
        }

        $barangays = BarangayHelper::getAll();
        include __DIR__ . '/../../public/contacts/add.php';
    }

    public function convert() {
        AuthMiddleware::requireRole(['super_admin', 'health_worker']);

        $id = $_GET['id'] ?? null;
        if (!$id) { Flash::set('danger','Missing contact ID'); header("Location: /WEBSYS_FINAL_PROJECT/public/?route=contact/list"); exit; }

        $contact = ContactModel::getById($id);
        if (!$contact) { Flash::set('danger','Contact not found'); header("Location: /WEBSYS_FINAL_PROJECT/public/?route=contact/list"); exit; }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process form
            $data = $_POST;
            $data['created_by'] = $_SESSION['user']['user_id'];
            $data['patient_code'] = PatientModel::generatePatientCode();

            $newPatientId = PatientModel::create($data);

            // archive contact instead of delete to keep history
            ContactModel::archive($id, $newPatientId);

            LogModel::insertLog(
                $_SESSION['user']['user_id'],
                'convert_contact',
                'contacts',
                $id,
                json_encode($contact),
                json_encode(['converted_patient_id' => $newPatientId, 'patient_data' => $data]),
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            );

            Flash::set('success','Contact converted to patient.');
            header("Location: /WEBSYS_FINAL_PROJECT/public/?route=patient/view&id=".$newPatientId);
            exit;
        } else {
            // Show form
            $linkedPatient = PatientModel::getById($contact['patient_id']);
            include __DIR__ . '/../../public/contacts/convert.php';
        }
    }
}

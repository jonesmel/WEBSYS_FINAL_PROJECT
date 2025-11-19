<?php
require_once __DIR__ . '/../models/PatientModel.php';
require_once __DIR__ . '/../models/LogModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/Flash.php';

class PatientController {

    public function index() {
        AuthMiddleware::requireLogin();
        $user = $_SESSION['user'];

        if ($user['role'] === 'health_worker') {
            $patients = PatientModel::getAllByBarangay($user['barangay_assigned']);
        } else {
            $patients = PatientModel::getAll();
        }

        include __DIR__ . '/../../public/patients/list.php';
    }

    public function create() {
        AuthMiddleware::requireRole(['super_admin', 'health_worker']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $user = $_SESSION['user'] ?? null;
            if ($user && $user['role'] === 'health_worker') {
                $data['barangay'] = $user['barangay_assigned'];
            }

            if (empty($data['patient_code'])) {
                $data['patient_code'] = PatientModel::generatePatientCode();
            }
            
            // Auto generate patient code if missing
            if (empty($data['patient_code'])) {
                $data['patient_code'] = PatientModel::generatePatientCode();
            }

            $data['created_by'] = $_SESSION['user']['user_id'] ?? null;

            $id = PatientModel::create($data);

            LogModel::insertLog(
                $_SESSION['user']['user_id'],
                'create',
                'patients',
                $id,
                null,
                json_encode($data),
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            );

            Flash::set('success','Patient added.');
            header("Location: /WEBSYS_FINAL_PROJECT/public/?route=patient/index");
            exit;
        }

        include __DIR__ . '/../../public/patients/add.php';
    }

    public function edit() {
        AuthMiddleware::requireRole(['super_admin', 'health_worker']);

        $id = $_GET['id'] ?? null;

        if (!$id) { Flash::set('danger','Missing ID'); header("Location: /WEBSYS_FINAL_PROJECT/public/?route=patient/index"); exit; }

        $patient = PatientModel::getById($id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $oldData = $patient;

            PatientModel::update($id, $_POST);

            LogModel::insertLog(
                $_SESSION['user']['user_id'],
                'update',
                'patients',
                $id,
                json_encode($oldData),
                json_encode($_POST),
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            );

            Flash::set('success','Patient updated.');
            header("Location: /WEBSYS_FINAL_PROJECT/public/?route=patient/view&id=".$id);
            exit;
        }

        include __DIR__ . '/../../public/patients/edit.php';
    }

    public function view() {
        AuthMiddleware::requireLogin();

        $id = $_GET['id'] ?? null;
        if (!$id) { Flash::set('danger','Missing ID'); header("Location: /WEBSYS_FINAL_PROJECT/public/?route=patient/index"); exit; }

        $patient = PatientModel::getById($id);

        include __DIR__ . '/../../public/patients/view.php';
    }

    public function delete() {
        AuthMiddleware::requireRole(['super_admin']);

        $id = $_GET['id'] ?? null;
        if (!$id) die("Missing ID");

        PatientModel::delete($id);

        LogModel::insertLog(
            $_SESSION['user']['user_id'],
            'delete',
            'patients',
            $id,
            null,
            null,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        );

        Flash::set('success','Patient deleted.');
        header("Location: /WEBSYS_FINAL_PROJECT/public/?route=patient/index");
        exit;
    }
}

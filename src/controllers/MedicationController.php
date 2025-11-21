<?php
require_once __DIR__ . '/../models/MedicationModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../models/LogModel.php';
require_once __DIR__ . '/../models/PatientModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/Flash.php';

class MedicationController {

    public function list() {
        AuthMiddleware::requireRole(['super_admin', 'health_worker']);
        $rows = MedicationModel::getAll();
        include __DIR__ . '/../../public/medications/list.php';
    }

    public function add() {
        AuthMiddleware::requireRole(['super_admin', 'health_worker']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $data['created_by'] = $_SESSION['user']['user_id'];

            $id = MedicationModel::create($data);

            // Schedule reminder
            if (!empty($data['start_date'])) {
                $sched = date('Y-m-d H:i:s', strtotime($data['start_date'] . ' -1 day'));

                NotificationModel::create([
                    'patient_id'        => $data['patient_id'],
                    'notification_type' => 'patient_reminder',
                    'title'             => 'Medication Reminder',
                    'message'           => 'Your medication will start on ' . $data['start_date'],
                    'scheduled_at'      => $sched
                ]);
            }

            LogModel::insertLog(
                $_SESSION['user']['user_id'],
                'create',
                'medications',
                $id,
                null,
                json_encode($data),
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            );

            Flash::set('success', 'Medication added successfully.');
            header("Location: /WEBSYS_FINAL_PROJECT/public/?route=medication/list");
            exit;
        }

        include __DIR__ . '/../../public/medications/add.php';
    }
    
    public function my_medications() {
        AuthMiddleware::requireRole(['patient']);

        $uid = $_SESSION['user']['user_id'];

        // Get patient's patient_id
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT patient_id FROM patients WHERE user_id = ?");
        $stmt->execute([$uid]);
        $pid = $stmt->fetchColumn();

        if (!$pid) {
            $rows = [];
        } else {
            require_once __DIR__ . '/../models/MedicationModel.php';
            $rows = MedicationModel::getByPatient($pid);
        }

        include __DIR__ . '/../../public/patient/medications.php';
    }

    public function edit() {
        AuthMiddleware::requireRole(['super_admin','health_worker']);
        $id = $_GET['id'] ?? null;
        if (!$id) { Flash::set('danger','Missing ID'); header("Location: /?route=medication/list"); exit; }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            MedicationModel::update($id, $_POST);
            LogModel::insertLog($_SESSION['user']['user_id'],'update','medications',$id,null,json_encode($_POST), $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '');
            Flash::set('success','Medication updated.');
            header("Location: /WEBSYS_FINAL_PROJECT/public/?route=medication/list");
            exit;
        }

        $med = MedicationModel::getById($id);
        $patients = PatientModel::getAll();
        include __DIR__ . '/../../public/medications/edit.php';
    }

    public function delete() {
        AuthMiddleware::requireRole(['super_admin']);
        $id = $_GET['id'] ?? null;
        MedicationModel::delete($id);
        LogModel::insertLog($_SESSION['user']['user_id'],'delete','medications',$id,null,null,$_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '');
        Flash::set('success','Medication deleted.');
        header("Location: /WEBSYS_FINAL_PROJECT/public/?route=medication/list");
        exit;
    }
}

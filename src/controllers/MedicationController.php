<?php
require_once __DIR__ . '/../models/MedicationModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../models/LogModel.php';
require_once __DIR__ . '/../models/PatientModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/Flash.php';
require_once __DIR__ . '/../models/UserModel.php';

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

            // Create medication record
            $id = MedicationModel::create($data);

            $patient_id = intval($data['patient_id']);
            $start_date = $data['start_date'] ?? null;

            // 1) Create DB notification for patient (immediate) and attempt immediate email (will be sent only if verified)
            NotificationModel::createForPatientUser(
                $patient_id,
                'medication_created',
                'New Medication Added',
                'A new medication schedule has been added to your record. Please check your medications list.',
                "/WEBSYS_FINAL_PROJECT/public/?route=patient/medications"
            );

            // 2) Notify health workers assigned to patient's barangay (DB + immediate email if verified)
            $pdo = getDB();
            $stmt = $pdo->prepare("SELECT barangay FROM patients WHERE patient_id = ?");
            $stmt->execute([$patient_id]);
            $barangay = $stmt->fetchColumn();

            if ($barangay) {
                $workers = UserModel::getHealthWorkersByBarangay($barangay);
                foreach ($workers as $hw) {
                    NotificationModel::create([
                        'user_id' => $hw['user_id'],
                        'patient_id' => $patient_id,
                        'type' => 'medication_created_hw',
                        'title' => 'Medication Added',
                        'message' => 'A medication schedule was added for a patient in your barangay.',
                        'link' => "/WEBSYS_FINAL_PROJECT/public/?route=patient/view&id=" . $patient_id
                    ]);
                }
            }

            // 3) Schedule reminders for patient (1 day before + same day) — stored as scheduled notifications
            if (!empty($start_date)) {
                $one_day_before = date('Y-m-d 09:00:00', strtotime($start_date . ' -1 day'));
                NotificationModel::create([
                    'patient_id' => $patient_id,
                    'type' => 'medication_pre_reminder',
                    'title' => 'Medication Reminder',
                    'message' => "Your medication begins tomorrow ({$start_date}).",
                    'link' => "/WEBSYS_FINAL_PROJECT/public/?route=patient/medications",
                    'scheduled_at' => $one_day_before
                ]);

                $same_day = date('Y-m-d 08:00:00', strtotime($start_date));
                NotificationModel::create([
                    'patient_id' => $patient_id,
                    'type' => 'medication_today',
                    'title' => 'Medication Starts Today',
                    'message' => "Your medication begins today ({$start_date}).",
                    'link' => "/WEBSYS_FINAL_PROJECT/public/?route=patient/medications",
                    'scheduled_at' => $same_day
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

        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT patient_id FROM patients WHERE user_id = ?");
        $stmt->execute([$uid]);
        $pid = $stmt->fetchColumn();

        $rows = $pid ? MedicationModel::getByPatient($pid) : [];

        include __DIR__ . '/../../public/patient/medications.php';
    }

    public function edit() {
        AuthMiddleware::requireRole(['super_admin','health_worker']);
        $id = $_GET['id'] ?? null;
        if (!$id) { Flash::set('danger','Missing ID'); header("Location: /WEBSYS_FINAL_PROJECT/public/?route=medication/list"); exit; }

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
        header('Location: /WEBSYS_FINAL_PROJECT/public/?route=medication/list');
        exit;
    }
}

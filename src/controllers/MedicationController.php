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

        $userRole = $_SESSION['user']['role'] ?? null;
        $userBarangay = $_SESSION['user']['barangay_assigned'] ?? null;

        if ($userRole === 'health_worker' && $userBarangay !== '') {
            // Health workers only see medications for patients in their barangay
            $rows = MedicationModel::getByBarangay($userBarangay);
        } else {
            // Super admin sees all
            $rows = MedicationModel::getAll();
        }

        include __DIR__ . '/../../public/medications/list.php';
    }

    public function add() {
        AuthMiddleware::requireRole(['super_admin', 'health_worker']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $sharedData = [
                'patient_id' => intval($data['patient_id']),
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? null,
                'created_by' => $_SESSION['user']['user_id']
            ];

            // Handle multiple drugs - check if it's an array (new UI) or single drug (old UI)
            $drugs = $data['drugs'] ?? [];
            $notes = $data['notes'] ?? [];

            // Backward compatibility: if drugs is not an array, treat it as single drug
            if (!is_array($drugs)) {
                $drugs = [$drugs];
                $notes = [$notes];
            }

            $createdIds = [];
            $notificationSent = false;

            foreach ($drugs as $index => $drugName) {
                if (empty($drugName)) continue; // Skip empty drugs

                // Create medication record for each drug
                $medicationData = array_merge($sharedData, [
                    'drugs' => $drugName,
                    'notes' => $notes[$index] ?? ''
                ]);

                $id = MedicationModel::create($medicationData);
                $createdIds[] = $id;

                // Set up compliance tracking dates for this drug
                $compliance_deadline = !empty($sharedData['end_date']) ?
                    $sharedData['end_date'] :
                    date('Y-m-d', strtotime($sharedData['start_date'] . ' +30 days'));

                // Update medication with compliance tracking fields
                MedicationModel::update($id, [
                    'drugs' => $drugName,
                    'start_date' => $sharedData['start_date'],
                    'end_date' => $sharedData['end_date'],
                    'notes' => $notes[$index] ?? '',
                    'scheduled_for_date' => $sharedData['start_date'],
                    'compliance_deadline' => $compliance_deadline
                ]);

                // Schedule reminders only once per patient (to avoid duplicate notifications)
                if (!$notificationSent && !empty($sharedData['start_date'])) {
                    $notificationSent = true;
                    $this->scheduleMedicationReminders($sharedData['patient_id'], $sharedData['start_date']);
                }
            }

            $patient_id = $sharedData['patient_id'];

            // 1) Create DB notification for patient (only once per patient)
            if (!empty($createdIds)) {
                NotificationModel::createForPatientUser(
                    $patient_id,
                    'medication_created',
                    'New Medication Added',
                    count($drugs) > 1 ?
                        'New medication drugs have been added to your record. Please check your medications list.' :
                        'A new medication schedule has been added to your record. Please check your medications list.',
                    "/WEBSYS_FINAL_PROJECT/public/?route=patient/medications"
                );

                // 2) Notify health workers assigned to patient's barangay
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
                            'message' => count($drugs) > 1 ?
                                'Medication drugs were added for a patient in your barangay.' :
                                'A medication schedule was added for a patient in your barangay.',
                            'link' => "/WEBSYS_FINAL_PROJECT/public/?route=patient/view&id=" . $patient_id
                        ]);
                    }
                }

                // 3) Log the action
                LogModel::insertLog(
                    $_SESSION['user']['user_id'],
                    'create',
                    'medications',
                    $createdIds[0], // Use first ID for primary log
                    null,
                    json_encode([
                        'multiple_drugs' => count($drugs),
                        'created_ids' => $createdIds,
                        'data' => $medicationData
                    ]),
                    $_SERVER['REMOTE_ADDR'],
                    $_SERVER['HTTP_USER_AGENT'] ?? ''
                );
            }

            $drugCount = count(array_filter($drugs)); // Count non-empty drugs
            Flash::set('success', "Medication " . ($drugCount > 1 ? 'drugs' : 'drug') . " added successfully.");
            header("Location: /WEBSYS_FINAL_PROJECT/public/?route=medication/list");
            exit;
        }

        include __DIR__ . '/../../public/medications/add.php';
    }

    // Helper method to schedule medication reminders
    private function scheduleMedicationReminders($patient_id, $start_date) {
        $today = date('Y-m-d');
        $start_timestamp = strtotime($start_date);
        $today_timestamp = strtotime($today);

        // Only schedule if medication hasn't started yet
        if ($start_timestamp >= $today_timestamp) {

            // 1 week before (7 days) - only if it's at least 8 days in future
            $one_week_before_timestamp = strtotime('-7 days', $start_timestamp);
            if ($one_week_before_timestamp >= $today_timestamp) {
                $one_week_before = date('Y-m-d 09:00:00', $one_week_before_timestamp);
                NotificationModel::create([
                    'patient_id' => $patient_id,
                    'type' => 'medication_week_reminder',
                    'title' => 'Upcoming Medication Schedule',
                    'message' => "Your medication begins in 1 week on ({$start_date}). Please prepare.",
                    'link' => "/WEBSYS_FINAL_PROJECT/public/?route=patient/medications",
                    'scheduled_at' => $one_week_before
                ]);
            }

            // 1 day before - only if it's at least 2 days in future
            $one_day_before_timestamp = strtotime('-1 day', $start_timestamp);
            if ($one_day_before_timestamp >= $today_timestamp) {
                $one_day_before = date('Y-m-d 09:00:00', $one_day_before_timestamp);
                NotificationModel::create([
                    'patient_id' => $patient_id,
                    'type' => 'medication_pre_reminder',
                    'title' => 'Medication Reminder',
                    'message' => "Your medication begins tomorrow ({$start_date}).",
                    'link' => "/WEBSYS_FINAL_PROJECT/public/?route=patient/medications",
                    'scheduled_at' => $one_day_before
                ]);
            }

            // Same day - early morning
            $same_day = date('Y-m-d 08:00:00', $start_timestamp);
            NotificationModel::create([
                'patient_id' => $patient_id,
                'type' => 'medication_today',
                'title' => 'Medication Starts Today',
                'message' => "Your medication begins today ({$start_date}).",
                'link' => "/WEBSYS_FINAL_PROJECT/public/?route=patient/medications",
                'scheduled_at' => $same_day
            ]);
        }
    }

    public function my_medications() {
        AuthMiddleware::requireRole(['patient']);

        $uid = $_SESSION['user']['user_id'];

        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT patient_id FROM patients WHERE user_id = ?");
        $stmt->execute([$uid]);
        $patientRecord = $stmt->fetch();
        $pid = $patientRecord ? $patientRecord['patient_id'] : null;

        if ($pid) {
            // Get medications with compliance information
            try {
                $stmt = $pdo->prepare("SELECT * FROM medications WHERE patient_id = ? ORDER BY created_at DESC");
                $stmt->execute([$pid]);
                $rows = $stmt->fetchAll();
            } catch (Exception $e) {
                // If query fails, return empty array
                $rows = [];
            }
        } else {
            $rows = [];
        }

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

    public function compliance() {
        AuthMiddleware::requireRole(['super_admin', 'health_worker']);

        $userRole = $_SESSION['user']['role'] ?? null;
        $userBarangay = $_SESSION['user']['barangay_assigned'] ?? null;

        if ($userRole === 'health_worker' && $userBarangay !== '') {
            // Health workers only see pending compliance for their barangay
            $rows = MedicationModel::getPendingCompliance($userBarangay);
        } else {
            // Super admin sees all pending compliance
            $rows = MedicationModel::getPendingCompliance();
        }

        include __DIR__ . '/../../public/medications/compliance.php';
    }

    public function mark_compliance() {
        AuthMiddleware::requireRole(['super_admin', 'health_worker']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $medication_id = intval($_POST['medication_id']);
            $compliance_status = $_POST['compliance_status'];
            $compliance_notes = trim($_POST['compliance_notes'] ?? '');

            // Update compliance
            $updateData = [
                'compliance_status' => $compliance_status,
                'compliance_date' => null, // Will use current date
                'compliance_marked_by' => $_SESSION['user']['user_id'],
                'compliance_notes' => $compliance_notes
            ];

            if (MedicationModel::updateCompliance($medication_id, $updateData)) {
                // Log the action
                LogModel::insertLog(
                    $_SESSION['user']['user_id'],
                    'compliance_marked',
                    'medications',
                    $medication_id,
                    null,
                    json_encode(['status' => $compliance_status, 'notes' => $compliance_notes]),
                    $_SERVER['REMOTE_ADDR'],
                    $_SERVER['HTTP_USER_AGENT'] ?? ''
                );

                // If marked as missed, create staff follow-up notifications for admins and health workers
                if ($compliance_status === 'missed') {
                    $med = MedicationModel::getById($medication_id);

                    // Get health workers in the patient's barangay to notify them
                    $pdo = getDB();
                    $workerStmt = $pdo->prepare("
                        SELECT user_id FROM users
                        WHERE role = 'health_worker' AND barangay_assigned = (
                            SELECT barangay FROM patients WHERE patient_id = ?
                        )
                    ");
                    $workerStmt->execute([$med['patient_id']]);
                    $healthWorkers = $workerStmt->fetchAll();

                    // Create staff follow-up for administrators (send to super admin user)
                    NotificationModel::create([
                        'user_id' => 1, // Super admin user ID
                        'patient_id' => $med['patient_id'], // Patient reference for display
                        'type' => 'staff_follow_up',
                        'title' => 'Missed Medication Follow-up Required',
                        'message' => "Patient medication '{$med['drugs']}' has been marked as missed. Follow-up required.",
                        'link' => "/WEBSYS_FINAL_PROJECT/public/?route=patient/view&id=" . $med['patient_id']
                    ]);

                    // Notify health workers individually via email (but don't create duplicate follow-up entries)
                    foreach ($healthWorkers as $worker) {
                        NotificationModel::create([
                            'user_id' => $worker['user_id'],
                            'type' => 'health_worker_alert', // Separate type to avoid duplicate follow-up entries
                            'title' => 'Medication Follow-up Required in Your Area',
                            'message' => "Patient medication '{$med['drugs']}' (ID: {$medication_id}) has been marked as missed. Please follow up immediately.",
                            'link' => "/WEBSYS_FINAL_PROJECT/public/?route=patient/view&id=" . $med['patient_id']
                        ]);
                    }
                }

                Flash::set('success', "Medication compliance marked as '{$compliance_status}'.");
            } else {
                Flash::set('danger', 'Failed to update compliance status.');
            }

            header("Location: /WEBSYS_FINAL_PROJECT/public/?route=medication/compliance");
            exit;
        }

        // Show form for marking compliance
        $medication_id = intval($_GET['id'] ?? 0);
        if (!$medication_id) {
            Flash::set('danger', 'Missing medication ID.');
            header("Location: /WEBSYS_FINAL_PROJECT/public/?route=medication/compliance");
            exit;
        }

        $medication = MedicationModel::getById($medication_id);
        if (!$medication) {
            Flash::set('danger', 'Medication not found.');
            header("Location: /WEBSYS_FINAL_PROJECT/public/?route=medication/compliance");
            exit;
        }

        include __DIR__ . '/../../public/medications/mark_compliance.php';
    }
}

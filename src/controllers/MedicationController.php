<?php
require_once __DIR__ . '/../models/MedicationModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../models/LogModel.php';
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
}

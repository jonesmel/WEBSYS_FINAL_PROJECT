<?php
require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../models/LogModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/Flash.php';

class StafffollowupController {

    // list staff_follow_up notifications
    public function index() {
        AuthMiddleware::requireRole(['super_admin']);
        // Only show unread staff follow-up notifications
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE type = ? AND is_read = 0 ORDER BY created_at DESC");
        $stmt->execute(['staff_follow_up']);
        $rows = $stmt->fetchAll();
        include __DIR__ . '/../../public/staff_follow_up/index.php';
    }

    // mark one notification resolved (delete or mark read & set type/resolution)
    public function resolve() {
        AuthMiddleware::requireRole(['super_admin']);
        $id = $_GET['id'] ?? null;
        if (!$id) {
            Flash::set('danger','Missing ID');
            header("Location: /WEBSYS_FINAL_PROJECT/public/?route=stafffollowup/index");
            exit;
        }

        // Get notification details before marking as resolved
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE notification_id = ?");
        $stmt->execute([$id]);
        $notification = $stmt->fetch();

        if (!$notification) {
            Flash::set('danger', 'Notification not found.');
            header("Location: /WEBSYS_FINAL_PROJECT/public/?route=stafffollowup/index");
            exit;
        }

        // mark read as resolved
        NotificationModel::markRead($id);

        // Get health workers in the patient's barangay to notify them
        if (!empty($notification['patient_id'])) {
            $pdo = getDB();
            $workerStmt = $pdo->prepare("
                SELECT user_id FROM users
                WHERE role = 'health_worker' AND barangay_assigned = (
                    SELECT barangay FROM patients WHERE patient_id = ?
                )
            ");
            $workerStmt->execute([$notification['patient_id']]);
            $healthWorkers = $workerStmt->fetchAll();

            // Log health workers found
            LogModel::insertLog(
                $_SESSION['user']['user_id'],
                'follow_up_resolved',
                'notifications',
                $notification['notification_id'],
                null,
                json_encode([
                    'patient_id' => $notification['patient_id'],
                    'health_workers_found' => count($healthWorkers),
                    'health_workers' => $healthWorkers
                ]),
                $_SERVER['REMOTE_ADDR'],
                'health worker notification debug'
            );

            // Notify health workers that follow-up has been resolved
            foreach ($healthWorkers as $worker) {
                $hw_notification_id = NotificationModel::create([
                    'user_id' => $worker['user_id'],
                    'patient_id' => $notification['patient_id'],
                    'type' => 'follow_up_resolved_health_worker',
                    'title' => 'Follow-up Completed in Your Area',
                    'message' => 'A medication adherence follow-up has been resolved and addressed. The patient has been contacted.',
                    'link' => '/WEBSYS_FINAL_PROJECT/public/?route=patient/view&id=' . $notification['patient_id']
                ]);

                // Test immediate notification creation and email sending
                $debug_info = [
                    'worker_user_id' => $worker['user_id'],
                    'patient_id' => $notification['patient_id'],
                    'notification_id' => $hw_notification_id
                ];

                // Check if notification was created and user exists
                $pdo = getDB();
                $checkStmt = $pdo->prepare("
                    SELECT n.*, u.email, u.is_verified, u.role
                    FROM notifications n
                    LEFT JOIN users u ON u.user_id = n.user_id
                    WHERE n.notification_id = ?
                ");
                $checkStmt->execute([$hw_notification_id]);
                $debug_result = $checkStmt->fetch();

                $debug_info['user_found'] = !empty($debug_result);
                $debug_info['email'] = $debug_result['email'] ?? 'no email';
                $debug_info['is_verified'] = $debug_result['is_verified'] ?? 'no verification';
                $debug_info['role'] = $debug_result['role'] ?? 'no role';

                LogModel::insertLog(
                    $_SESSION['user']['user_id'],
                    'health_worker_followup_completion',
                    'notifications',
                    $hw_notification_id,
                    null,
                    json_encode($debug_info),
                    $_SERVER['REMOTE_ADDR'],
                    'follow-up resolution debug'
                );
            }

            // Notify the patient that follow-up has been resolved
            NotificationModel::create([
                'patient_id' => $notification['patient_id'],
                'type' => 'follow_up_resolved_patient',
                'title' => 'Follow-up Completed',
                'message' => 'Your healthcare follow-up regarding medication adherence has been addressed. Thank you for working with our team.',
                'link' => '/WEBSYS_FINAL_PROJECT/public/?route=patient/medications'
            ]);
        }

        Flash::set('success','Follow-up marked as resolved. Patient and health workers have been notified.');
        header("Location: /WEBSYS_FINAL_PROJECT/public/?route=stafffollowup/index");
        exit;
    }
}
?>

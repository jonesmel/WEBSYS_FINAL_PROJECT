<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/helpers/EmailHelper.php';
require_once __DIR__ . '/../src/models/LogModel.php';

$pdo = getDB();

// Get notifications that need to be sent
$sql = "SELECT * FROM notifications WHERE is_sent = 0 AND scheduled_at <= NOW()";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$due = $stmt->fetchAll();

foreach ($due as $n) {
    // Fetch patient email
    $q = $pdo->prepare("SELECT u.email, u.user_id
                        FROM patients p
                        JOIN users u ON u.user_id = p.user_id
                        WHERE p.patient_id = ?");
    $q->execute([$n['patient_id']]);
    $usr = $q->fetch();

    $sent = false;

    if ($usr && !empty($usr['email'])) {
        try {
            EmailHelper::sendReminder($usr['email'], $n['title'], $n['message']);
            $sent = true;
        } catch (Exception $e) {
            error_log("[CRON] Email send failed: " . $e->getMessage());
            $sent = false;
        }
    }

    if ($sent) {
        // Mark notification as sent
        $upd = $pdo->prepare("UPDATE notifications SET is_sent = 1, sent_at = NOW() WHERE notification_id = ?");
        $upd->execute([$n['notification_id']]);

        // Log event
        LogModel::insertLog(
            $usr['user_id'] ?? null,
            'notification_sent',
            'notifications',
            $n['notification_id'],
            null,
            json_encode(['sent' => true]),
            'CRON',
            'system'
        );
    } else {
        // If email unavailable, mark for staff follow-up
        $fallback = $pdo->prepare("INSERT INTO notifications (patient_id, notification_type, title, message, scheduled_at, is_sent)
                                   VALUES (?, 'staff_follow_up', ?, ?, NOW(), 0)");
        $fallback->execute([
            $n['patient_id'],
            'Patient unreachable',
            'Patient has no email. Manual follow-up required.'
        ]);
    }
}
?>
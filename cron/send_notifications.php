<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/helpers/EmailHelper.php';
require_once __DIR__ . '/../src/models/NotificationModel.php';
require_once __DIR__ . '/../src/models/LogModel.php';

$pdo = getDB();

// Get notifications scheduled <= now and not sent
$sql = "SELECT notification_id 
        FROM notifications 
        WHERE is_sent = 0 
        AND scheduled_at IS NOT NULL 
        AND scheduled_at <= NOW()";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$due = $stmt->fetchAll();

foreach ($due as $row) {

    $nid = $row['notification_id'];

    // Attempt to send via NotificationModel (checks verified status)
    $sent = NotificationModel::sendNow($nid);

    if (!$sent) {

        // Fetch for context
        $q = $pdo->prepare("SELECT * FROM notifications WHERE notification_id = ?");
        $q->execute([$nid]);
        $n = $q->fetch();

        $msg = "Scheduled notification {$nid} (type: {$n['type']}) "
             . "for patient {$n['patient_id']} could NOT be sent. "
             . "Recipient has no verified email.";

        // Create staff follow-up entry
        $insert = $pdo->prepare("
            INSERT INTO notifications
                (user_id, patient_id, type, title, message, link, is_read, created_at)
            VALUES (NULL, ?, 'staff_follow_up', 'Notification Failed', ?, NULL, 0, NOW())
        ");

        $insert->execute([
            $n['patient_id'],
            $msg
        ]);

    } else {
        // Log successful send
        LogModel::insertLog(
            null,
            'notification_sent',
            'notifications',
            $nid,
            null,
            json_encode(['sent' => true]),
            'CRON',
            'system'
        );
    }
}

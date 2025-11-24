<?php
require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/Flash.php';

class StaffFollowUpController {

    // list staff_follow_up notifications
    public function index() {
        AuthMiddleware::requireRole(['super_admin']);
        $rows = NotificationModel::getByType('staff_follow_up');
        include __DIR__ . '/../../public/staff_follow_up/index.php';
    }

    // mark one notification resolved (delete or mark read & set type/resolution)
    public function resolve() {
        AuthMiddleware::requireRole(['super_admin']);
        $id = $_GET['id'] ?? null;
        if (!$id) {
            Flash::set('danger','Missing ID');
            header("Location: /WEBSYS_FINAL_PROJECT/public/?route=staff_follow_up/index");
            exit;
        }

        // mark read as resolved (you might instead delete or update a 'resolved' column)
        NotificationModel::markRead($id);
        Flash::set('success','Marked as handled.');
        header("Location: /WEBSYS_FINAL_PROJECT/public/?route=staff_follow_up/index");
        exit;
    }
}
?>

<?php
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/EmailHelper.php';
require_once __DIR__ . '/../models/LogModel.php';
require_once __DIR__ . '/../helpers/Flash.php';

class UserController {
    public function delete_user() {
        AuthMiddleware::requireRole(['super_admin']);

        $id = $_GET['id'] ?? null;
        if (!$id) die("Missing user ID");

        // Never allow deleting super admin accounts
        $user = UserModel::getById($id);
        if ($user && $user['role'] === 'super_admin') {
            Flash::set('danger','Cannot delete super admin account.');
            header("Location: /WEBSYS_FINAL_PROJECT/public/?route=admin/users");
            exit;
        }

        UserModel::delete($id);

        LogModel::insertLog(
            $_SESSION['user']['user_id'],
            'delete',
            'users',
            $id,
            null,
            null,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        );

        Flash::set('success','User deleted.');
        header("Location: /WEBSYS_FINAL_PROJECT/public/?route=admin/users");
        exit;
    }

    public function create_patient_user() {
        AuthMiddleware::requireRole(['super_admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Flash::set('danger', 'Invalid request.');
            header("Location: /WEBSYS_FINAL_PROJECT/public/?route=admin/users");
            exit;
        }

        $patient_id = $_POST['patient_id'] ?? null;
        $email = trim($_POST['email'] ?? '');

        if (!$patient_id || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::set('danger', 'Invalid input. Select patient and enter a valid email.');
            header("Location: /WEBSYS_FINAL_PROJECT/public/?route=admin/users");
            exit;
        }

        // Already exists?
        if (UserModel::emailExists($email)) {
            Flash::set('danger', 'Email already registered.');
            header("Location: /WEBSYS_FINAL_PROJECT/public/?route=admin/users");
            exit;
        }

        try {
            $token = bin2hex(random_bytes(16));
            $tempPass = bin2hex(random_bytes(6));

            // Create user
            $uid = UserModel::createPatientUser($email, $tempPass, $token);

            // Assign to patient
            $pdo = getDB();
            $stmt = $pdo->prepare("UPDATE patients SET user_id=? WHERE patient_id=?");
            $stmt->execute([$uid, $patient_id]);

            // Send verification
            EmailHelper::sendVerificationEmail($email, $token);

            LogModel::insertLog(
                $_SESSION['user']['user_id'],
                'create',
                'users',
                $uid,
                null,
                json_encode(['role'=>'patient','email'=>$email]),
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            );

            Flash::set('success', 'Patient user created and verification email sent.');

        } catch (Exception $e) {
            Flash::set('danger', 'Error: '.$e->getMessage());
        }

        header("Location: /WEBSYS_FINAL_PROJECT/public/?route=admin/users");
        exit;
    }

    public function create_health_worker() {
        AuthMiddleware::requireRole(['super_admin']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $email = trim($_POST['email']);
            $barangay_assigned = trim($_POST['barangay_assigned']);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Flash::set('danger','Invalid email.');
                include __DIR__ . '/../../public/admin/create_health_worker.php';
                return;
            }

            $tempPass = bin2hex(random_bytes(6));
            $token = bin2hex(random_bytes(16));

            try {
                $uid = UserModel::createHealthWorker($email, $barangay_assigned, $tempPass, $token);
                EmailHelper::sendVerificationEmail($email, $token);

                LogModel::insertLog($_SESSION['user']['user_id'],'create','users',$uid,null,json_encode(['role'=>'health_worker','email'=>$email]), $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '');

                Flash::set('success','Health worker created. Verification email sent.');
            } catch (Exception $e) {
                Flash::set('danger', $e->getMessage());

                header("Location: /WEBSYS_FINAL_PROJECT/public/?route=user/create_health_worker");
                exit;
            }

            header("Location: /WEBSYS_FINAL_PROJECT/public/?route=user/create_health_worker");
            exit;
        }

        include __DIR__ . '/../../public/admin/create_health_worker.php';
    }
}

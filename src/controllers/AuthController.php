<?php
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/LogModel.php';
require_once __DIR__ . '/../helpers/Flash.php';
require_once __DIR__ . '/../helpers/EmailHelper.php';

class AuthController {

    public function login() {

        // Prevent visiting login while logged in
        if (!empty($_SESSION['user'])) {
            $role = $_SESSION['user']['role'];

            if ($role === 'super_admin') {
                header("Location: /WEBSYS_FINAL_PROJECT/public/?route=admin/dashboard");
            } elseif ($role === 'health_worker') {
                header("Location: /WEBSYS_FINAL_PROJECT/public/?route=health/dashboard");
            } else {
                header("Location: /WEBSYS_FINAL_PROJECT/public/?route=patientdashboard/index");
            }
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            $user = UserModel::getByEmail($email);

            if ($user && password_verify($password, $user['password_hash'])) {

                if (!$user['is_verified']) {
                    Flash::set('danger', 'Please verify your email first.');
                    header("Location: /WEBSYS_FINAL_PROJECT/public/login.php");
                    exit;
                }

                // force password change ONLY if required
                if ($user['password_reset_required']) {
                    header("Location: /WEBSYS_FINAL_PROJECT/public/set_new_password.php?uid=".$user['user_id']);
                    exit;
                }

                // otherwise proceed normally
                session_regenerate_id(true);
                $_SESSION['user'] = $user;

                // Redirect based on role
                if ($user['role'] === 'super_admin') {
                    header("Location: /WEBSYS_FINAL_PROJECT/public/?route=admin/dashboard");
                } elseif ($user['role'] === 'health_worker') {
                    header("Location: /WEBSYS_FINAL_PROJECT/public/?route=health/dashboard");
                } else {
                    header("Location: /WEBSYS_FINAL_PROJECT/public/?route=patientdashboard/index");
                }
                exit;
            }

            Flash::set('danger', 'Invalid email or password.');
            header("Location: /WEBSYS_FINAL_PROJECT/public/login.php");
            exit;
        }
        
        include __DIR__ . '/../../public/login.php';
    }

    public function logout() {
        session_destroy();
        header("Location: /WEBSYS_FINAL_PROJECT/public/login.php");
        exit;
    }

    public function verify() {
        $token = $_GET['token'] ?? null;

        if (!$token) {
            Flash::set('danger', 'Invalid verification token.');
            header("Location: /WEBSYS_FINAL_PROJECT/public/login.php");
            exit;
        }

        $user = UserModel::getByToken($token);

        if (!$user) {
            Flash::set('danger', 'This verification link is invalid or already used.');
            header("Location: /WEBSYS_FINAL_PROJECT/public/login.php");
            exit;
        }

        // Mark as verified but KEEP TOKEN
        UserModel::markEmailVerified($user['user_id']);

        Flash::set('success', 'Email verified. Please set your new password.');
        header("Location: /WEBSYS_FINAL_PROJECT/public/set_new_password.php?uid=" . $user['user_id']);
        exit;
    }

    public function reset_password() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $uid = $_POST['uid'];
            $pass = $_POST['password'];
            $confirm = $_POST['confirm_password'];

            if ($pass !== $confirm) {
                Flash::set('danger', 'Passwords do not match.');
                header("Location: /WEBSYS_FINAL_PROJECT/public/set_new_password.php?uid=$uid");
                exit;
            }

            // Update password
            UserModel::updatePassword($uid, password_hash($pass, PASSWORD_DEFAULT));

            // FINAL TOKEN CLEAR HERE ONLY
            UserModel::clearVerificationToken($uid);

            Flash::set('success', 'Password created successfully. You may now log in.');
            header("Location: /WEBSYS_FINAL_PROJECT/public/login.php");
            exit;
        }

        include __DIR__ . '/../../public/set_new_password.php';
    }

    public function change_password() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $uid = $_POST['uid'];
            $current = $_POST['current_password'] ?? '';
            $new = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            $user = UserModel::getById($uid);

            if (!$user) {
                Flash::set('danger', 'User not found.');
            }
            elseif (!password_verify($current, $user['password_hash'])) {
                Flash::set('danger', 'Current password is incorrect.');
            }
            elseif (password_verify($new, $user['password_hash'])) {
                Flash::set('danger', 'New password cannot be the same as the old one.');
            }
            elseif ($new !== $confirm) {
                Flash::set('danger', 'New passwords do not match.');
            }
            else {
                UserModel::updatePassword($uid, password_hash($new, PASSWORD_DEFAULT));
                Flash::set('success', 'Password updated successfully.');
            }

            // Redirect based on role
            if ($user['role'] === 'patient') {
                header("Location: /WEBSYS_FINAL_PROJECT/public/?route=patientdashboard/profile");
            } elseif ($user['role'] === 'health_worker') {
                header("Location: /WEBSYS_FINAL_PROJECT/public/?route=health/profile");
            } elseif ($user['role'] === 'super_admin') {
                header("Location: /WEBSYS_FINAL_PROJECT/public/?route=admin/profile");
            }
            exit;
        }
    }
}

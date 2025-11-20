<?php
require_once __DIR__ . '/../../config/db.php';

class UserModel {

    public static function getByEmail($email) {
        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public static function getById($id) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function getByToken($token) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE verification_token = ?");
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    // NEW: safer email check function (no exceptions)
    public static function emailExists($email) {
        return self::getByEmail($email) ? true : false;
    }

    // VERIFICATION NOW DOES NOT CLEAR TOKEN
    public static function markEmailVerified($uid) {
        $pdo = getDB();
        $stmt = $pdo->prepare("UPDATE users SET is_verified = 1 WHERE user_id = ?");
        return $stmt->execute([$uid]);
    }

    // FINAL TOKEN CLEAR WHEN PASSWORD IS SET
    public static function clearVerificationToken($uid) {
        $pdo = getDB();
        $stmt = $pdo->prepare("UPDATE users SET verification_token = NULL WHERE user_id = ?");
        return $stmt->execute([$uid]);
    }

    public static function createPatientUser($email, $plain, $token) {
        if (self::emailExists($email)) return false;

        $pdo = getDB();
        $hash = password_hash($plain, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO users (email, password_hash, role, is_verified, verification_token, password_reset_required)
            VALUES (?, ?, 'patient', 0, ?, 1)
        ");

        $stmt->execute([$email, $hash, $token]);
        return $pdo->lastInsertId();
    }

    public static function updatePassword($uid, $hash) {
        $pdo = getDB();
        $stmt = $pdo->prepare("
            UPDATE users 
            SET password_hash = ?, password_reset_required = 0 
            WHERE user_id = ?
        ");
        return $stmt->execute([$hash, $uid]);
    }

    public static function delete($id) {
        $pdo = getDB();
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id=?");
        return $stmt->execute([$id]);
    }

    public static function createHealthWorker($email, $barangay_assigned, $plain, $token) {

        if (self::emailExists($email)) return false;

        $pdo = getDB();
        $hash = password_hash($plain, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO users (email, password_hash, role, is_verified, verification_token, barangay_assigned, password_reset_required)
            VALUES (?, ?, 'health_worker', 0, ?, ?, 1)
        ");

        $stmt->execute([$email, $hash, $token, $barangay_assigned]);

        return $pdo->lastInsertId();
    }

    public static function getHealthWorkerByBarangay($barangay) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE role='health_worker' AND barangay_assigned=? LIMIT 1");
        $stmt->execute([$barangay]);
        return $stmt->fetch();
    }

    public static function getHealthWorkersByBarangay($barangay) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE role='health_worker' AND barangay_assigned=?");
        $stmt->execute([$barangay]);
        return $stmt->fetchAll();
    }
}
?>

<?php
class RoleMiddleware {
  public static function ensureBarangayMatch($patientBarangay) {
    if (empty($_SESSION['user'])) return false;
    $u = $_SESSION['user'];

    // full access
    if ($u['role'] === 'super_admin') return true;

    // limit to assigned barangay
    if ($u['role'] === 'health_worker') {
      return $u['barangay_assigned'] === $patientBarangay;
    }

    return false;
  }
}
?>
<?php
class AuthMiddleware {
  public static function checkPatientTreatmentStatus($user_id) {
    // For patient users, check if they have appropriate treatment outcome access
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT treatment_outcome FROM patients WHERE user_id = ?");
    $stmt->execute([$user_id]);

    $patient = $stmt->fetch();
    if (!$patient) return true; // Not a patient user, allow access

    $outcome = $patient['treatment_outcome'];

    // Define which outcomes prevent access
    $blockedOutcomes = [
      'died' => 'Account disabled due to patient status. Please contact healthcare provider.',
      'transferred_out' => 'Account access suspended due to transfer. Contact your current healthcare facility.',
      'lost_to_followup' => 'Account temporarily suspended. Please contact healthcare provider to resume access.',
      'failed' => 'Account access suspended. Please contact healthcare provider.'
    ];

    // Final outcomes prevent access
    $finalOutcomes = ['cured', 'treatment_completed'];

    if (isset($blockedOutcomes[$outcome])) {
      Flash::set('danger', $blockedOutcomes[$outcome]);
      header('Location: /WEBSYS_FINAL_PROJECT/public/?route=login');
      exit;
    }

    // For inactive patients, restrict certain actions but allow viewing
    $inactiveOutcomes = array_merge($finalOutcomes, ['transferred_out', 'lost_to_followup', 'failed']);

    if (in_array($outcome, $inactiveOutcomes)) {
      // Set session flag for UI restrictions
      $_SESSION['patient_status_restricted'] = true;
    }

    return true;
  }

  public static function requireLogin() {
    if (empty($_SESSION['user'])) {
      header('Location: /WEBSYS_FINAL_PROJECT/public/login.php');
      exit;
    }
  }

  public static function requireRole($roles) {
    self::requireLogin();
    $roles = (array)$roles;
    $user = $_SESSION['user'];

    if (!in_array($user['role'], $roles)) {
      header("Location: /WEBSYS_FINAL_PROJECT/public/error.php?code=403&msg=Access+Denied");
      exit;
    }
  }

  public static function requireVerified() {
    self::requireLogin();
    $u = $_SESSION['user'];

    if (!$u['is_verified']) {
      header("Location: /WEBSYS_FINAL_PROJECT/public/error.php?code=403&msg=Please+verify+your+email+first");
      exit;
    }

    if ($u['password_reset_required']) {
      header('Location: /WEBSYS_FINAL_PROJECT/public/set_new_password.php?uid='.$u['user_id']);
      exit;
    }
  }
}
?>

<?php
require_once __DIR__ . '/../models/ReferralModel.php';
require_once __DIR__ . '/../models/PatientModel.php';
require_once __DIR__ . '/../models/LogModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/Flash.php';
require_once __DIR__ . '/../helpers/EmailHelper.php';
require_once __DIR__ . '/../helpers/BarangayHelper.php';

class ReferralController {
  public function index() {
    $role = $_SESSION['user']['role'];

    // filters: q, receiving_barangay, status
    $q = trim($_GET['q'] ?? '');
    $receiving = trim($_GET['receiving_barangay'] ?? '');
    $status = trim($_GET['status'] ?? '');

    if ($role === 'super_admin') {
        $rows = ReferralModel::getAllFiltered($q, $receiving, $status);
    } else {
        header("Location: /WEBSYS_FINAL_PROJECT/public/?route=referral/sent");
        exit;
    }

    $barangays = BarangayHelper::getAll();
    include __DIR__ . '/../../public/referrals/list.php';
  }

  public function sent() {
    AuthMiddleware::requireRole(['health_worker']);
    $barangay = $_SESSION['user']['barangay_assigned'];
    $rows = ReferralModel::getSentByBarangay($barangay);
    include __DIR__ . '/../../public/referrals/sent.php';
  }

  public function incoming() {
    AuthMiddleware::requireRole(['health_worker']);
    $barangay = $_SESSION['user']['barangay_assigned'];
    $rows = ReferralModel::getIncomingForBarangay($barangay);
    include __DIR__ . '/../../public/referrals/incoming.php';
  }

  public function create() {
    AuthMiddleware::requireRole(['super_admin','health_worker']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $data = $_POST;
        $data['created_by'] = $_SESSION['user']['user_id'];

        if ($_SESSION['user']['role'] === 'health_worker') {
            $data['referring_unit'] = $_SESSION['user']['barangay_assigned'];
            $data['referring_email'] = $_SESSION['user']['email'];
            $data['referring_address'] = $_SESSION['user']['barangay_assigned'];
        }

        $patient = PatientModel::getById($data['patient_id']);
        if (!$patient) {
            Flash::set('danger', 'Patient not found.');
            header("Location: /WEBSYS_FINAL_PROJECT/public/?route=referral/create");
            exit;
        }

        // Store minimal patient information at time of referral (simple approach)
        $data['tb_case_number'] = $patient['tb_case_number'];

        $data['referral_code'] = 'REF-' . date('Ymd') . '-' . rand(1000,9999);

        $id = ReferralModel::create($data);

        LogModel::insertLog($_SESSION['user']['user_id'], 'create', 'referrals', $id, null, json_encode($data),
                            $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '');

        // Notify receiving barangay health workers -> DB notification & immediate email only if verified
        $receivers = UserModel::getHealthWorkersByBarangay($data['receiving_barangay']);
        foreach ($receivers as $r) {
            NotificationModel::create([
                'user_id' => $r['user_id'],
                'patient_id' => $data['patient_id'],
                'type' => 'incoming_referral',
                'title' => 'Incoming Referral',
                'message' => "Referral {$data['referral_code']} for patient {$patient['patient_code']} has been assigned to your barangay.",
                'link' => "/WEBSYS_FINAL_PROJECT/public/?route=referral/view&id=$id"
            ]);
        }

        // Notify patient (DB + immediate email to verified account)
        NotificationModel::createForPatientUser(
            $data['patient_id'],
            'referral_created',
            'You have been referred',
            "A referral ({$data['referral_code']}) has been created for you.",
            "/WEBSYS_FINAL_PROJECT/public/?route=patientdashboard/referrals"
        );

        Flash::set('success','Referral created.');
        header("Location: /WEBSYS_FINAL_PROJECT/public/?route=referral/index");
        exit;
    }

    $user = $_SESSION['user'];
    if ($user['role'] === 'health_worker') {
        $patients = PatientModel::getAllByBarangay($user['barangay_assigned']);
    } else {
        $patients = PatientModel::getAll();
    }

    $barangays = BarangayHelper::getAll();

    include __DIR__ . '/../../public/referrals/create.php';
  }

  public function view() {
    AuthMiddleware::requireRole(['super_admin','health_worker','patient']);
    $id = $_GET['id'] ?? null;

    if (!$id) { Flash::set('danger','Missing ID'); header('Location: /WEBSYS_FINAL_PROJECT/public/?route=referral/index'); exit; }

    $ref = ReferralModel::getById($id);
    if (!$ref) { Flash::set('danger','Referral not found'); header('Location: /WEBSYS_FINAL_PROJECT/public/?route=referral/index'); exit; }

    include __DIR__ . '/../../public/referrals/view.php';
  }

  public function receive() {
    AuthMiddleware::requireRole(['super_admin','health_worker']);
    $id = $_GET['id'] ?? null;

    if (!$id) { Flash::set('danger','Missing ID'); header('Location: /WEBSYS_FINAL_PROJECT/public/?route=referral/incoming'); exit; }

    $ref = ReferralModel::getById($id);
    if (!$ref) { Flash::set('danger','Referral not found'); header('Location: /WEBSYS_FINAL_PROJECT/public/?route=referral/incoming'); exit; }

    $userBarangay = $_SESSION['user']['barangay_assigned'] ?? null;
    if ($_SESSION['user']['role'] !== 'super_admin' && $ref['receiving_barangay'] !== $userBarangay) {
      Flash::set('danger','Not authorized.'); header('Location: /WEBSYS_FINAL_PROJECT/public/?route=referral/incoming'); exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $data = [
        'receiving_unit' => $_SESSION['user']['barangay_assigned'],
        'receiving_officer' => $_POST['receiving_officer'],
        'date_received' => $_POST['date_received'] ?? date('Y-m-d'),
        'action_taken' => $_POST['action_taken'],
        'remarks' => $_POST['remarks'],
        'received_by' => $_SESSION['user']['user_id'],
        'referral_status' => 'received'
      ];

      ReferralModel::updateReceiving($id, $data);

      // Fetch patient data before updating
      $patient = PatientModel::getById($ref['patient_id']);

      PatientModel::update($ref['patient_id'], [
          'patient_code' => $patient['patient_code'],
          'name' => $patient['name'] ?? '',
          'tb_case_number' => $patient['tb_case_number'],
          'philhealth_id' => $patient['philhealth_id'] ?? null,
          'age' => $patient['age'],
          'sex' => $patient['sex'],
          'barangay' => $ref['receiving_barangay'],
          'contact_number' => $patient['contact_number'],
          'bacteriological_status' => $patient['bacteriological_status'],
          'anatomical_site' => $patient['anatomical_site'],
          'drug_susceptibility' => $patient['drug_susceptibility'],
          'treatment_history' => $patient['treatment_history'],
          'treatment_outcome' => $patient['treatment_outcome']
      ]);

      LogModel::insertLog($_SESSION['user']['user_id'], 'receive', 'referrals',
                          $id, null, json_encode($data),
                          $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '');

      // Notify sender (DB + immediate email if verified)
      if (!empty($ref['created_by'])) {
        NotificationModel::create([
          'user_id' => $ref['created_by'],
          'patient_id' => $ref['patient_id'],
          'type' => 'referral_received',
          'title' => 'Referral Received',
          'message' => "Referral {$ref['referral_code']} was marked as received.",
          'link' => "/WEBSYS_FINAL_PROJECT/public/?route=referral/view&id=$id"
        ]);
      }

      // Notify patient (DB and immediate email if verified)
      NotificationModel::createForPatientUser(
          $ref['patient_id'],
          'referral_received_patient',
          'Your Referral Was Received',
          "Referral {$ref['referral_code']} has been received by {$ref['receiving_barangay']}.",
          "/WEBSYS_FINAL_PROJECT/public/?route=patientdashboard/referrals"
      );

      Flash::set('success','Referral marked as received.');
      header('Location: /WEBSYS_FINAL_PROJECT/public/?route=referral/incoming');
      exit;
    }

    include __DIR__ . '/../../public/referrals/receive.php';
  }

  public function edit() {
    AuthMiddleware::requireRole(['super_admin','health_worker']);

    $id = $_GET['id'] ?? null;
    if (!$id) { Flash::set('danger','Missing ID'); header('Location: /WEBSYS_FINAL_PROJECT/public/?route=referral/index'); exit; }
    $ref = ReferralModel::getById($id);
    if (!$ref) { Flash::set('danger','Referral not found'); header('Location: /WEBSYS_FINAL_PROJECT/public/?route=referral/index'); exit; }
    if ($ref['referral_status'] === 'received') {
      Flash::set('danger','Received referrals cannot be edited.');
      header("Location: /WEBSYS_FINAL_PROJECT/public/?route=referral/view&id=$id");
      exit;
    }
    if ($_SESSION['user']['role'] !== 'super_admin' && $ref['created_by'] != $_SESSION['user']['user_id']) {
      Flash::set('danger','Not authorized.');
      header("Location: /WEBSYS_FINAL_PROJECT/public/?route=referral/view&id=$id");
      exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $data = $_POST;
      $patient = PatientModel::getById($data['patient_id']);
      if (!$patient) {
        Flash::set('danger','Invalid patient.');
        header("Location: /WEBSYS_FINAL_PROJECT/public/?route=referral/edit&id=$id");
        exit;
      }
      $data['tb_case_number'] = $patient['tb_case_number'];

      // Check if receiving barangay changed — we'll notify new barangay after update
      $oldReceiving = $ref['receiving_barangay'] ?? '';
      $newReceiving = $data['receiving_barangay'] ?? '';

      ReferralModel::update($id, $data);

      LogModel::insertLog($_SESSION['user']['user_id'], 'update', 'referrals',
                          $id, null, json_encode($data),
                          $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '');

      // If receiving barangay changed, notify health workers of the new barangay
      if (!empty($newReceiving) && $newReceiving !== $oldReceiving) {
          $receivers = UserModel::getHealthWorkersByBarangay($newReceiving);
          foreach ($receivers as $r) {
              NotificationModel::create([
                  'user_id' => $r['user_id'],
                  'patient_id' => $data['patient_id'],
                  'type' => 'incoming_referral',
                  'title' => 'Incoming Referral (updated)',
                  'message' => "Referral {$ref['referral_code']} has been assigned/changed to your barangay.",
                  'link' => "/WEBSYS_FINAL_PROJECT/public/?route=referral/view&id=$id"
              ]);
          }
      }

      Flash::set('success','Referral updated.');
      header("Location: /WEBSYS_FINAL_PROJECT/public/?route=referral/view&id=$id");
      exit;
    }

    $user = $_SESSION['user'];
    if ($user['role'] === 'health_worker') {
        $patients = PatientModel::getAllByBarangay($user['barangay_assigned']);
    } else {
        $patients = PatientModel::getAll();
    }

    $barangays = BarangayHelper::getAll();
    include __DIR__ . '/../../public/referrals/edit.php';
  }

  public function delete() {
    AuthMiddleware::requireRole(['super_admin']);

    $id = $_GET['id'] ?? null;

    if (!$id) {
      Flash::set('danger','Missing ID');
      header('Location: /WEBSYS_FINAL_PROJECT/public/?route=referral/index');
      exit;
    }

    ReferralModel::delete($id);

    LogModel::insertLog($_SESSION['user']['user_id'], 'delete', 'referrals',
                        $id, null, null,
                        $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '');

    Flash::set('success','Referral deleted.');
    header('Location: /WEBSYS_FINAL_PROJECT/public/?route=referral/index');
    exit;
  }

  public function print() {
      AuthMiddleware::requireRole(['super_admin','health_worker','patient']);

      $id = $_GET['id'] ?? null;
      if (!$id) {
          Flash::set('danger','Missing ID');
          header('Location: /WEBSYS_FINAL_PROJECT/public/?route=referral/index');
          exit;
      }

      $ref = ReferralModel::getById($id);
      if (!$ref) {
          Flash::set('danger','Referral not found');
          header('Location: /WEBSYS_FINAL_PROJECT/public/?route=referral/index');
          exit;
      }

      // SECURITY CHECK: patients can only view their own referral
      if ($_SESSION['user']['role'] === 'patient') {
          $pdo = getDB();
          $stmt = $pdo->prepare("SELECT patient_id FROM patients WHERE user_id = ?");
          $stmt->execute([$_SESSION['user']['user_id']]);
          $pid = $stmt->fetchColumn();

          if ($ref['patient_id'] != $pid) {
              Flash::set('danger','Access denied.');
              header('Location: /WEBSYS_FINAL_PROJECT/public/?route=patientdashboard/referrals');
              exit;
          }
      }

      require_once __DIR__ . '/../helpers/PDFHelper.php';
      PDFHelper::generateReferralPDF($id);
      exit;
  }

  public function received() {
    AuthMiddleware::requireRole(['health_worker']);
    $barangay = $_SESSION['user']['barangay_assigned'];

    $rows = ReferralModel::getReceivedByBarangay($barangay);
    include __DIR__ . '/../../public/referrals/received.php';
  }
}
?>

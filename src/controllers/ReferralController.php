<?php
require_once __DIR__ . '/../models/ReferralModel.php';
require_once __DIR__ . '/../models/PatientModel.php';
require_once __DIR__ . '/../models/LogModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/Flash.php';

class ReferralController {
  public function index() {
    $role = $_SESSION['user']['role'];

    if ($role === 'super_admin') {
        $rows = ReferralModel::getAll();
    } else {
        // health workers should NOT be sent to XAMPP dashboard
        header("Location: /WEBSYS_FINAL_PROJECT/public/?route=referral/sent");
        exit;
    }

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

        // HEALTH WORKER → autofill referring info
        if ($_SESSION['user']['role'] === 'health_worker') {

            $data['referring_unit'] = $_SESSION['user']['barangay_assigned']; 
            $data['referring_email'] = $_SESSION['user']['email'];
            $data['referring_address'] = $_SESSION['user']['barangay_assigned'];
        }

        // SUPER ADMIN → selected from dropdown (referring_unit already provided in POST)

        // Validate patient
        $patient = PatientModel::getById($data['patient_id']);
        if (!$patient) {
            Flash::set('danger', 'Patient not found.');
            header("Location: /WEBSYS_FINAL_PROJECT/public/?route=referral/create");
            exit;
        }

        $data['tb_case_number'] = $patient['tb_case_number'];
        $data['referral_code'] = 'REF-' . date('Ymd') . '-' . rand(1000,9999);

        $id = ReferralModel::create($data);

        LogModel::insertLog($_SESSION['user']['user_id'], 'create', 'referrals', $id, null, json_encode($data),
                            $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '');

        // Notify receiving barangay health workers
        $receivers = UserModel::getHealthWorkersByBarangay($data['receiving_barangay']);
        foreach ($receivers as $r) {
            NotificationModel::create([
                'user_id' => $r['user_id'],
                'type' => 'referral_received',
                'title' => 'New Referral Assigned',
                'message' => "Referral {$data['referral_code']} for patient {$patient['patient_code']} assigned to your barangay.",
                'link' => "/WEBSYS_FINAL_PROJECT/public/?route=referral/view&id=$id"
            ]);
        }

        Flash::set('success','Referral created.');
        header("Location: /WEBSYS_FINAL_PROJECT/public/?route=referral/index");
        exit;
    }

    // GET — Load form
    $user = $_SESSION['user'];

    if ($user['role'] === 'health_worker') {
        $patients = PatientModel::getAllByBarangay($user['barangay_assigned']);
    } else {
        $patients = PatientModel::getAll();
    }

    $barangays = [
        'Ambiong','Loakan Proper','Pacdal','BGH Compound','Bakakeng Central','Camp 7'
    ];

    include __DIR__ . '/../../public/referrals/create.php';
  }

  public function view() {
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

    include __DIR__ . '/../../public/referrals/view.php';
  }

  public function receive() {
    AuthMiddleware::requireRole(['super_admin','health_worker']);
    $id = $_GET['id'] ?? null;

    if (!$id) {
      Flash::set('danger','Missing ID');
      header('Location: /WEBSYS_FINAL_PROJECT/public/?route=referral/incoming');
      exit;
    }

    $ref = ReferralModel::getById($id);
    if (!$ref) {
      Flash::set('danger','Referral not found');
      header('Location: /WEBSYS_FINAL_PROJECT/public/?route=referral/incoming');
      exit;
    }

    $userBarangay = $_SESSION['user']['barangay_assigned'] ?? null;

    if ($_SESSION['user']['role'] !== 'super_admin' &&
        $ref['receiving_barangay'] !== $userBarangay) 
    {
      Flash::set('danger','Not authorized.');
      header('Location: /WEBSYS_FINAL_PROJECT/public/?route=referral/incoming');
      exit;
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

      // Log
      LogModel::insertLog($_SESSION['user']['user_id'], 'receive', 'referrals',
                          $id, null, json_encode($data),
                          $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '');

      // Notify sender
      if (!empty($ref['created_by'])) {
        NotificationModel::create([
          'user_id' => $ref['created_by'],
          'type' => 'referral_received_notification',
          'title' => 'Referral Received',
          'message' => "Referral {$ref['referral_code']} was marked as received.",
          'link' => "/WEBSYS_FINAL_PROJECT/public/?route=referral/view&id=$id"
        ]);
      }

      Flash::set('success','Referral marked as received.');
      header('Location: /WEBSYS_FINAL_PROJECT/public/?route=referral/incoming');
      exit;
    }

    include __DIR__ . '/../../public/referrals/receive.php';
  }

  public function edit() {
    AuthMiddleware::requireRole(['super_admin','health_worker']);

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

    if ($ref['referral_status'] === 'received') {
      Flash::set('danger','Received referrals cannot be edited.');
      header("Location: /WEBSYS_FINAL_PROJECT/public/?route=referral/view&id=$id");
      exit;
    }

    // Only super_admin or sender can edit
    if ($_SESSION['user']['role'] !== 'super_admin' &&
        $ref['created_by'] != $_SESSION['user']['user_id']) 
    {
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

      ReferralModel::update($id, $data);

      LogModel::insertLog($_SESSION['user']['user_id'], 'update', 'referrals', 
                          $id, null, json_encode($data),
                          $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'] ?? '');

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
    $barangays = [
      'Loakan Proper','North Fairview','Burnham','Quezon Hill','Upper Bonifacio',
      'Session Road','Pinsao','Shaw','Camp 7','Balili'
    ];

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
    AuthMiddleware::requireRole(['super_admin','health_worker']);

    $id = $_GET['id'] ?? null;
    if (!$id) {
      Flash::set('danger','Missing ID');
      header('Location: /WEBSYS_FINAL_PROJECT/public/?route=referral/index');
      exit;
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

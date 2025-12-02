<?php
require_once __DIR__ . '/../models/PatientModel.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/ImportLogModel.php';
require_once __DIR__ . '/../models/LogModel.php';
require_once __DIR__ . '/../helpers/EmailHelper.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helpers/Flash.php';

class ImportController {

    public function upload() {
        AuthMiddleware::requireRole(['super_admin']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {

            $file = $_FILES['csv_file'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                Flash::set('danger', 'Upload error');
                header("Location: /WEBSYS_FINAL_PROJECT/public/?route=import/upload");
                exit;
            }

            $handle = fopen($file['tmp_name'], 'r');
            if (!$handle) {
                Flash::set('danger', 'Could not read file');
                header("Location: /WEBSYS_FINAL_PROJECT/public/?route=import/upload");
                exit;
            }

            $header = fgetcsv($handle);
            $rowsImported = 0;
            $skipped = 0;
            $errors = [];

            while (($row = fgetcsv($handle)) !== false) {
                $data = @array_combine($header, $row);
                if (!$data) { $skipped++; continue; }
                if (empty($data['barangay'])) { $skipped++; continue; }

                if (empty($data['patient_code'])) {
                    $data['patient_code'] = PatientModel::generatePatientCode();
                }

                if (PatientModel::existsByCode($data['patient_code'])) {
                    $skipped++;
                    continue;
                }

                $userId = null;
                if (!empty($data['email'])) {
                    $existing = UserModel::getByEmail($data['email']);

                    if ($existing) {
                        // If the user exists — do not auto-verify. Reset token and force verification
                        $token = bin2hex(random_bytes(16));
                        $pdo = getDB();
                        $stmt = $pdo->prepare("UPDATE users SET is_verified = 0, verification_token = ?, password_reset_required = 1 WHERE user_id = ?");
                        $stmt->execute([$token, $existing['user_id']]);
                        EmailHelper::sendVerificationEmail($existing['email'], $token);
                        $userId = $existing['user_id'];
                    } else {
                        // Check treatment outcome before creating user
                        if (isset($data['treatment_outcome']) && $data['treatment_outcome'] === 'died') {
                            $errors[] = "Cannot create user account for deceased patients (row)";
                            $skipped++;
                            continue;
                        }

                        // Create new patient user (unverified)
                        try {
                            $tempPass = bin2hex(random_bytes(5));
                            $token = bin2hex(random_bytes(16));
                            $userId = UserModel::createPatientUser($data['email'], $tempPass, $token);
                            EmailHelper::sendVerificationEmail($data['email'], $token);
                        } catch (PDOException $e) {
                            $errors[] = "Email error on row: " . $e->getMessage();
                            $skipped++;
                            continue;
                        }
                    }
                }

                if (!is_numeric($userId)) $userId = null;

                if (empty($data['tb_case_number'])) {
                    $data['tb_case_number'] = PatientModel::generateTbCaseNumber();
                }

                PatientModel::createFromImport($data, $userId);
                $rowsImported++;
            }

            fclose($handle);

            ImportLogModel::logUpload($file['name'], $rowsImported, $_SESSION['user']['user_id']);

            LogModel::insertLog(
                $_SESSION['user']['user_id'],
                'import_patients',
                'patients',
                null,
                null,
                json_encode([
                    'file' => $file['name'],
                    'inserted' => $rowsImported,
                    'skipped' => $skipped,
                    'errors' => $errors
                ]),
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT']
            );

            $f = $file;
            $rows = $rowsImported;
            $inserted = $rowsImported;

            include __DIR__ . '/../../public/import/upload_result.php';
            return;
        }

        include __DIR__ . '/../../public/import/upload.php';
    }
}

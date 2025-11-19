<?php
require_once __DIR__.'/../../src/middleware/AuthMiddleware.php';
require_once __DIR__.'/../../src/helpers/PDFHelper.php';
AuthMiddleware::requireRole(['super_admin','health_worker']);

$id = $_GET['id'] ?? null;
if (!$id) die('Missing referral ID');

PDFHelper::generateReferralPDF($id);
exit;
?>
<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../../src/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../src/models/NotificationModel.php';

$user = $_SESSION['user'];
$role = $user['role'];

$type_map = [
  'incoming_referral' => ['label'=>'Incoming Referral','class'=>'info'],
  'referral_received' => ['label'=>'Referral Received','class'=>'success'],
  'referral_received_patient' => ['label'=>'Referral Received (Patient)','class'=>'success'],
  'referral_created' => ['label'=>'Referral Created','class'=>'primary'],
  'medication_schedule' => ['label'=>'Medication Reminder (Scheduled)','class'=>'warning'],
  'medication_pre_reminder' => ['label'=>'Medication — 1 day before','class'=>'warning'],
  'medication_today' => ['label'=>'Medication — Today','class'=>'danger'],
  'medication_created' => ['label'=>'Medication Added','class'=>'primary'],
  'medication_created_hw' => ['label'=>'Medication Added (HW)','class'=>'primary'],
  'staff_follow_up' => ['label'=>'Staff Follow-up Required','class'=>'danger'],
  'account_verification' => ['label'=>'Account Verification','class'=>'secondary']
];
?>

<div class="container py-4">
    <h3 class="mb-4">
        <?= $role === 'super_admin' ? 'All Notifications' : 'My Notifications' ?>
    </h3>

    <div class="card shadow-sm p-4">
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-light">
                    <tr style="text-align: center;">
                        <?php if ($role === 'super_admin'): ?>
                            <th style="width:200px; min-width:150px;">User</th>
                        <?php endif; ?>
                        <th style="width:200px; min-width:150px;">Title</th>
                        <th style="width:350px; min-width:250px;">Message</th>
                        <th style="width:150px; min-width:120px;">Type</th>
                        <th style="width:120px; min-width:100px;">Date</th>
                        <th style="width:100px; min-width:80px;">Actions</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (!empty($rows)): ?>
                    <?php foreach ($rows as $n): ?>
                        <tr class="<?= $n['is_read'] ? '' : 'table-warning' ?>">
                            <?php if ($role === 'super_admin'): ?>
                                <td class="text-center">
                                    <?php if (!empty($n['email'])): ?>
                                        <strong class="text-primary"><?= htmlspecialchars(ucfirst($n['role'])) ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($n['email']) ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">Unknown User</span>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>

                            <td class="text-center fw-bold text-primary"><?= htmlspecialchars($n['title']) ?></td>
                            <td class="text-start"><?= nl2br(htmlspecialchars($n['message'])) ?></td>

                            <?php
                              $meta = $type_map[$n['type']] ?? null;
                              $label = $meta['label'] ?? ucfirst(str_replace('_',' ', $n['type'] ?? ''));
                              $badgeClass = $meta['class'] ?? 'secondary';
                            ?>
                            <td class="text-center"><span class="badge bg-<?= htmlspecialchars($badgeClass) ?>"><?= htmlspecialchars($label) ?></span></td>

                            <td class="text-center"><?= htmlspecialchars($n['created_at']) ?></td>

                            <td class="text-center">
                                <?php if (!empty($n['link'])): ?>
                                    <a href="<?= htmlspecialchars($n['link']) ?>" class="btn btn-sm btn-outline-primary">Open</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?= $role === 'super_admin' ? 6 : 5 ?>" class="text-center">
                            No notifications found.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>

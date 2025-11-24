<?php
if (!isset($_SESSION)) session_start();
$user = $_SESSION['user'] ?? null;
require_once __DIR__ . '/../../src/models/NotificationModel.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<nav class="navbar navbar-expand-lg shadow-sm" data-bs-theme="dark" style="background:#124e96;">
  <div class="container-fluid">

    <?php
      $brandLink = "/WEBSYS_FINAL_PROJECT/public/login.php";

      if ($user) {
          if ($user['role'] === 'super_admin') {
              $brandLink = "/WEBSYS_FINAL_PROJECT/public/?route=admin/dashboard";
          } elseif ($user['role'] === 'health_worker') {
              $brandLink = "/WEBSYS_FINAL_PROJECT/public/?route=health/dashboard";
          } elseif ($user['role'] === 'patient') {
              $brandLink = "/WEBSYS_FINAL_PROJECT/public/?route=patientdashboard/index";
          }
      }
    ?>

    <a class="navbar-brand fw-bold text-white" href="<?= $brandLink ?>">TB-MAS</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarColor01">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarColor01">
      <ul class="navbar-nav me-auto">

        <?php if ($user): ?>

          <!-- SUPER ADMIN -->
          <?php if ($user['role'] === 'super_admin'): ?>
            <li class="nav-item"><a class="nav-link text-white" href="/WEBSYS_FINAL_PROJECT/public/?route=admin/dashboard">Dashboard</a></li>

            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle text-white" data-bs-toggle="dropdown" href="#">User Management</a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="/WEBSYS_FINAL_PROJECT/public/?route=patient/index">Patients</a></li>
                <li><a class="dropdown-item" href="/WEBSYS_FINAL_PROJECT/public/?route=admin/users">Add Users</a></li>
                <li><a class="dropdown-item" href="/WEBSYS_FINAL_PROJECT/public/?route=user/create_health_worker">Add Health Worker</a></li>
                <li><a class="dropdown-item" href="/WEBSYS_FINAL_PROJECT/public/?route=admin/profile">Profile</a></li>
              </ul>
            </li>

            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle text-white" data-bs-toggle="dropdown" href="#">Records</a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="/WEBSYS_FINAL_PROJECT/public/?route=contact/list">Contacts</a></li>
                <li><a class="dropdown-item" href="/WEBSYS_FINAL_PROJECT/public/?route=referral/index">Referrals</a></li>
                <li><a class="dropdown-item" href="/WEBSYS_FINAL_PROJECT/public/?route=medication/list">Medications</a></li>
              </ul>
            </li>

            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle text-white" data-bs-toggle="dropdown" href="#">Tools</a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="/WEBSYS_FINAL_PROJECT/public/?route=import/upload">Import CSV</a></li>
                <li><a class="dropdown-item" href="/WEBSYS_FINAL_PROJECT/public/?route=log/index">Audit Logs</a></li>
                <li><a class="dropdown-item" href="/WEBSYS_FINAL_PROJECT/public/?route=notification/list">Notifications</a></li>
                <li><a class="dropdown-item" href="/WEBSYS_FINAL_PROJECT/public/?route=stafffollowup/index">Staff follow-up</a></li>
              </ul>
            </li>
          <?php endif; ?>

          <!-- HEALTH WORKER -->
          <?php if ($user['role'] === 'health_worker'): ?>
            <li class="nav-item"><a class="nav-link text-white" href="/WEBSYS_FINAL_PROJECT/public/?route=health/dashboard">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/WEBSYS_FINAL_PROJECT/public/?route=patient/index">Patients</a></li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle text-white" data-bs-toggle="dropdown" href="#">Referrals</a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="/WEBSYS_FINAL_PROJECT/public/?route=referral/sent">Sent Referrals</a></li>
                <li><a class="dropdown-item" href="/WEBSYS_FINAL_PROJECT/public/?route=referral/incoming">Incoming Referrals</a></li>
                <li><a class="dropdown-item" href="/WEBSYS_FINAL_PROJECT/public/?route=referral/received">Received Referrals</a></li>
              </ul>
            </li>
            <li class="nav-item"><a class="nav-link text-white" href="/WEBSYS_FINAL_PROJECT/public/?route=medication/list">Medications</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/WEBSYS_FINAL_PROJECT/public/?route=notification/list">Notifications</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/WEBSYS_FINAL_PROJECT/public/?route=health/profile">Profile</a></li>
          <?php endif; ?>

          <!-- PATIENT -->
          <?php if ($user['role'] === 'patient'): ?>
            <li class="nav-item"><a class="nav-link text-white" href="/WEBSYS_FINAL_PROJECT/public/?route=patientdashboard/index">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/WEBSYS_FINAL_PROJECT/public/?route=patientdashboard/referrals">Referrals</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/WEBSYS_FINAL_PROJECT/public/?route=patientdashboard/notifications">Notifications</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/WEBSYS_FINAL_PROJECT/public/?route=patientdashboard/medications">Medications</a></li>
            <li class="nav-item"><a class="nav-link text-white" href="/WEBSYS_FINAL_PROJECT/public/?route=patientdashboard/profile">Profile</a></li>
          <?php endif; ?>

        <?php endif; ?>
      </ul>

      <!-- RIGHT SIDE -->
      <ul class="navbar-nav align-items-center">
        <?php if ($user): ?>

          <li class="nav-item dropdown me-2">
            <?php $unread = NotificationModel::countUnreadForUser($user['user_id']); ?>

            <a class="nav-link position-relative text-white" href="#" id="navNotifDropdown" data-bs-toggle="dropdown">
              <i class="bi bi-bell" style="font-size:1.3rem;"></i>
              <span id="notif-badge"
                    class="<?= $unread > 0 ? 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger' : 'd-none' ?>">
                <?= intval($unread) ?>
              </span>
            </a>

            <ul class="dropdown-menu dropdown-menu-end p-2" style="min-width:360px;" id="notif-dropdown">
              <li class="d-flex justify-content-between align-items-center mb-2 px-2">
                <strong>Notifications</strong>
                <a href="/WEBSYS_FINAL_PROJECT/public/?route=notification/list" class="small">View all</a>
              </li>
              <li><div id="notif-list" style="max-height:320px;overflow:auto;"></div></li>
              <li class="dropdown-divider"></li>
              <li class="px-2">
                <button id="mark-all-read" class="btn btn-sm btn-outline-secondary w-100">Mark all as read</button>
              </li>
            </ul>
          </li>

          <li class="nav-item">
            <span class="nav-link disabled text-light">Logged in as: <?= htmlspecialchars($user['role']) ?></span>
          </li>

          <li class="nav-item">
            <a class="nav-link text-white" href="/WEBSYS_FINAL_PROJECT/public/?route=auth/logout">Logout</a>
          </li>

        <?php else: ?>
          <li class="nav-item"><a class="nav-link text-white" href="/WEBSYS_FINAL_PROJECT/public/login.php">Login</a></li>
        <?php endif; ?>
      </ul>

    </div>
  </div>
</nav>

<?php require_once __DIR__ . '/../../src/helpers/Flash.php'; ?>
<?php Flash::display(); ?>

<script>
/* Notification JS unchanged */
</script>

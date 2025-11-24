<?php
if (!isset($_SESSION)) session_start();
$user = $_SESSION['user'] ?? null;
?>

<nav class="navbar navbar-expand-lg tb-navbar mb-4" data-bs-theme="dark">
  <div class="container-fluid">

    <?php
      $brandLink = "/WEBSYS_FINAL_PROJECT/public/login.php"; // default for guests

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

    <a class="navbar-brand fw-bold" href="<?= $brandLink ?>">TB-MAS</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarColor01">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarColor01">
      <ul class="navbar-nav me-auto">

        <?php if ($user): ?>

          <!-- ===================== SUPER ADMIN ===================== -->
          <?php if ($user['role'] === 'super_admin'): ?>

            <!-- MAIN LINKS -->
            <li class="nav-item"><a class="nav-link" href="/WEBSYS_FINAL_PROJECT/public/?route=admin/dashboard">Dashboard</a></li>

            <!-- USERS DROPDOWN -->
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#">Users</a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="/WEBSYS_FINAL_PROJECT/public/?route=patient/index">Patients</a></li>
                <li><a class="dropdown-item" href="/WEBSYS_FINAL_PROJECT/public/?route=admin/users">Add Users</a></li>
                <li><a class="dropdown-item" href="/WEBSYS_FINAL_PROJECT/public/?route=user/create_health_worker">Add Health Worker</a></li>
                <li><a class="dropdown-item" href="/WEBSYS_FINAL_PROJECT/public/?route=admin/profile">Profile</a></li>
              </ul>
            </li>

            <!-- RECORDS DROPDOWN -->
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#">Records</a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="/WEBSYS_FINAL_PROJECT/public/?route=contact/list">Contacts</a></li>
                <li><a class="dropdown-item" href="/WEBSYS_FINAL_PROJECT/public/?route=referral/index">Referrals</a></li>
                <li><a class="dropdown-item" href="/WEBSYS_FINAL_PROJECT/public/?route=medication/list">Medications</a></li>
              </ul>
            </li>

            <!-- TOOLS DROPDOWN -->
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#">Tools</a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="/WEBSYS_FINAL_PROJECT/public/?route=import/upload">Import CSV</a></li>
                <li><a class="dropdown-item" href="/WEBSYS_FINAL_PROJECT/public/?route=log/index">Audit Logs</a></li>
                <li><a class="dropdown-item" href="/WEBSYS_FINAL_PROJECT/public/?route=notification/list">Notifications</a></li>
              </ul>
            </li>

          <?php endif; ?>

          <!-- ===================== HEALTH WORKER ===================== -->
          <?php if ($user['role'] === 'health_worker'): ?>
            <li class="nav-item"><a class="nav-link" href="/WEBSYS_FINAL_PROJECT/public/?route=health/dashboard">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="/WEBSYS_FINAL_PROJECT/public/?route=patient/index">Patients</a></li>

            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#">Referrals</a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="/WEBSYS_FINAL_PROJECT/public/?route=referral/sent">Sent Referrals</a></li>
                <li><a class="dropdown-item" href="/WEBSYS_FINAL_PROJECT/public/?route=referral/incoming">Incoming Referrals</a></li>
                <li><a class="dropdown-item" href="/WEBSYS_FINAL_PROJECT/public/?route=referral/received">Received Referrals</a></li>
              </ul>
            </li>

            <li class="nav-item"><a class="nav-link" href="/WEBSYS_FINAL_PROJECT/public/?route=medication/list">Medications</a></li>
            <li class="nav-item"><a class="nav-link" href="/WEBSYS_FINAL_PROJECT/public/?route=notification/list">Notifications</a></li>
            <li class="nav-item"><a class="nav-link" href="/WEBSYS_FINAL_PROJECT/public/?route=health/profile">Profile</a></li>
          <?php endif; ?>

          <!-- ===================== PATIENT ===================== -->
          <?php if ($user['role'] === 'patient'): ?>
            <li class="nav-item"><a class="nav-link" href="/WEBSYS_FINAL_PROJECT/public/?route=patientdashboard/index">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="/WEBSYS_FINAL_PROJECT/public/?route=patientdashboard/referrals">Referrals</a></li>
            <li class="nav-item"><a class="nav-link" href="/WEBSYS_FINAL_PROJECT/public/?route=patientdashboard/notifications">Notifications</a></li>
            <li class="nav-item"><a class="nav-link" href="/WEBSYS_FINAL_PROJECT/public/?route=patientdashboard/profile">Profile</a></li>
          <?php endif; ?>
        <?php endif; ?>
      </ul>

      <!-- ===================== RIGHT SIDE ===================== -->
      <ul class="navbar-nav">
        <?php if ($user): ?>
          <li class="nav-item">
            <span class="nav-link disabled text-light">Logged in as: <?= htmlspecialchars($user['role']) ?></span>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/WEBSYS_FINAL_PROJECT/public/?route=auth/logout">Logout</a>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="/WEBSYS_FINAL_PROJECT/public/login.php">Login</a></li>
        <?php endif; ?>
      </ul>
      
    </div>
  </div>
</nav>

<?php require_once __DIR__ . '/../../src/helpers/Flash.php'; ?>
<?php Flash::display(); ?>

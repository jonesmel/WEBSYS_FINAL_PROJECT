<?php
include __DIR__.'/../partials/header.php';
include __DIR__.'/../partials/navbar.php';
?>

<div class="container py-4" style="max-width:800px;">
  <h3 class="mb-4">CSV Import Result</h3>

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <p><strong>File:</strong> <?=htmlspecialchars($f['name'] ?? 'Unknown')?></p>
      <p><strong>Total Rows Read:</strong> <?=htmlspecialchars($rows ?? 0)?></p>
      <p><strong>Inserted:</strong> <?=htmlspecialchars($inserted ?? 0)?></p>
      <p><strong>Skipped:</strong> <?=htmlspecialchars($skipped ?? 0)?></p>
    </div>
  </div>

  <?php if (!empty($errors)): ?>
  <div class="card border-danger shadow-sm mb-4">
    <div class="card-header bg-danger text-white">Errors</div>
    <div class="card-body">
      <ul class="text-danger mb-0">
        <?php foreach ($errors as $e): ?>
          <li><?=htmlspecialchars($e)?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
  <?php endif; ?>

  <a href="/WEBSYS_FINAL_PROJECT/public/?route=import/upload" class="btn btn-secondary">Back to Import</a>
</div>

<?php include __DIR__.'/../partials/footer.php'; ?>
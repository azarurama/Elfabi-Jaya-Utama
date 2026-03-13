<?php include __DIR__ . '/partials/admin_header.php'; ?>
<h1>Dashboard</h1>
<p>Selamat datang di panel admin. Berikut ringkasan singkat:</p>
<div class="card-grid">
  <div class="card"><h3>Total Layanan</h3><p>
    <?php $cnt = db()->query('SELECT COUNT(*) as c FROM services')->fetch()['c'] ?? 0; echo (int)$cnt; ?>
  </p></div>
  <div class="card"><h3>Total Portofolio</h3><p>
    <?php $cnt = db()->query('SELECT COUNT(*) as c FROM portfolio')->fetch()['c'] ?? 0; echo (int)$cnt; ?>
  </p></div>
  <div class="card"><h3>Total Klien</h3><p>
    <?php $cnt = db()->query('SELECT COUNT(*) as c FROM clients')->fetch()['c'] ?? 0; echo (int)$cnt; ?>
  </p></div>
  <div class="card"><h3>Kontak</h3><p>
    <strong>Email:</strong> <?= e(get_setting('company_email', 'info@elfabi.co.id')) ?><br>
    <strong>Telepon:</strong> <?= e(get_setting('company_phone', '021-1234567')) ?>
  </p></div>
</div>

<div class="card mt-4">
  <div class="card-header">
    <h2 class="h4 mb-0">Backup & Restore Database</h2>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col-md-6">
        <h4>Backup Database</h4>
        <p>Unduh salinan database terbaru sebagai file SQL.</p>
        <a href="backup_database.php" class="btn btn-primary">
          <i class="fas fa-download me-2"></i>Unduh Backup
        </a>
      </div>
      <div class="col-md-6">
        <h4>Restore Database</h4>
        <p>Pulihkan database dari file backup.</p>
        <a href="restore_database.php" class="btn btn-outline-secondary">
          <i class="fas fa-upload me-2"></i>Restore Database
        </a>
      </div>
    </div>
    <div class="mt-3 text-muted small">
      <i class="fas fa-info-circle me-1"></i> Backup database secara teratur untuk mencegah kehilangan data.
    </div>
  </div>
</div>

<style>
.card-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.card {
  background: white;
  border-radius: 8px;
  padding: 1.5rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card h3 {
  margin-top: 0;
  color: #333;
  font-size: 1.1rem;
  margin-bottom: 0.5rem;
}

.card p {
  margin: 0;
  color: #666;
  font-size: 1.5rem;
  font-weight: bold;
}

.card p strong {
  display: inline-block;
  min-width: 80px;
  font-weight: normal;
  font-size: 0.9rem;
}

.mt-4 { margin-top: 1.5rem; }
.mb-0 { margin-bottom: 0 !important; }
.mb-3 { margin-bottom: 1rem; }
.mt-3 { margin-top: 1rem; }
.me-1 { margin-right: 0.25rem; }
.me-2 { margin-right: 0.5rem; }
.h4 { font-size: 1.25rem; }
.small { font-size: 0.875em; }
.text-muted { color: #6c757d !important; }
</style>
<?php include __DIR__ . '/partials/admin_footer.php'; ?>

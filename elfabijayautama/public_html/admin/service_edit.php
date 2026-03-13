<?php include __DIR__ . '/partials/admin_header.php'; ?>
<?php
$pdo = db();
$id = (int)($_GET['id'] ?? 0);
$service = $pdo->prepare('SELECT * FROM services WHERE id = ?');
$service->execute([$id]);
$service = $service->fetch();
if (!$service) { set_flash('error', 'Layanan tidak ditemukan.'); redirect('admin/services.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verify_csrf()) { set_flash('error', 'Token tidak valid.'); }
  else {
    $title = trim($_POST['title'] ?? '');
    $icon = trim($_POST['icon'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $stmt = $pdo->prepare('UPDATE services SET title=?, icon=?, description=? WHERE id=?');
    $stmt->execute([$title, $icon, $description, $id]);
    set_flash('success', 'Layanan diperbarui.');
    redirect('admin/services.php');
  }
}
?>
<h1>Edit Layanan</h1>
<div class="card">
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <div class="form-group">
      <label>Judul</label>
      <input type="text" name="title" required value="<?= e($service['title']) ?>">
    </div>
    <div class="form-group">
      <label>Ikon</label>
      <input type="text" name="icon" value="<?= e($service['icon']) ?>">
    </div>
    <div class="form-group">
      <label>Deskripsi</label>
      <textarea name="description" rows="5"><?= e($service['description']) ?></textarea>
    </div>
    <button class="btn btn-accent" type="submit">Simpan</button>
  </form>
</div>
<?php include __DIR__ . '/partials/admin_footer.php'; ?>

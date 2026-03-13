<?php include __DIR__ . '/partials/admin_header.php'; ?>
<?php
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verify_csrf()) {
    set_flash('error', 'Token tidak valid.');
  } else {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $icon = trim($_POST['icon'] ?? '');
    if ($title === '') {
      set_flash('error', 'Judul wajib diisi.');
    } else {
      $stmt = $pdo->prepare('INSERT INTO services(title, description, icon) VALUES(?,?,?)');
      $stmt->execute([$title, $description, $icon]);
      set_flash('success', 'Layanan berhasil ditambahkan.');
      redirect('admin/services.php');
    }
  }
}

if (($_GET['delete'] ?? '') !== '') {
  $id = (int)$_GET['delete'];
  $pdo->prepare('DELETE FROM services WHERE id = ?')->execute([$id]);
  set_flash('success', 'Layanan dihapus.');
  redirect('admin/services.php');
}

$flashError = get_flash('error');
$flashSuccess = get_flash('success');
$items = $pdo->query('SELECT * FROM services ORDER BY id DESC')->fetchAll();
?>
<h1>Kelola Layanan</h1>
<?php if ($flashError): ?><div class="err"><?= e($flashError) ?></div><?php endif; ?>
<?php if ($flashSuccess): ?><div class="msg"><?= e($flashSuccess) ?></div><?php endif; ?>
<div class="two-col">
  <div>
    <h2>Daftar Layanan</h2>
    <div class="card">
      <table style="width:100%; border-collapse: collapse">
        <thead>
          <tr><th style="text-align:left; border-bottom:1px solid #e5e7eb; padding:8px">Judul</th><th style="text-align:left; border-bottom:1px solid #e5e7eb; padding:8px">Ikon</th><th style="border-bottom:1px solid #e5e7eb; padding:8px"></th></tr>
        </thead>
        <tbody>
          <?php foreach ($items as $it): ?>
          <tr>
            <td style="padding:8px;"><?= e($it['title']) ?></td>
            <td style="padding:8px;"><?= e($it['icon']) ?></td>
            <td style="padding:8px; text-align:right">
              <a class="btn btn-outline" href="<?= url('admin/service_edit.php?id=' . (int)$it['id']) ?>">Edit</a>
              <a class="btn btn-outline" href="<?= url('admin/services.php?delete=' . (int)$it['id']) ?>" onclick="return confirm('Hapus layanan ini?')">Hapus</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div>
    <h2>Tambah Layanan</h2>
    <div class="card">
      <form method="post">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <div class="form-group">
          <label>Judul</label>
          <input type="text" name="title" required>
        </div>
        <div class="form-group">
          <label>Ikon (emoji atau nama ikon)</label>
          <input type="text" name="icon" placeholder="Contoh: 🎯">
        </div>
        <div class="form-group">
          <label>Deskripsi</label>
          <textarea name="description" rows="4"></textarea>
        </div>
        <button class="btn btn-accent" type="submit">Simpan</button>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/partials/admin_footer.php'; ?>

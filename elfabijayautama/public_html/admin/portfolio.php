<?php include __DIR__ . '/partials/admin_header.php'; ?>
<?php
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
  if (!verify_csrf()) { set_flash('error', 'Token tidak valid.'); }
  else {
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $client = trim($_POST['client'] ?? '');
    $project_date = trim($_POST['project_date'] ?? '');
    $services_used = trim($_POST['services_used'] ?? '');
    $image_path = null;

    if ($title === '' || $category === '') { 
      set_flash('error', 'Judul dan Kategori wajib diisi.'); 
    } else {
      if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = dirname(__DIR__) . "/uploads/portfolio/";
        $result = handle_file_upload(
            $_FILES['image'],
            $target_dir,
            ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            5 * 1024 * 1024 // 5MB
        );
        
        if (!$result['success']) {
            set_flash('error', 'Gagal mengunggah gambar: ' . $result['error']);
            header('Location: portfolio.php');
            exit;
        }
        
        // Get the relative path for database
        $new_filename = basename($result['path']);
        $image_path = 'uploads/portfolio/' . $new_filename;
      }

      if (!get_flash('error')) {
        $stmt = $pdo->prepare('INSERT INTO portfolio(title, category, image, description, client, project_date, services_used) VALUES(?,?,?,?,?,?,?)');
        $stmt->execute([$title, $category, $image_path, $description, $client, $project_date, $services_used]);
        set_flash('success', 'Proyek portofolio ditambahkan.');
        redirect('admin/portfolio.php');
      }
    }
  }
}

if (($_GET['delete'] ?? '') !== '') {
  $id = (int)$_GET['delete'];
  $pdo->prepare('DELETE FROM portfolio WHERE id = ?')->execute([$id]);
  set_flash('success', 'Portofolio dihapus.');
  redirect('admin/portfolio.php');
}

$flashError = get_flash('error');
$flashSuccess = get_flash('success');
$items = $pdo->query('SELECT * FROM portfolio ORDER BY id DESC')->fetchAll();
?>
<h1>Kelola Portofolio</h1>
<?php if ($flashError): ?><div class="err"><?= e($flashError) ?></div><?php endif; ?>
<?php if ($flashSuccess): ?><div class="msg"><?= e($flashSuccess) ?></div><?php endif; ?>
<div class="two-col">
  <div>
    <h2>Daftar Proyek</h2>
    <div class="card">
      <table style="width:100%; border-collapse: collapse">
        <thead>
          <tr><th style="text-align:left; border-bottom:1px solid #e5e7eb; padding:8px">Judul</th><th style="text-align:left; border-bottom:1px solid #e5e7eb; padding:8px">Kategori</th><th style="border-bottom:1px solid #e5e7eb; padding:8px"></th></tr>
        </thead>
        <tbody>
          <?php foreach ($items as $it): ?>
          <tr>
            <td style="padding:8px;"><?= e($it['title']) ?></td>
            <td style="padding:8px;"><?= e($it['category']) ?></td>
            <td style="padding:8px; text-align:right">
              <a class="btn btn-outline" href="<?= url('admin/portfolio_edit.php?id=' . (int)$it['id']) ?>">Edit</a>
              <a class="btn btn-outline" href="<?= url('admin/portfolio.php?delete=' . (int)$it['id']) ?>" onclick="return confirm('Hapus proyek ini?')">Hapus</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div>
    <h2>Tambah Proyek</h2>
    <div class="card">
      <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="action" value="add">
        <div class="form-group">
          <label>Judul</label>
          <input type="text" name="title" required>
        </div>
        <div class="form-group">
          <label>Kategori</label>
          <select name="category" required>
            <option value="">- Pilih -</option>
            <option value="outdoor">Outdoor</option>
            <option value="indoor">Indoor</option>
            <option value="branding">Branding</option>
          </select>
        </div>
        <div class="form-group">
          <label>Gambar Proyek</label>
          <input type="file" name="image" accept="image/*">
        </div>
        <div class="form-group">
          <label>Deskripsi</label>
          <textarea name="description" rows="4"></textarea>
        </div>
        <div class="form-group">
          <label>Klien</label>
          <input type="text" name="client">
        </div>
        <div class="form-group">
          <label>Tanggal Proyek</label>
          <input type="date" name="project_date">
        </div>
        <div class="form-group">
          <label>Layanan yang Digunakan</label>
          <input type="text" name="services_used" placeholder="Contoh: Billboard, Neon Box">
        </div>
        <button class="btn btn-accent" type="submit">Simpan</button>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/partials/admin_footer.php'; ?>

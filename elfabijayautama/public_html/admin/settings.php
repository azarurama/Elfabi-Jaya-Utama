<?php 
include __DIR__ . '/partials/admin_header.php'; 

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!verify_csrf()) { 
    set_flash('error', 'Token tidak valid.'); 
  } else {
    // Save contact settings
    set_setting('company_address', trim($_POST['company_address'] ?? ''));
    set_setting('company_phone', trim($_POST['company_phone'] ?? ''));
    set_setting('company_email', trim($_POST['company_email'] ?? ''));

    // Save 'About Us' page settings
    set_setting('about_title', trim($_POST['about_title'] ?? ''));
    set_setting('about_text1', trim($_POST['about_text1'] ?? ''));
    set_setting('about_text2', trim($_POST['about_text2'] ?? ''));
    set_setting('vision_text', trim($_POST['vision_text'] ?? ''));
    set_setting('mission_text', trim($_POST['mission_text'] ?? ''));

    // Handle about image upload
    if (isset($_FILES['about_image']) && $_FILES['about_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = dirname(__DIR__) . '/uploads/';
        $result = handle_file_upload(
            $_FILES['about_image'],
            $upload_dir,
            ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            5 * 1024 * 1024 // 5MB
        );
        
        if ($result['success']) {
            // Normalize to relative path (remove APP_URL prefix if present)
            $stored = $result['path'];
            $path = ltrim(str_replace('\\', '/', $stored), '/');
            $app = ltrim(APP_URL, '/');
            if (strpos($path, $app . '/') === 0) {
                $path = substr($path, strlen($app) + 1);
            }

            // Delete old image if it exists (normalize first)
            $old_image_path = get_setting('about_image');
            if (!empty($old_image_path)) {
                $old_rel = ltrim(str_replace(['\\\\','\\'], '/', $old_image_path), '/');
                if (strpos($old_rel, $app . '/') === 0) {
                    $old_rel = substr($old_rel, strlen($app) + 1);
                }
                $abs_old = dirname(__DIR__) . '/' . $old_rel;
                if (file_exists($abs_old)) {
                    @unlink($abs_old);
                }
            }

            set_setting('about_image', $path);
        }
    }

    set_flash('success', 'Pengaturan berhasil diperbarui.');
    redirect('admin/settings.php');
  }
}

// Get current settings from DB
$company_address = get_setting('company_address', 'Jl. Contoh No. 123, Jakarta');
$company_phone = get_setting('company_phone', '021-1234567');
$company_email = get_setting('company_email', 'info@elfabi.co.id');
$about_title = get_setting('about_title', 'Sejarah Perusahaan');
$about_text1 = get_setting('about_text1', 'PT Elfabi Jaya Utama berdiri untuk memberikan solusi kreatif dan berkualitas bagi brand di Indonesia. Dengan pengalaman dalam periklanan outdoor & indoor, printing, offset, dan interior, kami berkomitmen untuk hasil terbaik.');
$about_text2 = get_setting('about_text2', 'Tim kami terdiri dari profesional berpengalaman yang siap membantu kebutuhan promosi dan branding Anda.');
$about_image = get_setting('about_image');
$vision_text = get_setting('vision_text', 'Menjadi mitra terpercaya dalam membangun citra brand yang kuat dan berkesan.');
$mission_text = get_setting('mission_text', "Menyediakan layanan periklanan yang efektif dan inovatif.\nMemberikan kualitas cetak dan instalasi terbaik.\nMengutamakan kepuasan klien melalui layanan profesional.");
?>

<h1>Pengaturan Website</h1>

<?php display_flash_messages(); ?>

<form method="post" enctype="multipart/form-data">
  <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

  <div class="card">
    <h2>Pengaturan Kontak & Umum</h2>
    <div class="form-group">
      <label>Alamat Perusahaan</label>
      <textarea name="company_address" rows="3"><?= e($company_address) ?></textarea>
    </div>
    <div class="form-group">
      <label>Telepon</label>
      <input type="text" name="company_phone" value="<?= e($company_phone) ?>">
    </div>
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="company_email" value="<?= e($company_email) ?>">
    </div>
  </div>

  <div class="card">
    <h2>Pengaturan Halaman "Tentang Kami"</h2>
    <div class="form-group">
      <label>Judul Sejarah</label>
      <input type="text" name="about_title" value="<?= e($about_title) ?>">
    </div>
    <div class="form-group">
      <label>Paragraf Sejarah 1</label>
      <textarea name="about_text1" rows="4"><?= e($about_text1) ?></textarea>
    </div>
    <div class="form-group">
      <label>Paragraf Sejarah 2</label>
      <textarea name="about_text2" rows="3"><?= e($about_text2) ?></textarea>
    </div>
    <div class="form-group">
      <label>Gambar Halaman Tentang</label>
      <?php if ($about_image): ?>
        <?php 
          $img_prev = ltrim(str_replace(['\\\\','\\'], '/', $about_image), '/');
          $app = ltrim(APP_URL, '/');
          if (strpos($img_prev, $app . '/') === 0) { $img_prev = substr($img_prev, strlen($app) + 1); }
          $posA = strpos($img_prev, 'uploads/');
          $posB = strpos($img_prev, '/uploads/');
          $usePos = $posA !== false ? $posA : ($posB !== false ? ($posB + 1) : false);
          if ($usePos !== false) { $img_prev = substr($img_prev, $usePos); }
        ?>
        <img src="<?= url($img_prev) ?>" alt="About Image" style="max-width: 200px; height: auto; margin-bottom: 10px; border-radius: 10px;">
      <?php endif; ?>
      <input type="file" name="about_image" accept="image/*">
      <small>Unggah gambar baru untuk menggantikan yang lama.</small>
    </div>
    <hr style="margin: 20px 0;">
    <div class="form-group">
      <label>Visi</label>
      <textarea name="vision_text" rows="2"><?= e($vision_text) ?></textarea>
    </div>
    <div class="form-group">
      <label>Misi (satu poin per baris)</label>
      <textarea name="mission_text" rows="4"><?= e($mission_text) ?></textarea>
    </div>
  </div>

  <button class="btn btn-accent" type="submit">Simpan Semua Pengaturan</button>
</form>

<?php include __DIR__ . '/partials/admin_footer.php'; ?>

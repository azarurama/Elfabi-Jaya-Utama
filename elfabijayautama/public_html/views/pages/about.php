<?php 
$title = 'Tentang Kami | ' . APP_NAME;
$meta_description = 'Pelajari lebih lanjut tentang PT Elfabi Jaya Utama, sejarah, visi, dan misi kami dalam menyediakan solusi periklanan dan branding berkualitas tinggi di Indonesia.';

// Fetch content from settings
$about_title = get_setting('about_title', 'Sejarah Perusahaan');
$about_text1 = get_setting('about_text1', 'PT Elfabi Jaya Utama berdiri untuk memberikan solusi kreatif dan berkualitas bagi brand di Indonesia. Dengan pengalaman dalam periklanan outdoor & indoor, printing, offset, dan interior, kami berkomitmen untuk hasil terbaik.');
$about_text2 = get_setting('about_text2', 'Tim kami terdiri dari profesional berpengalaman yang siap membantu kebutuhan promosi dan branding Anda.');
$about_image = get_setting('about_image');
$vision_text = get_setting('vision_text', 'Menjadi mitra terpercaya dalam membangun citra brand yang kuat dan berkesan.');
$mission_text = get_setting('mission_text', "Menyediakan layanan periklanan yang efektif dan inovatif.\nMemberikan kualitas cetak dan instalasi terbaik.\nMengutamakan kepuasan klien melalui layanan profesional.");

// Split mission text into a list
$mission_items = array_filter(array_map('trim', explode("\n", $mission_text)));

// Normalize about image path to a relative path suitable for url()
$about_image_url = '';
if (!empty($about_image)) {
    $raw = (string)$about_image;
    // 1) Normalize slashes
    $path = str_replace(['\\\\','\\'], '/', $raw);
    // 2) Strip leading slash
    $path = ltrim($path, '/');
    // 3) Remove APP_URL prefix if present
    $app = ltrim(APP_URL, '/');
    if (strpos($path, $app . '/') === 0) {
        $path = substr($path, strlen($app) + 1);
    }
    // 4) Always trim to part after 'uploads/' if present (handles absolute paths like C:/.../uploads/file.png)
    $posA = strpos($path, 'uploads/');
    $posB = strpos($path, '/uploads/');
    $usePos = $posA !== false ? $posA : ($posB !== false ? ($posB + 1) : false);
    if ($usePos !== false) {
        $path = substr($path, $usePos);
    }
    // 5) Verify existence, else fallback to raw with url()
    $abs = dirname(__DIR__, 2) . '/' . $path;
    if (file_exists($abs)) {
        $about_image_url = url($path);
    } else {
        // last resort fallback: try raw via url()
        $about_image_url = url($raw);
    }
}
?>

<section class="page-hero">
  <div class="container">
    <h1>Tentang Kami</h1>
  </div>
</section>

<section class="about section">
  <div class="container two-col">
    <div>
      <h2><?= e($about_title) ?></h2>
      <p><?= nl2br(e($about_text1)) ?></p>
      <p><?= nl2br(e($about_text2)) ?></p>
    </div>
    <div class="image-box" style="<?php if ($about_image_url): ?>background-image: url('<?= e($about_image_url) ?>'); background-size: cover; background-position: center; background-repeat: no-repeat;<?php endif; ?>">
      <?php if ($about_image_url): ?>
        <img src="<?= e($about_image_url) ?>" alt="Gambar Tentang Kami" loading="lazy" decoding="async">
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="vision-mission section">
  <div class="container two-col">
    <div>
      <h2>Visi & Misi</h2>
      <h3>Visi</h3>
      <p><?= e($vision_text) ?></p>
      <h3>Misi</h3>
      <?php if (!empty($mission_items)): ?>
      <ul>
        <?php foreach ($mission_items as $item): ?>
          <li><?= e($item) ?></li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </div>
    <div class="two-col-icons">
      <div class="card small"><div class="icon">🎯</div><p>Fokus pada hasil</p></div>
      <div class="card small"><div class="icon">🤝</div><p>Kolaborasi erat</p></div>
      <div class="card small"><div class="icon">⚡</div><p>Eksekusi cepat</p></div>
      <div class="card small"><div class="icon">✅</div><p>Kualitas terjamin</p></div>
    </div>
  </div>
</section>

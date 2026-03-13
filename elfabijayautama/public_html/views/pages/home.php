<?php 
$title = 'Solusi Kreatif untuk Brand Anda | ' . APP_NAME;
$meta_description = 'PT Elfabi Jaya Utama adalah partner kreatif Anda di bidang periklanan outdoor & indoor, digital printing, offset, dan desain interior. Hubungi kami untuk solusi branding terbaik.'; 
?>

<section class="hero">
  <div class="hero-overlay"></div>
  <div class="container hero-content">
    <div class="hero-text">
      <h1>Solusi Kreatif untuk Brand Anda</h1>
      <p class="lead">Kami membantu brand tampil menonjol melalui media outdoor & indoor, printing, offset, dan interior.</p>
      <div class="hero-cta">
        <a href="<?= url('index.php?page=services') ?>" class="btn btn-accent">Jelajahi Layanan Kami</a>
      </div>
    </div>
  </div>
</section>

<section class="clients section">
  <div class="container">
    <h2>Klien Terpercaya Kami</h2>
    <div class="client-logos">
        <?php
          $pdo_clients = db_connect_without_exit();
          $clients = [];
          if ($pdo_clients) {
            $clients = $pdo_clients->query('SELECT * FROM clients ORDER BY name ASC LIMIT 8')->fetchAll();
          }
          if (!empty($clients)):
            foreach ($clients as $client):
        ?>
          <?php if (!empty($client['website'])): ?>
            <a href="<?= e($client['website']) ?>" target="_blank" rel="noopener noreferrer" class="client-logo-link">
          <?php endif; ?>
            <img src="<?= e(url($client['logo'])) ?>" alt="<?= e($client['name']) ?>" title="<?= e($client['name']) ?>" loading="lazy" decoding="async">
          <?php if (!empty($client['website'])): ?>
            </a>
          <?php endif; ?>
        <?php 
            endforeach;
          else:
        ?>
          <p>Daftar klien akan segera ditampilkan di sini.</p>
        <?php endif; ?>
      </div>
  </div>
</section>

<section class="services section bg-light">
  <div class="container">
    <div class="section-header">
      <h2>Layanan Utama Kami</h2>
      <p class="section-subtitle">Solusi lengkap untuk kebutuhan branding dan promosi bisnis Anda</p>
    </div>
    <?php 
    $services = [];
    if ($pdo) {
      $services = $pdo->query('SELECT * FROM services ORDER BY id ASC')->fetchAll();
    }
    ?>
    
    <div class="card-grid">
      <?php if (!empty($services)): ?>
        <?php 
        // Show only the first 3 services on the home page
        $count = 0;
        foreach ($services as $service): 
          if ($count >= 3) break;
          $count++;
        ?>
          <article class="card card-hover">
            <div class="card-icon"><?= e($service['icon'] ?: '🎯') ?></div>
            <h3><?= e($service['title']) ?></h3>
            <p><?= e($service['short_description'] ?? $service['description']) ?></p>
          </article>
        <?php endforeach; ?>
      <?php else: ?>
        <article class="card card-hover">
          <div class="card-icon">📢</div>
          <h3>Media Outdoor</h3>
          <p>Billboard, baliho, spanduk, neon box, videotron, dan media luar ruang lainnya.</p>
        </article>
        <article class="card card-hover">
          <div class="card-icon">🖨️</div>
          <h3>Printing & Offset</h3>
          <p>Cetak berkualitas tinggi untuk brosur, katalog, kemasan, dan material promosi lainnya.</p>
        </article>
        <article class="card card-hover">
          <div class="card-icon">🏢</div>
          <h3>Interior & Branding</h3>
          <p>Desain interior komersial, signage, wayfinding, dan dekorasi untuk memperkuat identitas brand.</p>
        </article>
      <?php endif; ?>
    </div>
    <div class="text-center mt-4">
      <a href="<?= url('index.php?page=services') ?>" class="btn btn-outline">Lihat Semua Layanan</a>
    </div>
  </div>
</section>

<?php
// Fetch featured portfolio items (limit to 4 most recent)
$pdo = db_connect_without_exit();
$featured_items = [];

if ($pdo) {
    try {
        $stmt = $pdo->query('SELECT * FROM portfolio ORDER BY id DESC LIMIT 4');
        $featured_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Log error or handle it as needed
        error_log('Database error: ' . $e->getMessage());
    }
}
?>

<section class="portfolio-preview section">
  <div class="container">
    <div class="section-header">
      <h2>Proyek Unggulan</h2>
      <a class="btn btn-outline" href="<?= url('portfolio') ?>">Lihat Semua Portofolio</a>
    </div>
    <div class="portfolio-grid">
      <?php if (!empty($featured_items)): ?>
        <?php foreach ($featured_items as $item): ?>
          <a href="<?= e(url('portfolio/' . $item['id'])) ?>" class="portfolio-card-link" aria-label="<?= e($item['title']) ?>">
            <div class="portfolio-card">
              <?php if (!empty($item['image'])): ?>
                <div class="thumb" style="background-image: url('<?= e(url($item['image'])) ?>');"></div>
              <?php else: ?>
                <div class="thumb"></div>
              <?php endif; ?>
              <div class="overlay">
                <span><?= e($item['title']) ?></span>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      <?php else: ?>
        <!-- Fallback if no portfolio items found -->
        <?php for ($i = 1; $i <= 4; $i++): ?>
          <div class="portfolio-card">
            <div class="thumb"></div>
            <div class="overlay"><span>Proyek Contoh <?= $i ?></span></div>
          </div>
        <?php endfor; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

  <?php 
  $title = 'Layanan Kami | ' . APP_NAME;
  $meta_description = 'Jelajahi berbagai layanan yang kami tawarkan, mulai dari periklanan outdoor, indoor, digital printing, hingga desain interior untuk kebutuhan branding Anda.'; 
  ?>

  <section class="page-hero">
    <div class="container">
      <h1>Layanan Kami</h1>
    </div>
  </section>

  <section class="services section bg-light">
    <div class="container">
      <div class="section-header text-center">
        <h2>Layanan Kami</h2>
        <p class="section-subtitle">Solusi lengkap untuk kebutuhan branding dan promosi bisnis Anda</p>
      </div>
      
      <?php 
      $pdo = db_connect_without_exit(); 
      $services = []; 
      if ($pdo) { 
        $services = $pdo->query('SELECT * FROM services ORDER BY id ASC')->fetchAll(); 
      }
      ?>
      
      <div class="card-grid">
        <?php if (!empty($services)): ?>
          <?php foreach ($services as $service): ?>
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
      
    </div>
  </section>

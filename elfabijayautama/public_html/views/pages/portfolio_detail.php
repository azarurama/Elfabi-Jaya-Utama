<?php

// Get item ID from URL, ensure it's an integer
$id = (int)($_GET['id'] ?? 0);

if ($id === 0) {
    // If no ID, redirect to portfolio page
    redirect('portfolio');
}

$pdo = db();
$stmt = $pdo->prepare('SELECT * FROM portfolio WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$item = $stmt->fetch();

// Generate dynamic title and meta description
$title = e($item['title'] ?? 'Detail Proyek') . ' | ' . APP_NAME;
$meta_description = substr(strip_tags($item['description'] ?? ''), 0, 155) . '...';

// Fetch next and previous portfolio items for navigation
$prev_stmt = $pdo->prepare('SELECT id, title FROM portfolio WHERE id < ? ORDER BY id DESC LIMIT 1');
$prev_stmt->execute([$id]);
$prev_item = $prev_stmt->fetch();

$next_stmt = $pdo->prepare('SELECT id, title FROM portfolio WHERE id > ? ORDER BY id ASC LIMIT 1');
$next_stmt->execute([$id]);
$next_item = $next_stmt->fetch();

if (!$item) {
    // If item not found, show a 404 message or redirect
    http_response_code(404);
    $title = 'Proyek Tidak Ditemukan'; // Set title for header
    echo '<section class="page-hero"><div class="container"><h1>Proyek tidak ditemukan</h1><p>Maaf, proyek yang Anda cari tidak ada.</p><a href="' . url('index.php?page=portfolio') . '" class="btn">Kembali ke Portofolio</a></div></section>';
    return; // Stop further execution
}

// Set page title for the header
$title = e($item['title']) . ' - Portofolio';

?>



<section class="portfolio-detail-header">
    <div class="container">
        <h1><?= e($item['title']) ?></h1>
        <p><strong>Kategori:</strong> <?= e($item['category']) ?></p>
    </div>
</section>

<section class="portfolio-detail-content">
    <div class="container">
        <div class="two-col-uneven">
            <div>
                <img src="<?= url($item['image']) ?>" alt="<?= e($item['title']) ?>" class="portfolio-detail-img" loading="lazy" decoding="async">
                <h3>Deskripsi Proyek</h3>
                <p><?= nl2br(e($item['description'])) ?></p>
            </div>
            <div class="sidebar">
                <h3>Detail Proyek</h3>
                <ul class="portfolio-detail-meta">
                    <li><strong>Klien:</strong> <?= e($item['client']) ?></li>
                    <li><strong>Tanggal Proyek:</strong> <?= e(date('d F Y', strtotime($item['project_date']))) ?></li>
                    <li><strong>Layanan:</strong> <?= e($item['services_used']) ?></li>
                </ul>
                <a href="<?= url('portfolio') ?>" class="btn btn-outline" style="width: 100%;">Kembali ke Portofolio</a>
            </div>
        </div>
    </div>
    <div class="portfolio-nav-buttons">
        <?php if ($prev_item): ?>
            <a href="<?= url('portfolio/' . e($prev_item['id'])) ?>" class="nav-button prev-button">
                &larr; Proyek Sebelumnya
            </a>
        <?php endif; ?>
        
        <?php if ($next_item): ?>
            <a href="<?= url('portfolio/' . e($next_item['id'])) ?>" class="nav-button next-button">
                Proyek Berikutnya &rarr;
            </a>
        <?php endif; ?>
    </div>
</section>

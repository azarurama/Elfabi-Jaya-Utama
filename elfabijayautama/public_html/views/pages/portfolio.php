<?php
$title = 'Portofolio | ' . APP_NAME;
$meta_description = 'Lihat koleksi proyek sukses kami. Portofolio PT Elfabi Jaya Utama menampilkan keahlian kami dalam periklanan, printing, dan desain interior.';

$pdo = db_connect_without_exit();
$items = [];
$all_categories = [];
$current_category = $_GET['category'] ?? 'all';

if ($pdo) {
    // Get all unique categories for the filter buttons
    $cat_stmt = $pdo->query('SELECT DISTINCT category FROM portfolio');
    $all_categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);

    // Get portfolio items, filtered by category if one is selected
    if ($current_category !== 'all') {
        $stmt = $pdo->prepare('SELECT * FROM portfolio WHERE category = ? ORDER BY id DESC');
        $stmt->execute([$current_category]);
    } else {
        $stmt = $pdo->query('SELECT * FROM portfolio ORDER BY id DESC');
    }
    $items = $stmt->fetchAll();
}
?>

<section class="page-hero">
    <div class="container">
        <h1>Portofolio</h1>
    </div>
</section>

<section class="portfolio section">
    <div class="container">
        <div class="filters">
            <a href="<?= url('portfolio?category=all') ?>" class="filter-btn <?= $current_category === 'all' ? 'active' : '' ?>">Semua</a>
            <?php foreach ($all_categories as $cat): ?>
                <a href="<?= url('portfolio?category=' . e($cat)) ?>" class="filter-btn <?= $current_category === $cat ? 'active' : '' ?>">
                    <?= ucfirst(e($cat)) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="portfolio-grid">
            <?php if (!empty($items)): ?>
                <?php foreach ($items as $item): ?>
                    <a href="<?= url('portfolio/' . e($item['id'])) ?>" class="portfolio-card-link" aria-label="<?= e($item['title']) ?>">
                        <div class="portfolio-card">
                            <?php
                            $bg_style = '';
                            if (!empty($item['image'])) {
                                $image_url = e(url($item['image']));
                                $bg_style = "background-image: url('{$image_url}'); background-size: cover; background-position: center;";
                            }
                            ?>
                            <div class="thumb" style="<?= $bg_style ?>"></div>
                            <div class="overlay"><span><?= e($item['title']) ?></span></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else:
                echo '<p class="text-center">Tidak ada proyek yang ditemukan dalam kategori ini.</p>';
            endif; ?>
        </div>
    </div>
</section>

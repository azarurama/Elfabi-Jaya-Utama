<?php
require_once __DIR__ . '/core/bootstrap.php';

$page = $_GET['page'] ?? 'home';
// Allowed pages including custom 404
$allowed = ['home', 'about', 'services', 'portfolio', 'portfolio_detail', 'contact', '404'];
if (!in_array($page, $allowed, true)) {
    http_response_code(404);
    $page = '404';
}

// Page title mapping
$titles = [
    'home' => 'Beranda',
    'about' => 'Tentang Kami',
    'services' => 'Layanan',
    'portfolio' => 'Portofolio',
    'portfolio_detail' => 'Detail Portofolio',
    'contact' => 'Kontak',
    '404' => 'Halaman Tidak Ditemukan',
];

// Meta description mapping (default descriptions per page)
$meta_descriptions = [
    'home' => 'PT Elfabi Jaya Utama adalah partner kreatif Anda di bidang periklanan outdoor & indoor, digital printing, offset, dan desain interior.',
    'about' => 'Pelajari profil PT Elfabi Jaya Utama, visi misi, dan tim profesional kami dalam memberikan solusi branding dan periklanan.',
    'services' => 'Jelajahi layanan kami: media outdoor & indoor, digital printing, offset, dan desain interior untuk kebutuhan promosi bisnis Anda.',
    'portfolio' => 'Lihat koleksi proyek sukses kami di berbagai kategori seperti outdoor, indoor, dan branding.',
    'contact' => 'Hubungi kami untuk konsultasi proyek dan penawaran terbaik. Kami siap membantu kebutuhan branding Anda.',
    '404' => 'Maaf, halaman yang Anda cari tidak ditemukan. Kembali ke Beranda untuk menjelajah situs.',
];

$title = ($titles[$page] ?? 'Halaman') . ' - ' . APP_NAME;
$meta_description = $meta_descriptions[$page] ?? 'PT Elfabi Jaya Utama - Solusi kreatif untuk brand Anda melalui layanan periklanan, printing, dan interior.';

// Precompute dynamic meta for portfolio detail
if ($page === 'portfolio_detail') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        $pdo = db_connect_without_exit();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare('SELECT title, description FROM portfolio WHERE id = ? LIMIT 1');
                $stmt->execute([$id]);
                $item = $stmt->fetch();
                if ($item) {
                    $title = ($item['title'] ?? 'Detail Proyek') . ' | ' . APP_NAME;
                    $meta_description = substr(trim(strip_tags($item['description'] ?? '')), 0, 155);
                } else {
                    http_response_code(404);
                    $page = '404';
                    $title = 'Proyek Tidak Ditemukan - ' . APP_NAME;
                    $meta_description = 'Proyek yang Anda cari tidak ditemukan.';
                }
            } catch (Throwable $e) {
                // Keep defaults on error
            }
        }
    }
}

include __DIR__ . '/views/partials/header.php';
include __DIR__ . '/views/pages/' . $page . '.php';
include __DIR__ . '/views/partials/footer.php';

<?php
require_once __DIR__ . '/../core/bootstrap.php';

if (!is_admin()) {
    header('Location: ' . url('admin/index.php'));
    exit;
}

$title = 'Pengaturan Peta';
$pdo = db();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    
    $settings = [
        'map_latitude' => $_POST['latitude'] ?? '-6.2088',
        'map_longitude' => $_POST['longitude'] ?? '106.8456',
        'map_zoom' => $_POST['zoom'] ?? '15',
        'map_marker_title' => $_POST['marker_title'] ?? 'Kantor Kami'
    ];
    
    $stmt = $pdo->prepare('INSERT INTO settings(`key`, `value`) VALUES(:key, :value) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)');
    
    foreach ($settings as $key => $value) {
        $stmt->execute([':key' => $key, ':value' => $value]);
    }
    
    set_flash('success', 'Pengaturan peta berhasil diperbarui.');
    header('Location: settings_map.php');
    exit;
}

// Get current settings
$settings = [
    'latitude' => get_setting('map_latitude', '-6.2088'),
    'longitude' => get_setting('map_longitude', '106.8456'),
    'zoom' => get_setting('map_zoom', '15'),
    'marker_title' => get_setting('map_marker_title', 'Kantor Kami')
];

include 'partials/admin_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col">
            <h1 class="h3 mb-4 text-gray-800"><?= e($title) ?></h1>
            <?php display_flash_messages(); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Pengaturan Lokasi Peta</h6>
                </div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        
                        <div class="form-group">
                            <label for="latitude">Latitude</label>
                            <input type="text" class="form-control" id="latitude" name="latitude" 
                                   value="<?= e($settings['latitude']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="longitude">Longitude</label>
                            <input type="text" class="form-control" id="longitude" name="longitude" 
                                   value="<?= e($settings['longitude']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="zoom">Zoom Level (1-20)</label>
                            <input type="number" class="form-control" id="zoom" name="zoom" 
                                   min="1" max="20" value="<?= e($settings['zoom']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="marker_title">Judul Marker</label>
                            <input type="text" class="form-control" id="marker_title" name="marker_title" 
                                   value="<?= e($settings['marker_title']) ?>" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Panduan</h6>
                </div>
                <div class="card-body">
                    <h6>Cara Mendapatkan Koordinat:</h6>
                    <ol>
                        <li>Buka <a href="https://www.google.com/maps" target="_blank">Google Maps</a></li>
                        <li>Klik kanan pada lokasi yang diinginkan</li>
                        <li>Pilih "Apa yang ada di sini?"</n                        <li>Salin angka koordinat yang muncul di kotak pencarian</li>
                        <li>Masukkan ke dalam form di samping</li>
                    </ol>
                    <p class="mb-0"><strong>Contoh format koordinat yang benar:</strong></p>
                    <ul class="mb-0">
                        <li>Latitude: -6.2088</li>
                        <li>Longitude: 106.8456</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'partials/admin_footer.php'; ?>

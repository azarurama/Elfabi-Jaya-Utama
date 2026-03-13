<?php
require_once __DIR__ . '/../core/bootstrap.php';

if (!is_admin()) {
    header('Location: ' . url('admin/index.php'));
    exit;
}

$title = 'Kelola Klien';
$pdo = db();

// Handle form submissions (Create, Update, Delete)
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = $_POST['name'] ?? '';
    $website = $_POST['website'] ?? '';
    $logo_path = '';

    // Handle file upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = dirname(__DIR__) . '/uploads/clients/';
        $result = handle_file_upload(
            $_FILES['logo'],
            $upload_dir,
            ['image/jpeg', 'image/png', 'image/svg+xml', 'image/webp'],
            2 * 1024 * 1024 // 2MB
        );
        
        if ($result['success']) {
            $logo_path = $result['path'];
        } else {
            set_flash('error', 'Gagal mengunggah logo: ' . $result['error']);
            header('Location: clients.php');
            exit;
        }
    }

    if ($action === 'add') {
        if (!empty($name) && !empty($logo_path)) {
            $stmt = $pdo->prepare('INSERT INTO clients (name, logo, website) VALUES (?, ?, ?)');
            $stmt->execute([$name, $logo_path, $website]);
            set_flash('success', 'Klien berhasil ditambahkan.');
        }
    } elseif ($action === 'edit' && $id) {
        $current_logo = $_POST['current_logo'] ?? '';
        $new_logo = $logo_path ?: $current_logo;
        if (!empty($name)) {
            $stmt = $pdo->prepare('UPDATE clients SET name = ?, logo = ?, website = ? WHERE id = ?');
            $stmt->execute([$name, $new_logo, $website, $id]);
            set_flash('success', 'Klien berhasil diperbarui.');
        }
    } elseif ($action === 'delete' && $id) {
        $stmt = $pdo->prepare('DELETE FROM clients WHERE id = ?');
        $stmt->execute([$id]);
        set_flash('success', 'Klien berhasil dihapus.');
        header('Location: clients.php');
        exit;
    }
    header('Location: clients.php');
    exit;
}

// Fetch data for display
$clients = $pdo->query('SELECT * FROM clients ORDER BY name ASC')->fetchAll();
$client_to_edit = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare('SELECT * FROM clients WHERE id = ?');
    $stmt->execute([$id]);
    $client_to_edit = $stmt->fetch();
}

include 'partials/admin_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col">
            <h1 class="h3 mb-4 text-gray-800"><?= e($title) ?></h1>
            <?php display_flash_messages(); ?>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><?= $action === 'edit' ? 'Edit Klien' : 'Tambah Klien Baru' ?></h6>
        </div>
        <div class="card-body">
            <form action="clients.php?action=<?= $action === 'edit' ? 'edit&id=' . e($id) : 'add' ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div class="form-group mb-3">
                    <label for="name">Nama Klien</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= e($client_to_edit['name'] ?? '') ?>" required>
                </div>
                <div class="form-group mb-3">
                    <label for="logo">Logo Klien (Format: PNG, JPG, SVG)</label>
                    <input type="file" class="form-control" id="logo" name="logo" <?= $action !== 'edit' ? 'required' : '' ?>>
                    <?php if ($action === 'edit' && !empty($client_to_edit['logo'])):
                        echo '<small class="form-text text-muted mt-2">Logo saat ini: <img src="' . url($client_to_edit['logo']) . '" alt="' . e($client_to_edit['name']) . '" height="30"></small>';
                        echo '<input type="hidden" name="current_logo" value="' . e($client_to_edit['logo']) . '">';
                    endif; ?>
                </div>
                <div class="form-group mb-3">
                    <label for="website">Website Klien (Opsional)</label>
                    <input type="url" class="form-control" id="website" name="website" 
                           placeholder="https://example.com" 
                           value="<?= e($client_to_edit['website'] ?? '') ?>">
                    <small class="form-text text-muted">Biarkan kosong jika tidak ada website</small>
                </div>
                <button type="submit" class="btn btn-primary"><?= $action === 'edit' ? 'Perbarui' : 'Simpan' ?></button>
                <?php if ($action === 'edit'): ?>
                    <a href="clients.php" class="btn btn-secondary">Batal</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Klien</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Logo</th>
                            <th>Nama Klien</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clients as $client): ?>
                        <tr>
                            <td><img src="<?= url(e($client['logo'])) ?>" alt="<?= e($client['name']) ?>" height="40" style="max-width: 100px; object-fit: contain;"></td>
                            <td><?= e($client['name']) ?></td>
                            <td>
                                <a href="clients.php?action=edit&id=<?= e($client['id']) ?>" class="btn btn-sm btn-warning">Edit</a>
                                <form action="clients.php?action=delete&id=<?= e($client['id']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Anda yakin ingin menghapus klien ini?');">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include 'partials/admin_footer.php'; ?>

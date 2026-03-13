<?php 
// Aktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Buat log file khusus di folder admin
ini_set('log_errors', 1);
$logFile = __DIR__ . '/portfolio_edit_errors.log';
ini_set('error_log', $logFile);

// Log informasi server
error_log("=== DEBUGGING ENABLED ===");
error_log("PHP Version: " . PHP_VERSION);

require_once __DIR__ . '/../core/bootstrap.php';

// Log informasi tambahan setelah bootstrap
error_log("POST Data: " . print_r($_POST, true));
error_log("FILES Data: " . print_r($_FILES, true));

// Pastikan user sudah login sebagai admin
if (!is_admin()) {
    set_flash('error', 'Akses ditolak. Hanya admin yang dapat mengakses halaman ini.');
    redirect('index.php');
}

$pdo = db();
$id = (int)($_GET['id'] ?? 0);

// Ambil data item
$item = $pdo->prepare('SELECT * FROM portfolio WHERE id = ?');
$item->execute([$id]);
$item = $item->fetch();

if (!$item) { 
    set_flash('error', 'Proyek tidak ditemukan.');
    redirect(url('admin/portfolio.php'));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Enable detailed error logging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    $isAjax = !empty($_POST['is_ajax']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    
    // Log request details
    error_log('=== FORM SUBMISSION START ===');
    error_log('Is AJAX: ' . ($isAjax ? 'Yes' : 'No'));
    error_log('POST Data: ' . print_r($_POST, true));
    error_log('FILES Data: ' . print_r($_FILES, true));
    
    if (!verify_csrf()) {
        error_log('CSRF token validation failed');
        $errorMsg = 'Token keamanan tidak valid.';
        if ($isAjax) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $errorMsg]);
            exit;
        }
        set_flash('error', $errorMsg);
    } else {
        // Debug: Log all POST data
        error_log('POST data: ' . print_r($_POST, true));
        
        // Ambil dan validasi input
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $category = isset($_POST['category']) ? trim($_POST['category']) : '';
        // Get raw description and log it
$rawDescription = $_POST['description'] ?? '';
error_log('Raw description type: ' . gettype($rawDescription));
error_log('Raw description length: ' . strlen($rawDescription));

// Clean and validate description
$description = is_string($rawDescription) ? trim($rawDescription) : '';
if ($description === '') {
    // Check if it's a false positive empty string
    $description = trim(file_get_contents('php://input'));
    error_log('Description from input stream: ' . substr($description, 0, 100) . '...');
}
        $client = isset($_POST['client']) ? trim($_POST['client']) : '';
        $project_date = isset($_POST['project_date']) ? trim($_POST['project_date']) : '';
        $services_used = isset($_POST['services_used']) ? trim($_POST['services_used']) : '';
        $image_path = $item['image']; // Simpan path gambar lama sebagai default
        
        $errors = [];
        
        // Validasi input
        if (empty($title)) {
            $errors[] = 'Judul proyek harus diisi';
        } elseif (strlen($title) > 200) {
            $errors[] = 'Judul maksimal 200 karakter';
        }
        
        if (empty($category)) {
            $errors[] = 'Kategori harus diisi';
        }
        
        // Validasi deskripsi
        if (empty($description)) {
            $errors[] = 'Deskripsi harus diisi';
        } elseif (strlen($description) < 10) {
            $errors[] = 'Deskripsi terlalu pendek. Minimal 10 karakter.';
        } else {
            // Debug: Log the description value
            error_log('Description value: ' . $description);
            // Ensure proper encoding
            $description = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
        }
        
        // Handle upload gambar baru
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $target_dir = dirname(__DIR__) . "/uploads/portfolio/";
            
            // Buat direktori jika belum ada
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            
            $result = handle_file_upload(
                $_FILES['image'],
                $target_dir,
                ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
                5 * 1024 * 1024 // 5MB
            );
            
            if ($result['success']) {
                // Hapus gambar lama jika ada
                if (!empty($item['image']) && $item['image'] !== $image_path) {
                    $old_image = dirname(__DIR__) . '/' . $item['image'];
                    if (file_exists($old_image) && is_file($old_image)) {
                        @unlink($old_image);
                    }
                }
                $image_path = 'uploads/portfolio/' . basename($result['path']);
            } else {
                $errors[] = 'Gagal mengunggah gambar: ' . $result['error'];
            }
        }
        
        // Handle hapus gambar yang ada
        if (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
            if (!empty($item['image'])) {
                $old_image = dirname(__DIR__) . '/' . $item['image'];
                if (file_exists($old_image) && is_file($old_image)) {
                    @unlink($old_image);
                }
                $image_path = ''; // Kosongkan path gambar
            }
        }
        
        // Jika tidak ada error, update data
        // Log final values before update
error_log('Values before update:');
error_log('- Title: ' . $title);
error_log('- Category: ' . $category);
error_log('- Description length: ' . strlen($description));

if (empty($errors)) {
            try {
                // Debug: Log the update query and parameters
                $updateQuery = 'UPDATE portfolio SET title=?, category=?, description=?, image=?, client=?, project_date=?, services_used=? WHERE id=?';
                
                // Prepare parameters
                $params = [
                    'title' => $title,
                    'category' => $category,
                    'description' => $description,
                    'image' => $image_path,
                    'client' => $client,
                    'project_date' => $project_date,
                    'services_used' => $services_used,
                    'id' => $id
                ];
                
                // Debug log
                error_log('=== UPDATE ATTEMPT ===');
                error_log('Query: ' . $updateQuery);
                error_log('Parameters: ' . print_r($params, true));
                error_log('Description length: ' . strlen($description));
                
                // Prepare and execute the query
                $stmt = $pdo->prepare($updateQuery);
                $success = $stmt->execute([
                    $params['title'],
                    $params['category'],
                    $params['description'],
                    $params['image'],
                    $params['client'],
                    $params['project_date'],
                    $params['services_used'],
                    $params['id']
                ]);
                
                if ($success) {
                    error_log('=== UPDATE SUCCESSFUL ===');
                    error_log('Updated portfolio ID: ' . $id);
                    error_log('Stored description length: ' . strlen($description));
                    error_log('Stored title: ' . $title);
                    
                    if ($isAjax) {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'status' => 'success',
                            'message' => 'Proyek berhasil diperbarui.',
                            'redirect' => url('admin/portfolio.php')
                        ]);
                        exit;
                    } else {
                        set_flash('success', 'Proyek berhasil diperbarui.');
                        redirect(url('admin/portfolio_edit.php?id=' . $id));
                    }
                } else {
                    throw new Exception('Gagal memperbarui data.');
                }
            } catch (PDOException $e) {
                error_log('Error updating portfolio: ' . $e->getMessage());
                $errorMsg = 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.';
                
                if ($isAjax) {
                    header('Content-Type: application/json');
                    http_response_code(500);
                    echo json_encode([
                        'status' => 'error',
                        'message' => $errorMsg
                    ]);
                    exit;
                }
                $errors[] = $errorMsg;
            }
        }
        
        // Handle errors
        if (!empty($errors)) {
            $errorMsg = implode('<br>', $errors);
            error_log('Validation errors: ' . $errorMsg);
            
            if ($isAjax) {
                header('Content-Type: application/json');
                http_response_code(400);
                $response = [
                    'status' => 'error',
                    'message' => $errorMsg,
                    'errors' => $errors
                ];
                error_log('Sending error response: ' . json_encode($response));
                echo json_encode($response);
                exit;
            }
            set_flash('error', $errorMsg);
        }
    }
}

// Include header after processing form to prevent header already sent error
include __DIR__ . '/partials/admin_header.php';
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-edit text-primary me-2"></i>Edit Proyek
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= url('admin/dashboard.php') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= url('admin/item.php') ?>">Portfolio</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
    </div>

    <?php if (get_flash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> <?= get_flash('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate id="portfolioForm" onsubmit="return false;">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <input type="hidden" name="is_ajax" value="1">
        
        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-info-circle me-2"></i>Informasi Dasar
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="title" class="form-label fw-semibold">Judul Proyek <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-heading"></i></span>
                                    <input type="text" class="form-control" id="title" name="title" required 
                                           value="<?= e($item['title'] ?? '') ?>" placeholder="Masukkan judul proyek">
                                </div>
                                <div class="invalid-feedback">
                                    Harap isi judul proyek
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="category" class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-tags"></i></span>
                                    <select class="form-select" id="category" name="category" required>
                                        <option value="" disabled>Pilih kategori</option>
                                        <option value="outdoor" <?= ($item['category'] ?? '') === 'outdoor' ? 'selected' : '' ?>>Outdoor</option>
                                        <option value="indoor" <?= ($item['category'] ?? '') === 'indoor' ? 'selected' : '' ?>>Indoor</option>
                                        <option value="branding" <?= ($item['category'] ?? '') === 'branding' ? 'selected' : '' ?>>Branding</option>
                                        <option value="digital" <?= ($item['category'] ?? '') === 'digital' ? 'selected' : '' ?>>Digital</option>
                                        <option value="event" <?= ($item['category'] ?? '') === 'event' ? 'selected' : '' ?>>Event</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="project_date" class="form-label fw-semibold">Tanggal Proyek</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                                    <input type="date" class="form-control" id="project_date" name="project_date" 
                                           value="<?= e($item['project_date'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="description" class="form-label fw-semibold">Deskripsi Proyek <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text align-items-start pt-3"><i class="fas fa-align-left"></i></span>
                                    <textarea class="form-control" id="description" name="description" rows="6" required
                                              placeholder="Masukkan deskripsi lengkap proyek"
                                              style="min-height: 150px;"><?= e($item['description'] ?? '') ?></textarea>
                                </div>
                                <div class="invalid-feedback">
                                    Harap isi deskripsi proyek
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-info-circle me-2"></i>Informasi Tambahan
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="services_used" class="form-label fw-semibold">Layanan yang Digunakan</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-tools"></i></span>
                                    <input type="text" class="form-control" id="services_used" name="services_used" 
                                           value="<?= e($item['services_used'] ?? '') ?>" 
                                           placeholder="Contoh: Billboard, Neon Box, Digital Printing">
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="client" class="form-label fw-semibold">Nama Klien</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user-tie"></i></span>
                                    <input type="text" class="form-control" id="client" name="client" 
                                           value="<?= e($item['client'] ?? '') ?>" placeholder="Nama klien">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-image me-2"></i>Gambar Proyek
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($item['image'])): ?>
                        <div class="mb-3 text-center">
                            <img src="<?= url($item['image']) ?>" alt="<?= e($item['title'] ?? 'Gambar Proyek') ?>" 
                                 class="img-fluid rounded mb-3" style="max-height: 200px;">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image" value="1">
                                <label class="form-check-label" for="remove_image">
                                    Hapus gambar ini
                                </label>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="image" class="form-label fw-semibold">
                                <?= empty($item['image']) ? 'Unggah Gambar' : 'Ganti Gambar' ?>
                            </label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <div class="form-text">Format: JPG, PNG, GIF, atau WebP. Maksimal 5MB.</div>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Simpan Perubahan
                    </button>
                    <a href="<?= url('admin/portfolio.php') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Handle form submission with AJAX
function submitForm(form) {
    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    
    // Create FormData object
    const formData = new FormData(form);
    
    // Add AJAX flag
    formData.append('is_ajax', '1');
    
    // Send AJAX request
    fetch(window.location.href, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Cache': 'no-cache'
        },
        credentials: 'same-origin'
    })
    .then(async response => {
        const responseData = await response.text();
        console.log('Response status:', response.status);
        console.log('Response headers:', Object.fromEntries(response.headers.entries()));
        console.log('Response data:', responseData);
        
        if (!response.ok) {
            let errorMsg = `HTTP error! status: ${response.status}`;
            try {
                const errorData = JSON.parse(responseData);
                errorMsg = errorData.message || errorMsg;
            } catch (e) {
                errorMsg = responseData || errorMsg;
            }
            throw new Error(errorMsg);
        }
        return JSON.parse(responseData);
    })
    .then(data => {
        if (data.status === 'success') {
            showFlashMessage(data.message, 'success');
            // Optionally update the form with any returned data
        } else if (data.status === 'error') {
            showFlashMessage(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error details:', {
            name: error.name,
            message: error.message,
            stack: error.stack
        });
        showFlashMessage('Terjadi kesalahan: ' + error.message, 'error');
    })
    .finally(() => {
        // Reset button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
        
        // Re-enable any disabled form elements
        const formElements = form.elements;
        for (let i = 0; i < formElements.length; i++) {
            formElements[i].disabled = false;
        }
    });
    
    // Prevent default form submission
    return false;
}

// Show flash message
function showFlashMessage(message, type = 'success') {
    const flashDiv = document.createElement('div');
    flashDiv.className = `flash-message ${type}`;
    flashDiv.innerHTML = `
        <div class="container">
            ${message}
            <span class="close-flash">&times;</span>
        </div>
    `;
    
    // Add to body
    document.body.appendChild(flashDiv);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        flashDiv.style.animation = 'fadeOut 0.3s forwards';
        setTimeout(() => flashDiv.remove(), 300);
    }, 5000);
    
    // Close button
    const closeBtn = flashDiv.querySelector('.close-flash');
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            flashDiv.style.animation = 'fadeOut 0.3s forwards';
            setTimeout(() => flashDiv.remove(), 300);
        });
    }
}

// Initialize form elements
function initializeForm() {
    // Re-initialize any form elements that need it
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
    
    // Re-initialize image preview
    initImagePreview();
}

// Form validation and image preview
document.addEventListener('DOMContentLoaded', function() {
    initializeForm();
});

// Initialize form validation
function initFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    // Loop over forms and prevent submission if invalid
    Array.from(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
}

// Initialize image preview
function initImagePreview() {
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('imagePreview');
    const removeImageCheckbox = document.getElementById('remove_image');
    
    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', function(e) {
            const file = this.files[0];
            if (file) {
                // Validate file type
                const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    showFlashMessage('Format file tidak didukung. Harap unggah file gambar (JPG, PNG, GIF, atau WebP).', 'error');
                    this.value = '';
                    return;
                }
                
                // Validate file size (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    showFlashMessage('Ukuran file terlalu besar. Maksimal 2MB.', 'error');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                    
                    // Show remove image option
                    if (removeImageCheckbox) {
                        removeImageCheckbox.closest('.form-check').style.display = 'block';
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Handle remove image checkbox
    if (removeImageCheckbox) {
        removeImageCheckbox.addEventListener('change', function() {
            if (this.checked) {
                imagePreview.style.display = 'none';
                imagePreview.src = '';
            } else if (imageInput.files.length > 0) {
                const file = imageInput.files[0];
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize form submission
    const form = document.getElementById('portfolioForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitForm(this);
        });
    }
    
    initFormValidation();
    initImagePreview();
});
</script>

<?php include __DIR__ . '/partials/admin_footer.php'; ?>

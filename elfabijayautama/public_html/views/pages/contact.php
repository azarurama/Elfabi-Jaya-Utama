<?php 
$title = 'Kontak Kami | ' . APP_NAME;
$meta_description = 'Hubungi PT Elfabi Jaya Utama untuk konsultasi atau pertanyaan. Temukan alamat, email, dan nomor telepon kami di sini. Kami siap membantu proyek Anda.'; 
?>

<section class="page-hero">
  <div class="container">
    <h1>Kontak Kami</h1>
  </div>
</section>

<section class="contact section">
  <div class="container two-col">
    <div>
      <h2>Informasi Kontak</h2>
      <ul class="contact-list">
        <li>Alamat: <?= e(get_setting('company_address', 'Jl. Contoh No. 123, Jakarta')) ?></li>
        <li>Telepon: <?= e(get_setting('company_phone', '021-1234567')) ?></li>
        <li>Email: <?= e(get_setting('company_email', 'info@elfabi.co.id')) ?></li>
        <li>Instagram: @elfabi.advertising</li>
      </ul>
      <div class="map-box">
        <?php
        $lat = urlencode(get_setting('map_latitude', '-6.2088'));
        $lng = urlencode(get_setting('map_longitude', '106.8456'));
        $zoom = (int)get_setting('map_zoom', '15');
        $title = e(get_setting('map_marker_title', 'Lokasi Kantor'));
        $map_url = "https://maps.google.com/maps?q={$lat},{$lng}&z={$zoom}&output=embed";
        ?>
        <iframe 
          title="<?= $title ?>" 
          src="<?= $map_url ?>" 
          width="100%" 
          height="300" 
          style="border:0;" 
          allowfullscreen="" 
          loading="lazy" 
          referrerpolicy="no-referrer-when-downgrade">
        </iframe>
        <div class="text-center mt-2">
          <a href="https://www.google.com/maps?q=<?= e(get_setting('map_latitude', '-6.2088')) ?>,<?= e(get_setting('map_longitude', '106.8456')) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
            Buka di Google Maps
          </a>
        </div>
      </div>
    </div>

    <div>
      <h2>Kirim Pesan</h2>
      <?php if ($msg = get_flash('success')): ?>
        <div class="alert alert-success"><?= e($msg) ?></div>
      <?php elseif ($msg = get_flash('error')): ?>
        <div class="alert alert-error"><?= e($msg) ?></div>
      <?php endif; ?>
      <form id="contactForm" method="post" action="<?= url('contact_handler.php') ?>" class="contact-form">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <div class="form-group">
          <label for="name">Nama</label>
          <input type="text" id="name" name="name" minlength="3" maxlength="100" required>
          <div class="error-message">Nama minimal 3 karakter</div>
        </div>
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" required>
          <div class="error-message">Masukkan email yang valid</div>
        </div>
        <div class="form-group">
          <label for="message">Pesan</label>
          <textarea id="message" name="message" rows="5" minlength="10" required></textarea>
          <div class="error-message">Pesan minimal 10 karakter</div>
        </div>
        <button type="submit" class="btn btn-accent">
          <span class="button-text">Kirim Pesan</span>
          <span class="button-loader" style="display:none;">Mengirim...</span>
        </button>
      </form>
      <script>
      document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('contactForm');
        if (!form) return; // Exit if form not found
        
        // Initialize form validation
        const fields = form.querySelectorAll('input, textarea');
        
        // Add validation events to each field
        fields.forEach(field => {
          // Skip hidden fields
          if (field.type === 'hidden') return;
          
          field.addEventListener('input', function() {
            validateField(this);
          });
          
          field.addEventListener('blur', function() {
            validateField(this);
          });
        });
        
        // Form submission handler
        form.addEventListener('submit', function(e) {
          e.preventDefault();
          
          // Validate all fields
          let isValid = true;
          fields.forEach(field => {
            if (field.type !== 'hidden' && !validateField(field)) {
              isValid = false;
            }
          });
          
          if (!isValid) {
            showNotification('Harap perbaiki input yang salah', 'error');
            return false;
          }
          
          // Show loading state
          const submitBtn = form.querySelector('button[type="submit"]');
          const buttonText = submitBtn ? submitBtn.querySelector('.button-text') : null;
          const buttonLoader = submitBtn ? submitBtn.querySelector('.button-loader') : null;
          
          if (buttonText) buttonText.style.display = 'none';
          if (buttonLoader) buttonLoader.style.display = 'inline-block';
          if (submitBtn) submitBtn.disabled = true;
          
          // Submit form data
          const formData = new FormData(form);
          
          fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
              'X-Requested-With': 'XMLHttpRequest'
            }
          })
          .then(response => {
            if (!response.ok) {
              throw new Error('Network response was not ok');
            }
            return response.json();
          })
          .then(data => {
            if (data && data.success) {
              showNotification(data.message || 'Pesan berhasil dikirim!', 'success');
              form.reset();
            } else {
              showNotification(data.message || 'Gagal mengirim pesan. Silakan coba lagi.', 'error');
            }
          })
          .catch(error => {
            console.error('Error:', error);
            showNotification('Terjadi kesalahan. Silakan coba lagi nanti.', 'error');
          })
          .finally(() => {
            if (buttonText) buttonText.style.display = 'inline-block';
            if (buttonLoader) buttonLoader.style.display = 'none';
            if (submitBtn) submitBtn.disabled = false;
          });
          
          return false;
        });
        
        function validateField(field) {
          const formGroup = field.closest('.form-group');
          if (!formGroup) return true; // Skip if no form group found
          
          const errorMessage = formGroup.querySelector('.error-message');
          if (!errorMessage) return true; // Skip if no error message element
          
          // Reset state
          formGroup.classList.remove('invalid');
          
          // Check required
          if (field.required && !field.value.trim()) {
            formGroup.classList.add('invalid');
            errorMessage.textContent = 'Field ini wajib diisi';
            return false;
          }
          
          // Check min length for text inputs and textareas
          if (field.minLength > 0 && field.value.length < field.minLength) {
            formGroup.classList.add('invalid');
            errorMessage.textContent = `Minimal ${field.minLength} karakter`;
            return false;
          }
          
          // Email validation
          if (field.type === 'email' && field.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(field.value)) {
              formGroup.classList.add('invalid');
              errorMessage.textContent = 'Masukkan email yang valid';
              return false;
            }
          }
          
          return true;
        }
        
        // Show notification function
        function showNotification(message, type = 'error') {
          // Remove any existing notifications
          const existing = document.querySelector('.form-notification');
          if (existing) existing.remove();
          
          // Create and show new notification
          const notification = document.createElement('div');
          notification.className = `form-notification ${type}`;
          notification.textContent = message;
          
          // Add to form or body if form not found
          const target = form || document.body;
          target.insertBefore(notification, target.firstChild);
          
          // Auto-hide after 5 seconds
          setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 300);
          }, 5000);
        }
      });
      </script>
    </div>
  </div>
</section>

      </div> <!-- Close admin-content -->
      
      <!-- Add any admin-specific scripts here -->
      <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
      <script>
        $(document).ready(function() {
          // Initialize tooltips
          $('[data-toggle="tooltip"]').tooltip();
          
          // Add active class to current page in navigation
          const currentPage = window.location.pathname.split('/').pop() || 'dashboard.php';
          $('.admin-nav a').removeClass('active');
          $(`.admin-nav a[href$="${currentPage}"]`).addClass('active');
          
          // Handle flash messages
          const flashMessages = document.querySelectorAll('.flash-message');
          
          flashMessages.forEach(message => {
            // Auto-hide after 5 seconds
            const timer = setTimeout(() => {
              message.style.animation = 'fadeOut 0.3s forwards';
              setTimeout(() => message.remove(), 300);
            }, 5000);
            
            // Close button
            const closeBtn = message.querySelector('.close-flash');
            if (closeBtn) {
              closeBtn.addEventListener('click', () => {
                clearTimeout(timer);
                message.style.animation = 'fadeOut 0.3s forwards';
                setTimeout(() => message.remove(), 300);
              });
            }
          });
        });
        
        // Add fadeOut animation
        const style = document.createElement('style');
        style.textContent = `
          @keyframes fadeOut {
            to {
              opacity: 0;
              transform: translate(-50%, -20px);
            }
          }
        `;
        document.head.appendChild(style);
      </script>
    </main> <!-- Close admin-main -->
  </div> <!-- Close admin-container -->
  
  <!-- Toast Notifications -->
  <?php if (isset($_SESSION['success'])): ?>
    <div class="toast show" style="position: fixed; bottom: 20px; right: 20px; min-width: 250px; background: #4CAF50; color: white; padding: 15px; border-radius: 4px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); z-index: 1000;">
      <?= $_SESSION['success'] ?>
      <button type="button" class="close-toast" style="position: absolute; top: 5px; right: 10px; color: white; background: none; border: none; font-size: 16px; cursor: pointer;">&times;</button>
    </div>
    <script>
      // Auto-hide success message after 5 seconds
      setTimeout(function() {
        $('.toast').fadeOut();
      }, 5000);
      
      // Close button functionality
      $('.close-toast').click(function() {
        $(this).closest('.toast').fadeOut();
      });
    </script>
    <?php unset($_SESSION['success']); ?>
  <?php endif; ?>
  
  <?php if (isset($_SESSION['error'])): ?>
    <div class="toast error" style="position: fixed; bottom: 20px; right: 20px; min-width: 250px; background: #f44336; color: white; padding: 15px; border-radius: 4px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); z-index: 1000;">
      <?= $_SESSION['error'] ?>
      <button type="button" class="close-toast" style="position: absolute; top: 5px; right: 10px; color: white; background: none; border: none; font-size: 16px; cursor: pointer;">&times;</button>
    </div>
    <script>
      // Auto-hide error message after 5 seconds
      setTimeout(function() {
        $('.toast.error').fadeOut();
      }, 5000);
      
      // Close button functionality
      $('.close-toast').click(function() {
        $(this).closest('.toast').fadeOut();
      });
    </script>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>
</body>
</html>

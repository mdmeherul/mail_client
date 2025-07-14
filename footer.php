<?php
  // Set timezone if not already
  date_default_timezone_set('Asia/Dhaka');

  $now        = new DateTime();
  $dateTime   = $now->format('F j, Y · h:i A');
  $clientIP   = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
?>
    </div><!-- End .container -->

    <footer class="footer-custom mt-5">
      <div class="container py-4">
        <div class="row text-center text-md-start align-items-center gy-3">

          <!-- Branding -->
          <div class="col-md-4 fw-bold fs-5 text-primary">
            <i class="fas fa-envelope-open-text me-1"></i> MailBurst<span class="text-secondary">®</span>
          </div>

          <!-- Center: Date · Time · IP -->
          <div class="col-md-4 small text-muted text-center">
            <i class="fas fa-calendar-alt me-1"></i><?= $dateTime ?><br class="d-md-none">
            <span class="d-none d-md-inline">|</span>
            <i class="fas fa-wifi ms-2 me-1"></i>IP: <?= htmlspecialchars($clientIP) ?>
          </div>

          <!-- Right: Credits -->
          <div class="col-md-4 text-md-end small">
            <div>
              © <?= date('Y') ?> <a href="https://www.mailburst.store" class="text-decoration-none text-primary fw-semibold">MailBurst</a>
            </div>
            <div>
              Powered by <a href="https://www.mailburst.store" class="text-decoration-none text-primary">mailburst.store</a>
            </div>
          </div>

        </div>
      </div>
    </footer>

    <!-- JS Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" integrity="sha512..." crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  </body>
</html>

<!-- Embedded Footer Styling -->
<style>
  .footer-custom {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-top: 1px solid #dee2e6;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.05);
  }

  .footer-custom a {
    color: #0d6efd;
    transition: 0.2s ease;
  }

  .footer-custom a:hover {
    text-decoration: underline;
  }

  .footer-custom .fas {
    vertical-align: middle;
    opacity: 0.75;
  }
</style>

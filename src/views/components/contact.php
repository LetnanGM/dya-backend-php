<?php $flash = flash_pull(); ?>
<div id="popup-place" aria-live="polite">
  <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> mb-0" role="alert">
      <?= e($flash['message']) ?>
    </div>
  <?php endif; ?>
</div>

<section id="contact" class="py-5 bg-light">
  <div class="container py-4">
    <div class="text-center mb-5">
      <h2 class="fw-bold text-dark">Get In Touch</h2>
      <p class="text-muted mx-auto" style="max-width: 500px">
        Have a project in mind or an API to build? Drop a message directly into
        my inbox.
      </p>
    </div>

    <div class="card border-0 shadow-lg overflow-hidden mx-auto" style="max-width: 1000px; border-radius: 16px">
      <div class="row g-0">
        <div class="col-lg-5 bg-dark text-white p-5 d-flex flex-column justify-content-between position-relative">
          <div class="position-absolute top-0 start-0 w-100 h-100 opacity-5 kontakku"></div>

          <div class="position-relative z-1">
            <div class="d-flex align-items-center mb-4">
              <div class="bg-success rounded-circle me-2" style="width: 12px; height: 12px"></div>
              <small class="text-success font-monospace fw-bold">HTTP/2 OK // API_READY</small>
            </div>

            <h4 class="fw-bold mb-3">Contact Information</h4>
            <p class="text-white-50 small mb-4">
              Feel free to ping me directly or submit the payload wrapper form.
            </p>

            <div class="mb-4">
              <div class="d-flex align-items-center mb-3">
                <div class="bg-secondary bg-opacity-20 p-2 rounded me-3 text-center" style="width: 40px; height: 40px">
                  <i class="font-monospace fw-bold">@</i>
                </div>
                <div>
                  <small class="text-muted d-block">EMAIL</small>
                  <a href="mailto:<?= e($site['email']) ?>"
                    class="text-white text-decoration-none small font-monospace"><?= e($site['email']) ?></a>
                </div>
              </div>

              <div class="d-flex align-items-center mb-3">
                <div class="bg-secondary bg-opacity-20 p-2 rounded me-3 text-center" style="width: 40px; height: 40px">
                  <i class="font-monospace fw-bold">&gt;_</i>
                </div>
                <div>
                  <small class="text-muted d-block">GITHUB</small>
                  <a href="https://github.com/<?= e($site['social']['Github']) ?>" target="_blank"
                    class="text-white text-decoration-none small font-monospace"
                    rel="noopener nofollow me">https://github.com/<?= e($site['social']['Github']) ?></a>
                </div>
              </div>
            </div>
          </div>

          <div class="position-relative z-1 mt-4 pt-3 border-top border-secondary border-opacity-20">
            <span class="text-muted font-monospace small">v5.3.0 // Powered by Bootstrap</span>
          </div>
        </div>

        <div class="col-lg-7 bg-white p-5">
          <h4 class="fw-bold text-dark mb-4">Send a Message</h4>

          <form id="contact-form" action="<?= e(url('contact.php')) ?>" method="POST" data-ajax
            hx-target="#popup-place">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>" />
            <!-- Honeypot anti-spam: harus tetap kosong, disembunyikan dari manusia -->
            <div style="position: absolute; left: -9999px" aria-hidden="true">
              <label for="website">Website</label>
              <input type="text" id="website" name="website" tabindex="-1" autocomplete="off" />
            </div>
            <div class="row g-3">
              <div class="col-md-6">
                <div class="form-floating">
                  <input type="text" class="form-control border-0 bg-light" id="name" name="name" maxlength="100"
                    placeholder="John Doe" required style="border-radius: 8px" />
                  <label for="name" class="text-muted">Your Name</label>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-floating">
                  <input type="email" class="form-control border-0 bg-light" id="email" name="email"
                    placeholder="name@example.com" required style="border-radius: 8px" />
                  <label for="email" class="text-muted">Email Address</label>
                </div>
              </div>

              <div class="col-12">
                <div class="form-floating">
                  <input type="text" class="form-control border-0 bg-light" id="subject" name="subject" maxlength="150"
                    placeholder="Project Idea" required style="border-radius: 8px" />
                  <label for="subject" class="text-muted">Subject</label>
                </div>
              </div>

              <div class="col-12">
                <div class="form-floating">
                  <textarea class="form-control border-0 bg-light" id="message" name="message" maxlength="5000"
                    placeholder="Leave a message here" style="height: 150px; border-radius: 8px" required></textarea>
                  <label for="message" class="text-muted">Message Details</label>
                </div>
              </div>

              <div id="button-area" class="col-12 mt-4">
                <button type="submit" class="btn btn-dark w-100 py-3 fw-bold shadow-sm"
                  style="border-radius: 8px; transition: transform 0.2s">
                  Transmit Payload &rarr;
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
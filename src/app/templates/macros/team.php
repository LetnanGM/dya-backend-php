<?php 
function card(array $profile) { 
  ?>
<div class="col-md-4">
  <article class="team-member text-center p-4 h-100">
    <?php if ( $profile["status"] ) { ?>
    <!-- Ubah url_for jadi path langsung. Di Vite, taruh gambarmu di folder public/images/ -->
    <img
      src="/{{ profile.profil_img }}"
      alt="{{ profile.name }}"
      class="mb-4 shadow rounded-circle"
      width="120"
      height="120"
      style="object-fit: cover"
    />
    <?php } else { ?>
    <div
      class="team-placeholder mb-4 mx-auto shadow rounded-circle d-flex align-items-center justify-content-center"
      style="width: 120px; height: 120px"
    >
      <i class="fas fa-user-plus fa-2x"></i>
    </div>
    <?php } ?>

    <h5 class="mb-1">
      <?php
        if ($profile["status"]) {
          $name = $profile["name"];
          echo $name ?? 'Your Name Here';
        }
      ?>
    </h5>

    <p class="text-muted mb-3">
      <?php
        if ($profile["status"]) {
          $role = $profile["peran"];
          echo $role ?? "Future DyA Member";
        }
      ?>
    </p>

    <p class="small mb-4">
      <?php
        if ($profile["status"]) {
          $short_description = $profile["shortdesc"];
          echo $short_description ?? "We're looking for someone who wants to build, experiment, and create something together.";
        }
      ?>
    </p>

    <div class="social-links d-flex justify-content-center gap-2">
      <?php
        if ($profile["status"]) {
          $socmeds = $profile["link"];
          foreach ($socmeds as $socmed) {
            if (isset($socmed) && !is_null($socmed)) {
            { ?> 
              <a
                href="<?= $socmed ?>"
                aria-label="<?= $profile["name"] ?> on <?= $socmed ?>"
                class="btn btn-sm btn-outline-dark rounded-circle"
              >
                <i class="fab fa-linkedin-in"></i>
              </a>
            <?php }
          } else {
            ?>
                      <a
          href="#"
          aria-label="Join DYA"
          class="btn btn-sm btn-dark rounded-circle"
        >
          <i class="fas fa-arrow-right"></i>
        </a>
          <?php
          }
        }
      ?>
    </div>
  </article>
</div>
<?php } ?>
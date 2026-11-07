<?php

/** Tampilan 404 untuk profil member yang tidak ada (status HTTP diatur di pages/members.php). */
?>
<section class="not-found">

    <div>

        <div class="not-found-code">
            404<span class="text-primary">.</span>
        </div>

        <h1 class="font-display fw-bold h3 mt-4">
            Member not found.
        </h1>

        <p class="member-muted">
            The profile you're looking for doesn't exist.
        </p>

        <a href="<?= e(url('member')) ?>" class="btn btn-primary mt-3">
            Explore Members
        </a>

    </div>

</section>

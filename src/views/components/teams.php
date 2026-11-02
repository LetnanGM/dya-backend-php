<section class="py-5">
  <div class="container">
    <div class="row text-center mb-5">
      <div class="col-12">
        <h2 class="section-title">Meet Our Team</h2>
        <p class="text-muted">
          Three people, one team, and probably too many ideas.
        </p>
      </div>
    </div>

    <div class="row g-4 justify-content-center">
      <?php if (!empty($teams)) : ?>
        <?php foreach ($teams as $profile) : ?>
          <?php partial('partials/team_card', ['profile' => $profile]); ?>
        <?php endforeach; ?>
      <?php else : ?>
        <p class="text-muted">There's have problem to show the developers.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

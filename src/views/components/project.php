<section id="projects" class="py-5 mt-lg-5 bg-light">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="fw-bold">Our Projects</h2>
      <p class="text-muted">
        A showcase of my recent backend and full-stack work.
      </p>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
      <?php if (!empty($projects)) : ?>
        <?php foreach ($projects as $project) : ?>
          <?php partial('partials/project_card', ['project' => $project]); ?>
        <?php endforeach; ?>
      <?php else : ?>
        <p class="text-muted">There's no project on this page..</p>
      <?php endif; ?>
    </div>
  </div>
</section>

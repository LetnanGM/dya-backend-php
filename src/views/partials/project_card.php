<div class="col">
  <div class="card h-100 bg-white border border-dark rounded-0">
    <img
      src="<?= e(image_src($project['thumb_image'])) ?>"
      class="card-img-top rounded-0 border-bottom border-dark"
      alt="<?= e($project['name']) ?>"
      loading="lazy"
      style="filter: grayscale(100%); object-fit: cover; height: 180px"
    />

    <div class="card-body d-flex flex-column p-4">
      <h5
        class="card-title fw-bold text-dark text-uppercase fs-6 tracking-wide mb-2"
      >
        <?= e($project['name']) ?>
      </h5>
      <p class="card-text text-secondary small flex-grow-1 lh-sm mb-3">
        <?= e($project['shortdesc']) ?>
      </p>

      <div class="mb-3">
        <?php foreach ($project['techstack'] ?? [] as $tech) : ?>
        <span
          class="badge bg-transparent border border-dark text-dark rounded-0 me-1 fw-normal text-uppercase"
          style="font-size: 0.7rem"
        >
          <?= e($tech) ?>
        </span>
        <?php endforeach; ?>
      </div>

      <a
        href="<?= e($project['linkview']) ?>"
        class="btn btn-dark btn-sm rounded-0 mt-auto w-100 fw-semibold text-uppercase tracking-wider"
        >View Details &rarr;</a
      >
    </div>
  </div>
</div>

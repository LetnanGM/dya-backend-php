(() => {
    const lightbox = document.getElementById('lightbox');
    const lightboxImage = document.getElementById('lightboxImage');
    const lightboxCaption = document.getElementById('lightboxCaption');
    const closeButton = document.getElementById('lightboxClose');

    let previousFocus = null;

    function openLightbox(image, caption, trigger) {
        previousFocus = trigger;

        lightboxImage.src = image;
        lightboxImage.alt = caption;
        lightboxCaption.textContent = caption;

        lightbox.classList.add('active');
        lightbox.setAttribute('aria-hidden', 'false');

        document.body.style.overflow = 'hidden';
        closeButton.focus();
    }

    function closeLightbox() {
        lightbox.classList.remove('active');
        lightbox.setAttribute('aria-hidden', 'true');

        lightboxImage.src = '';
        document.body.style.overflow = '';

        previousFocus?.focus();
    }

    document.querySelectorAll('.gallery-item').forEach(button => {
        button.addEventListener('click', () => {
            openLightbox(
                button.dataset.image,
                button.dataset.caption,
                button
            );
        });
    });

    closeButton.addEventListener('click', closeLightbox);

    lightbox.addEventListener('click', event => {
        if (event.target === lightbox) {
            closeLightbox();
        }
    });

    document.addEventListener('keydown', event => {
        if (!lightbox.classList.contains('active')) return;

        if (event.key === 'Escape') {
            closeLightbox();
        }
    });
})();

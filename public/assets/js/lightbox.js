/**
 * Lightbox gallery profil member.
 *
 * Memakai event delegation di `document`, jadi cukup dimuat SEKALI (di layout) dan tetap
 * bekerja untuk gallery yang masuk lewat htmx. Kalau listener dipasang langsung ke tiap
 * tombol, galeri baru hasil swap tidak akan ikut terpasang.
 */
(() => {
    let previousFocus = null;

    const lightbox = () => document.getElementById('lightbox');

    function openLightbox(trigger) {
        const box = lightbox();
        if (!box) return;

        const image = document.getElementById('lightboxImage');
        const caption = document.getElementById('lightboxCaption');

        previousFocus = trigger;
        image.src = trigger.dataset.image;
        image.alt = trigger.dataset.caption;
        caption.textContent = trigger.dataset.caption;

        box.classList.add('active');
        box.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        document.getElementById('lightboxClose').focus();
    }

    function closeLightbox() {
        const box = lightbox();
        if (!box || !box.classList.contains('active')) return;

        box.classList.remove('active');
        box.setAttribute('aria-hidden', 'true');
        document.getElementById('lightboxImage').src = '';
        document.body.style.overflow = '';

        previousFocus?.focus();
    }

    document.addEventListener('click', event => {
        if (!(event.target instanceof Element)) return;

        const trigger = event.target.closest('.gallery-item');
        if (trigger) {
            openLightbox(trigger);
        } else if (event.target.closest('#lightboxClose') || event.target.id === 'lightbox') {
            closeLightbox();
        }
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') closeLightbox();
    });

    // Pindah halaman / tombol Back saat lightbox terbuka: jangan biarkan scroll halaman terkunci.
    document.addEventListener('htmx:beforeSwap', () => {
        document.body.style.overflow = '';
    });
})();

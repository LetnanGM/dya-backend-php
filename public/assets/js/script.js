/**
 * Behaviors situs
 * ---------------
 *
 * Builded by Syafiq
 * MIT License
 *
 * Free Modified
 *
 * Catatan htmx:
 * Dengan hx-boost, pindah halaman tidak me-reload browser, jadi script ini TIDAK dijalankan
 * ulang. Karena itu animasi dipasang lewat initPage(), yang dipanggil tiap kali ada konten baru
 * masuk, dan aman dipanggil berkali-kali (penanda data-ready mencegah dobel).
 * Timer berhenti sendiri kalau elemennya sudah dibuang dari halaman (isConnected).
 */

/**
 * Live Clock
 */
function initClock() {
  const el = document.getElementById("clock");
  if (!el || el.dataset.ready) return;
  el.dataset.ready = "1";

  const format = new Intl.DateTimeFormat("en-GB", {
    timeZone: "Asia/Jakarta",
    hour: "2-digit",
    minute: "2-digit",
    second: "2-digit",
    hour12: false,
  });

  const tick = () => {
    if (!el.isConnected) return clearInterval(timer);
    el.textContent = format.format(new Date());
  };
  const timer = setInterval(tick, 1000);
  tick();
}

/**
 *  typing / cycling role text
 *
 *  animation for hero text
 */
function initTyping() {
  const el = document.getElementById("role-text");
  if (!el || el.dataset.ready) return;
  el.dataset.ready = "1";

  const words = ["interfaces", "products", "experiences", "ideas"];
  let wIndex = 0,
    cIndex = 0,
    deleting = false;

  function typeLoop() {
    if (!el.isConnected) return; // halaman sudah diganti -> berhenti

    const current = words[wIndex];
    if (!deleting) {
      cIndex++;
      el.textContent = current.slice(0, cIndex);
      if (cIndex === current.length) {
        deleting = true;
        setTimeout(typeLoop, 1400);
        return;
      }
    } else {
      cIndex--;
      el.textContent = current.slice(0, cIndex);
      if (cIndex === 0) {
        deleting = false;
        wIndex = (wIndex + 1) % words.length;
      }
    }
    setTimeout(typeLoop, deleting ? 40 : 80);
  }
  setTimeout(typeLoop, 1000);
}

/**
 * cursor spotlight :D
 * i just add it cause i think it cool
 */
function initSpotlight() {
  const hero = document.querySelector(".hero-box");
  const spot = document.getElementById("spotlight");
  if (!hero || !spot || hero.dataset.ready) return;
  hero.dataset.ready = "1";

  hero.addEventListener("mousemove", (e) => {
    const r = hero.getBoundingClientRect();
    spot.style.setProperty("--x", e.clientX - r.left + "px");
    spot.style.setProperty("--y", e.clientY - r.top + "px");
  });
}

function initPage() {
  initClock();
  initTyping();
  initSpotlight();
}

// htmx.onLoad jalan untuk halaman pertama DAN setiap konten baru hasil swap.
// Kalau htmx gagal dimuat (CDN down), situs tetap jalan: link & form biasa + initPage sekali.
if (window.htmx) {
  htmx.onLoad(initPage);
} else {
  document.addEventListener("DOMContentLoaded", initPage);
}

/**
 * htmx: perilaku global (didaftarkan sekali)
 * ------------------------------------------
 */
if (window.htmx) {
  // 1. Secara bawaan htmx TIDAK menampilkan respons 4xx/5xx. Kita izinkan:
  //    - 404 saat pindah halaman (mis. profil member tidak ada): itu halaman "normal", jadi
  //      isError = false.
  //    - error pada form kontak (422/419/429/500): pesannya harus tampil di #popup-place.
  //      isError SENGAJA dibiarkan true, karena event.detail.successful = !isError. Kalau
  //      dipaksa false, form ikut di-reset (hx-on::after-request) dan isian pengunjung hilang.
  document.addEventListener("htmx:beforeSwap", (evt) => {
    const { xhr, target } = evt.detail;
    if (xhr.status === 404) {
      evt.detail.shouldSwap = true;
      evt.detail.isError = false;
    } else if (target && target.id === "popup-place" && xhr.status >= 400) {
      evt.detail.shouldSwap = true;
    }
  });

  // 2. Menu offcanvas (mobile) ditutup begitu salah satu linknya diklik.
  //    Navbar tidak ikut di-swap, jadi tanpa ini menu tetap terbuka di atas halaman baru.
  document.addEventListener("htmx:beforeRequest", (evt) => {
    const menu = document.getElementById("navbar");
    if (menu && menu.contains(evt.detail.elt) && window.bootstrap) {
      bootstrap.Offcanvas.getInstance(menu)?.hide();
    }
  });

  // 3. Balasan form kontak: tampilkan alert ke tengah layar, tutup otomatis kalau sukses.
  document.addEventListener("htmx:afterSwap", (evt) => {
    const target = evt.detail.target;
    if (!target || target.id !== "popup-place") return;

    target.scrollIntoView({ behavior: "smooth", block: "center" });
    const ok = target.querySelector(".alert-success");
    if (ok) setTimeout(() => ok.remove(), 3000);
  });

  // 4. Pindah halaman gagal (server error / jaringan putus): buka halaman itu secara normal,
  //    supaya pengunjung melihat halaman aslinya, bukan klik yang "tidak terjadi apa-apa".
  const openNormally = (evt) => {
    const { boosted, target, pathInfo } = evt.detail;
    if (boosted && target && target.id === "maincontent" && pathInfo) {
      window.location.assign(pathInfo.requestPath);
    }
  };
  document.addEventListener("htmx:responseError", openNormally);
  document.addEventListener("htmx:sendError", openNormally);
}

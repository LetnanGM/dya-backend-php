/**
 * Live Clock
 * ----------
 *
 * Builded by Syafiq
 * MIT License
 *
 * Free Modified
 */
const clockEl = document.getElementById("clock");
function updateClock() {
  const opts = {
    timeZone: "Asia/Jakarta",
    hour: "2-digit",
    minute: "2-digit",
    second: "2-digit",
    hour12: false,
  };
  clockEl.textContent = new Intl.DateTimeFormat("en-GB", opts).format(
    new Date()
  );
}
if (clockEl) {
  updateClock();
  setInterval(updateClock, 1000);
}

/**
 *  typing / cycling role text
 *
 *  animation for hero text
 */
const words = ["interfaces", "products", "experiences", "ideas"];
const roleEl = document.getElementById("role-text");
let wIndex = 0,
  cIndex = 0,
  deleting = false;
function typeLoop() {
  const current = words[wIndex];
  if (!deleting) {
    cIndex++;
    roleEl.textContent = current.slice(0, cIndex);
    if (cIndex === current.length) {
      deleting = true;
      setTimeout(typeLoop, 1400);
      return;
    }
  } else {
    cIndex--;
    roleEl.textContent = current.slice(0, cIndex);
    if (cIndex === 0) {
      deleting = false;
      wIndex = (wIndex + 1) % words.length;
    }
  }
  setTimeout(typeLoop, deleting ? 40 : 80);
}
if (roleEl) setTimeout(typeLoop, 1000);

/**
 * cursor spotlight :D
 * i just add it cause i think it cool
 */
const hero = document.querySelector(".hero-box");
const spot = document.getElementById("spotlight");
if (hero && spot) {
  hero.addEventListener("mousemove", (e) => {
    const r = hero.getBoundingClientRect();
    spot.style.setProperty("--x", e.clientX - r.left + "px");
    spot.style.setProperty("--y", e.clientY - r.top + "px");
  });
}

/**
 * Contact form (vanilla fetch)
 * ----------------------------
 * Pengganti htmx. Form tetap bisa dikirim tanpa JavaScript
 * (submit biasa -> contact.php -> redirect); script ini hanya
 * meng-upgrade-nya supaya tanpa reload halaman.
 */
const contactForm = document.getElementById("contact-form");
const popupPlace = document.getElementById("popup-place");

function showAlert(type, message, autoCloseMs) {
  if (!popupPlace) return;
  const box = document.createElement("div");
  box.className = "alert alert-" + type + " mb-0";
  box.setAttribute("role", "alert");
  box.textContent = message; // textContent: aman dari injeksi HTML
  popupPlace.replaceChildren(box);
  if (autoCloseMs) setTimeout(() => box.remove(), autoCloseMs);
}

if (contactForm) {
  contactForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const button = contactForm.querySelector('button[type="submit"]');
    const label = button.innerHTML;
    button.disabled = true;
    button.textContent = "Sending...";

    try {
      const res = await fetch(contactForm.action, {
        method: "POST",
        headers: { Accept: "application/json" },
        body: new FormData(contactForm),
        credentials: "same-origin",
      });
      const data = await res.json();

      if (data.ok) {
        contactForm.reset();
        showAlert("success", "Success! " + data.message + " (Popup will be closed in 3 seconds..)", 3000);
      } else {
        showAlert("danger", data.message || "Something went wrong.");
      }
    } catch (err) {
      showAlert("danger", "Network error. Please try again.");
    } finally {
      button.disabled = false;
      button.innerHTML = label;
      popupPlace?.scrollIntoView({ behavior: "smooth", block: "center" });
    }
  });
}

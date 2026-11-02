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
updateClock();
setInterval(updateClock, 1000);

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
setTimeout(typeLoop, 1000);

/**
 * cursor spotlight :D
 * i just add it cause i think it cool
 */
const hero = document.querySelector(".hero-box");
const spot = document.getElementById("spotlight");
hero.addEventListener("mousemove", (e) => {
  const r = hero.getBoundingClientRect();
  spot.style.setProperty("--x", e.clientX - r.left + "px");
  spot.style.setProperty("--y", e.clientY - r.top + "px");
});

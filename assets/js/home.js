(() => {
  "use strict";

  /* ── Hero carousel ───────────────────────────────────────────────── */
  const track = document.querySelector(".home-carousel-track");
  if (!track) return;

  const slides   = Array.from(track.querySelectorAll(".home-slide"));
  const dotsWrap = document.querySelector(".home-carousel-dots");
  const dots     = dotsWrap ? Array.from(dotsWrap.querySelectorAll(".home-dot")) : [];
  const prevBtn  = document.getElementById("carousel-prev");
  const nextBtn  = document.getElementById("carousel-next");

  let current   = 0;
  let autoTimer = null;
  const DELAY   = 5500;
  const prefersReduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  const goTo = (index) => {
    slides[current].classList.remove("is-active");
    slides[current].setAttribute("aria-hidden", "true");
    slides[current].inert = true;
    if (dots[current]) dots[current].classList.remove("is-active");
    if (dots[current]) dots[current].setAttribute("aria-selected", "false");

    current = (index + slides.length) % slides.length;

    slides[current].classList.add("is-active");
    slides[current].setAttribute("aria-hidden", "false");
    slides[current].inert = false;
    if (dots[current]) dots[current].classList.add("is-active");
    if (dots[current]) dots[current].setAttribute("aria-selected", "true");
  };

  const startAuto = () => {
    if (prefersReduced) return;
    clearInterval(autoTimer);
    autoTimer = setInterval(() => goTo(current + 1), DELAY);
  };

  const stopAuto = () => clearInterval(autoTimer);

  if (prevBtn) prevBtn.addEventListener("click", () => { goTo(current - 1); stopAuto(); startAuto(); });
  if (nextBtn) nextBtn.addEventListener("click", () => { goTo(current + 1); stopAuto(); startAuto(); });

  dots.forEach((dot, i) =>
    dot.addEventListener("click", () => { goTo(i); stopAuto(); startAuto(); })
  );

  if (!prefersReduced) startAuto();

  track.addEventListener("mouseenter", stopAuto);
  track.addEventListener("focusin", stopAuto);
  track.addEventListener("mouseleave", startAuto);
  track.addEventListener("focusout", (event) => {
    if (!track.contains(event.relatedTarget)) startAuto();
  });
  document.addEventListener("visibilitychange", () => {
    if (document.hidden) stopAuto();
    else startAuto();
  });

  // Keyboard support
  track.addEventListener("keydown", (e) => {
    if (e.key === "ArrowLeft")  { goTo(current - 1); stopAuto(); startAuto(); }
    if (e.key === "ArrowRight") { goTo(current + 1); stopAuto(); startAuto(); }
  });
})();

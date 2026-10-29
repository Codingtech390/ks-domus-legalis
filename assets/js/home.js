(() => {
  "use strict";

  const carousel = document.querySelector(".home-carousel");
  if (!carousel) return;

  const slides = [...carousel.querySelectorAll(".home-slide")];
  const dots = [...carousel.querySelectorAll("[data-carousel-dot]")];
  const previous = carousel.querySelector("[data-carousel-prev]");
  const next = carousel.querySelector("[data-carousel-next]");

  if (!slides.length) return;

  let current = 0;
  let timer = null;

  const reducedMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)"
  );

  function goToSlide(index) {
    current = (index + slides.length) % slides.length;

    slides.forEach((slide, i) => {
      const active = i === current;

      slide.classList.toggle("is-active", active);
      slide.setAttribute("aria-hidden", String(!active));

      slide.querySelectorAll("a, button").forEach(element => {
        element.tabIndex = active ? 0 : -1;
      });
    });

    dots.forEach((dot, i) => {
      const active = i === current;
      dot.classList.toggle("is-active", active);
      dot.setAttribute("aria-current", String(active));
    });
  }

  function stopAutoplay() {
    if (timer !== null) {
      clearInterval(timer);
      timer = null;
    }
  }

  function startAutoplay() {
    stopAutoplay();

    if (reducedMotion.matches || document.hidden) return;

    timer = setInterval(() => {
      goToSlide(current + 1);
    }, 6500);
  }

  previous?.addEventListener("click", () => {
    goToSlide(current - 1);
    startAutoplay();
  });

  next?.addEventListener("click", () => {
    goToSlide(current + 1);
    startAutoplay();
  });

  dots.forEach((dot, index) => {
    dot.addEventListener("click", () => {
      goToSlide(index);
      startAutoplay();
    });
  });

  carousel.addEventListener("mouseenter", stopAutoplay);
  carousel.addEventListener("mouseleave", startAutoplay);
  carousel.addEventListener("focusin", stopAutoplay);
  carousel.addEventListener("focusout", event => {
    if (!carousel.contains(event.relatedTarget)) startAutoplay();
  });

  document.addEventListener("visibilitychange", () => {
    if (document.hidden) stopAutoplay();
    else startAutoplay();
  });

  document.addEventListener("keydown", event => {
    if (!carousel.contains(document.activeElement)) return;

    if (event.key === "ArrowLeft") {
      goToSlide(current - 1);
      startAutoplay();
    } else if (event.key === "ArrowRight") {
      goToSlide(current + 1);
      startAutoplay();
    }
  });

  goToSlide(0);
  startAutoplay();
})();

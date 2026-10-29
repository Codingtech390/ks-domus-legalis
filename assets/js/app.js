(() => {
  "use strict";
  const loader = document.getElementById("page-loader");
  const menuButton = document.getElementById("mobile-menu-button");
  const mobileMenu = document.getElementById("mobile-menu");
  const hideLoader = () => {
    if (loader) {
      loader.classList.add("is-hidden");
      loader.removeAttribute("aria-busy");
    }
  };
  const showLoader = () => {
    if (loader) {
      loader.classList.remove("is-hidden");
      loader.setAttribute("aria-busy", "true");
    }
  };
  if (document.readyState === "complete") hideLoader();
  else window.addEventListener("load", hideLoader, { once: true });
  window.addEventListener("pageshow", hideLoader);
  if (menuButton && mobileMenu) {
    menuButton.addEventListener("click", () => {
      const expanded = menuButton.getAttribute("aria-expanded") === "true";
      menuButton.setAttribute("aria-expanded", String(!expanded));
      mobileMenu.hidden = expanded;
      document.body.classList.toggle("menu-open", !expanded);
    });
    mobileMenu.querySelectorAll("a").forEach((link) =>
      link.addEventListener("click", () => {
        menuButton.setAttribute("aria-expanded", "false");
        mobileMenu.hidden = true;
        document.body.classList.remove("menu-open");
      }),
    );
  }
  document.addEventListener("click", (event) => {
    const target = event.target;
    if (!(target instanceof Element)) return;
    const copyButton = target.closest('[data-share="copy"]');
    if (copyButton) {
      event.preventDefault();
      const status = document.getElementById("share-status");
      const fallbackCopy = () => {
        const field = document.createElement("textarea");
        field.value = window.location.href;
        field.setAttribute("readonly", "");
        field.style.position = "fixed";
        field.style.opacity = "0";
        document.body.appendChild(field);
        field.select();
        let copied = false;
        try {
          copied = document.execCommand("copy");
        } catch (_) {}
        field.remove();
        return copied;
      };
      const announce = (message) => {
        if (status) status.textContent = message;
        copyButton.textContent = message === "Link copied to clipboard." ? "Copied ✓" : "Copy link";
      };
      if (navigator.clipboard && window.isSecureContext)
        navigator.clipboard
          .writeText(window.location.href)
          .then(() => announce("Link copied to clipboard."))
          .catch(() =>
            announce(
              fallbackCopy()
                ? "Link copied to clipboard."
                : "Copy failed — please copy the URL from your address bar.",
            ),
          );
      else
        announce(
          fallbackCopy()
            ? "Link copied to clipboard."
            : "Copy failed — please copy the URL from your address bar.",
        );
      return;
    }
    const anchor = target.closest("a");
    if (
      !anchor ||
      anchor.target === "_blank" ||
      anchor.hasAttribute("download") ||
      event.ctrlKey ||
      event.metaKey ||
      event.shiftKey ||
      event.altKey
    )
      return;
    const href = anchor.getAttribute("href");
    if (!href || href.startsWith("#") || href.startsWith("javascript:")) return;
    try {
      const destination = new URL(href, window.location.href);
      if (
        destination.origin === window.location.origin &&
        destination.href !== window.location.href
      )
        showLoader();
    } catch (_) {}
  });
document.addEventListener("submit", (event) => {
  const form = event.target;

  if (!(form instanceof HTMLFormElement)) {
    return;
  }

  const confirmationMessage = form.getAttribute("data-confirm");

  if (confirmationMessage && !window.confirm(confirmationMessage)) {
    event.preventDefault();
    return;
  }

  if (form.hasAttribute("data-no-loader")) {
    return;
  }

  showLoader();

  form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((button) => {
    button.setAttribute("aria-busy", "true");
  });
});
  const revealItems = document.querySelectorAll(".reveal-on-scroll");
  if ("IntersectionObserver" in window && revealItems.length) {
    const observer = new IntersectionObserver(
      (entries) =>
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add("is-revealed");
            observer.unobserve(entry.target);
          }
        }),
      { threshold: 0.12 },
    );
    revealItems.forEach((item) => observer.observe(item));
  } else revealItems.forEach((item) => item.classList.add("is-revealed"));
  const header = document.getElementById("site-header");
  const progress = document.getElementById("reading-progress-bar");
  const updateScroll = () => {
    if (header) header.classList.toggle("is-scrolled", window.scrollY > 12);
    if (progress) {
      const article = document.getElementById("article-content");
      if (article) {
        const rect = article.getBoundingClientRect();
        const total = article.offsetHeight - window.innerHeight;
        const current = Math.max(0, Math.min(total, -rect.top));
        progress.style.width = (total > 0 ? (current / total) * 100 : 0) + "%";
      }
    }
  };
  window.addEventListener("scroll", updateScroll, { passive: true });
  updateScroll();

  // Confirm destructive admin actions before submitting.
  document.addEventListener("submit", (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement)) {
      return;
    }

    const message = form.getAttribute("data-confirm");

    if (!message) {
      return;
    }

    if (!window.confirm(message)) {
      event.preventDefault();
    }
  });
})();

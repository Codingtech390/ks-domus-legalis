(() => {
    ('use strict');

    /* =========================================================
     DOM REFERENCES
  ========================================================= */

    const loader = document.getElementById('page-loader');
    const header = document.getElementById('site-header');
    const progressBar = document.getElementById('reading-progress-bar');

    const menuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');

    const dropdownTriggers = document.querySelectorAll('.nav-dropdown-trigger');

    const consultationModal = document.getElementById('consultation-payment-modal');

    const consultationPanel = document.getElementById('consultation-payment-panel');

    const consultationCloseButton = document.getElementById('consultation-payment-close');

    const consultationOpenButtons = document.querySelectorAll(
        '[data-consultation-open], #consultation-payment-open',
    );

    /* =========================================================
     PAGE LOADER
  ========================================================= */

    let loaderShowTimer = null;

    const hideLoader = () => {
        if (!loader) return;

        clearTimeout(loaderShowTimer);

        loader.classList.add('is-hidden');
        loader.removeAttribute('aria-busy');
    };

    const showLoader = () => {
        if (!loader) return;

        clearTimeout(loaderShowTimer);

        loaderShowTimer = window.setTimeout(() => {
            loader.classList.remove('is-hidden');
            loader.setAttribute('aria-busy', 'true');
        }, 140);
    };

    if (document.readyState === 'complete') {
        hideLoader();
    } else {
        window.addEventListener('load', hideLoader, { once: true });
    }

    // Handles browser back/forward cache restoration.
    window.addEventListener('pageshow', hideLoader);

    /* =========================================================
     MOBILE NAVIGATION
  ========================================================= */

    const closeMobileMenu = () => {
        if (!menuButton || !mobileMenu) return;

        menuButton.setAttribute('aria-expanded', 'false');
        mobileMenu.hidden = true;

        document.body.classList.remove('menu-open');
    };

    if (menuButton && mobileMenu) {
        menuButton.addEventListener('click', () => {
            const isExpanded = menuButton.getAttribute('aria-expanded') === 'true';

            menuButton.setAttribute('aria-expanded', String(!isExpanded));
            mobileMenu.hidden = isExpanded;

            document.body.classList.toggle('menu-open', !isExpanded);
        });

        mobileMenu.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', closeMobileMenu);
        });
    }

    /* =========================================================
     CONSULTATION MODAL
  ========================================================= */

    let lastConsultationTrigger = null;

    const openConsultationModal = (event) => {
        if (!consultationModal || !consultationPanel) return;

        lastConsultationTrigger = event.currentTarget;

        consultationModal.classList.remove('hidden');
        consultationModal.classList.add('flex');

        document.body.style.overflow = 'hidden';

        if (consultationCloseButton) {
            consultationCloseButton.focus();
        }
    };

    const closeConsultationModal = () => {
        if (!consultationModal) return;

        consultationModal.classList.add('hidden');
        consultationModal.classList.remove('flex');

        document.body.style.overflow = '';

        if (lastConsultationTrigger && typeof lastConsultationTrigger.focus === 'function') {
            lastConsultationTrigger.focus();
        }
    };

    consultationOpenButtons.forEach((button) => {
        button.addEventListener('click', openConsultationModal);
    });

    if (consultationCloseButton) {
        consultationCloseButton.addEventListener('click', closeConsultationModal);
    }

    if (consultationModal) {
        consultationModal.addEventListener('click', (event) => {
            if (event.target === consultationModal) {
                closeConsultationModal();
            }
        });
    }

    document.addEventListener('keydown', (event) => {
        if (
            event.key === 'Escape' &&
            consultationModal &&
            !consultationModal.classList.contains('hidden')
        ) {
            closeConsultationModal();
        }
    });

    /* =========================================================
     COPY ARTICLE LINK
  ========================================================= */

    const copyArticleLink = async (button) => {
        const status = document.getElementById('share-status');
        const currentUrl = window.location.href;

        const announce = (message) => {
            if (status) {
                status.textContent = message;
            }

            button.textContent = message === 'Link copied to clipboard.' ? 'Copied ✓' : 'Copy link';
        };

        const fallbackCopy = () => {
            const textarea = document.createElement('textarea');

            textarea.value = currentUrl;
            textarea.setAttribute('readonly', '');

            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';

            document.body.appendChild(textarea);

            textarea.select();

            let copied = false;

            try {
                copied = document.execCommand('copy');
            } catch {
                copied = false;
            }

            textarea.remove();

            return copied;
        };

        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(currentUrl);

                announce('Link copied to clipboard.');
                return;
            }
        } catch {
            // Use the fallback below.
        }

        announce(
            fallbackCopy()
                ? 'Link copied to clipboard.'
                : 'Copy failed — please copy the URL from your address bar.',
        );
    };

    /* =========================================================
     GLOBAL CLICK HANDLER
  ========================================================= */

    document.addEventListener('click', async (event) => {
        if (!(event.target instanceof Element)) return;

        /* Copy-link button */
        const copyButton = event.target.closest('[data-share="copy"]');

        if (copyButton) {
            event.preventDefault();

            await copyArticleLink(copyButton);

            return;
        }

        /* Close navigation dropdowns */
        if (!event.target.closest('.nav-dropdown-wrap')) {
            dropdownTriggers.forEach((trigger) => {
                trigger.setAttribute('aria-expanded', 'false');

                const menuId = trigger.getAttribute('aria-controls');
                const menu = menuId ? document.getElementById(menuId) : null;

                menu?.classList.remove('is-open');
            });
        }

        /* Internal-link page loader */
        const anchor = event.target.closest('a');

        if (!anchor) return;

        if (
            anchor.target === '_blank' ||
            anchor.hasAttribute('download') ||
            event.ctrlKey ||
            event.metaKey ||
            event.shiftKey ||
            event.altKey
        ) {
            return;
        }

        const href = anchor.getAttribute('href');

        if (!href || href.startsWith('#') || href.startsWith('javascript:')) {
            return;
        }

        try {
            const destination = new URL(href, window.location.href);

            const isInternalLink = destination.origin === window.location.origin;

            const isDifferentPage = destination.href !== window.location.href;

            if (isInternalLink && isDifferentPage) {
                showLoader();
            }
        } catch {
            // Ignore invalid URLs.
        }
    });

    /* =========================================================
     NAVIGATION DROPDOWNS
  ========================================================= */

    const closeAllDropdowns = () => {
        dropdownTriggers.forEach((trigger) => {
            trigger.setAttribute('aria-expanded', 'false');

            const menuId = trigger.getAttribute('aria-controls');
            const menu = menuId ? document.getElementById(menuId) : null;

            menu?.classList.remove('is-open');
        });
    };

    dropdownTriggers.forEach((trigger) => {
        const menuId = trigger.getAttribute('aria-controls');
        const menu = menuId ? document.getElementById(menuId) : null;

        if (!menu) return;

        trigger.addEventListener('click', (event) => {
            event.stopPropagation();

            const isOpen = trigger.getAttribute('aria-expanded') === 'true';

            closeAllDropdowns();

            trigger.setAttribute('aria-expanded', String(!isOpen));
            menu.classList.toggle('is-open', !isOpen);
        });
    });

    /* =========================================================
     FORM SUBMISSION
  ========================================================= */

    document.addEventListener('submit', (event) => {
        if (event.defaultPrevented) return;

        const form = event.target;

        if (!(form instanceof HTMLFormElement)) return;

        /* Confirmation dialog */
        const confirmationMessage = form.getAttribute('data-confirm');

        if (confirmationMessage && !window.confirm(confirmationMessage)) {
            event.preventDefault();
            return;
        }

        /* Allow forms to opt out of loading animation */
        if (form.hasAttribute('data-no-loader')) return;

        showLoader();

        form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((button) => {
            button.setAttribute('aria-busy', 'true');
        });
    });

    /* =========================================================
     SCROLL REVEAL
  ========================================================= */

    const revealItems = document.querySelectorAll('.reveal-on-scroll');

    if ('IntersectionObserver' in window && revealItems.length) {
        const revealObserver = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;

                    entry.target.classList.add('is-revealed');

                    revealObserver.unobserve(entry.target);
                });
            },
            {
                threshold: 0.12,
            },
        );

        revealItems.forEach((item) => {
            revealObserver.observe(item);
        });
    } else {
        revealItems.forEach((item) => {
            item.classList.add('is-revealed');
        });
    }

    /* =========================================================
     STICKY HEADER + ARTICLE READING PROGRESS
  ========================================================= */

    const updateScrollEffects = () => {
        if (header) {
            header.classList.toggle('is-scrolled', window.scrollY > 12);
        }

        if (!progressBar) return;

        const article = document.getElementById('article-content');

        if (!article) return;

        const articleRect = article.getBoundingClientRect();

        const totalScrollDistance = article.offsetHeight - window.innerHeight;

        const currentScrollDistance = Math.max(0, Math.min(totalScrollDistance, -articleRect.top));

        const progress =
            totalScrollDistance > 0 ? (currentScrollDistance / totalScrollDistance) * 100 : 0;

        progressBar.style.width = `${progress}%`;
    };

    window.addEventListener('scroll', updateScrollEffects, {
        passive: true,
    });

    updateScrollEffects();

    /* =========================================================
   LEGAL DISCLAIMER POPUP
========================================================= */

    const legalDisclaimerModal = document.getElementById('legal-disclaimer-modal');

    const legalDisclaimerAgreeButton = document.getElementById('legal-disclaimer-agree');

    const legalDisclaimerExitLink = document.getElementById('legal-disclaimer-exit');

    if (legalDisclaimerModal && legalDisclaimerAgreeButton && legalDisclaimerExitLink) {
        const DISCLAIMER_STORAGE_KEY = 'ksdl_disclaimer_accepted_v1';

        let disclaimerIsOpen = false;

        const getFocusableElements = () => {
            return legalDisclaimerModal.querySelectorAll(
                'button:not([disabled]), a[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
            );
        };

        const openLegalDisclaimer = () => {
            legalDisclaimerModal.hidden = false;

            document.body.classList.add('legal-disclaimer-open');

            disclaimerIsOpen = true;

            /*
             * Allow the element to become visible before
             * applying the opening animation.
             */
            window.requestAnimationFrame(() => {
                legalDisclaimerModal.classList.add('is-visible');

                legalDisclaimerAgreeButton.focus();
            });
        };

        const closeLegalDisclaimer = () => {
            legalDisclaimerModal.classList.remove('is-visible');

            document.body.classList.remove('legal-disclaimer-open');

            disclaimerIsOpen = false;

            /*
             * Wait for the closing animation.
             */
            window.setTimeout(() => {
                legalDisclaimerModal.hidden = true;
            }, 350);
        };

        /*
         * CHECK ACCEPTANCE
         *
         * The disclaimer appears only when the visitor
         * has not previously accepted the current version.
         */
        let disclaimerAccepted = false;

        try {
            disclaimerAccepted = localStorage.getItem(DISCLAIMER_STORAGE_KEY) === 'true';
        } catch (error) {
            /*
             * If localStorage is unavailable, the popup
             * will still function for the current visit.
             */
            disclaimerAccepted = false;
        }

        if (!disclaimerAccepted) {
            openLegalDisclaimer();
        }

        /*
         * I AGREE & ENTER
         */
        legalDisclaimerAgreeButton.addEventListener('click', () => {
            try {
                localStorage.setItem(DISCLAIMER_STORAGE_KEY, 'true');
            } catch (error) {
                /*
                 * Continue into the website even if
                 * browser storage is unavailable.
                 */
            }

            closeLegalDisclaimer();
        });

        /*
         * EXIT
         *
         * The exit link points to the Bar Council of India
         * website. No acceptance is saved.
         */
        legalDisclaimerExitLink.addEventListener('click', () => {
            try {
                localStorage.removeItem(DISCLAIMER_STORAGE_KEY);
            } catch (error) {
                // Ignore storage errors.
            }
        });

        /*
         * PREVENT ESCAPE FROM BYPASSING DISCLAIMER
         */
        document.addEventListener(
            'keydown',
            (event) => {
                if (!disclaimerIsOpen) {
                    return;
                }

                /*
                 * The Escape key cannot dismiss the
                 * disclaimer without acceptance.
                 */
                if (event.key === 'Escape') {
                    event.preventDefault();
                    event.stopPropagation();
                    return;
                }

                /*
                 * Keep keyboard focus inside the modal.
                 */
                if (event.key === 'Tab') {
                    const focusableElements = getFocusableElements();

                    if (!focusableElements.length) {
                        event.preventDefault();
                        return;
                    }

                    const firstElement = focusableElements[0];

                    const lastElement = focusableElements[focusableElements.length - 1];

                    if (event.shiftKey && document.activeElement === firstElement) {
                        event.preventDefault();
                        lastElement.focus();
                    } else if (!event.shiftKey && document.activeElement === lastElement) {
                        event.preventDefault();
                        firstElement.focus();
                    }
                }
            },
            true,
        );

        /*
         * PREVENT CLICKING OUTSIDE THE MODAL
         */
        legalDisclaimerModal.addEventListener('click', (event) => {
            if (event.target === legalDisclaimerModal) {
                event.preventDefault();
                event.stopPropagation();
            }
        });

        /*
         * Prevent the page behind the disclaimer from
         * being interacted with.
         */
        legalDisclaimerModal.addEventListener('pointerdown', (event) => {
            if (event.target === legalDisclaimerModal) {
                event.preventDefault();
            }
        });
    }
})();

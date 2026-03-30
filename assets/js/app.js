document.addEventListener('DOMContentLoaded', function () {
            if (window.lucide) {
                lucide.createIcons();
            }

                        const siteNav = document.getElementById('siteNav');
            const siteNavToggle = siteNav ? siteNav.querySelector('.site-nav-toggle') : null;
            const siteNavPanel = siteNav ? siteNav.querySelector('.site-nav-panel') : null;
            const siteNavLinks = Array.from(document.querySelectorAll('.site-nav-link[href^="#"]'));
            const siteNavSections = siteNavLinks
                .map(function (link) {
                    const id = link.getAttribute('href');
                    const section = id ? document.querySelector(id) : null;
                    return section ? { link: link, section: section } : null;
                })
                .filter(Boolean);

            function setSiteNavOpen(isOpen) {
                if (!siteNav || !siteNavToggle) {
                    return;
                }

                siteNav.classList.toggle('is-open', isOpen);
                siteNavToggle.setAttribute('aria-expanded', String(isOpen));
                siteNavToggle.setAttribute('aria-label', isOpen ? 'Закрыть меню' : 'Открыть меню');

                if (siteNavPanel) {
                    siteNavPanel.setAttribute('aria-hidden', String(!isOpen));
                }

                if (window.innerWidth <= 980) {
                    document.body.classList.toggle('site-nav-open', isOpen);
                } else {
                    document.body.classList.remove('site-nav-open');
                }
            }

            function closeSiteNav() {
                setSiteNavOpen(false);
            }

            if (siteNav && siteNavToggle) {
                siteNavToggle.addEventListener('click', function () {
                    const isOpen = !siteNav.classList.contains('is-open');
                    setSiteNavOpen(isOpen);
                });
            }

            siteNavLinks.forEach(function (link) {
                link.addEventListener('click', closeSiteNav);
            });

            document.addEventListener('click', function (event) {
                if (!siteNav || !siteNav.classList.contains('is-open')) {
                    return;
                }

                if (siteNav.contains(event.target)) {
                    return;
                }

                closeSiteNav();
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeSiteNav();
                }
            });

            setSiteNavOpen(false);

            function updateSiteNavState() {
                if (!siteNav) {
                    return;
                }
                siteNav.classList.toggle('is-scrolled', window.scrollY > 18);

                if (!siteNavSections.length) {
                    return;
                }

                const probe = window.scrollY + 150;
                let activeSection = siteNavSections[0];

                siteNavSections.forEach(function (item) {
                    if (probe >= item.section.offsetTop) {
                        activeSection = item;
                    }
                });

                siteNavSections.forEach(function (item) {
                    item.link.classList.toggle('is-active', item === activeSection);
                });
            }

            updateSiteNavState();
            window.addEventListener('scroll', updateSiteNavState, { passive: true });
            window.addEventListener('resize', function () {
                if (window.innerWidth > 980) {
                    closeSiteNav();
                }
                updateSiteNavState();
            });

            document.querySelectorAll('a[href^="#"]').forEach(function (link) {
                link.addEventListener('click', function (event) {
                    const target = document.querySelector(this.getAttribute('href'));
                    if (!target) {
                        return;
                    }

                    event.preventDefault();
                    closeSiteNav();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            });

            const bookedCards = document.querySelectorAll('[data-minutes]');

            
           

            const heroCarousel = document.getElementById('heroCarousel');
            const heroSlides = Array.from(document.querySelectorAll('.hero-slide'));
            const heroDots = Array.from(document.querySelectorAll('.hero-dot'));
            const heroPrev = document.getElementById('heroPrev');
            const heroNext = document.getElementById('heroNext');
            let heroIndex = 0;
            let heroTimer = null;

            function setHeroSlide(index) {
                if (!heroSlides.length) {
                    return;
                }

                heroIndex = (index + heroSlides.length) % heroSlides.length;

                heroSlides.forEach(function (slide, slideIndex) {
                    slide.classList.toggle('is-active', slideIndex === heroIndex);
                });

                heroDots.forEach(function (dot, dotIndex) {
                    const isActive = dotIndex === heroIndex;
                    dot.classList.toggle('is-active', isActive);
                    dot.setAttribute('aria-current', isActive ? 'true' : 'false');
                });
            }

            function startHeroAutoplay() {
                if (!heroSlides.length) {
                    return;
                }

                if (heroTimer) {
                    clearInterval(heroTimer);
                }

                heroTimer = setInterval(function () {
                    setHeroSlide(heroIndex + 1);
                }, 6200);
            }

            if (heroSlides.length) {
                setHeroSlide(0);

                if (heroPrev) {
                    heroPrev.addEventListener('click', function () {
                        setHeroSlide(heroIndex - 1);
                        startHeroAutoplay();
                    });
                }

                if (heroNext) {
                    heroNext.addEventListener('click', function () {
                        setHeroSlide(heroIndex + 1);
                        startHeroAutoplay();
                    });
                }

                heroDots.forEach(function (dot) {
                    dot.addEventListener('click', function () {
                        setHeroSlide(Number(dot.dataset.slide));
                        startHeroAutoplay();
                    });
                });

                if (heroCarousel) {
                    heroCarousel.addEventListener('mouseenter', function () {
                        clearInterval(heroTimer);
                    });

                    heroCarousel.addEventListener('mouseleave', function () {
                        startHeroAutoplay();
                    });

                    heroCarousel.addEventListener('focusin', function () {
                        clearInterval(heroTimer);
                    });

                    heroCarousel.addEventListener('focusout', function () {
                        startHeroAutoplay();
                    });
                }

                startHeroAutoplay();
            }

            if (window.gsap && window.ScrollTrigger) {
                gsap.registerPlugin(ScrollTrigger);

                gsap.from('.hero-shell', {
                    opacity: 0,
                    y: 28,
                    duration: 0.75,
                    ease: 'power2.out'
                });

                gsap.from('.hero-meta', {
                    opacity: 0,
                    y: 20,
                    duration: 0.7,
                    delay: 0.2,
                    ease: 'power2.out'
                });

                gsap.utils.toArray('.reveal').forEach(function (element) {
                    gsap.to(element, {
                        opacity: 1,
                        y: 0,
                        duration: 0.8,
                        ease: 'power2.out',
                        scrollTrigger: {
                            trigger: element,
                            start: 'top 86%'
                        }
                    });
                });
            } else {
                document.querySelectorAll('.reveal').forEach(function (element) {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                });
            }
        });

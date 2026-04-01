<?php

declare(strict_types=1);

require __DIR__ . '/app/site_settings.php';
$config = siteLoadConfig(__DIR__ . '/config/config.php');
require __DIR__ . '/app/database.php';
require __DIR__ . '/app/cars.php';

$pdo = dbConnect($config['db']);
ensureCarsCategorySchema($pdo);
$cars = fetchCars($pdo, null, 300);
$contacts = $config['contacts'];
$bookingCta = siteBuildBookingCta($config);
$seo = siteResolvePageSeo($config, 'classes');
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function formatPrice(int $price): string
{
    return number_format($price, 0, '.', ' ');
}

function classAnchor(string $class): string
{
    $map = [
        'Премиум' => 'premium',
        'Внедорожники' => 'suv',
        'Бизнес' => 'business',
        'Седаны' => 'sedan',
        'Минивэны' => 'minivan',
    ];

    return $map[$class] ?? 'class';
}

$classOrder = ['Премиум', 'Внедорожники', 'Бизнес', 'Седаны', 'Минивэны'];
$classMeta = [
    'Премиум' => [
        'eyebrow' => '✨ Премиум',
        'title' => 'Премиальные автомобили для особых случаев и высокого уровня комфорта.',
        'text' => 'Флагманские модели для статуса, деловых встреч и выразительной подачи.',
    ],
    'Внедорожники' => [
        'eyebrow' => '🏔️ Внедорожники',
        'title' => 'Авто для горных маршрутов, трасс и активных выездов по региону.',
        'text' => 'Надежные машины для сложных дорог и дальних направлений.',
    ],
    'Бизнес' => [
        'eyebrow' => '💼 Бизнес',
        'title' => 'Сбалансированные автомобили для рабочих задач и ежедневного графика.',
        'text' => 'Практичный класс для встреч, командировок и комфортной городской езды.',
    ],
    'Седаны' => [
        'eyebrow' => '🚗 Седаны',
        'title' => 'Экономичные и удобные автомобили для города и междугородних поездок.',
        'text' => 'Оптимальный вариант для повседневных маршрутов и семейных задач.',
    ],
    'Минивэны' => [
        'eyebrow' => '👨‍👩‍👧‍👦 Минивэны',
        'title' => 'Вместительные автомобили для семьи, компании и большого багажа.',
        'text' => 'Лучше всего подходят для поездок группой и длительных путешествий.',
    ],
];

$groupedCars = array_fill_keys($classOrder, []);
foreach ($cars as $car) {
    $class = (string) ($car['category'] ?? '');
    if (!array_key_exists($class, $groupedCars)) {
        $class = 'Седаны';
    }

    $groupedCars[$class][] = $car;
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="/assets/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/apple-touch-icon.png">
    <link rel="manifest" href="/assets/site.webmanifest">
    <meta name="theme-color" content="#111827">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="DadaevCAR">
    <meta name="description" content="<?= e((string) $seo['description']) ?>">
    <?php if (trim((string) $seo['keywords']) !== ''): ?>
    <meta name="keywords" content="<?= e((string) $seo['keywords']) ?>">
    <?php endif; ?>
    <meta name="robots" content="<?= e((string) $seo['robots']) ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ru_RU">
    <meta property="og:site_name" content="DadaevCAR">
    <meta property="og:title" content="<?= e((string) $seo['title']) ?>">
    <meta property="og:description" content="<?= e((string) $seo['description']) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e((string) $seo['title']) ?>">
    <meta name="twitter:description" content="<?= e((string) $seo['description']) ?>">
    <title><?= e((string) $seo['title']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Manrope:wght@600;700;800&family=Sora:wght@600;700;800&display=swap"
        rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
    <script src="https://unpkg.com/lucide@1.7.0/dist/umd/lucide.min.js"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="classes-page">
    <nav class="site-nav" id="siteNav">
        <div class="container site-nav-container">
            <div class="site-nav-shell">
                <a class="site-nav-brand" href="index.php#top" aria-label="RentalCar">
                    <span class="site-nav-brand-badge">Dc</span>
                    <span class="site-nav-brand-copy">
                        <span class="site-nav-brand-title">DadaevCAR</span>
                        <span class="site-nav-brand-subtitle">Аренда автомобилей в Дагестане</span>
                    </span>
                </a>
                <button class="site-nav-toggle" type="button" aria-label="Открыть меню" aria-controls="siteNavPanel"
                    aria-expanded="false">
                    <span class="site-nav-toggle-bars"></span>
                </button>
                <div class="site-nav-panel" id="siteNavPanel">
                    <ul class="site-nav-menu">
                        <li><a class="site-nav-link" href="index.php#advantages">✨ Преимущества</a></li>
                        <li><a class="site-nav-link" href="index.php#catalog">🚗 Каталог</a></li>
                        <li><a class="site-nav-link" href="index.php#reviews">📝 Отзывы</a></li>
                        <li><a class="site-nav-link" href="index.php#faq">❓ Вопрос-ответ</a></li>
                        <li><a class="site-nav-link" href="index.php#contact">📞 Контакты</a></li>
                    </ul>
                    <a class="site-nav-cta" href="index.php#catalog">🚗 Выбрать авто</a>
                </div>
            </div>
        </div>
    </nav>

    <main id="top">
        <section class="section classes-intro-section">
            <div class="container">
                <div class="section-head">
                    <div>
                        <div class="eyebrow">🚘 Каталог категорий</div>
                        <h1 class="section-title reveal">Выберите категорию и найдите подходящую машину за пару кликов.</h1>
                    </div>
                    <p class="section-text reveal">Быстрая навигация по категориям, поиск по названию и удобная мобильная карусель.</p>
                </div>

                <div class="classes-utility">
                    <div class="classes-nav glass reveal" aria-label="Навигация по категориям">
                        <?php foreach ($classOrder as $class): ?>
                        <a class="classes-nav-btn" href="#<?= e(classAnchor($class)) ?>"><?= e($class) ?></a>
                        <?php endforeach; ?>
                    </div>

                    <div class="classes-search glass reveal">
                        <label class="classes-search-label" for="carSearch">Поиск авто по названию</label>
                        <div class="classes-search-row">
                            <input id="carSearch" class="classes-search-input" type="search" placeholder="Например: Camry, BMW, Porsche" autocomplete="off">
                            <button id="carSearchClear" class="classes-search-clear" type="button">Сбросить</button>
                        </div>
                    </div>

                    <p id="classesSearchEmpty" class="classes-search-empty" hidden>По вашему запросу ничего не найдено.</p>
                </div>
            </div>
        </section>

        <?php foreach ($classOrder as $class): ?>
        <?php $carsByClass = $groupedCars[$class]; ?>
        <section class="section classes-category-section" id="<?= e(classAnchor($class)) ?>" data-category-section>
            <div class="container">
                <div class="section-head">
                    <div>
                        <div class="eyebrow"><?= e($classMeta[$class]['eyebrow']) ?></div>
                        <h2 class="section-title reveal"><?= e($classMeta[$class]['title']) ?></h2>
                    </div>
                    <p class="section-text reveal"><?= e($classMeta[$class]['text']) ?></p>
                </div>

                <?php if (count($carsByClass) === 0): ?>
                <div class="class-empty-alert glass reveal">
                    <strong>В категории пока нет авто</strong>
                    <span>Мы обновляем парк. Напишите нам, и предложим ближайшие доступные варианты.</span>
                </div>
                <?php else: ?>
                <p class="class-carousel-hint">Свайпайте карточки влево и вправо</p>

                <div class="catalog-grid classes-catalog-grid" data-carousel-track>
                    <?php foreach ($carsByClass as $car): ?>
                    <article class="car-card glass reveal class-car-card" data-car-name="<?= e((string) $car['name']) ?>">
                        <div class="card-media">
                            <img src="<?= e((string) $car['image_url']) ?>" alt="<?= e((string) $car['name']) ?>">
                            <div class="card-badge"><?= e((string) $car['category']) ?></div>
                        </div>
                        <div class="card-body">
                            <div class="card-top">
                                <div>
                                    <h3 class="card-title"><?= e((string) $car['name']) ?></h3>
                                    <p class="card-subtitle"><?= e((string) $car['description']) ?></p>
                                </div>
                            </div>

                            <div class="specs">
                                <div class="spec"><?= e(number_format((float) $car['engine_volume'], 1, '.', '')) ?> л</div>
                                <div class="spec"><?= e((string) $car['drive_type']) ?> привод</div>
                                <div class="spec"><?= e((string) $car['fuel_type']) ?></div>
                                <div class="spec"><?= e((string) $car['category']) ?></div>
                            </div>

                            <div class="card-footer">
                                <div class="price-group">
                                    <strong><?= e(formatPrice((int) $car['price_per_day'])) ?> ₽</strong>
                                    <span>за сутки</span>
                                </div>
                                <a class="rent-btn" href="<?= e((string) $bookingCta['href']) ?>"<?= (string) $bookingCta['target'] !== '' ? ' target="' . e((string) $bookingCta['target']) . '"' : '' ?><?= (string) $bookingCta['rel'] !== '' ? ' rel="' . e((string) $bookingCta['rel']) . '"' : '' ?>>📩 Забронировать</a>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>

                <div class="class-carousel-controls" data-carousel-controls>
                    <button class="class-carousel-arrow" type="button" data-carousel-prev aria-label="Предыдущий авто">‹</button>
                    <div class="class-carousel-dots" data-carousel-dots></div>
                    <button class="class-carousel-arrow" type="button" data-carousel-next aria-label="Следующий авто">›</button>
                </div>
                <?php endif; ?>
            </div>
        </section>
        <?php endforeach; ?>
    </main>

    <footer>
        <div class="container footer-inner">
            <div>DadaevCar, 2026. Автопарк по категориям.</div>
            <a href="index.php#top">Вернуться на главную</a>
        </div>
    </footer>

    <script src="assets/js/app.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('carSearch');
            const clearBtn = document.getElementById('carSearchClear');
            const globalEmpty = document.getElementById('classesSearchEmpty');
            const sections = Array.from(document.querySelectorAll('[data-category-section]'));
            const navButtons = Array.from(document.querySelectorAll('.classes-nav-btn'));
            const navContainer = document.querySelector('.classes-nav');

            function navButtonForSection(section) {
                return navButtons.find(function (btn) {
                    return btn.getAttribute('href') === '#' + section.id;
                }) || null;
            }

            function getCards(section) {
                return Array.from(section.querySelectorAll('.class-car-card'));
            }

            function getVisibleCards(section) {
                return getCards(section).filter(function (card) {
                    return !card.classList.contains('is-hidden');
                });
            }

            function currentIndex(section) {
                const track = section.querySelector('[data-carousel-track]');
                const cards = getVisibleCards(section);
                if (!track || !cards.length) {
                    return 0;
                }

                let index = 0;
                let bestDistance = Number.POSITIVE_INFINITY;
                cards.forEach(function (card, cardIndex) {
                    const distance = Math.abs(card.offsetLeft - track.scrollLeft);
                    if (distance < bestDistance) {
                        bestDistance = distance;
                        index = cardIndex;
                    }
                });

                return index;
            }

            function scrollToIndex(section, index, smooth) {
                const track = section.querySelector('[data-carousel-track]');
                const cards = getVisibleCards(section);
                if (!track || !cards.length) {
                    return;
                }

                const clamped = Math.max(0, Math.min(index, cards.length - 1));
                track.scrollTo({
                    left: cards[clamped].offsetLeft,
                    behavior: smooth === false ? 'auto' : 'smooth'
                });
            }

            function updateCarouselUi(section) {
                const controls = section.querySelector('[data-carousel-controls]');
                const dotsWrap = section.querySelector('[data-carousel-dots]');
                const prev = section.querySelector('[data-carousel-prev]');
                const next = section.querySelector('[data-carousel-next]');
                const hint = section.querySelector('.class-carousel-hint');
                const track = section.querySelector('[data-carousel-track]');
                const cards = getVisibleCards(section);

                if (!controls || !dotsWrap || !prev || !next || !track) {
                    return;
                }

                track.classList.toggle('is-single-card', cards.length === 1);

                dotsWrap.innerHTML = '';
                cards.forEach(function (_, index) {
                    const dot = document.createElement('button');
                    dot.type = 'button';
                    dot.className = 'class-carousel-dot';
                    dot.setAttribute('aria-label', 'Слайд ' + (index + 1));
                    dot.dataset.index = String(index);
                    dotsWrap.appendChild(dot);
                });

                const hasMany = cards.length > 1;
                controls.hidden = !hasMany;
                if (hint) {
                    hint.style.display = hasMany ? '' : 'none';
                }

                const index = currentIndex(section);
                const dots = Array.from(dotsWrap.querySelectorAll('.class-carousel-dot'));
                dots.forEach(function (dot, dotIndex) {
                    dot.classList.toggle('is-active', dotIndex === index);
                });

                prev.disabled = !hasMany || index <= 0;
                next.disabled = !hasMany || index >= cards.length - 1;
            }

            function visibleSectionsForActive() {
                return sections.filter(function (section) {
                    return !section.classList.contains('is-hidden-by-search');
                });
            }

            function updateActiveCategoryButton() {
                const visibleSections = visibleSectionsForActive();

                navButtons.forEach(function (button) {
                    button.classList.remove('is-active');
                });

                if (!visibleSections.length) {
                    return;
                }

                const focusLine = window.innerHeight * 0.32;
                let activeSection = visibleSections[0];
                let nearestSection = visibleSections[0];
                let nearestDistance = Number.POSITIVE_INFINITY;
                let intersectsFocusLine = false;

                visibleSections.forEach(function (section) {
                    const rect = section.getBoundingClientRect();
                    const distance = Math.abs(rect.top - focusLine);

                    if (distance < nearestDistance) {
                        nearestDistance = distance;
                        nearestSection = section;
                    }

                    if (rect.top <= focusLine && rect.bottom >= focusLine) {
                        activeSection = section;
                        intersectsFocusLine = true;
                    }
                });

                if (!intersectsFocusLine) {
                    activeSection = nearestSection;
                }

                const activeBtn = navButtonForSection(activeSection);
                if (activeBtn) {
                    activeBtn.classList.add('is-active');
                }
            }

            function applySearch() {
                if (!searchInput) {
                    return;
                }

                const query = searchInput.value.trim().toLowerCase();
                let totalVisibleCars = 0;

                sections.forEach(function (section) {
                    const cards = getCards(section);
                    let visibleCount = 0;

                    cards.forEach(function (card) {
                        const name = (card.getAttribute('data-car-name') || '').toLowerCase();
                        const match = query === '' || name.indexOf(query) !== -1;
                        card.classList.toggle('is-hidden', !match);
                        if (match) {
                            visibleCount += 1;
                        }
                    });


                    const hideSection = query !== '' && visibleCount === 0;
                    section.classList.toggle('is-hidden-by-search', hideSection);

                    const navButton = navButtonForSection(section);
                    if (navButton) {
                        navButton.classList.toggle('is-hidden-by-search', hideSection);
                    }

                    if (!hideSection) {
                        totalVisibleCars += visibleCount;
                        scrollToIndex(section, 0, false);
                        updateCarouselUi(section);
                    }
                });

                const noGlobalMatches = query !== '' && totalVisibleCars === 0;

                if (globalEmpty) {
                    globalEmpty.hidden = !noGlobalMatches;
                }

                if (navContainer) {
                    navContainer.classList.toggle('is-hidden-by-search', noGlobalMatches);
                }

                updateActiveCategoryButton();
            }

            sections.forEach(function (section) {
                const track = section.querySelector('[data-carousel-track]');
                const prev = section.querySelector('[data-carousel-prev]');
                const next = section.querySelector('[data-carousel-next]');
                const dotsWrap = section.querySelector('[data-carousel-dots]');

                if (track) {
                    track.addEventListener('scroll', function () {
                        updateCarouselUi(section);
                    }, { passive: true });
                }

                if (prev) {
                    prev.addEventListener('click', function () {
                        scrollToIndex(section, currentIndex(section) - 1, true);
                    });
                }

                if (next) {
                    next.addEventListener('click', function () {
                        scrollToIndex(section, currentIndex(section) + 1, true);
                    });
                }

                if (dotsWrap) {
                    dotsWrap.addEventListener('click', function (event) {
                        const button = event.target.closest('.class-carousel-dot');
                        if (!button) {
                            return;
                        }

                        const index = Number(button.dataset.index || '0');
                        scrollToIndex(section, index, true);
                    });
                }

                updateCarouselUi(section);
            });

            if (searchInput) {
                searchInput.addEventListener('input', applySearch);
            }

            if (clearBtn && searchInput) {
                clearBtn.addEventListener('click', function () {
                    searchInput.value = '';
                    searchInput.focus();
                    applySearch();
                });
            }

            navButtons.forEach(function (button) {
                button.addEventListener('click', function (event) {
                    if (button.classList.contains('is-hidden-by-search')) {
                        event.preventDefault();
                        return;
                    }

                    navButtons.forEach(function (btn) {
                        btn.classList.remove('is-active');
                    });
                    button.classList.add('is-active');
                });
            });

            applySearch();
            updateActiveCategoryButton();
            window.addEventListener('scroll', updateActiveCategoryButton, { passive: true });
            window.addEventListener('resize', function () {
                sections.forEach(function (section) {
                    updateCarouselUi(section);
                });
                updateActiveCategoryButton();
            });
        });
    </script>
</body>

</html>
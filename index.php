<?php declare(strict_types=1); $config = require __DIR__ . '/config/config.php'; require __DIR__ . '/app/database.php'; require __DIR__ . '/app/cars.php'; $pdo = dbConnect($config['db']); ensureCarsCategorySchema($pdo); $catalogPreviewCars = fetchCatalogPreviewCars($pdo, 6); $totalCarsCount = countActiveCars($pdo); $contacts = $config['contacts']; $widgets = $config['widgets'] ?? []; $yandexReviewsSrc = (string) ($widgets['yandex_reviews_src'] ?? ''); function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } function formatPrice(int $price): string { return number_format($price, 0, '.', ' '); } ?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentalCar — Прокат автомобилей</title>
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

<body>
    <nav class="site-nav" id="siteNav">
        <div class="container site-nav-container">
            <div class="site-nav-shell"> <a class="site-nav-brand" href="#top" aria-label="RentalCar"> <span
                        class="site-nav-brand-badge">Dc</span> <span class="site-nav-brand-copy"> <span
                            class="site-nav-brand-title">DadaevCAR</span> <span class="site-nav-brand-subtitle">Аренда
                            автомобилей в Дагестане</span> </span> </a> <button class="site-nav-toggle" type="button"
                    aria-label="Открыть меню" aria-controls="siteNavPanel" aria-expanded="false"> <span
                        class="site-nav-toggle-bars"></span> </button>
                <div class="site-nav-panel" id="siteNavPanel">
                    <ul class="site-nav-menu">
                        <li><a class="site-nav-link" href="#advantages">✨ Преимущества</a></li>
                        <li><a class="site-nav-link" href="#catalog">🚗 Каталог</a></li>
                        <li><a class="site-nav-link" href="#reviews">📝 Отзывы</a></li>
                        <li><a class="site-nav-link" href="#faq">❓ Вопрос-ответ</a></li>
                        <li><a class="site-nav-link" href="#contact">📞 Контакты</a></li>
                    </ul> <a class="site-nav-cta" href="#catalog">🚗 Выбрать авто</a>
                </div>
            </div>
        </div>
    </nav>
    <main id="top">
        <section class="hero">
            <div class="container">
                <div class="hero-shell">
                    <div class="hero-carousel" id="heroCarousel">
                        <div class="hero-track">
                            <article class="hero-slide is-active" data-slide="0"
                                style="--hero-bg: url('https://images.unsplash.com/photo-1493238792000-8113da705763?auto=format&fit=crop&w=1600&q=80');">
                                <div class="hero-slide-grid">
                                    <div class="hero-copy">
                                        <div class="hero-kicker">⛰️ Автопрокат в Махачкале</div>
                                        <h1 class="hero-title">Твой ключ к свободе на дорогах <span>Дагестана</span>
                                        </h1>
                                        <p class="hero-text">Арендуйте проверенный автомобиль для поездок в горы, на
                                            Каспий или по делам. Подадим чистую и заправленную машину в аэропорт или к
                                            вашему отелю точно ко времени — без очередей и долгого оформления.</p>
                                        <ul class="hero-reasons">
                                            <li>🛡️ Машины полностью обслужены и готовы к горным серпантинам</li>
                                            <li>⚡ Бронь за 3 минуты — нужен только паспорт и права</li>
                                            <li>📍 Встретим с ключами в аэропорту Уйташ или любой точке города</li>
                                        </ul>
                                        <div class="hero-actions"> <a class="btn" href="#catalog">🚘 Выбрать
                                                автомобиль</a> <a class="btn-secondary"
                                                href="<?= e($contacts['whatsapp_link']) ?>" target="_blank"
                                                rel="noopener">💬 Написать в WhatsApp</a> </div>
                                    </div>
                                    <div class="hero-media"> <img
                                            src="https://images.unsplash.com/photo-1493238792000-8113da705763?auto=format&fit=crop&w=1600&q=80"
                                            alt="Аренда авто для поездок по Дагестану">
                                        <div class="hero-proof">
                                            <div class="hero-proof-item"><strong>10 мин</strong><span>быстрое
                                                    оформление</span></div>
                                            <div class="hero-proof-item"><strong>24/7</strong><span>помощь на
                                                    дорогах</span></div>
                                            <div class="hero-proof-item"><strong>0₽</strong><span>скрытых доплат</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </div>
                    </div>
                    <div class="hero-meta"> <span class="hero-meta-item">✅ КАСКО и ОСАГО уже работают</span> <span
                            class="hero-meta-item">🗺️ Выгодные тарифы для поездок по республике</span> <span
                            class="hero-meta-item">🧼 Гарантия идеальной чистоты салона</span> </div>
                </div>
            </div>
        </section>
        <section class="section" id="advantages">
            <div class="container">
                <div class="section-head">
                    <div>
                        <div class="eyebrow">🤝 Отношение важнее формальностей</div>
                        <h2 class="section-title reveal">Делаем так, чтобы вы наслаждались горами, а не решали проблемы
                            с машиной.</h2>
                    </div>
                    <p class="section-text reveal">Забудьте про грязные салоны, долгие проверки и скрытые штрафы. Мы
                        выдаем автомобили, на которых с удовольствием ездили бы сами.</p>
                </div>
                <div class="feature-strip">
                    <article class="feature-card glass reveal">
                        <div class="feature-icon"><i data-lucide="clock-3"></i></div>
                        <h3> Без бюрократии</h3>
                        <p>Никаких долгих анкет. Скинули документы в мессенджер, подписали договор при встрече — и ключи
                            ваши.</p>
                    </article>
                    <article class="feature-card glass reveal">
                        <div class="feature-icon"><i data-lucide="badge-check"></i></div>
                        <h3>Ожидание = Реальность</h3>
                        <p>Если на фото чистый салон и идеальный кузов, значит, к вам приедет именно такая машина.
                            Готовим авто как для себя.</p>
                    </article>
                    <article class="feature-card glass reveal">
                        <div class="feature-icon"><i data-lucide="map-pinned"></i></div>
                        <h3>Привезем куда скажете</h3>
                        <p>Аэропорт Уйташ, гостиница в центре или любимая кофейня — просто назовите адрес, и машина
                            будет ждать вас там.</p>
                    </article>
                    <article class="feature-card glass reveal">
                        <div class="feature-icon"><i data-lucide="wallet"></i></div>
                        <h3> Никаких сюрпризов</h3>
                        <p>Вы заранее знаете финальную цену. Мы не придумываем штрафы за царапины, которых не было, и не
                            навязываем лишние услуги.</p>
                    </article>
                </div>
            </div>
        </section>
        <section class="section" id="catalog">
            <div class="container">
                <div class="section-head">
                    <div>
                        <div class="eyebrow">🚘 Наш автопарк</div>
                        <h2 class="section-title reveal">Машины для любых маршрутов: от Сулакского каньона до деловых
                            встреч.</h2>
                    </div>
                    <p class="section-text reveal">Подберите авто по формату поездки, бюджету и категории — все актуальные варианты собраны в одном каталоге.</p>
                </div>
                <div class="catalog-grid"> <?php foreach ($catalogPreviewCars as $car): ?>
                    <article class="car-card glass reveal">
                        <div class="card-media"> <img src="<?= e((string) $car['image_url']) ?>"
                                alt="<?= e((string) $car['name']) ?>">
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
                                <div class="spec"> <?= e(number_format((float) $car['engine_volume'], 1, '.', '')) ?> л
                                </div>
                                <div class="spec"> <?= e((string) $car['drive_type']) ?> привод</div>
                                <div class="spec"> <?= e((string) $car['category']) ?></div>
                            </div>
                            <div class="card-footer">
                                <div class="price-group"> <strong><?= e(formatPrice((int) $car['price_per_day'])) ?>
                                        ₽</strong> <span>за сутки</span> </div> <a class="rent-btn" href="<?= e($contacts['whatsapp_link']) ?>" target="_blank" rel="noopener">📩 Забронировать</a>
                            </div>
                        </div>
                    </article> <?php endforeach; ?> </div> <?php if (count($catalogPreviewCars) === 0): ?> <p class="section-text"
                    style="margin-top:18px;">В базе пока нет автомобилей. Запустите database/setup_cars.php.</p>
                <?php endif; ?>

                <div class="catalog-more-wrap">
                    <a class="btn" href="classes.asp">Посмотреть весь каталог (<?= e((string) $totalCarsCount) ?> шт.)</a>
                </div>
            </div>
        </section>
        <section class="section" id="reviews">
            <div class="container">
                <div class="section-head">
                    <div>
                        <div class="eyebrow">📝 Отзывы с Яндекс Карт</div>
                        <h2 class="section-title reveal">Реальные отзывы клиентов о сервисе.</h2>
                    </div>
                </div>

                <div class="reviews-widget-shell glass reveal">
                    <div class="reviews-widget-wrap">
                        <?php if ($yandexReviewsSrc !== ''): ?>
                        <iframe class="reviews-widget-frame" src="<?= e($yandexReviewsSrc) ?>" frameborder="0"
                            loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                            title="Отзывы Яндекс Карт"></iframe>
                        <?php else: ?>
                        <p class="reviews-widget-hint">Укажите <code>widgets.yandex_reviews_src</code> в
                            <code>config/config.php</code>, чтобы вывести отзывы.
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
        <section class="section" id="faq">
            <div class="container">
                <div class="section-head">
                    <div>
                        <div class="eyebrow">❓ Вопрос-ответ</div>
                        <h2 class="section-title reveal">Частые вопросы перед бронированием.</h2>
                    </div>
                    <p class="section-text reveal">Если не нашли ответ, просто напишите нам в WhatsApp или Telegram:
                        сориентируем по условиям под ваш маршрут и даты.</p>
                </div>

                <div class="faq-list">
                    <details class="faq-item glass reveal">
                        <summary>
                            <span>Какие документы нужны для аренды автомобиля?</span>
                            <span class="faq-icon" aria-hidden="true"></span>
                        </summary>
                        <p>Нужны паспорт и водительское удостоверение. Данные можно отправить заранее в мессенджер, чтобы
                            на выдаче не тратить лишнее время.</p>
                    </details>

                    <details class="faq-item glass reveal">
                        <summary>
                            <span>Можно ли получить машину в аэропорту или у отеля?</span>
                            <span class="faq-icon" aria-hidden="true"></span>
                        </summary>
                        <p>Да. Мы подаем автомобиль в аэропорт Уйташ, к вашему отелю или в удобную точку по городу в
                            заранее согласованное время.</p>
                    </details>

                    <details class="faq-item glass reveal">
                        <summary>
                            <span>Есть ли залог и когда он возвращается?</span>
                            <span class="faq-icon" aria-hidden="true"></span>
                        </summary>
                        <p>Размер залога зависит от класса автомобиля. После возврата машины и стандартной проверки
                            состояния залог возвращается в оговоренный срок.</p>
                    </details>

                    <details class="faq-item glass reveal">
                        <summary>
                            <span>Страховка уже включена в аренду?</span>
                            <span class="faq-icon" aria-hidden="true"></span>
                        </summary>
                        <p>Да, базовая страховка уже действует. По запросу подскажем варианты расширенной защиты под ваш
                            формат поездки.</p>
                    </details>

                    <details class="faq-item glass reveal">
                        <summary>
                            <span>Как забронировать автомобиль?</span>
                            <span class="faq-icon" aria-hidden="true"></span>
                        </summary>
                        <p>Выберите подходящую машину в каталоге и нажмите кнопку «Забронировать». Мы быстро уточним даты,
                            стоимость и подтвердим бронь в мессенджере.</p>
                    </details>

                    <details class="faq-item glass reveal">
                        <summary>
                            <span>Есть ли ограничения по маршруту и пробегу?</span>
                            <span class="faq-icon" aria-hidden="true"></span>
                        </summary>
                        <p>Для поездок по Дагестану ограничений обычно нет, но для дальних направлений лучше заранее
                            согласовать маршрут. Мы сразу подскажем оптимальный тариф.</p>
                    </details>

                    <details class="faq-item glass reveal">
                        <summary>
                            <span>Можно ли продлить аренду, если планы изменились?</span>
                            <span class="faq-icon" aria-hidden="true"></span>
                        </summary>
                        <p>Да, продление возможно при свободном графике автомобиля. Напишите нам заранее, и мы подтвердим
                            новые сроки без лишней бюрократии.</p>
                    </details>
                </div>
            </div>
        </section>
                <section class="section" id="contact">
            <div class="container">
                <div class="section-head">
                    <div>
                        <div class="eyebrow">📞 Контакты</div>
                        <h2 class="section-title reveal">Свяжитесь с нами удобным способом, и мы быстро подберем авто под ваш маршрут.</h2>
                    </div>
                    <p class="section-text reveal">Подскажем по свободным моделям, стоимости, условиям выдачи и времени подачи в нужную локацию.</p>
                </div>

                <div class="contact-grid">
                    <article class="contact-main glass reveal">
                        <h3 class="contact-main-title">Поможем выбрать автомобиль под встречу, отдых или деловую поездку 🚘</h3>
                        <p class="contact-main-text">Если не хочется самостоятельно сравнивать карточки, просто напишите нам. Уточним задачи, бюджет и формат поездки, после чего предложим оптимальные варианты из доступных сейчас.</p>

                        <div class="contact-actions">
                            <a class="btn" href="tel:<?= e($contacts['phone_href']) ?>">📞 Позвонить</a>
                            <a class="btn-secondary" href="<?= e($contacts['whatsapp_link']) ?>" target="_blank" rel="noopener">💬 WhatsApp</a>
                        </div>

                        <div class="contact-chips">
                            <span class="contact-chip">🕒 <?= e($contacts['work_time']) ?></span>
                            <span class="contact-chip">📍 <?= e($contacts['address_short']) ?></span>
                        </div>
                    </article>

                    <aside class="contact-panel glass reveal">
                        <div class="contact-panel-head">
                            <span class="contact-panel-label">На связи ежедневно</span>
                            <strong><?= e($contacts['phone']) ?></strong>
                            <p><?= e($contacts['contact_note']) ?></p>
                        </div>

                        <div class="contact-list">
                            <div class="contact-item">📍 <?= e($contacts['address']) ?></div>
                            <div class="contact-item">💬 Telegram: <a href="<?= e($contacts['telegram_link']) ?>" target="_blank" rel="noopener"><?= e($contacts['telegram']) ?></a></div>
                            <div class="contact-item">💬 WhatsApp: <a href="<?= e($contacts['whatsapp_link']) ?>" target="_blank" rel="noopener"><?= e($contacts['whatsapp']) ?></a></div>
                        </div>
                    </aside>
                </div>
            </div>
        </section>
    </main>
    <footer>
        <div class="container footer-inner">
            <div>DadaevCar, 2026. Прокат авто в Дагестане.</div>
        </div>
    </footer>
    <script src="assets/js/app.js"></script>
</body>

</html>
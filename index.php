<?php declare(strict_types=1); $config = require __DIR__ . '/config/config.php'; require __DIR__ . '/app/database.php'; require __DIR__ . '/app/cars.php'; $pdo = dbConnect($config['db']); $cars = fetchCars($pdo); $contacts = $config['contacts']; function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); } function formatPrice(int $price): string { return number_format($price, 0, '.', ' '); } ?>
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
                    <p class="section-text reveal">Арендованные автомобили остаются в этом же каталоге: вы сразу видите,
                        что свободно сейчас, а что освободится по таймеру.</p>
                </div>
                <div class="catalog-grid"> <?php foreach ($cars as $car): ?>
                    <?php $isRented = isCarRented($car); $minutesLeft = carMinutesLeft($car); ?> <article
                        class="car-card glass reveal<?= $isRented ? ' is-rented' : '' ?>"
                        <?= $isRented ? ' data-minutes="' . (string) $minutesLeft . '"' : '' ?>>
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
                                <div class="rating">
                                    <?= $isRented ? 'До ' . e(formatRentEnd((string) $car['rent_end_at'])) : '' ?>
                                </div>
                            </div>
                            <p class="rent-state<?= $isRented ? ' is-rented' : ' is-free' ?>">
                                <?= $isRented ? '⏳ Сейчас в аренде' : '✅ Доступно к бронированию' ?> </p>
                            <div class="specs">
                                <div class="spec"> <?= e(number_format((float) $car['engine_volume'], 1, '.', '')) ?> л
                                </div>
                                <div class="spec"> <?= e((string) $car['drive_type']) ?> привод</div>
                                <div class="spec"> <?= e((string) $car['category']) ?></div>
                            </div>
                            <div class="card-footer">
                                <div class="price-group"> <strong><?= e(formatPrice((int) $car['price_per_day'])) ?>
                                        ₽</strong> <span>за сутки</span> </div> <button
                                    class="rent-btn<?= $isRented ? ' is-disabled' : '' ?>" type="button"
                                    <?= $isRented ? ' disabled' : '' ?>>
                                    <?= $isRented ? '⏳ Сейчас арендован' : '📩 Забронировать' ?> </button>
                            </div>
                        </div>
                    </article> <?php endforeach; ?> </div> <?php if (count($cars) === 0): ?> <p class="section-text"
                    style="margin-top:18px;">В базе пока нет автомобилей. Запустите database/setup_cars.php.</p>
                <?php endif; ?>
            </div>
        </section>
        <section class="section" id="contact">
            <div class="container">
                <div class="cta-box glass reveal">
                    <div>
                        <div class="eyebrow">💬 Связаться за 2 минуты</div>
                        <h2>Поможем подобрать автомобиль под встречу, поездку, выходные или съемку 🚘</h2>
                        <p> Если не хочется листать каталог самостоятельно, напишите или позвоните. Подскажем, что лучше
                            взять под ваш маршрут, бюджет и нужный стиль подачи. </p>
                        <div class="hero-actions"> <a class="btn" href="tel:<?= e($contacts['phone_href']) ?>">📞
                                Позвонить</a> <a class="btn-secondary" href="<?= e($contacts['whatsapp_link']) ?>"
                                target="_blank" rel="noopener">💬 WhatsApp</a> </div>
                    </div>
                    <aside class="contact-card">
                        <div> <strong><?= e($contacts['phone']) ?></strong>
                            <p><?= e($contacts['contact_note']) ?></p>
                        </div>
                        <div class="contact-list">
                            <div class="contact-item">🕒 <?= e($contacts['work_time']) ?></div>
                            <div class="contact-item">📍 <?= e($contacts['address_short']) ?></div>
                            <div class="contact-item">💬 Telegram: <a href="<?= e($contacts['telegram_link']) ?>"
                                    target="_blank" rel="noopener"><?= e($contacts['telegram']) ?></a> / WhatsApp: <a
                                    href="<?= e($contacts['whatsapp_link']) ?>" target="_blank"
                                    rel="noopener"><?= e($contacts['whatsapp']) ?></a></div>
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
<?php

declare(strict_types=1);

function siteContactKeys(): array
{
    return [
        'address',
        'address_short',
        'phone',
        'phone_href',
        'telegram',
        'telegram_link',
        'whatsapp',
        'whatsapp_link',
        'work_time',
        'contact_note',
    ];
}

function siteSeoPageKeys(): array
{
    return ['index', 'classes'];
}

function siteSeoFieldKeys(): array
{
    return ['title', 'description', 'keywords', 'robots'];
}

function siteSeoPageOptions(): array
{
    return [
        'index' => 'Главная страница',
        'classes' => 'Каталог авто',
    ];
}

function siteSeoDefaults(): array
{
    return [
        'index' => [
            'title' => 'DadaevCAR - Прокат автомобилей в Дагестане',
            'description' => 'Прокат авто в Махачкале и по Дагестану. Подача по городу, прозрачные условия и быстрое бронирование.',
            'keywords' => 'прокат авто махачкала, аренда авто дагестан, прокат автомобилей',
            'robots' => 'index,follow',
        ],
        'classes' => [
            'title' => 'Каталог автомобилей по категориям - DadaevCAR',
            'description' => 'Полный каталог автомобилей: Премиум, Внедорожники, Бизнес, Седаны и Минивэны. Выберите подходящее авто за пару минут.',
            'keywords' => 'каталог авто махачкала, премиум, внедорожники, бизнес, седаны, минивэны',
            'robots' => 'index,follow',
        ],
    ];
}

function siteNormalizeSeoSettings(array $seo): array
{
    $defaults = siteSeoDefaults();
    $normalized = [];

    foreach (siteSeoPageKeys() as $page) {
        $source = isset($seo[$page]) && is_array($seo[$page]) ? $seo[$page] : [];
        $normalized[$page] = [];

        foreach (siteSeoFieldKeys() as $field) {
            $value = trim((string) ($source[$field] ?? $defaults[$page][$field] ?? ''));
            if ($value === '') {
                $value = (string) ($defaults[$page][$field] ?? '');
            }

            if ($field === 'robots') {
                $value = str_replace(';', ',', $value);
            }

            $normalized[$page][$field] = $value;
        }
    }

    return $normalized;
}

function siteResolvePageSeo(array $config, string $page): array
{
    $page = strtolower(trim($page));
    $seo = siteNormalizeSeoSettings(isset($config['seo']) && is_array($config['seo']) ? $config['seo'] : []);

    if (array_key_exists($page, $seo)) {
        return $seo[$page];
    }

    $defaults = siteSeoDefaults();

    return $defaults['index'];
}

function siteBookingDestinationOptions(): array
{
    return [
        'whatsapp' => 'WhatsApp',
        'telegram' => 'Telegram',
        'phone' => 'Прямой звонок',
    ];
}

function siteNormalizeBookingDestination(string $value): string
{
    $value = strtolower(trim($value));

    return array_key_exists($value, siteBookingDestinationOptions()) ? $value : 'whatsapp';
}

function siteSettingsFilePath(string $configFilePath): string
{
    return dirname($configFilePath) . '/site-settings.php';
}

function siteApplyEditableOverrides(array $config, array $overrides): array
{
    if (!isset($config['contacts']) || !is_array($config['contacts'])) {
        $config['contacts'] = [];
    }

    if (isset($overrides['contacts']) && is_array($overrides['contacts'])) {
        foreach (siteContactKeys() as $key) {
            if (array_key_exists($key, $overrides['contacts'])) {
                $config['contacts'][$key] = (string) $overrides['contacts'][$key];
            }
        }
    }

    if (array_key_exists('booking_destination', $overrides)) {
        $config['booking_destination'] = siteNormalizeBookingDestination((string) $overrides['booking_destination']);
    }

    $baseSeo = isset($config['seo']) && is_array($config['seo']) ? $config['seo'] : [];
    if (isset($overrides['seo']) && is_array($overrides['seo'])) {
        $baseSeo = array_replace_recursive($baseSeo, $overrides['seo']);
    }
    $config['seo'] = siteNormalizeSeoSettings($baseSeo);

    return $config;
}

function siteLoadConfig(string $configFilePath): array
{
    $config = require $configFilePath;

    if (!is_array($config)) {
        throw new RuntimeException('Invalid config payload.');
    }

    $settingsFile = siteSettingsFilePath($configFilePath);
    if (is_file($settingsFile)) {
        $overrides = require $settingsFile;
        if (is_array($overrides)) {
            $config = siteApplyEditableOverrides($config, $overrides);
        }
    }

    if (!isset($config['contacts']) || !is_array($config['contacts'])) {
        $config['contacts'] = [];
    }

    $config['booking_destination'] = siteNormalizeBookingDestination((string) ($config['booking_destination'] ?? 'whatsapp'));
    $config['seo'] = siteNormalizeSeoSettings(isset($config['seo']) && is_array($config['seo']) ? $config['seo'] : []);

    return $config;
}

function siteEditableSettingsFromConfig(array $config): array
{
    $contactsSource = isset($config['contacts']) && is_array($config['contacts']) ? $config['contacts'] : [];
    $contacts = [];

    foreach (siteContactKeys() as $key) {
        $contacts[$key] = (string) ($contactsSource[$key] ?? '');
    }

    $seoSource = isset($config['seo']) && is_array($config['seo']) ? $config['seo'] : [];

    return [
        'contacts' => $contacts,
        'booking_destination' => siteNormalizeBookingDestination((string) ($config['booking_destination'] ?? 'whatsapp')),
        'seo' => siteNormalizeSeoSettings($seoSource),
    ];
}

function siteSaveEditableSettings(string $configFilePath, array $settings): void
{
    $contactsSource = isset($settings['contacts']) && is_array($settings['contacts']) ? $settings['contacts'] : [];
    $contacts = [];

    foreach (siteContactKeys() as $key) {
        $contacts[$key] = (string) ($contactsSource[$key] ?? '');
    }

    $seoSource = isset($settings['seo']) && is_array($settings['seo']) ? $settings['seo'] : [];

    $payload = [
        'contacts' => $contacts,
        'booking_destination' => siteNormalizeBookingDestination((string) ($settings['booking_destination'] ?? 'whatsapp')),
        'seo' => siteNormalizeSeoSettings($seoSource),
    ];

    $settingsFile = siteSettingsFilePath($configFilePath);
    $php = "<?php\n\ndeclare(strict_types=1);\n\nreturn " . var_export($payload, true) . ";\n";

    if (file_put_contents($settingsFile, $php, LOCK_EX) === false) {
        throw new RuntimeException('Unable to save site settings.');
    }
}

function siteBookingDestinationLabel(string $destination): string
{
    $options = siteBookingDestinationOptions();

    return $options[$destination] ?? $options['whatsapp'];
}

function siteBuildBookingCta(array $config): array
{
    $contacts = isset($config['contacts']) && is_array($config['contacts']) ? $config['contacts'] : [];
    $destination = siteNormalizeBookingDestination((string) ($config['booking_destination'] ?? 'whatsapp'));

    $href = '';
    $target = '';
    $rel = '';

    if ($destination === 'telegram') {
        $href = (string) ($contacts['telegram_link'] ?? '');
        $target = '_blank';
        $rel = 'noopener';
    } elseif ($destination === 'phone') {
        $phoneHref = trim((string) ($contacts['phone_href'] ?? ''));
        if ($phoneHref !== '' && stripos($phoneHref, 'tel:') !== 0) {
            $phoneHref = 'tel:' . $phoneHref;
        }
        $href = $phoneHref;
    } else {
        $href = (string) ($contacts['whatsapp_link'] ?? '');
        $target = '_blank';
        $rel = 'noopener';
    }

    if ($href === '') {
        $fallbackWhatsapp = trim((string) ($contacts['whatsapp_link'] ?? ''));
        $fallbackTelegram = trim((string) ($contacts['telegram_link'] ?? ''));
        $fallbackPhone = trim((string) ($contacts['phone_href'] ?? ''));

        if ($fallbackWhatsapp !== '') {
            $href = $fallbackWhatsapp;
            $target = '_blank';
            $rel = 'noopener';
            $destination = 'whatsapp';
        } elseif ($fallbackTelegram !== '') {
            $href = $fallbackTelegram;
            $target = '_blank';
            $rel = 'noopener';
            $destination = 'telegram';
        } elseif ($fallbackPhone !== '') {
            if (stripos($fallbackPhone, 'tel:') !== 0) {
                $fallbackPhone = 'tel:' . $fallbackPhone;
            }
            $href = $fallbackPhone;
            $target = '';
            $rel = '';
            $destination = 'phone';
        }
    }

    if ($href === '') {
        $href = '#';
    }

    return [
        'href' => $href,
        'target' => $target,
        'rel' => $rel,
        'destination' => $destination,
    ];
}

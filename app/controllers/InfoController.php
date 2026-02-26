<?php
// app/controllers/InfoController.php

class InfoController extends Controller
{
    private const ORS_ORIGIN = [
        'lat' => 56.054764,
        'lon' => 92.909267,
    ];

    public function about(): void
    {
        $this->render('info-about', [
            'pageMeta' => [
                'title' => 'О нас — Bunch flowers',
                'description' => 'История, философия и подход Bunch flowers.',
                'headerTitle' => 'Информация',
            ],
        ]);
    }

    public function roses(): void
    {
        $this->render('info-roses', [
            'pageMeta' => [
                'title' => 'Наши розы — Bunch flowers',
                'description' => 'Рассказываем о сортах, свежести и поставках роз.',
                'headerTitle' => 'Информация',
            ],
        ]);
    }

    public function delivery(): void
    {
        $settings = new Setting();
        $defaults = $settings->getDeliveryDefaults();
        $pricingMode = $settings->get(Setting::DELIVERY_PRICING_MODE, $defaults[Setting::DELIVERY_PRICING_MODE] ?? 'turf');
        $pricingMode = in_array($pricingMode, ['turf', 'ors'], true) ? $pricingMode : 'turf';

        $this->render('info-delivery', [
            'deliveryPricingMode' => $pricingMode,
            'deliveryDistanceRates' => (new DeliveryDistanceRate())->getAll(),
            'orsApiKey' => (string) $settings->get(Setting::OPENROUTE_API_KEY, $defaults[Setting::OPENROUTE_API_KEY] ?? ''),
            'orsOrigin' => self::ORS_ORIGIN,
            'deliveryFallbackPrice' => (int) ($defaults['defaultDeliveryPrice'] ?? 0),
            'dadataConfig' => $this->getDadataSettings(),
            'testAddresses' => (new DeliveryZone())->getTestAddresses(),
            'pageMeta' => [
                'title' => 'Оплата и доставка — Bunch flowers',
                'description' => 'Условия оплаты, доставки и самовывоза.',
                'headerTitle' => 'Информация',
            ],
        ]);
    }

    public function discount(): void
    {
        $this->render('info-discount', [
            'pageMeta' => [
                'title' => 'Как получить скидку? — Bunch flowers',
                'description' => 'Способы получить выгоду при покупке роз.',
                'headerTitle' => 'Информация',
            ],
        ]);
    }
}

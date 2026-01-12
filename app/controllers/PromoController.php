<?php
// app/controllers/PromoController.php

class PromoController extends Controller
{
    public function index()
    {
        if ($this->isWholesaleUser() && !$this->isAdminUser()) {
            header('Location: /?page=home');
            return;
        }

        $pageMeta = [
            'title' => 'Акции и спецпредложения — Bunch flowers',
            'description' => 'Разовые акции и спецпредложения с ограниченным количеством.',
            'headerTitle' => 'Bunch flowers',
            'headerSubtitle' => 'Акции и спецпредложения',
        ];

        $promoItemModel = new PromoItem();
        $promoCategoryModel = new PromoCategory();
        $promoCategories = $promoCategoryModel->getMap();

        $showPromoItems = (bool) ($promoCategories['promo']['is_active'] ?? true);
        $promoItems = $showPromoItems ? $promoItemModel->getActiveList() : [];
        $oneTimeItems = array_map(function (array $item): array {
            $quantity = $item['quantity'] !== null ? (int) $item['quantity'] : 1;
            $stockText = $quantity > 1 ? 'Осталось ' . $quantity . ' шт' : 'Осталось 1 шт';
            $periodText = 'Ограничено наличием';

            if (!empty($item['ends_at'])) {
                $endsAt = new DateTime($item['ends_at']);
                $periodText = 'До ' . $endsAt->format('d.m H:i');
            }

            return [
                'product_id' => (int) ($item['product_id'] ?? 0),
                'title' => $item['title'],
                'price' => number_format((float) $item['price'], 0, '.', ' ') . ' ₽',
                'stock' => $stockText,
                'period' => $periodText,
                'label' => $item['label'] ?: 'Разовая акция',
                'photo' => $item['photo_url'],
            ];
        }, $promoItems);

        $showAuctions = (bool) ($promoCategories['auction']['is_active'] ?? false);
        $auctionLots = $showAuctions ? (new AuctionLot())->getPromoList() : [];

        $showLotteries = (bool) ($promoCategories['lottery']['is_active'] ?? false);
        $lotteries = $showLotteries ? (new Lottery())->getPromoList() : [];

        $this->render('promo', [
            'pageMeta' => $pageMeta,
            'oneTimeItems' => $oneTimeItems,
            'auctionLots' => $auctionLots,
            'lotteries' => $lotteries,
            'promoCategories' => $promoCategories,
        ]);
    }
}

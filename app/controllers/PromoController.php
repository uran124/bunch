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
            $quantity = $item['quantity'] !== null ? (int) $item['quantity'] : null;
            $remainingQty = $item['remaining_qty'] !== null ? (int) $item['remaining_qty'] : null;
            $periodText = null;
            $endsAtIso = null;

            if (!empty($item['ends_at'])) {
                $endsAt = new DateTime($item['ends_at']);
                $periodText = $endsAt->format('d.m.Y H:i');
                $endsAtIso = $endsAt->format(DateTimeInterface::ATOM);
            }

            return [
                'product_id' => (int) ($item['product_id'] ?? 0),
                'title' => $item['title'],
                'description' => $item['description'],
                'base_price' => (int) floor((float) ($item['base_price'] ?? 0)),
                'price' => (int) floor((float) $item['price']),
                'stock' => $remainingQty,
                'quantity' => $quantity,
                'ends_at' => $item['ends_at'],
                'ends_at_iso' => $endsAtIso,
                'period' => $periodText,
                'label' => $item['label'] ?: 'Разовая акция',
                'photo' => $item['photo_url'],
            ];
        }, $promoItems);

        $auctionLots = (new AuctionLot())->getPromoList();
        $lotteries = (new Lottery())->getPromoList();

        $isAuthenticated = Auth::check();
        $botUsername = '';
        $botConnected = false;
        if ($isAuthenticated) {
            $userModel = new User();
            $user = $userModel->findById((int) Auth::userId());
            $botConnected = !empty($user['telegram_chat_id']);
        }

        $settings = new Setting();
        $defaults = $settings->getTelegramDefaults();
        $botUsernameRaw = $settings->get(Setting::TG_BOT_USERNAME, $defaults[Setting::TG_BOT_USERNAME] ?? '');
        $botUsername = ltrim((string) $botUsernameRaw, '@');

        $this->render('promo', [
            'pageMeta' => $pageMeta,
            'oneTimeItems' => $oneTimeItems,
            'auctionLots' => $auctionLots,
            'lotteries' => $lotteries,
            'promoCategories' => $promoCategories,
            'isAuthenticated' => $isAuthenticated,
            'botConnected' => $botConnected,
            'botUsername' => $botUsername,
        ]);
    }
}

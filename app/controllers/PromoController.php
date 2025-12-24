<?php
// app/controllers/PromoController.php

class PromoController extends Controller
{
    public function index()
    {
        $pageMeta = [
            'title' => 'Акции и спецпредложения — Bunch flowers',
            'description' => 'Аукционы, лотереи и разовые товары с ограниченным количеством.',
            'headerTitle' => 'Bunch flowers',
            'headerSubtitle' => 'Акции и спецпредложения',
        ];

        $auctionModel = new AuctionLot();
        $lotteryModel = new Lottery();
        $promoItemModel = new PromoItem();
        $auctions = $auctionModel->getPromoList();
        $lotteries = $lotteryModel->getPromoList();

        $promoItems = $promoItemModel->getActiveList();
        $oneTimeItems = array_map(function (array $item): array {
            $quantity = $item['quantity'] !== null ? (int) $item['quantity'] : 1;
            $stockText = $quantity > 1 ? 'Осталось ' . $quantity . ' шт' : 'Осталось 1 шт';
            $periodText = 'Ограничено наличием';

            if (!empty($item['ends_at'])) {
                $endsAt = new DateTime($item['ends_at']);
                $periodText = 'До ' . $endsAt->format('d.m H:i');
            }

            return [
                'title' => $item['title'],
                'price' => number_format((float) $item['price'], 0, '.', ' ') . ' ₽',
                'stock' => $stockText,
                'period' => $periodText,
                'label' => $item['label'] ?: 'Разовая акция',
                'photo' => $item['photo_url'],
            ];
        }, $promoItems);

        $this->render('promo', [
            'pageMeta' => $pageMeta,
            'auctions' => $auctions,
            'lotteries' => $lotteries,
            'oneTimeItems' => $oneTimeItems,
        ]);
    }
}

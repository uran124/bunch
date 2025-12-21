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
        $auctions = $auctionModel->getPromoList();
        $lotteries = $lotteryModel->getPromoList();

        $oneTimeItems = [
            [
                'title' => 'Флеш-распродажа 6 часов: роза Freedom',
                'price' => '75 ₽ за стебель',
                'stock' => 'Осталось 120 стеблей',
                'period' => 'Доступно до 16:00',
                'label' => 'Sale',
                'photo' => 'https://images.unsplash.com/photo-1501004318641-b39e6451bec6?auto=format&fit=crop&w=1200&q=80',
            ],
            [
                'title' => 'Разовая поставка: пионы Sarah Bernhardt',
                'price' => '389 ₽ · упаковка 10 шт',
                'stock' => 'Только самовывоз',
                'period' => 'Поставка 15.06, выдача до 18:00',
                'label' => 'One-time',
                'photo' => 'https://images.unsplash.com/photo-1438109491414-7198515b166b?auto=format&fit=crop&w=1200&q=80',
            ],
            [
                'title' => 'Разовая акция на эвкалипт Cinerea',
                'price' => '59 ₽ за ветку',
                'stock' => 'Осталось 35 шт',
                'period' => 'Только 14.06',
                'label' => 'Limited',
                'photo' => 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=1200&q=80',
            ],
        ];

        $this->render('promo', [
            'pageMeta' => $pageMeta,
            'auctions' => $auctions,
            'lotteries' => $lotteries,
            'oneTimeItems' => $oneTimeItems,
        ]);
    }
}

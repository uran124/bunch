<?php
// app/controllers/InfoController.php

class InfoController extends Controller
{
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
        $this->render('info-delivery', [
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

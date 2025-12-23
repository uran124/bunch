<?php
// app/controllers/LegalController.php

class LegalController extends Controller
{
    public function policy(): void
    {
        $this->render('legal-policy', [
            'pageMeta' => [
                'title' => 'Политика обработки персональных данных — Bunch flowers',
                'description' => 'Политика обработки персональных данных ИП Карлова Юрия Владимировича.',
                'headerTitle' => 'Документы',
            ],
        ]);
    }

    public function consent(): void
    {
        $this->render('legal-consent', [
            'pageMeta' => [
                'title' => 'Согласие на обработку персональных данных — Bunch flowers',
                'description' => 'Краткий текст согласия на обработку персональных данных.',
                'headerTitle' => 'Документы',
            ],
        ]);
    }

    public function offer(): void
    {
        $this->render('legal-offer', [
            'pageMeta' => [
                'title' => 'Пользовательское соглашение — Bunch flowers',
                'description' => 'Правила использования сайта и оформления заказов.',
                'headerTitle' => 'Документы',
            ],
        ]);
    }
}

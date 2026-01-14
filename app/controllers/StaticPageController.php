<?php
// app/controllers/StaticPageController.php

class StaticPageController extends Controller
{
    public function show(): void
    {
        $slug = trim((string) ($_GET['slug'] ?? ''));
        $slug = ltrim($slug, '/');

        if ($slug === '') {
            http_response_code(404);
            $this->render('static-page', [
                'page' => null,
                'pageMeta' => [
                    'title' => 'Страница не найдена — Bunch flowers',
                    'description' => 'Статичная страница не найдена.',
                    'headerTitle' => 'Информация',
                ],
            ]);
            return;
        }

        $pageModel = new StaticPage();
        $page = $pageModel->getActiveBySlug($slug);

        if (!$page) {
            http_response_code(404);
            $this->render('static-page', [
                'page' => null,
                'pageMeta' => [
                    'title' => 'Страница не найдена — Bunch flowers',
                    'description' => 'Статичная страница не найдена.',
                    'headerTitle' => 'Информация',
                ],
            ]);
            return;
        }

        $plainText = trim(strip_tags((string) $page['content']));
        $description = $plainText !== '' ? mb_substr($plainText, 0, 160) : 'Информация Bunch flowers.';

        $this->render('static-page', [
            'page' => $page,
            'pageMeta' => [
                'title' => $page['title'] . ' — Bunch flowers',
                'description' => $description,
                'headerTitle' => 'Информация',
            ],
        ]);
    }
}

<?php
// app/core/View.php

class View
{
    public static function render(string $view, array $data = [], string $layout = 'layouts/main'): void
    {
        $viewPath = __DIR__ . '/../views/' . $view . '.php';
        $layoutPath = __DIR__ . '/../views/' . $layout . '.php';

        extract($data);

        if (!file_exists($viewPath)) {
            $escapedView = htmlspecialchars($view, ENT_QUOTES, 'UTF-8');
            $content = sprintf(
                '<section class="rounded-2xl border border-rose-200 bg-rose-50 p-6 text-sm text-rose-900">Шаблон "%s" не найден.</section>',
                $escapedView
            );
            error_log(sprintf('Missing view: %s (%s)', $view, $viewPath));

            if ($layout && file_exists($layoutPath)) {
                include $layoutPath;
            } else {
                echo $content;
            }
            return;
        }

        ob_start();
        include $viewPath;
        $content = ob_get_clean();

        if ($layout && file_exists($layoutPath)) {
            include $layoutPath;
        } else {
            echo $content;
        }
    }
}

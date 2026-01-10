<?php
// app/core/View.php

class View
{
    public static function render(string $view, array $data = [], string $layout = 'layouts/main'): void
    {
        $viewsRoot = realpath(__DIR__ . '/../views') ?: (__DIR__ . '/../views');
        $viewPath = $viewsRoot . '/' . $view . '.php';
        $layoutPath = $viewsRoot . '/' . $layout . '.php';

        if (!file_exists($viewPath)) {
            $escapedView = htmlspecialchars($viewName, ENT_QUOTES, 'UTF-8');
            $content = sprintf(
                '<section class="rounded-2xl border border-rose-200 bg-rose-50 p-6 text-sm text-rose-900">Шаблон "%s" не найден.</section>',
                $escapedView
            );
            error_log(sprintf('Missing view: %s (%s)', $viewName, $viewPath));

            if ($layout && file_exists($layoutPath)) {
                extract($data, EXTR_SKIP);
                include $layoutPath;
            } else {
                echo $content;
            }
            return;
        }

        extract($data, EXTR_SKIP);

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
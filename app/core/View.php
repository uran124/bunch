<?php
// app/core/View.php

class View
{
    public static function render(string $view, array $data = [], string $layout = 'layouts/main'): void
    {
        $viewPath = __DIR__ . '/../views/' . $view . '.php';
        $layoutPath = __DIR__ . '/../views/' . $layout . '.php';

        if (!file_exists($viewPath)) {
            throw new RuntimeException("View {$view} not found");
        }

        extract($data);

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

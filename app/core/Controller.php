<?php
// app/core/Controller.php

abstract class Controller
{
    protected function render(string $view, array $data = [], string $layout = 'layouts/main')
    {
        View::render($view, $data, $layout);
    }
}

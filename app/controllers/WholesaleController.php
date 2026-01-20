<?php
// app/controllers/WholesaleController.php

class WholesaleController extends Controller
{
    public function index(): void
    {
        if (!$this->isWholesaleUser() && !$this->isAdminUser()) {
            header('Location: /');
            return;
        }

        $productModel = new Product();
        $products = $productModel->getWholesaleCatalog();

        foreach ($products as &$product) {
            $product['price_tiers'] = $productModel->getPriceTiers((int) $product['id']);
        }
        unset($product);

        $this->render('wholesale', [
            'products' => $products,
            'pageMeta' => [
                'title' => 'Опт — Bunch flowers',
                'description' => 'Коробки для оптовых предзаказов.',
                'headerTitle' => 'Bunch flowers',
                'headerSubtitle' => 'Оптовые предзаказы',
            ],
        ]);
    }
}

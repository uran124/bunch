<?php
// app/controllers/HomeController.php

class HomeController extends Controller
{
    public function index()
    {
        $productModel = new Product();

        $isWholesaleUser = $this->isWholesaleUser();
        $canModerateCatalog = $this->hasAnyRole('admin', 'manager');
        $products = $productModel->getMainCatalog($isWholesaleUser, $canModerateCatalog);

        foreach ($products as &$product) {
            $product['price_tiers'] = $productModel->getPriceTiers((int) $product['id']);
            $product['attributes'] = $productModel->getAttributesWithValues((int) $product['id']);
        }
        unset($product);

        $this->render('home', [
            'products' => $products,
            'isWholesaleUser' => $isWholesaleUser,
            'canModerateCatalog' => $canModerateCatalog,
            'pageMeta' => [
                'title' => 'Bunch flowers — витрина',
                'description' => 'Выбирайте стебли и оформление на главной странице.',
                'headerTitle' => 'Bunch flowers',
                'headerSubtitle' => 'Главная витрина',
            ],
        ]);
    }
}

<?php
// app/controllers/HomeController.php

class HomeController extends Controller
{
    public function index()
    {
        $productModel = new Product();
        $attributeModel = new AttributeModel();

        $products = $productModel->getAll();
        $attributes = $attributeModel->getAllWithValues();

        $attributesById = [];
        foreach ($attributes as $attribute) {
            $attribute['values'] = array_values(array_filter(
                $attribute['values'],
                static fn ($value) => (int) ($value['is_active'] ?? 0) === 1
            ));

            if ((int) ($attribute['is_active'] ?? 0) === 1) {
                $attributesById[$attribute['id']] = $attribute;
            }
        }

        foreach ($products as &$product) {
            $product['price_tiers'] = $productModel->getPriceTiers((int) $product['id']);
            $attributeIds = $productModel->getAttributeIds((int) $product['id']);

            $product['attributes'] = [];
            foreach ($attributeIds as $attributeId) {
                if (isset($attributesById[$attributeId])) {
                    $product['attributes'][] = $attributesById[$attributeId];
                }
            }
        }
        unset($product);

        $this->render('home', [
            'products' => $products,
            'pageMeta' => [
                'title' => 'Bunch flowers — витрина',
                'description' => 'Выбирайте стебли и оформление на главной странице.',
                'headerTitle' => 'Bunch flowers',
                'headerSubtitle' => 'Главная витрина',
            ],
        ]);
    }
}

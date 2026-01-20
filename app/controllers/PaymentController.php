<?php
// app/controllers/PaymentController.php

class PaymentController extends Controller
{
    public function result(): void
    {
        $this->renderStatusPage('result');
    }

    public function success(): void
    {
        $this->renderStatusPage('success');
    }

    public function fail(): void
    {
        $this->renderStatusPage('fail');
    }

    private function renderStatusPage(string $statusKey): void
    {
        $orderId = $this->resolveOrderId();
        $orderNumber = $orderId > 0 ? '№' . str_pad((string) $orderId, 4, '0', STR_PAD_LEFT) : null;

        $statuses = [
            'result' => [
                'title' => 'Платёж получен',
                'message' => 'Мы получили ответ от платёжной системы. Заказ появится в работе после подтверждения оплаты.',
                'icon' => 'hourglass_top',
            ],
            'success' => [
                'title' => 'Оплата прошла успешно',
                'message' => 'Спасибо! Мы уже начали подготовку вашего заказа.',
                'icon' => 'check_circle',
            ],
            'fail' => [
                'title' => 'Оплата не прошла',
                'message' => 'Попробуйте снова или выберите другой способ оплаты. Если списание произошло, мы проверим статус вручную.',
                'icon' => 'error',
            ],
        ];

        $status = $statuses[$statusKey] ?? $statuses['result'];
        $returnLink = Auth::check() ? '/?page=orders' : '/?page=home';
        $returnLabel = Auth::check() ? 'Перейти к заказам' : 'На главную';

        $pageMeta = [
            'title' => $status['title'] . ' — Bunch flowers',
            'description' => 'Статус оплаты заказа.',
            'headerTitle' => 'Bunch flowers',
            'headerSubtitle' => 'Оплата',
        ];

        $this->render('payment-status', [
            'pageMeta' => $pageMeta,
            'statusKey' => $statusKey,
            'status' => $status,
            'orderId' => $orderId,
            'orderNumber' => $orderNumber,
            'returnLink' => $returnLink,
            'returnLabel' => $returnLabel,
        ]);
    }

    private function resolveOrderId(): int
    {
        $payload = array_merge($_GET, $_POST);
        $candidates = ['InvId', 'inv_id', 'order_id', 'id'];

        foreach ($candidates as $candidate) {
            if (!isset($payload[$candidate])) {
                continue;
            }

            $value = (int) $payload[$candidate];
            if ($value > 0) {
                return $value;
            }
        }

        return 0;
    }
}

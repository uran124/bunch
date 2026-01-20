<?php
// app/controllers/SupportController.php

class SupportController extends Controller
{
    private const SUPPORT_CHAT_ID = -1002055168794;
    private const SUPPORT_THREAD_ID = 1155;

    private User $userModel;
    private Setting $settings;
    private SupportChat $supportChat;

    public function __construct()
    {
        $this->userModel = new User();
        $this->settings = new Setting();
        $this->supportChat = new SupportChat();
    }

    public function sendMessage(): void
    {
        header('Content-Type: application/json');

        $userId = Auth::userId();
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Требуется вход в аккаунт']);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $message = trim((string) ($payload['message'] ?? ''));
        if ($message === '') {
            http_response_code(422);
            echo json_encode(['error' => 'Введите сообщение']);
            return;
        }

        $user = $this->userModel->findById($userId) ?? [];
        $name = trim((string) ($user['name'] ?? ''));
        $phone = trim((string) ($user['phone'] ?? ''));
        $label = $name !== '' ? $name : ('Пользователь #' . $userId);
        $header = "Новый запрос поддержки\nКлиент: {$label}";
        if ($phone !== '') {
            $header .= "\nТелефон: {$phone}";
        }
        $header .= "\nID: {$userId}\n\n";

        $entry = $this->supportChat->appendMessage($userId, 'user', $message);
        $telegramMessageId = $this->sendToSupportChat($header . $message);
        if ($telegramMessageId) {
            $this->supportChat->mapTelegramMessage($telegramMessageId, $userId);
        }

        echo json_encode(['ok' => true, 'message' => $entry]);
    }

    public function listMessages(): void
    {
        header('Content-Type: application/json');

        $userId = Auth::userId();
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Требуется вход в аккаунт']);
            return;
        }

        $afterId = isset($_GET['after']) ? (string) $_GET['after'] : null;
        $messages = $this->supportChat->listMessages($userId, $afterId);

        echo json_encode(['messages' => $messages]);
    }

    private function sendToSupportChat(string $text): ?int
    {
        $defaults = $this->settings->getTelegramDefaults();
        $token = $this->settings->get(Setting::TG_BOT_TOKEN, $defaults[Setting::TG_BOT_TOKEN] ?? '');
        if ($token === '') {
            return null;
        }

        $telegram = new Telegram($token);
        $response = $telegram->sendMessage(self::SUPPORT_CHAT_ID, $text, [
            'message_thread_id' => self::SUPPORT_THREAD_ID,
            'disable_web_page_preview' => true,
        ]);

        return isset($response['result']['message_id']) ? (int) $response['result']['message_id'] : null;
    }
}

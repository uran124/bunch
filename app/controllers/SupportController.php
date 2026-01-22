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

        $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $message = trim((string) ($payload['message'] ?? ''));
        if ($message === '') {
            http_response_code(422);
            echo json_encode(['error' => 'Введите сообщение']);
            return;
        }

        if ($userId) {
            $identity = $this->resolveSupportIdentity();
        } else {
            $guestName = trim((string) ($payload['guest_name'] ?? ''));
            $guestPhone = trim((string) ($payload['guest_phone'] ?? ''));
            if ($guestName === '' || $guestPhone === '') {
                http_response_code(422);
                echo json_encode(['error' => 'Укажите имя и телефон']);
                return;
            }

            Session::set('support_guest_name', $guestName);
            Session::set('support_guest_phone', $guestPhone);
            $identity = $this->resolveSupportIdentity();
        }

        $chatId = $identity['chatId'];
        $label = $identity['label'];
        $prefix = $label . ' ';

        $entry = $this->supportChat->appendMessage($chatId, 'user', $message);
        $telegramMessageId = $this->sendToSupportChat($prefix . $message);
        if ($telegramMessageId) {
            $this->supportChat->mapTelegramMessage($telegramMessageId, $chatId);
        }

        echo json_encode(['ok' => true, 'message' => $entry]);
    }

    public function listMessages(): void
    {
        header('Content-Type: application/json');

        $identity = $this->resolveSupportIdentity();
        $chatId = $identity['chatId'];

        $afterId = isset($_GET['after']) ? (string) $_GET['after'] : null;
        $messages = $this->supportChat->listMessages($chatId, $afterId);

        echo json_encode(['messages' => $messages]);
    }

    private function resolveSupportIdentity(): array
    {
        $userId = Auth::userId();
        if ($userId) {
            $user = $this->userModel->findById($userId) ?? [];
            $name = trim((string) ($user['name'] ?? ''));
            $phone = trim((string) ($user['phone'] ?? ''));
            $label = $name !== '' ? $name : ('Пользователь #' . $userId);
            if ($phone !== '') {
                $label .= ": {$phone}";
            }

            return [
                'chatId' => (string) $userId,
                'label' => $label,
            ];
        }

        $guestId = (string) Session::get('support_guest_id');
        if ($guestId === '') {
            $guestId = bin2hex(random_bytes(6));
            Session::set('support_guest_id', $guestId);
        }

        $shortId = substr($guestId, 0, 6);
        $guestName = trim((string) Session::get('support_guest_name'));
        $guestPhone = trim((string) Session::get('support_guest_phone'));
        $label = 'Гость #' . $shortId;
        if ($guestName !== '') {
            $label = 'Гость ' . $guestName;
        }
        if ($guestPhone !== '') {
            $label .= ": {$guestPhone}";
        }
        return [
            'chatId' => 'guest-' . $guestId,
            'label' => $label,
        ];
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

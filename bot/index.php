<?php
require_once __DIR__ . '/../config.php';

spl_autoload_register(function (string $class): void {
    $paths = [
        __DIR__ . '/../app/core/' . $class . '.php',
        __DIR__ . '/../app/controllers/' . $class . '.php',
        __DIR__ . '/../app/models/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

$settings = new Setting();
$defaults = $settings->getTelegramDefaults();

$webhookSecret = $settings->get(Setting::TG_WEBHOOK_SECRET, $defaults[Setting::TG_WEBHOOK_SECRET] ?? '');
$botToken = $settings->get(Setting::TG_BOT_TOKEN, $defaults[Setting::TG_BOT_TOKEN] ?? '');

if ($botToken === '') {
    http_response_code(500);
    echo "Missing TG_BOT_TOKEN";
    exit;
}

// --- auth: по умолчанию используем тот же secret, что и для webhook (например, bfb) ---
$adminKey = $settings->get('TG_ADMIN_KEY', $webhookSecret);
$providedKey = $_GET['key'] ?? '';
if ($adminKey === '' || !hash_equals((string)$adminKey, (string)$providedKey)) {
    http_response_code(403);
    echo "Forbidden";
    exit;
}

$dataDir = __DIR__ . '/data';
$lastUpdateFile = $dataDir . '/telegram_last_update.json';
$registryFile = $dataDir . '/telegram_chat_registry.json';

function tg_call(string $token, string $method, array $params = []): array
{
    $url = "https://api.telegram.org/bot{$token}/{$method}";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params, JSON_UNESCAPED_UNICODE));
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($resp === false) {
        return ['ok' => false, 'http_code' => $code, 'error' => $err];
    }
    $json = json_decode($resp, true);
    return ['ok' => true, 'http_code' => $code, 'json' => $json];
}

function read_json_file(string $file): ?array
{
    if (!file_exists($file)) return null;
    $d = json_decode((string) file_get_contents($file), true);
    return is_array($d) ? $d : null;
}

$actionResult = null;

$webhookUrlBase = 'https://bunchflowers.ru/bot/webhook.php';

// actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'getMe') {
        $actionResult = tg_call($botToken, 'getMe');
    }

    if ($action === 'getWebhookInfo') {
        $actionResult = tg_call($botToken, 'getWebhookInfo');
    }

    if ($action === 'setWebhook') {
        $useHeaderSecret = isset($_POST['use_header_secret']);
        $url = $webhookUrlBase;

        $params = ['url' => $url];

        if ($useHeaderSecret) {
            // Telegram будет слать X-Telegram-Bot-Api-Secret-Token
            $params['secret_token'] = $webhookSecret;
        } else {
            // старый вариант: secret в query (?secret=bfb)
            $params['url'] = $url . '?secret=' . urlencode($webhookSecret);
        }

        $params['drop_pending_updates'] = isset($_POST['drop_pending_updates']);
        $actionResult = tg_call($botToken, 'setWebhook', $params);
    }

    if ($action === 'deleteWebhook') {
        $actionResult = tg_call($botToken, 'deleteWebhook', [
            'drop_pending_updates' => isset($_POST['drop_pending_updates']),
        ]);
    }

    if ($action === 'sendTest') {
        $chatId = trim($_POST['chat_id'] ?? '');
        $threadId = trim($_POST['thread_id'] ?? '');
        $text = trim($_POST['text'] ?? 'Тест: бот на связи ✅');

        $params = [
            'chat_id' => $chatId,
            'text' => $text,
        ];
        if ($threadId !== '') {
            $params['message_thread_id'] = (int) $threadId;
        }

        $actionResult = tg_call($botToken, 'sendMessage', $params);
    }

    if ($action === 'clearMeta') {
        @unlink($lastUpdateFile);
        @unlink($registryFile);
        $actionResult = ['ok' => true, 'json' => ['ok' => true, 'description' => 'Meta cleared']];
    }
}

$last = read_json_file($lastUpdateFile);
$registry = read_json_file($registryFile);

?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <title>Bunch Bot Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
      background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 100%);
      color: #e4e4e7;
      min-height: 100vh;
      padding: 20px;
      line-height: 1.6;
    }

    .container {
      max-width: 1400px;
      margin: 0 auto;
      animation: fadeIn 0.5s ease-in;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    header {
      text-align: center;
      margin-bottom: 40px;
      padding-bottom: 20px;
      border-bottom: 2px solid rgba(255, 255, 255, 0.1);
    }

    h1 {
      font-size: 2.5rem;
      font-weight: 700;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 8px;
    }

    .subtitle {
      color: #a1a1aa;
      font-size: 0.95rem;
      font-family: 'Courier New', monospace;
    }

    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
      gap: 24px;
      margin-bottom: 24px;
    }

    @media (max-width: 768px) {
      .grid {
        grid-template-columns: 1fr;
      }
    }

    .card {
      background: rgba(30, 30, 46, 0.8);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 16px;
      padding: 24px;
      backdrop-filter: blur(10px);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .card:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
      border-color: rgba(102, 126, 234, 0.4);
    }

    .card h3 {
      color: #fafafa;
      font-size: 1.25rem;
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .card h3::before {
      content: '';
      width: 4px;
      height: 24px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 2px;
    }

    .card h4 {
      color: #d4d4d8;
      font-size: 1rem;
      margin: 20px 0 12px 0;
      font-weight: 600;
    }

    .button-group {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-bottom: 16px;
    }

    button {
      padding: 10px 20px;
      border: 1px solid rgba(102, 126, 234, 0.4);
      border-radius: 10px;
      background: rgba(102, 126, 234, 0.1);
      color: #e4e4e7;
      cursor: pointer;
      font-size: 0.9rem;
      font-weight: 500;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    button::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 0;
      height: 0;
      border-radius: 50%;
      background: rgba(102, 126, 234, 0.3);
      transform: translate(-50%, -50%);
      transition: width 0.6s, height 0.6s;
    }

    button:hover::before {
      width: 300px;
      height: 300px;
    }

    button:hover {
      background: rgba(102, 126, 234, 0.2);
      border-color: rgba(102, 126, 234, 0.6);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    button:active {
      transform: translateY(0);
    }

    button span {
      position: relative;
      z-index: 1;
    }

    input[type="text"],
    input[type="number"],
    textarea {
      width: 100%;
      padding: 12px 16px;
      background: rgba(15, 15, 35, 0.6);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 10px;
      color: #e4e4e7;
      font-size: 0.95rem;
      transition: all 0.3s ease;
      font-family: inherit;
    }

    input[type="text"]:focus,
    input[type="number"]:focus,
    textarea:focus {
      outline: none;
      border-color: rgba(102, 126, 234, 0.6);
      background: rgba(15, 15, 35, 0.8);
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    textarea {
      resize: vertical;
      min-height: 100px;
    }

    label {
      display: block;
      margin-bottom: 8px;
      color: #d4d4d8;
      font-size: 0.9rem;
      font-weight: 500;
    }

    .checkbox-label {
      display: flex;
      align-items: center;
      gap: 10px;
      margin: 12px 0;
      cursor: pointer;
      user-select: none;
    }

    input[type="checkbox"] {
      width: 18px;
      height: 18px;
      cursor: pointer;
      accent-color: #667eea;
    }

    pre {
      white-space: pre-wrap;
      word-break: break-word;
      background: rgba(15, 15, 35, 0.8);
      border: 1px solid rgba(255, 255, 255, 0.1);
      padding: 16px;
      border-radius: 12px;
      font-family: 'Courier New', monospace;
      font-size: 0.85rem;
      color: #a1a1aa;
      overflow-x: auto;
      max-height: 400px;
      overflow-y: auto;
    }

    pre::-webkit-scrollbar {
      width: 8px;
      height: 8px;
    }

    pre::-webkit-scrollbar-track {
      background: rgba(15, 15, 35, 0.4);
      border-radius: 4px;
    }

    pre::-webkit-scrollbar-thumb {
      background: rgba(102, 126, 234, 0.4);
      border-radius: 4px;
    }

    pre::-webkit-scrollbar-thumb:hover {
      background: rgba(102, 126, 234, 0.6);
    }

    .info-text {
      color: #a1a1aa;
      font-size: 0.85rem;
      margin-top: 8px;
      padding: 12px;
      background: rgba(102, 126, 234, 0.05);
      border-left: 3px solid rgba(102, 126, 234, 0.4);
      border-radius: 6px;
    }

    .empty-state {
      text-align: center;
      padding: 40px 20px;
      color: #71717a;
      font-style: italic;
    }

    .form-group {
      margin-bottom: 16px;
    }

    .full-width {
      grid-column: 1 / -1;
    }

    .status-badge {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 12px;
      font-size: 0.8rem;
      font-weight: 600;
      background: rgba(34, 197, 94, 0.1);
      color: #22c55e;
      border: 1px solid rgba(34, 197, 94, 0.3);
    }

    .mono {
      font-family: 'Courier New', monospace;
      background: rgba(102, 126, 234, 0.1);
      padding: 2px 8px;
      border-radius: 4px;
      color: #a78bfa;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateX(-20px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    .card {
      animation: slideIn 0.5s ease-out backwards;
    }

    .card:nth-child(1) { animation-delay: 0.1s; }
    .card:nth-child(2) { animation-delay: 0.2s; }
    .card:nth-child(3) { animation-delay: 0.3s; }
    .card:nth-child(4) { animation-delay: 0.4s; }
    .card:nth-child(5) { animation-delay: 0.5s; }
  </style>
</head>
<body>

<div class="container">
  <header>
    <h1>🤖 Telegram Bot Admin</h1>
    <div class="subtitle">Панель управления ботом • <span class="mono">key=***</span></div>
  </header>

  <div class="grid">
    <!-- API Info -->
    <div class="card">
      <h3>API Информация</h3>
      <div class="button-group">
        <form method="post" style="display: inline;">
          <input type="hidden" name="action" value="getMe">
          <button type="submit"><span>getMe</span></button>
        </form>
        <form method="post" style="display: inline;">
          <input type="hidden" name="action" value="getWebhookInfo">
          <button type="submit"><span>getWebhookInfo</span></button>
        </form>
      </div>
    </div>

    <!-- Webhook Setup -->
    <div class="card">
      <h3>Настройка Webhook</h3>
      <form method="post">
        <input type="hidden" name="action" value="setWebhook">
        
        <label class="checkbox-label">
          <input type="checkbox" name="use_header_secret" value="1" checked>
          <span>Использовать secret_token в заголовке</span>
        </label>
        
        <label class="checkbox-label">
          <input type="checkbox" name="drop_pending_updates" value="1">
          <span>Очистить накопленные обновления</span>
        </label>

        <div class="info-text">
          Webhook URL: <code class="mono"><?=h($webhookUrlBase)?></code>
        </div>

        <div class="button-group" style="margin-top: 16px;">
          <button type="submit"><span>✓ Установить Webhook</span></button>
        </div>
      </form>

      <form method="post" style="margin-top: 12px;">
        <input type="hidden" name="action" value="deleteWebhook">
        <label class="checkbox-label">
          <input type="checkbox" name="drop_pending_updates" value="1">
          <span>Очистить накопленные обновления</span>
        </label>
        <button type="submit" style="background: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.4);">
          <span>✗ Удалить Webhook</span>
        </button>
      </form>
    </div>

    <!-- Test Message -->
    <div class="card">
      <h3>Тестовое сообщение</h3>
      <form method="post">
        <input type="hidden" name="action" value="sendTest">
        
        <div class="form-group">
          <label>Chat ID</label>
          <input type="text" name="chat_id" value="<?=h($last['chat_id'] ?? '')?>" placeholder="-100...">
        </div>

        <div class="form-group">
          <label>Thread ID (опционально)</label>
          <input type="text" name="thread_id" value="<?=h($last['message_thread_id'] ?? '')?>" placeholder="321">
        </div>

        <div class="form-group">
          <label>Текст сообщения</label>
          <textarea name="text" rows="4">Тест: бот на связи ✅</textarea>
        </div>

        <button type="submit"><span>📤 Отправить</span></button>
      </form>
    </div>

    <!-- Last Update -->
    <div class="card">
      <h3>Последний апдейт</h3>
      <?php if ($last): ?>
        <pre><?=h(json_encode($last, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))?></pre>
        <div class="info-text">
          💡 Чтобы получить chat_id и thread_id: напишите сообщение в нужную тему или чат
        </div>
        <form method="post" style="margin-top: 12px;">
          <input type="hidden" name="action" value="clearMeta">
          <button type="submit"><span>🗑 Очистить</span></button>
        </form>
      <?php else: ?>
        <div class="empty-state">
          <p>Нет данных</p>
          <p style="margin-top: 8px; font-size: 0.85rem;">Отправьте сообщение боту для получения информации</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Registry -->
    <div class="card full-width">
      <h3>Реестр чатов и тем</h3>
      <?php if ($registry): ?>
        <pre><?=h(json_encode($registry, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))?></pre>
        <div class="info-text">
          📋 Реестр автоматически обновляется при получении сообщений от бота
        </div>
      <?php else: ?>
        <div class="empty-state">
          <p>Реестр пуст</p>
          <p style="margin-top: 8px; font-size: 0.85rem;">Напишите сообщение в группе или теме для добавления в реестр</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Action Result -->
    <?php if ($actionResult): ?>
    <div class="card full-width">
      <h3>Результат операции</h3>
      <pre><?=h(json_encode($actionResult, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))?></pre>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
  // Smooth scroll
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  });

  // Form submission animation
  document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
      const btn = this.querySelector('button[type="submit"]');
      if (btn) {
        btn.style.transform = 'scale(0.95)';
        setTimeout(() => {
          btn.style.transform = '';
        }, 200);
      }
    });
  });

  // Auto-hide success messages
  window.addEventListener('load', () => {
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
      card.style.opacity = '0';
      setTimeout(() => {
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        card.style.opacity = '1';
      }, index * 100);
    });
  });

  // Input focus effects
  document.querySelectorAll('input, textarea').forEach(input => {
    input.addEventListener('focus', function() {
      this.parentElement.style.transform = 'translateX(4px)';
      this.parentElement.style.transition = 'transform 0.3s ease';
    });
    
    input.addEventListener('blur', function() {
      this.parentElement.style.transform = '';
    });
  });
</script>

</body>
</html>

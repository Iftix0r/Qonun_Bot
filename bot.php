<?php
require_once 'config.php';
require_once 'db.php';

function apiRequest($method, $params) {
    $url = API_URL . $method;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($params),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
    ]);
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true);
}

function restrictUser($chatId, $userId) {
    apiRequest('restrictChatMember', [
        'chat_id' => $chatId,
        'user_id' => $userId,
        'permissions' => ['can_send_messages' => false],
    ]);
}

function allowUser($chatId, $userId) {
    apiRequest('restrictChatMember', [
        'chat_id' => $chatId,
        'user_id' => $userId,
        'permissions' => [
            'can_send_messages' => true,
            'can_send_media_messages' => true,
            'can_send_other_messages' => true,
            'can_add_web_page_previews' => true,
        ],
    ]);
}

function kickUser($chatId, $userId) {
    apiRequest('banChatMember', ['chat_id' => $chatId, 'user_id' => $userId]);
    // Unban qilamiz - faqat chiqarib yuborish uchun
    apiRequest('unbanChatMember', ['chat_id' => $chatId, 'user_id' => $userId]);
}

function sendVerifyMessage($chatId, $user) {
    $name = htmlspecialchars(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')));
    $userId = $user['id'];

    $text = "👋 Salom, <b>{$name}</b>!\n\nGuruhga xush kelibsiz. Iltimos, guruhga yozish uchun <b>inson ekanligingizni tasdiqlang</b>.\n\n⏳ <b>1 soat</b> ichida tasdiqlamasangiz, guruhdan chiqarilasiz.";

    $keyboard = [
        'inline_keyboard' => [[
            ['text' => '✅ Men insonman', 'callback_data' => "verify_{$userId}"]
        ]]
    ];

    $result = apiRequest('sendMessage', [
        'chat_id' => $chatId,
        'text' => $text,
        'parse_mode' => 'HTML',
        'reply_markup' => $keyboard,
    ]);

    return $result['result']['message_id'] ?? null;
}

$update = json_decode(file_get_contents('php://input'), true);

if (!$update) exit;

// Yangi a'zo qo'shilganda
if (isset($update['message']['new_chat_members'])) {
    $chatId = $update['message']['chat']['id'];

    foreach ($update['message']['new_chat_members'] as $user) {
        if ($user['is_bot']) continue;

        $userId = $user['id'];
        restrictUser($chatId, $userId);
        $msgId = sendVerifyMessage($chatId, $user);
        addPending($userId, $chatId, $msgId);
    }
}

// Callback - "Men insonman" tugmasi bosilganda
if (isset($update['callback_query'])) {
    $cb = $update['callback_query'];
    $data = $cb['data'];
    $chatId = $cb['message']['chat']['id'];
    $clickerId = $cb['from']['id'];
    $messageId = $cb['message']['message_id'];

    if (preg_match('/^verify_(\d+)$/', $data, $m)) {
        $targetUserId = (int)$m[1];

        // Faqat o'sha foydalanuvchi bosa oladi
        if ($clickerId !== $targetUserId) {
            apiRequest('answerCallbackQuery', [
                'callback_query_id' => $cb['id'],
                'text' => '❌ Bu tugma siz uchun emas!',
                'show_alert' => true,
            ]);
            exit;
        }

        allowUser($chatId, $targetUserId);
        removePending($targetUserId, $chatId);

        apiRequest('editMessageText', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => '✅ Tasdiqlandi! Endi guruhda yoza olasiz.',
        ]);

        apiRequest('answerCallbackQuery', ['callback_query_id' => $cb['id']]);
    }
}

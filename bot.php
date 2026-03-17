<?php
require_once 'config.php';
require_once 'db.php';

function apiRequest($method, $params) {
    $ch = curl_init(API_URL . $method);
    curl_setopt_array($ch, [
        CURLOPT_POST        => true,
        CURLOPT_POSTFIELDS  => json_encode($params),
        CURLOPT_HTTPHEADER  => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
    ]);
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true);
}

function restrictUser($chatId, $userId) {
    apiRequest('restrictChatMember', [
        'chat_id'     => $chatId,
        'user_id'     => $userId,
        'permissions' => ['can_send_messages' => false],
    ]);
}

function allowUser($chatId, $userId) {
    apiRequest('restrictChatMember', [
        'chat_id'     => $chatId,
        'user_id'     => $userId,
        'permissions' => [
            'can_send_messages'       => true,
            'can_send_media_messages' => true,
            'can_send_other_messages' => true,
            'can_add_web_page_previews' => true,
        ],
    ]);
}

function kickUser($chatId, $userId) {
    apiRequest('banChatMember',   ['chat_id' => $chatId, 'user_id' => $userId]);
    apiRequest('unbanChatMember', ['chat_id' => $chatId, 'user_id' => $userId]);
}

function sendVerifyMessage($chatId, $user) {
    $name   = htmlspecialchars(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')));
    $userId = $user['id'];

    $text = "👋 Salom, <b>{$name}</b>!\n\nGuruhga xush kelibsiz. Iltimos, guruhga yozish uchun <b>inson ekanligingizni tasdiqlang</b>.\n\n⏳ <b>1 soat</b> ichida tasdiqlamasangiz, guruhdan chiqarilasiz.";

    $result = apiRequest('sendMessage', [
        'chat_id'      => $chatId,
        'text'         => $text,
        'parse_mode'   => 'HTML',
        'reply_markup' => [
            'inline_keyboard' => [[
                ['text' => '✅ Men insonman', 'callback_data' => "verify_{$userId}"]
            ]]
        ],
    ]);

    return $result['result']['message_id'] ?? null;
}

$update = json_decode(file_get_contents('php://input'), true);
if (!$update) exit;

$message = $update['message'] ?? null;

// /start komandasi
if ($message && isset($message['text'])) {
    $text   = $message['text'];
    $chatId = $message['chat']['id'];
    $type   = $message['chat']['type'];

    if ($text === '/start') {
        if ($type === 'private') {
            apiRequest('sendMessage', [
                'chat_id'    => $chatId,
                'text'       => "👋 Salom! Men guruhlarni bot va nakrutkalardan himoya qiluvchi botman.\n\n📌 Meni guruhga <b>admin</b> qilib qo'shing va quyidagi huquqlarni bering:\n• Foydalanuvchilarni chiqarish\n• Xabarlarni o'chirish\n• Foydalanuvchilarni cheklash\n\nShundan so'ng guruhga yangi odam qo'shilganda avtomatik ishlaydi! ✅",
                'parse_mode' => 'HTML',
            ]);
        } else {
            apiRequest('sendMessage', [
                'chat_id'    => $chatId,
                'text'       => "✅ Bot faol! Yangi a'zolar qo'shilganda avtomatik tekshiriladi.",
                'parse_mode' => 'HTML',
            ]);
        }
    }
}

// Yangi a'zo qo'shilganda
if ($message && isset($message['new_chat_members'])) {
    $chatId = $message['chat']['id'];

    foreach ($message['new_chat_members'] as $user) {
        if ($user['is_bot'] ?? false) continue;

        $userId = $user['id'];
        restrictUser($chatId, $userId);
        $msgId = sendVerifyMessage($chatId, $user);
        addPending($userId, $chatId, $msgId);
    }
}

// Callback - "Men insonman" tugmasi bosilganda
if (isset($update['callback_query'])) {
    $cb        = $update['callback_query'];
    $data      = $cb['data'];
    $chatId    = $cb['message']['chat']['id'];
    $clickerId = $cb['from']['id'];
    $messageId = $cb['message']['message_id'];

    if (preg_match('/^verify_(\d+)$/', $data, $m)) {
        $targetUserId = (int)$m[1];

        if ($clickerId !== $targetUserId) {
            apiRequest('answerCallbackQuery', [
                'callback_query_id' => $cb['id'],
                'text'              => '❌ Bu tugma siz uchun emas!',
                'show_alert'        => true,
            ]);
            exit;
        }

        allowUser($chatId, $targetUserId);
        removePending($targetUserId, $chatId);

        apiRequest('editMessageText', [
            'chat_id'    => $chatId,
            'message_id' => $messageId,
            'text'       => '✅ Tasdiqlandi! Endi guruhda yoza olasiz.',
        ]);

        apiRequest('answerCallbackQuery', ['callback_query_id' => $cb['id']]);
    }
}

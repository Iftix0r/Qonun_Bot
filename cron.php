<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

function apiRequest($method, $params) {
    $ch = curl_init(API_URL . $method);
    curl_setopt_array($ch, [
        CURLOPT_POST        => true,
        CURLOPT_POSTFIELDS  => json_encode($params),
        CURLOPT_HTTPHEADER  => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
    ]);
    curl_exec($ch);
    curl_close($ch);
}

$expired = getExpired(VERIFY_TIMEOUT);

foreach ($expired as $row) {
    $chatId = $row['chat_id'];
    $userId = $row['user_id'];
    $msgId  = $row['message_id'];

    apiRequest('banChatMember',   ['chat_id' => $chatId, 'user_id' => $userId]);
    apiRequest('unbanChatMember', ['chat_id' => $chatId, 'user_id' => $userId]);

    if ($msgId) {
        apiRequest('deleteMessage', ['chat_id' => $chatId, 'message_id' => $msgId]);
    }

    removePending($userId, $chatId);
}

echo "Checked: " . count($expired) . " expired users kicked.\n";

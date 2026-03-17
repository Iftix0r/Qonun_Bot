<?php
define('DB_FILE', __DIR__ . '/pending.json');

function loadDB() {
    if (!file_exists(DB_FILE)) return [];
    return json_decode(file_get_contents(DB_FILE), true) ?? [];
}

function saveDB($data) {
    file_put_contents(DB_FILE, json_encode($data, JSON_PRETTY_PRINT));
}

function addPending($userId, $chatId, $messageId) {
    $db = loadDB();
    $db["{$userId}_{$chatId}"] = [
        'user_id'    => $userId,
        'chat_id'    => $chatId,
        'message_id' => $messageId,
        'joined_at'  => time(),
    ];
    saveDB($db);
}

function removePending($userId, $chatId) {
    $db = loadDB();
    unset($db["{$userId}_{$chatId}"]);
    saveDB($db);
}

function getExpired($timeout) {
    $db = loadDB();
    return array_filter($db, fn($row) => $row['joined_at'] < time() - $timeout);
}

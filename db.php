<?php
function getDB() {
    $db = new PDO('sqlite:' . __DIR__ . '/pending.db');
    $db->exec("CREATE TABLE IF NOT EXISTS pending (
        user_id INTEGER,
        chat_id INTEGER,
        message_id INTEGER,
        joined_at INTEGER,
        PRIMARY KEY (user_id, chat_id)
    )");
    return $db;
}

function addPending($userId, $chatId, $messageId) {
    $db = getDB();
    $stmt = $db->prepare("INSERT OR REPLACE INTO pending (user_id, chat_id, message_id, joined_at) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $chatId, $messageId, time()]);
}

function removePending($userId, $chatId) {
    $db = getDB();
    $db->prepare("DELETE FROM pending WHERE user_id = ? AND chat_id = ?")->execute([$userId, $chatId]);
}

function getExpired($timeout) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM pending WHERE joined_at < ?");
    $stmt->execute([time() - $timeout]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

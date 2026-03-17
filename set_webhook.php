<?php
require_once 'config.php';

$webhookUrl = 'https://YOUR_DOMAIN.com/bot.php'; // <-- o'zgartiring

$ch = curl_init(API_URL . 'setWebhook');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode(['url' => $webhookUrl]),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_RETURNTRANSFER => true,
]);
echo curl_exec($ch);
curl_close($ch);

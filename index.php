<?php

// ==============================
// CONFIGURATION
// ==============================
$botToken = getenv('8414483455:AAGs6rmmLdkx-uFCkpx3-9AEpFXEDXxEeXI');
$chatId   = getenv('5863793961');

// ==============================
// GET REQUEST DATA
// ==============================

// Try JSON body first
$input = json_decode(file_get_contents("php://input"), true);

// If not JSON, fallback to GET/POST
$username = $input['username'] ?? $_REQUEST['username'] ?? 'Unknown';
$ip       = $input['ip'] ?? $_REQUEST['ip'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$mac      = $input['mac'] ?? $_REQUEST['mac'] ?? 'Unknown';

$time = date("Y-m-d H:i:s");

// ==============================
// BUILD MESSAGE
// ==============================
$message  = "📶 WiFi Voucher Connected\n\n";
$message .= "👤 User: $username\n";
$message .= "🌐 IP: $ip\n";
$message .= "🖥 MAC: $mac\n";
$message .= "⏰ Time: $time\n";

// ==============================
// SEND TO TELEGRAM
// ==============================
$url = "https://api.telegram.org/bot{$botToken}/sendMessage";

$postFields = [
    'chat_id' => $chatId,
    'text'    => $message
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

// ==============================
// RESPONSE OUTPUT
// ==============================
header('Content-Type: application/json');

echo json_encode([
    "status" => "success",
    "message" => "Telegram alert sent",
    "data" => [
        "username" => $username,
        "ip" => $ip,
        "mac" => $mac,
        "time" => $time
    ]
]);

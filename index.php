<?php

// =======================
// CONFIG
// =======================
$botToken = getenv('8414483455:AAGs6rmmLdkx-uFCkpx3-9AEpFXEDXxEeX');
$chatId   = getenv('5863793961');

// =======================
// GET DATA FROM OMADA
// =======================
$username = $_REQUEST['username'] ?? 'Unknown';
$ip       = $_REQUEST['userip'] ?? $_SERVER['REMOTE_ADDR'];
$mac      = $_REQUEST['usermac'] ?? 'Unknown';
$site     = $_REQUEST['site'] ?? 'Office';

$time = date("Y-m-d H:i:s");

// =======================
// CREATE TELEGRAM MESSAGE
// =======================
$message  = "📶 Omada Voucher Login\n\n";
$message .= "👤 User: $username\n";
$message .= "🌐 IP: $ip\n";
$message .= "🖥 MAC: $mac\n";
$message .= "🏢 Site: $site\n";
$message .= "⏰ Time: $time";

// =======================
// SEND TO TELEGRAM
// =======================
$url = "https://api.telegram.org/bot{$botToken}/sendMessage";

$postData = [
    'chat_id' => $chatId,
    'text'    => $message
];

$options = [
    'http' => [
        'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($postData),
    ]
];

$context = stream_context_create($options);
file_get_contents($url, false, $context);

// =======================
// RETURN SUCCESS TO OMADA
// =======================
echo "success";

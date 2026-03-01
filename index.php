<?php
// ------------------------
// CONFIGURATION
// ------------------------
$wifi_name = "GELAI WIFI VOUCHER"; // SSID to monitor
$telegram_bot_token = "8631983368:AAG3yP5ry1FbyzGFWDhFuakefLksqXR1g1E";
$telegram_chat_id = "5863793961";

// ------------------------
// FUNCTIONS
// ------------------------
function sendTelegramAlert($message) {
    global $telegram_bot_token, $telegram_chat_id;
    $url = "https://api.telegram.org/bot$telegram_bot_token/sendMessage";
    $data = [
        'chat_id' => $telegram_chat_id,
        'text' => $message
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

// ------------------------
// RECEIVE WEBHOOK FROM OMADA
// ------------------------
$raw_input = file_get_contents('php://input');
$webhook_data = json_decode($raw_input, true);

// ------------------------
// FULL RESPONSE STRUCTURE (for logging/debugging)
// ------------------------
$response = [
    'received_raw' => $raw_input,
    'parsed_webhook' => $webhook_data,
    'wifi_monitored' => $wifi_name,
    'telegram_chat_id' => $telegram_chat_id,
    'telegram_bot_token' => substr($telegram_bot_token,0,5).'****'.substr($telegram_bot_token,-5),
    'alert_sent' => false
];

// ------------------------
// PROCESS CONNECT EVENTS
// ------------------------
if (isset($webhook_data['eventType']) && $webhook_data['eventType'] === 'client_connected') {
    $client = $webhook_data['client'] ?? [];
    
    if (isset($client['ssid']) && $client['ssid'] === $wifi_name) {
        $mac = $client['mac'] ?? 'Unknown MAC';
        $ip = $client['ip'] ?? 'Unknown IP';
        $name = $client['hostname'] ?? 'Unknown Device';
        $ap_name = $client['apName'] ?? 'Unknown AP';
        
        $message = "📶 New device connected to GELAI VOUCHER WIFI!\n";
        $message .= "Name: $name\nIP: $ip\nMAC: $mac\nAP: $ap_name";
        
        sendTelegramAlert($message);
        $response['alert_sent'] = true;
        $response['alert_message'] = $message;
    }
}

// ------------------------
// RETURN FULL RESPONSE
// ------------------------
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
?>

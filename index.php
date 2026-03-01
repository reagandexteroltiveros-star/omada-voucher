<?php
// ------------------------
// CONFIGURATION
// ------------------------
$wifi_name = "GELAI WIFI VOUCHER"; // SSID to monitor
$telegram_bot_token = "8631983368:AAG3yP5ry1FbyzGFWDhFuakefLksqXR1g1E";
$telegram_chat_id = "5863793961";
$log_file = 'webhook.log';

// ------------------------
// HELPER FUNCTION
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
// HANDLE REQUEST
// ------------------------
$raw_input = file_get_contents('php://input');

// Log all requests
file_put_contents($log_file, date('Y-m-d H:i:s') . " - " . $raw_input . PHP_EOL, FILE_APPEND);

// Decode JSON
$webhook_data = json_decode($raw_input, true);
$alert_sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Only alert if it's a client_connected event
    if (isset($webhook_data['eventType']) && $webhook_data['eventType'] === 'client_connected') {
        $client = $webhook_data['client'] ?? [];
        if (isset($client['ssid']) && $client['ssid'] === $wifi_name) {
            $mac = $client['mac'] ?? 'Unknown MAC';
            $ip = $client['ip'] ?? 'Unknown IP';
            $name = $client['hostname'] ?? 'Unknown Device';
            $ap_name = $client['apName'] ?? 'Unknown AP';
            
            $message = "📶 New device connected to $wifi_name!\n";
            $message .= "Name: $name\nIP: $ip\nMAC: $mac\nAP: $ap_name";
            
            sendTelegramAlert($message);
            $alert_sent = true;
        }
    }
    // Respond with JSON for Omada
    header('Content-Type: application/json');
    echo json_encode([
        'received_raw' => $raw_input,
        'alert_sent' => $alert_sent
    ], JSON_PRETTY_PRINT);
    exit;
}

// ------------------------
// GET REQUEST: Show simple log viewer
// ------------------------
echo "<h2>Omada Webhook Log Viewer</h2>";
echo "<p>Showing last 50 lines of <strong>$log_file</strong>:</p>";
if (file_exists($log_file)) {
    $lines = array_slice(file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES), -50);
    echo "<pre>" . implode("\n", $lines) . "</pre>";
} else {
    echo "<p>No log file yet.</p>";
}
?>

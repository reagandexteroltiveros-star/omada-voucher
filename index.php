<?php
// ------------------------
// CONFIGURATION
// ------------------------
$wifi_name = "GELAI WIFI VOUCHER"; // SSID to monitor
$telegram_bot_token = "8631983368:AAG3yP5ry1FbyzGFWDhFuakefLksqXR1g1E";
$telegram_chat_id = "5863793961";
$log_file = 'webhook.log';

// ------------------------
// HELPER FUNCTION: Send Telegram Alert
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
$webhook_data = json_decode($raw_input, true);
$alert_sent = false;
$alert_reason = "";

// Log all requests
file_put_contents($log_file, date('Y-m-d H:i:s') . " - RAW: " . $raw_input . PHP_EOL, FILE_APPEND);

// Process POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (isset($webhook_data['eventType'])) {
        $event_type = $webhook_data['eventType'];
        $alert_reason = "Event type: $event_type";

        // Only alert if client_connected and SSID matches
        if ($event_type === 'client_connected') {
            $client = $webhook_data['client'] ?? [];
            $client_ssid = $client['ssid'] ?? '';
            $alert_reason .= "; SSID: $client_ssid";

            if ($client_ssid === $wifi_name) {
                $mac = $client['mac'] ?? 'Unknown MAC';
                $ip = $client['ip'] ?? 'Unknown IP';
                $name = $client['hostname'] ?? 'Unknown Device';
                $ap_name = $client['apName'] ?? 'Unknown AP';

                $message = "📶 New device connected to $wifi_name!\n";
                $message .= "Name: $name\nIP: $ip\nMAC: $mac\nAP: $ap_name";

                sendTelegramAlert($message);
                $alert_sent = true;
                $alert_reason .= "; Telegram alert sent ✅";
            } else {
                $alert_reason .= "; SSID does not match; no alert sent";
            }
        } else {
            $alert_reason .= "; Not a client_connected event; no alert sent";
        }
    } else {
        $alert_reason = "No eventType found in payload; no alert sent";
    }

    // Log the alert reason
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - ALERT: " . $alert_reason . PHP_EOL, FILE_APPEND);

    // Respond to Omada
    header('Content-Type: application/json');
    echo json_encode([
        'received_raw' => $raw_input,
        'alert_sent' => $alert_sent,
        'alert_reason' => $alert_reason
    ], JSON_PRETTY_PRINT);
    exit;
}

// ------------------------
// GET REQUEST: Show log viewer
// ------------------------
echo "<h2>Omada Webhook Log Viewer</h2>";
echo "<p>Showing last 50 lines of <strong>$log_file</strong>:</p>";
if (file_exists($log_file)) {
    $lines = array_slice(file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES), -50);
    // Highlight client_connected events in green
    foreach ($lines as &$line) {
        if (strpos($line, 'client_connected') !== false) {
            $line = "<span style='color:green;font-weight:bold;'>$line</span>";
        }
    }
    echo "<pre>" . implode("\n", $lines) . "</pre>";
} else {
    echo "<p>No log file yet.</p>";
}
?>

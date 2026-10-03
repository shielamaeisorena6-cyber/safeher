<?php
// ─── PhilSMS Helper ───────────────────────────────────────────────────────────

/**
 * Send an SMS via PhilSMS REST API.
 *
 * @param  string $to      Recipient mobile number (e.g. 09171234567 or +639171234567)
 * @param  string $message Message body (max 160 chars per segment)
 * @return array           PhilSMS API response decoded from JSON
 */
function sendSMS(string $to, string $message): array {
    if (!SMS_ENABLED) {
        return ['status' => 'disabled', 'message' => 'SMS is disabled in config.'];
    }

    // Normalize to +63 format
    $to = normalizePhone($to);

    if (SMS_DRY_RUN) {
        error_log("[SMS DRY RUN] To: $to | Msg: $message");
        return ['status' => 'dry_run', 'message' => 'Dry run – SMS not sent.'];
    }

    $payload = json_encode([
        'recipient' => $to,
        'sender_id' => SMS_SENDER_NAME,
        'message'   => $message,
    ]);

    $ch = curl_init(SMS_API_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . SMS_API_KEY,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
    ]);

    $response  = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        error_log("[SMS ERROR] cURL error: $curlError");
        return ['status' => 'error', 'message' => $curlError];
    }

    $decoded = json_decode($response, true) ?? [];
    $decoded['http_code'] = $httpCode;

    // Log result to DB
    logSmsToDb($to, $message, $httpCode === 200 ? 'sent' : 'failed');

    return $decoded;
}

/**
 * Normalize PH mobile numbers to international format (+639XXXXXXXXX).
 */
function normalizePhone(string $phone): string {
    $phone = preg_replace('/\D/', '', $phone);
    if (str_starts_with($phone, '0')) {
        $phone = '63' . substr($phone, 1);
    }
    if (!str_starts_with($phone, '+')) {
        $phone = '+' . $phone;
    }
    return $phone;
}

/**
 * Log SMS attempt to the notifications table.
 */
function logSmsToDb(string $to, string $message, string $status): void {
    try {
        $db  = getDB();
        $sql = "INSERT INTO notifications (recipient_phone, message, sms_status, sent_at, created_at)
                VALUES (:phone, :msg, :status, NOW(), NOW())";
        $stmt = $db->prepare($sql);
        $stmt->execute([':phone' => $to, ':msg' => $message, ':status' => $status]);
    } catch (Exception $e) {
        error_log('[SMS LOG ERROR] ' . $e->getMessage());
    }
}

/**
 * Send a bulk SMS to multiple recipients.
 *
 * @param  array  $numbers  Array of phone numbers
 * @param  string $message
 * @return array            Results keyed by phone number
 */
function sendBulkSMS(array $numbers, string $message): array {
    $results = [];
    foreach ($numbers as $number) {
        $results[$number] = sendSMS($number, $message);
        usleep(300000); // 300 ms delay between sends to avoid rate-limiting
    }
    return $results;
}

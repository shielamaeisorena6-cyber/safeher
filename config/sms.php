<?php
// ─── PhilSMS Configuration ────────────────────────────────────────────────────
// Sign up at https://app.philsms.com and get your API key + approved Sender ID
define('SMS_ENABLED',     true);
define('SMS_API_KEY',     'YOUR_PHILSMS_API_KEY_HERE');
define('SMS_SENDER_NAME', 'SafeHer');          // Must match approved Sender ID
define('SMS_API_URL',     'https://app.philsms.com/api/v3/sms/send');

// Set to false to skip actual API calls (useful for local dev without credits)
define('SMS_DRY_RUN',     false);

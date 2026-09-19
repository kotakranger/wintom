<?php
// Wintom Curtain — Global Configuration
// SSoT: update nilai di sini sebelum deploy ke production

define('WA_NUMBER', '6289602052229');      // Format: 628xxxxxxxxxx (tanpa +)
define('WA_URL', 'https://wa.me/' . WA_NUMBER);

define('SITE_NAME', 'Wintom Curtain');
define('SITE_TAGLINE', 'Window Dressing & Motorized Blind');
define('SITE_EMAIL', 'info@wintom.co.id');
define('SITE_PHONE', '089-602-052-229');
define('SITE_PHONE_RAW', '089602052229');
define('SITE_ADDRESS', 'PIK 2, Jakarta Utara');
define('SITE_HOURS', 'Senin – Sabtu, 09:00 – 18:00 WIB');

define('BASE_URL', '');  // Kosongkan untuk root, atau '/subfolder' jika di subdirektori
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('DB_PATH', __DIR__ . '/../database/curtains.sqlite');

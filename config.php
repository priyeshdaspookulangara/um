<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'password');
define('DB_NAME', 'siva_ganga_db');

// Timezone
date_default_timezone_set('UTC');

// PHPMailer Configuration
define('SMTP_HOST', 'smtp.mailtrap.io');
define('SMTP_PORT', 2525);
define('SMTP_USERNAME', 'your_mailtrap_username');
define('SMTP_PASSWORD', 'your_mailtrap_password');
define('SMTP_FROM_EMAIL', 'no-reply@sivaganga.com');
define('SMTP_FROM_NAME', 'Siva Ganga Dance Costumes');
<?php
header('Content-Type: application/json; charset=utf-8');

// Only define DB constants if not defined yet
if (!defined('DB_HOST')) {
    if ($_SERVER['SERVER_NAME'] === 'localhost') {
        // Local development DB config
        define("DB_HOST", "localhost");
        define("DB_USER", "root");
        define("DB_PASS", "");
        define("DB_NAME", "ticker_system"); // your local DB name
    } else {
        // Production DB config
        define("DB_HOST", "localhost");
        define("DB_USER", "u833998383_ticket_master");
        define("DB_PASS", "u833998383_Ticket_master");
        define("DB_NAME", "u833998383_ticket_master");
    }
}

// Url
if (!defined('APP_URL')) {
    define("APP_URL","https://appsghar.com/projects/ticket_master/v1");
}

// Paths
if (!defined('PROFILE_PHOTOS_PATH')) {
    define("PROFILE_PHOTOS_PATH","/home/u833998383/domains/appsghar.com/public_html/projects/ticket_master/uploads/events/");
}
if (!defined('TEMP_PATH')) {
    define("TEMP_PATH","/home/u833998383/domains/appsghar.com/public_html/projects/ticket_master/uploads/temp/");
}

// Paths for url
if (!defined('PROFILE_PATH_URL')) {
    define("PROFILE_PATH_URL","/uploads/events/");
}

// Errors Configurations
if (!defined('ERROR_SUCCESS')) {
    define("ERROR_SUCCESS", 0);
}
if (!defined('ERROR_UNKNOWN')) {
    define("ERROR_UNKNOWN", 100);
}
if (!defined('ERROR_ACCOUNT_ID')) {
    define("ERROR_ACCOUNT_ID", 400);
}
if (!defined('REVOKED_ACCESS')) {
    define("REVOKED_ACCESS", 500);
}

// Email Configurations
if (!defined('HOST')) {
    define("HOST", "");
}
if (!defined('EMAIL')) {
    define("EMAIL", "");
}
if (!defined('PASSWORD')) {
    define("PASSWORD", "");
}
if (!defined('TITLE')) {
    define("TITLE", "");
}
?>

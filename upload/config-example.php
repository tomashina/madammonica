<?php
// Copy this file to config.php and adjust the environment-specific values.

$project_root = dirname(__DIR__);

// HTTP
define('HTTP_SERVER', 'https://madammonica.test/');

// HTTPS
define('HTTPS_SERVER', 'https://madammonica.test/');

// DIR
define('DIR_APPLICATION', $project_root . '/upload/catalog/');
define('DIR_SYSTEM', $project_root . '/upload/system/');
define('DIR_IMAGE', $project_root . '/upload/image/');
define('DIR_STORAGE', $project_root . '/storage/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/theme/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', '127.0.0.1');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'CHANGE_ME');
define('DB_DATABASE', 'madammonica');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');

define('FREE_SHIPPING', 1500);

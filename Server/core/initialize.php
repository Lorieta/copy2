<?php

header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Check if session is already started, prevent calling session_start() again
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Optional: Define helper functions for session management
function setSessionData($key, $value) {
    $_SESSION[$key] = $value;
}

function getSessionData($key) {
    return $_SESSION[$key] ?? null;
}

defined('DS') ? null : define('DS', DIRECTORY_SEPARATOR);


defined('SITE_ROOT') ? null : define('SITE_ROOT', dirname(__DIR__, 2));

defined('INC_PATH') ? null : define('INC_PATH', SITE_ROOT . DS . 'Server' . DS . 'includes');
defined('CORE_PATH') ? null : define('CORE_PATH', SITE_ROOT . DS . 'Server' . DS . 'core');

require_once(INC_PATH . DS . "config.php");
require_once(CORE_PATH . DS . "userhandler.php");
require_once(CORE_PATH . DS . "organization.php");
require_once(CORE_PATH . DS . "eventhandler.php");
require_once(CORE_PATH . DS . "emailhandler.php");
require_once(CORE_PATH . DS . "calendar.php");
require_once(CORE_PATH . DS . "homeEvent.php");
?>

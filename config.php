<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

define('CMD_BALANCE_CHANGE_BY_PARAGRAPH', 0);
define('CMD_BALANCE_CHANGE_BY_ARBITRARY_VALUE', 1);
define('CMD_BALANCE_SHOW', 2);
define('CMD_GENERATE_EXTRACTION', 3);
define('CMD_NEW_MEMBER', 4);
define('CMD_REQUEST_ACCEPT', 5);
define('CMD_REQUEST_VERIFY', 6);
define('CMD_REQUEST_REJECT', 7);
define('CMD_REQUEST_NEXT', 8);
define('CMD_KEYBOARD_SHOW', 9);
define('CMD_NOT_FOUND', 10);
define('PHRASE_BADWORD_FOUND', 11);

define('MAIN_CHAT', 1); // Default peer_id, if peer_id wasn't defined automatically
//define('ADMIN_CHAT', '');

define('DB_HOST', '127.0.0.1:49563'); //DB host
define('DB_USERNAME', ''); // Your database login
define('DB_PASSWORD', ''); // Your database password
define('DB_NAME',''); // Default DB name

define('CONFIRMATION_TOKEN', ''); // Community confirmation token
define('ACCESS_TOKEN', ''); // Community access_token

// Still insecure. If you can't use https, don't set these constants.
define('APP_ID', '0'); // VK app id
define('CLIENT_SECRET', ''); // VK app client secret
define('HOST', 'https://example.com/path/to/this/file/folder'); // Path to Sibyl root folder
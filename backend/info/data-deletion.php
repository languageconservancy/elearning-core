<?php

header('Content-type: text/html; charset=utf-8');

// Simple .env file loader
function loadEnvFile($filePath) {
    if (!file_exists($filePath)) {
        return false;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
    return true;
}

// Simple env function
function env($key, $default = null) {
    return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: $default;
}

// Simple config getter
function getConfigValue($key, $default = null) {
    global $localConfig, $mainConfig;

    // Check local config first (flat keys like 'App.name')
    if ($localConfig && isset($localConfig[$key])) {
        return $localConfig[$key];
    }

    // Check main config (nested access)
    if ($mainConfig) {
        $keys = explode('.', $key);
        $value = $mainConfig;
        foreach ($keys as $k) {
            if (is_array($value) && isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }
        return $value;
    }

    return $default;
}

// Load .env file
if (file_exists('../backend/config/.env')) {
    loadEnvFile('../backend/config/.env');
}

// Load configs
$mainConfig = null;
if (file_exists('../backend/config/app.php')) {
    $mainConfig = include('../backend/config/app.php');
}

$localConfig = null;
if (file_exists('../backend/config/app_local.php')) {
    $localConfig = include('../backend/config/app_local.php');
}

// Get database config
$dbHost = getConfigValue('Datasources.default.host', 'localhost');
$dbName = getConfigValue('Datasources.default.database', 'default_db');
$dbUser = env('DATABASE_USERNAME', 'root');
$dbPassword = env('DATABASE_PASSWORD', 'root');
$appName = getConfigValue('App.name', 'Default App');

// Make MySQLi connection
$dbConn = @($GLOBALS["___mysqli_ston"] = mysqli_connect(
    $dbHost,
    $dbUser,
    $dbPassword
)) or die('Cannot connect to MySQL.');

// Define UTF-8 character encoding
mysqli_query($GLOBALS["___mysqli_ston"], 'SET NAMES utf8');

// Select the database
(
    (bool) mysqli_query(
        $GLOBALS["___mysqli_ston"],
        "USE " . $dbName
    )
) or die('Unable to select database.');

// Get data deletion policy
$pq = mysqli_query(
    $dbConn,
    "SELECT `text` FROM `contents` WHERE `keyword` = 'data-deletion'"
);

$content = mysqli_fetch_row($pq)[0];

mysqli_free_result($pq);
mysqli_close($dbConn);

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset='utf-8'>
        <meta name='viewport' content='width=device-width'>
        <title>Data Deletion</title>
        <style> body {font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; padding: 1em; } </style>
    </head>
    <body>
        <h1>User Data Deletion</h1>
        <?php
            echo $content;
        ?>
    </body>
</html>
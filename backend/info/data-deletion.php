<?php

//error_reporting (E_ALL ^ E_NOTICE); /* 1st line (recommended) */
header('Content-type: text/html; charset=utf-8');

// Simple .env file loader function
function loadEnvFile($filePath) {
  if (!file_exists($filePath)) {
      return false;
  }

  $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
      $line = trim($line);

      // Skip comments and empty lines
      if (empty($line) || strpos($line, '#') === 0) {
          continue;
      }

      // Parse key=value pairs
      if (strpos($line, '=') !== false) {
          list($key, $value) = explode('=', $line, 2);
          $key = trim($key);
          $value = trim($value, " \t\n\r\0\x0B\"'"); // Remove quotes and whitespace

          if (!array_key_exists($key, $_ENV)) {
              $_ENV[$key] = $value;
              $_SERVER[$key] = $value;
              putenv("$key=$value");
          }
      }
  }
  return true;
}

// CakePHP-compatible env function
function env($key, $default = null) {
  if (array_key_exists($key, $_ENV)) {
      return $_ENV[$key];
  }
  if (array_key_exists($key, $_SERVER)) {
      return $_SERVER[$key];
  }
  return getenv($key) ?: $default;
}

// Try to load .env file from different possible locations
// When deployed to public_html/info/, backend would be at ../backend/
$envPaths = [
  '../backend/config/.env',  // When deployed to public_html/info/
];

foreach ($envPaths as $envPath) {
  if (loadEnvFile($envPath)) {
      break;
  }
}

// Try to load CakePHP config from different possible locations
$configPaths = [
  '../backend/config/app.php',  // When deployed to public_html/info/
];

$conf = null;
foreach ($configPaths as $configPath) {
  if (file_exists($configPath)) {
      $conf = include($configPath);
      break;
  }
}

$dbConf = $conf['Datasources']['default'];

DEFINE ('DB_USER', env('DATABASE_USERNAME', 'root'));
DEFINE ('DB_PASSWORD', env('DATABASE_PASSWORD', 'root'));
DEFINE('DB_HOST', $dbConf['host']);
DEFINE('DB_NAME', $dbConf['database']);

// Make MySQLi connection
$dbConn = @($GLOBALS["___mysqli_ston"] = mysqli_connect(
    DB_HOST,
    DB_USER,
    DB_PASSWORD
)) or die('Cannot connect to MySQL.');

// Define UTF-8 character encoding
mysqli_query($GLOBALS["___mysqli_ston"], 'SET NAMES utf8');

// Select the database
(
    (bool) mysqli_query(
        $GLOBALS["___mysqli_ston"],
        "USE " . constant('DB_NAME')
    )
) or die('Unable to select database.');

// Get privacy policy
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

<?php

//error_reporting (E_ALL ^ E_NOTICE); /* 1st line (recommended) */
header('Content-type: text/html; charset=utf-8');

// override env function from cakephp, to suppress errors due to not having
// dotenv functions available outside cakephp
function env($key, $value = null, $default = null) {
	return "";
}

// Read in cakephp config
$conf = include("../backend/config/app.php");
$dbConf = $conf['Datasources']['default'];

DEFINE ('DB_USER', $dbConf['username']);
DEFINE ('DB_PASSWORD', $dbConf['password']);
DEFINE ('DB_HOST', $dbConf['host']);
DEFINE ('DB_NAME', $dbConf['database']);

// Make MySQLi connection
$dbConn = @($GLOBALS["___mysqli_ston"] = mysqli_connect(
	DB_HOST,
	DB_USER,
	DB_PASSWORD
)) OR die ('Cannot connect to MySQL.');

// Define UTF-8 character encoding
mysqli_query($GLOBALS["___mysqli_ston"], 'SET NAMES utf8');

// Select the database
(
	(bool) mysqli_query(
		$GLOBALS["___mysqli_ston"], "USE " . constant('DB_NAME')
	)
) OR die ('Unable to select database.');

// Get privacy policy
$pq = mysqli_query($dbConn,
	"SELECT `text` FROM `contents` WHERE `keyword` = 'privacy'");

$content = mysqli_fetch_row($pq)[0];

mysqli_free_result($pq);
mysqli_close($dbConn);

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset='utf-8'>
		<meta name='viewport' content='width=device-width'>
		<title>Privacy Policy</title>
		<style> body {font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; padding: 1em; } </style>
	</head>
	<body>
		<h1>Privacy Policy</h1>
		<p>
			<?php
				echo $content;
			?>
		</p>
	</body>
</html>

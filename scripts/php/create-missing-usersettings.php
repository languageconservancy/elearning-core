<?php

/**
 * This script searches the database in app.php for users in the Users table
 * that don't have a matching row in the UserSettings table. If no matching row
 * is found, it creates that missing UserSettings row.
 * To access the database, the app.php file in backend/config is used, so this
 * file expects to be in the site ROOT/info directory.
 */

//error_reporting (E_ALL ^ E_NOTICE); /* 1st line (recommended) */
header('Content-type: text/html; charset=utf-8');

// override env function from cakephp, to suppress errors due to not having
// dotenv functions available outside cakephp
function env($key, $value = null, $default = null) {
	return "";
}

$modifyDb = true;

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

// Find Users without a matching UserSettings entity
$query = "SELECT * FROM users WHERE users.id NOT IN (SELECT user_id FROM user_settings)";
$result = mysqli_query($dbConn, $query);

if (mysqli_num_rows($result) > 0) {
	while ($row = mysqli_fetch_assoc($result)) {
		// Get basic info to create the missing UserSettings entity
		$userId = $row['id'];
		$userName = $row['name'];
		$userDob = $row['dob'] != null ? $row['dob'] : strtotime("2000/01/01");
		$profileDesc = addslashes("Hi, I am $userName. I am interested in learning Lakota.");
		$usersEighteenthBday = strtotime('+18 Years', strtotime($row['dob']));
		$userIsAdult = time() - $usersEighteenthBday > 0 ? 1 : 0;

		// Insert our missing UserSettings entity into the table
		$insertQuery = "INSERT INTO user_settings (user_id, profile_desc, age_over_adult) VALUES ('$userId', '$profileDesc', '$userIsAdult')";

		// Print the INSERT query, and print the result
		echo $insertQuery."<br>";
		if ($modifyDb) {
			if (mysqli_query($dbConn, $insertQuery) === true) {
				echo "New record created successfully<br>";
			} else {
				echo "Error: ".$insertQuery."<br>".mysqli_error($dbConn)."<br>";
			}
		}
	}
} else {
	echo "ERROR: Could not execute $sql. " . mysqli_error($dbConn);
}

?>
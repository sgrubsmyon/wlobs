<?php
header("Content-Type: application/json; charset=UTF-8");

$txt_dir = "../../../../../wlobs/";
// Secret token that must be provided by client
$secret_token = 'abc';

// Load database credentials
// (parse without sections)
$credentials = parse_ini_file($txt_dir . "config.ini");

// First of all, before doing anything, check if user provided the correct secret token:
$token_of_get = isset($_GET["token"]) ? $_GET["token"] : die();
if ($token_of_get != $secret_token) {
  die();
}

// Go to the directory that contains the TXT files:
chdir($txt_dir);

// Import the data from the TXT files into the DB:
$dbhost = $credentials["dbhost"];
$dbuser = $credentials["dbuser"];
$dbpass = $credentials["dbpass"];
$output = null;
$retval = null;
exec("mysql -h$dbhost -u$dbuser -p$dbpass -e 'source sql/update_article_table.sql'", $output, $retval);

if ($retval == 0) {
  echo json_encode(
    array(
      "info" => "Successfully imported articles from TXT files into the DB."
    )
  );
} else {
  $output_str = null;
  foreach ($output as $o) {
    $output_str = $output_str . $o . " ";
  }
  echo json_encode(
    array(
      "error" => "Unsuccessful mysql execution. Return value: $retval. Returned output from command: " . $output_str
    )
  );
}
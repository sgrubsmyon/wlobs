<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

// include database and object files
include_once '../config/database.php';
include_once '../objects/artikel.php';

// instantiate database and artikel object
$database = new Database();
$db = $database->getConnection();

// initialize object
$artikel = new Artikel($db);

// read name of product group from GET method
$groupname = isset($_GET['name']) ? $_GET['name'] : die();
// read type of product from GET method (if present)
$typ = isset($_GET['typ']) ? $_GET['typ'] : null;

// read the details of one product group
$products = $artikel->read_group($groupname, $typ);

if (is_null($products)) {
  // there was a DB error

  // set response code - 503 service unavailable
  http_response_code(503);

  // tell the user
  echo json_encode(array(
    "message" => "Unable to access DB."
  ));
} elseif (empty($products)) {
  // no records found

  // set response code - 404 Not found
  http_response_code(404);

  // tell the user no products found
  echo json_encode(array(
    "message" => "No products found."
  ));
} else {
  // set response code - 200 OK
  http_response_code(200);

  // show product data in json format
  echo json_encode(array(
    "records" => $products
  ));
}
?>

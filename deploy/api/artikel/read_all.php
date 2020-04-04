<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// include database and object files
include_once '../config/database.php';
include_once '../objects/artikel.php';

// instantiate database and artikel object
$database = new Database();
$db = $database->getConnection();

// initialize object
$artikel = new Artikel($db);
$products = $artikel->read_all();

if (is_null($products)) {
  // there was a DB error

  // set response code - 503 service unavailable
  http_response_code(503);

  // tell the user
  echo json_encode(array(
    "message" => "Unable to create Bestellung."
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
    "records" => $artikel_arr
  ));
}
?>

<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// include database and object files
include_once '../config/database.php';
include_once '../objects/produktgruppe.php';

// instantiate database and artikel object
$database = new Database();
$db = $database->getConnection();

// initialize object
$produktgruppe = new Produktgruppe($db);
$produktgruppen = $produktgruppe->read_all();

if (is_null($produktgruppen)) {
  // there was a DB error

  // set response code - 503 service unavailable
  http_response_code(503);

  // tell the user
  echo json_encode(array(
    "message" => "Unable to access DB."
  ));
} elseif (empty($produktgruppen)) {
  // no records found

  // set response code - 404 Not found
  http_response_code(404);

  // tell the user no produktgruppen found
  echo json_encode(array(
    "message" => "No product groups found."
  ));
} else {
  // set response code - 200 OK
  http_response_code(200);

  // show product data in json format
  echo json_encode(array(
    "records" => $produktgruppen
  ));
}
?>

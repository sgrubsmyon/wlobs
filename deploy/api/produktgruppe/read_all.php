<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// include database and object files
include_once '../config/database.php';
include_once '../objects/produktgruppe.php';

// instantiate database
$database = new Database();
$db = $database->getConnection();
// initialize object
$produktgruppe = new Produktgruppe($db);

// read type of product from GET method
$typ = isset($_GET['typ']) ? $_GET['typ'] : null;

if (is_null($typ)) {
  http_response_code(400); // Bad request
  echo json_encode(array("message" => "Need to set param 'typ'.")); // tell the user
  die();
}

// read the product groups of one product type
$produktgruppen = $produktgruppe->read_all($typ);

if (is_null($produktgruppen)) {
  // there was a DB error
  http_response_code(503); // Service unavailable
  echo json_encode(array("message" => "Unable to access DB.")); // tell the user
} elseif (empty($produktgruppen)) {
  // no records found
  http_response_code(404); // Not found
  // tell the user no produktgruppen found
  echo json_encode(array("message" => "No product groups found."));
} else {
  http_response_code(200); // OK
  echo json_encode(array("records" => $produktgruppen));
}
?>

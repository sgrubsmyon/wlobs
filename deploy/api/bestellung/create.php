<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// get database connection
include_once '../config/database.php';

// instantiate bestellung object
include_once '../objects/bestellung.php';

$database = new Database();
$db = $database->getConnection();

$bestellung = new Bestellung($db);

// get posted data
$data = json_decode(file_get_contents("php://input"));

// make sure data is not empty
if (!empty($data->details)) {
  // set bestellung property values
  $bestellung->details = $data->details;
  //$bestellung->created = date('Y-m-d H:i:s');

  // create the bestellung
  $bestell_data = $bestellung->create();
  if (!is_null($bestell_data)) {

    // set response code - 201 created
    http_response_code(201);

    // tell the user
    echo json_encode(array(
      "message" => "Bestellung was created.",
      "nr" => $bestell_data["nr"],
      "datum" => $bestell_data["datum"],
      "details" => $bestell_data["details"],
      "summe" => $bestell_data["summe"]
    ));
  } else {
    // if unable to create the bestellung, tell the user
    // set response code - 503 service unavailable
    http_response_code(503);

    // tell the user
    echo json_encode(array("message" => "Unable to create Bestellung."));
  }
} else {

  // tell the user data is incomplete
  // set response code - 400 bad request
  http_response_code(400);

  // tell the user
  echo json_encode(array("message" => "Unable to create Bestellung. Data is incomplete. Key 'details' missing."));
}
// Test with:
// curl -i --header "Content-Type: application/json" --request POST --data '{"details":[{"position":"1","stueckzahl":"3","lieferant_name":"Bantam","artikel_nr":"B300N"},{"position":"2","stueckzahl":"5","lieferant_name":"Bantam","artikel_nr":"G161N"}]}' http://localhost/coronashopper/api/bestellung/create.php
?>

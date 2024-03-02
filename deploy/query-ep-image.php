<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
$data = json_decode(file_get_contents("php://input"));
if (!empty($data)) {
  if (!empty($data->art_nr)) {
    $response = file_get_contents("https://www.el-puente.de/search/ajax/suggest/?q=" . $data->art_nr);
    if (FALSE === $response) {
      http_response_code(503); // Server error
      echo json_encode(array("message" => "Unable to load EP page."));
    } else {
      http_response_code(200); // OK
      echo $response;
    }
  } else {
    http_response_code(400); // Bad request
    echo json_encode(array("message" => "Need to POST the URL."));
  }
} else {
  http_response_code(400); // Bad request
  echo json_encode(array("message" => "Need to use POST request."));
}
?>
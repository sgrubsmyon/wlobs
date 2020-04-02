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

$stmt = $artikel->read();
$num = $stmt->rowCount();

// check if more than 0 record found
if ($num>0) {
    // artikel array
    $artikel_arr=array();
    $artikel_arr["records"]=array();

    // retrieve our table contents
    // fetch() is faster than fetchAll()
    // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        // extract row: this will allow to use the shortcut $name for $row['name']
        extract($row);

        $artikel_item=array(
            // "description" => html_entity_decode($description),
            "produktgruppen_name" => $produktgruppen_name,
            "lieferant_name" => $lieferant_name,
            "artikel_nr" => $artikel_nr,
            "artikel_name" => $artikel_name,
            "vk_preis" => $vk_preis,
            "pfand" => $pfand,
            "mwst_satz" => $mwst_satz
        );

        array_push($artikel_arr["records"], $artikel_item);
    }

    // set response code - 200 OK
    http_response_code(200);

    // show artikel data in json format
    echo json_encode($artikel_arr);
} else {

    // set response code - 404 Not found
    http_response_code(404);

    // tell the user no products found
    echo json_encode(
        array("message" => "No products found.")
    );
}
?>

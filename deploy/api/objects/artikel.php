<?php
class Artikel {
  // database connection and table name
  private $conn;
  private $table_name = "artikel";

  // these properties are only useful when we also want to create products
  // object properties
  //public $produktgruppen_name;
  //public $lieferant_name;
  //public $artikel_nr;
  //public $artikel_name;
  //public $vk_preis;
  //public $pfand;
  //public $mwst_satz;

  // constructor with $db as database connection
  public function __construct($db) {
    $this->conn = $db;
  }

  // read products
  function read_all() {
    // select all query
    $query = "SELECT
        produktgruppen_name, lieferant_name, artikel_nr, artikel_name,
        vk_preis, pfand, mwst_satz
      FROM " . $this->table_name . "
      ORDER BY produktgruppen_name, lieferant_name, artikel_nr";

    // prepare query statement
    $stmt = $this->conn->prepare($query);

    // execute query
    if ($stmt->execute()) {
      // artikel array
      $artikel_arr = array();

      $num = $stmt->rowCount();
      if ($num > 0) {
        // retrieve our table contents
        // fetch() is faster than fetchAll()
        // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
          array_push($artikel_arr, $row);
        }
      }

      return $artikel_arr;
    }

    return NULL;
  }
}
?>

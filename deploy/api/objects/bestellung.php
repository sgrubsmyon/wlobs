<?php
class Bestellung {

  // database connection and table name
  private $conn;
  private $table_name = "bestellung";

  // object properties
  //public $bestell_nr;
  //public $bestelldatum;
  public $details;

  // constructor with $db as database connection
  public function __construct($db) {
      $this->conn = $db;
  }

  // read bestellungen
  function read() {
    // select all query
    $query = "SELECT
        bestell_nr, bestelldatum,
        position, lieferant_name, artikel_nr, artikel_name, stueckzahl,
        ges_preis, ges_pfand, mwst_satz
      FROM " . $this->table_name . "
      INNER JOIN " . $this->table_name ."_details
      USING (bestell_nr)
      ORDER BY bestell_nr, position DESC";

    // prepare query statement
    $stmt = $this->conn->prepare($query);
    // execute query
    $stmt->execute();
    return $stmt;
  }

  // retrieve the current max value of bestell_nr from the server
  // because the bestellung table was locked, this is the bestell_nr of the
  // order currently being places and shall be used in other associated tables
  function max_bestell_nr() {
    $query = "SELECT MAX(bestell_nr) AS mx FROM " . $this->table_name;
    $stmt = $this->conn->prepare($query);
    if ($stmt->execute()) {
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      return $row['mx'];
    };
    return -1;
  }

  // retrieve the timestamp when order was created from SQL server
  function bestell_timestamp($bestell_nr) {
    $query = "SELECT bestelldatum FROM " . $this->table_name . "
      WHERE bestell_nr = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(1, $bestell_nr);
    if ($stmt->execute()) {
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      return $row['bestelldatum'];
    };
    return NULL;
  }

  // retrieve the details of the submitted order from SQL server
  function bestell_details($bestell_nr) {
    $query = "SELECT position, stueckzahl, lieferant_name, artikel_nr,
        artikel_name, ges_preis, ges_pfand, mwst_satz FROM " . $this->table_name . "_details
      WHERE bestell_nr = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(1, $bestell_nr);
    if ($stmt->execute()) {
      $details = array();
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        array_push($details, $row);
      }
      return $details;
    }
    return NULL;
  }

  // retrieve the details of the submitted order from SQL server
  function bestell_summe($bestell_nr) {
    $query = "SELECT IFNULL(SUM(ges_preis), 0) + IFNULL(SUM(ges_pfand), 0) AS summe FROM " . $this->table_name . "_details
      WHERE bestell_nr = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(1, $bestell_nr);
    if ($stmt->execute()) {
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      return $row['summe'];
    }
    return NULL;
  }

  // create bestellung
  function create() {
    // make sure no-one else inserts a bestellung
    // until finished so that bestell_nr is not messed up
    $this->conn->exec("LOCK TABLES " . $this->table_name . " WRITE");
    $this->conn->exec("START TRANSACTION");

    $query = "INSERT INTO " . $this->table_name . "
      SET bestelldatum = NOW()";

    // prepare query
    $stmt = $this->conn->prepare($query);

    if (!$stmt->execute()) {
      $this->conn->exec("ROLLBACK"); // things went wrong: go back to previous state
      $this->conn->exec("UNLOCK TABLES"); // make table available again in any case
      return NULL;
    }

    $bestell_nr = $this->max_bestell_nr();
    if ($bestell_nr < 0) {
      $this->conn->exec("ROLLBACK"); // things went wrong: go back to previous state
      $this->conn->exec("UNLOCK TABLES"); // make table available again in any case
      return NULL;
    }

    foreach ($this->details as $item) { // loop over the array of ordered products, sent via POST
      // Query if all data would be sent via POST:
      // $query = "INSERT INTO " . $this->table_name . "_details
      //   SET bestell_nr=:bestell_nr, position=:position, stueckzahl=:stueckzahl,
      //     lieferant_name=:lieferant_name, artikel_nr=:artikel_nr,
      //     artikel_name=:artikel_name, ges_preis=:ges_preis, ges_pfand=:ges_pfand, mwst_satz=:mwst_satz";

      // Query if only required data is sent via POST (position, stueckzahl, lieferant_name, artikel_nr):
      $query = "INSERT INTO " . $this->table_name . "_details
          (bestell_nr, position, stueckzahl, lieferant_name, artikel_nr,
          artikel_name, ges_preis, ges_pfand, mwst_satz)
          SELECT
            :bestell_nr, :position, :stueckzahl,
            lieferant_name, artikel_nr, artikel_name, :stueckzahl * vk_preis,
            :stueckzahl * pfand, mwst_satz
          FROM artikel WHERE lieferant_name = :lieferant_name AND artikel_nr = :artikel_nr";

      // prepare query
      $stmt = $this->conn->prepare($query);

      // sanitize
      $item->position = htmlspecialchars(strip_tags($item->position));
      $item->stueckzahl = htmlspecialchars(strip_tags($item->stueckzahl));
      $item->lieferant_name = htmlspecialchars(strip_tags($item->lieferant_name));
      $item->artikel_nr = htmlspecialchars(strip_tags($item->artikel_nr));

      // bind values
      $stmt->bindParam(":bestell_nr", $bestell_nr);
      $stmt->bindParam(":position", $item->position);
      $stmt->bindParam(":stueckzahl", $item->stueckzahl);
      $stmt->bindParam(":lieferant_name", $item->lieferant_name);
      $stmt->bindParam(":artikel_nr", $item->artikel_nr);

      // execute query
      if (!$stmt->execute()) {
        $this->conn->exec("ROLLBACK"); // things went wrong: go back to previous state
        $this->conn->exec("UNLOCK TABLES"); // make table available again in any case
        return NULL;
      }
    }
    $this->conn->exec("COMMIT"); // things went OK: write changes permanently to DB
    $this->conn->exec("UNLOCK TABLES"); // make table available again in any case
    return array(
      "nr" => $bestell_nr,
      "datum" => $this->bestell_timestamp($bestell_nr),
      "details" => $this->bestell_details($bestell_nr),
      "summe" => $this->bestell_summe($bestell_nr)
    );
  }

}
?>

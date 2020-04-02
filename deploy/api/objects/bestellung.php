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

  // function max_bestell_nr() {
  //   $query = "SELECT MAX(bestell_nr) AS mx FROM " . $this->table_name;
  //   $stmt = $this->conn->prepare($query);
  //   if ($stmt->execute()) {
  //     $row = $stmt->fetch(PDO::FETCH_ASSOC)
  //     return $row['mx'];
  //   };
  //   return -1;
  // }

  // create product
  function create(){
    "START TRANSACTION"
    "LOCK TABLES bestellung WRITE" // make sure no-one else inserts a bestellung
                                   // so that bestell_nr would be messed up
    $query = "INSERT INTO " . $this->table_name . "
      SET bestelldatum = NOW();"

    // prepare query
    $stmt = $this->conn->prepare($query);

    if ($stmt->execute()) {
      $bestell_nr = max_bestell_nr();
      if ($bestell_nr >= 0) {

        // $query = "INSERT INTO " . $this->table_name . "_details
        //   SET bestell_nr=:bestell_nr, position=:position, stueckzahl=:stueckzahl,
        //     lieferant_name=:lieferant_name, artikel_nr=:artikel_nr,
        //     artikel_name=:artikel_name, ges_preis=:ges_preis, ges_pfand=:ges_pfand, mwst_satz=:mwst_satz";

        $query = "INSERT INTO " . $this->table_name . "_details
          (bestell_nr, position, stueckzahl, lieferant_name, artikel_nr,
           artikel_name, ges_preis, ges_pfand, mwst_satz)
          SELECT
            (SELECT MAX(bestell_nr) FROM bestellung), :position, :stueckzahl,
            lieferant_name, artikel_nr, artikel_name, :stueckzahl * vk_preis,
            :stueckzahl * pfand, mwst_satz
          FROM artikel WHERE lieferant_name = :lieferant_name AND artikel_nr = :artikel_nr";

        // prepare query
        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->name=htmlspecialchars(strip_tags($this->name));
        $this->price=htmlspecialchars(strip_tags($this->price));
        $this->description=htmlspecialchars(strip_tags($this->description));
        $this->category_id=htmlspecialchars(strip_tags($this->category_id));
        $this->created=htmlspecialchars(strip_tags($this->created));

        // bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":created", $this->created);

        // execute query
        if ($stmt->execute()) {
            "UNLOCK TABLES" // make table available again in any case
            "COMMIT" // things went OK: write changes permanently to DB
            return true;
        }
      }
    }
    "UNLOCK TABLES" // make table available again in any case
    "ROLLBACK" // things went wrong: go back to previous state
    return false;
  }
}
?>

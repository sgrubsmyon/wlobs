<?php
class Artikel {

    // database connection and table name
    private $conn;
    private $table_name = "artikel";

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
    function read() {
        // select all query
        $query = "SELECT
            produktgruppen_name, lieferant_name, artikel_nr, artikel_name,
            vk_preis, pfand, mwst_satz
          FROM " . $this->table_name . "
          ORDER BY produktgruppen_name, lieferant_name, artikel_nr";

        // prepare query statement
        $stmt = $this->conn->prepare($query);

        // execute query
        $stmt->execute();

        return $stmt;
    }
}
?>

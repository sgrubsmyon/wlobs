<?php
class Artikel{

    // database connection and table name
    private $conn;
    private $table_name = "artikel";

    // object properties
    public $lieferant_name;
    public $artikel_nr;
    public $artikel_name;
    public $vk_preis;
    public $mwst_satz;
    public $pfand;

    // constructor with $db as database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // read products
    function read() {
        // select all query
        $query = "SELECT
          lieferant_name, artikel_nr, artikel_name, vk_preis, mwst_satz, pfand
          FROM " . $this->table_name . "
          ORDER BY
          (lieferant_name, artikel_nr) DESC";

        // prepare query statement
        $stmt = $this->conn->prepare($query);

        // execute query
        $stmt->execute();

        return $stmt;
    }
}
?>

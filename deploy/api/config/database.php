<?php
class Database{
    public $conn;

    // get the database connection
    public function getConnection(){
        // Load database credentials
        // (parse without sections)
        $credentials = parse_ini_file("../../../../../wlobs/config.ini");

        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $credentials["dbhost"] . ";dbname=" . $credentials["dbname"],
                $credentials["dbuser"], $credentials["dbpass"]
            );
            $this->conn->exec("SET NAMES utf8");
        } catch (PDOException $exception){
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
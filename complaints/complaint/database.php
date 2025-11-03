<?php
class Database {
    public $host = "localhost";
    public $username = "root";
    public $pass = "";
    public $dbname = "college_complaint_db";

    protected $conn;

    public function connect()
    {
        $this->conn = new PDO("mysql:host=$this->host;dbname=$this->dbname", $this->username, $this->pass);

        return $this->conn;
    }
}
?>
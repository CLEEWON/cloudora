<?php 
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'cloudora');

class Database {

    protected $mysqli;
    protected $query;

    public function __construct()
    {
        $this->mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if($this->mysqli->connect_errno){
            echo "Gagal konek ke database : " . $this->mysqli->connect_error;
        }
    }

    public function table($table)
    {

        $this->query = "SELECT * FROM $table";
        return $this;
    }

    public function where($arr = [])
    {
        $sql = ' WHERE ';

        if (count($arr) == 1){
            foreach ($arr as $key => $value) {
                $sql .= $key . ' = ' . $value;
            }
        }
        else {
            foreach ($arr as $key => $value) {
                $sql .= $key . " = '" . $value . "' AND ";
            }
            $sql = substr($sql, 0, -5);
        }

        $this->query .= $sql;
        return $this;
    }

    public function get(){

        $result = $this->mysqli->query($this->query);
        return $result->fetch_all(MYSQLI_ASSOC);

	// lihat query
        // echo $this->query;
    }

    public function connection()
    {
        return $this->mysqli;
    }

}

if (!isset($conn)) {
    $db = new Database();
    $conn = $db->connection();
}
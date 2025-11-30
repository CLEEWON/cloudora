<?php
require_once __DIR__ . '/../auth/config.php';

class Database {

    protected $mysqli;
    protected $query;

    public function __construct()
    {
        // Jika DB_PORT tidak ada di config, pakai default port 3306
        $port = defined('DB_PORT') ? DB_PORT : 3306;

        $this->mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, $port);

        if ($this->mysqli->connect_errno) {
            error_log("Database connection failed: " . $this->mysqli->connect_error);
            die("Connection failed: " . $this->mysqli->connect_error);
        }

        $this->mysqli->set_charset("utf8mb4");
    }

    public function table($table)
    {
        $this->query = "SELECT * FROM `$table`";
        return $this;
    }

    public function where($arr = [])
    {
        if (empty($arr)) return $this;

        $sql = " WHERE ";
        $conditions = [];

        foreach ($arr as $key => $value) {
            $value = $this->mysqli->real_escape_string($value);
            $conditions[] = "`$key` = '$value'";
        }

        $sql .= implode(" AND ", $conditions);

        $this->query .= $sql;
        return $this;
    }

    public function get()
    {
        $result = $this->mysqli->query($this->query);

        if (!$result) {
            die("Query error: " . $this->mysqli->error);
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function connection()
    {
        return $this->mysqli;
    }

    public function prepare($query)
    {
        return $this->mysqli->prepare($query);
    }

    public function real_escape_string($string)
    {
        return $this->mysqli->real_escape_string($string);
    }
}

if (!isset($conn)) {
    $db = new Database();
    $conn = $db->connection();
}

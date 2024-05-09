
<?php

class Conexion
{
    private $host;
    private $userName;
    private $password;
    private $db;
    protected $conn;
    protected $configFile = "./config/db.csv";

    public function __construct()
    {
        $this->connect();
    }

    public function __destruct()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }

    public function connect()
    {
        $configFile = fopen($this->configFile, "r") or die("Unable to open file!");
        try {
            if (!feof($configFile)) {
                $connData = fgetcsv($configFile);
                $this->host = $connData[0];
                $this->userName = $connData[1];
                $this->password = $connData[2];
                $this->db = $connData[3];
                $this->conn = new mysqli($this->host, $this->userName, $this->password, $this->db);
                if ($this->conn->connect_error) {
                    throw new Exception("Connection failed: " . $this->conn->connect_error);
                }
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        } finally {
            fclose($configFile);
        }
    }
}
?>
<?php
class Database
{
    private $hostname = 'localhost', $database = 'dbname', $username = 'root', $password = '';
    private $conn;

    public function __construct()
    {
    	$this->conn = new PDO("mysql:host=$this->hostname;dbname=$this->database", $this->username, $this->password);
    }

    public function connect(string $hostname, string $database, string $username, string $passsword)
    {
        $this->hostname = $hostname;
        $this->database = $database;
        $this->username = $username;
        $this->password = $passsword;
        $this->conn = new PDO("mysql:host=$this->hostname;dbname=$this->database", $this->username, $this->password);
    }

    /**
     * Execute a query
     * @param string $query
     * @param array  $placeholders
     * @example executeQuery("INSERT INTO users (name, email) VALUES (:name, :email)", array(':name' => ['Jeff', PDO::PARAM_STR]))
     * @return bool|PDOStatement
     */
    protected function executeQuery(string $query, array $placeholders)
    {
        $stmt = $this->conn->prepare($query);
        foreach ($placeholders as $key => $value) {
            $stmt->bindParam($key, $value[0], $value[1]);
        }
        $stmt->execute();
        return $stmt;
    }

}
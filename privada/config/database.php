<?php
/**
 * Configuración de la base de datos
 */

class Database {
    private $host = 'localhost';
    private $dbname = 'bd_hospedajes';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';
    
    public $db;
    
    public function __construct() {
        $this->connect();
    }
    
    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->db = new PDO($dsn, $this->username, $this->password, $options);
        } catch(PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }
    
    public function getConnection() {
        return $this->db;
    }
}

// Función para compatibilidad con código existente
function getDatabaseConnection() {
    static $db = null;
    if ($db === null) {
        $database = new Database();
        $db = $database->getConnection();
    }
    return $db;
}
?>

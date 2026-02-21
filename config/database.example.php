<?php
// Configuración de base de datos (usa variables de entorno; en producción Railway inyecta DB_*)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'tienda_3d');
define('DB_PORT', getenv('DB_PORT') !== false && getenv('DB_PORT') !== '' ? (int) getenv('DB_PORT') : null);

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            if (DB_PORT !== null) {
                $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
            } else {
                $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            }
            
            if ($this->connection->connect_error) {
                throw new Exception("Error de conexión: " . $this->connection->connect_error);
            }
            
            $this->connection->set_charset("utf8mb4");
        } catch (Exception $e) {
            die("Error de base de datos: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function query($sql) {
        return $this->connection->query($sql);
    }

    public function prepare($sql) {
        return $this->connection->prepare($sql);
    }

    public function escape($string) {
        return $this->connection->real_escape_string($string);
    }

    public function getLastInsertId() {
        return $this->connection->insert_id;
    }
}

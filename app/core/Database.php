<?php
namespace Barkios\core;
use PDO;
use PDOException;

abstract class Database {

    private static $instance = null;
    protected $db;

    protected function __construct() {
        try {
            $this->db = new PDO(
                'mysql:host=barkios-db;dbname=barkios_db;charset=utf8',
                'barkios_admin',
                'barkios_pass123',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {

            die("Error de conexión: " . $e->getMessage());
        }
    }

    abstract public static function getInstance();


    public function getConnection() {
        return $this->db;
    }
}
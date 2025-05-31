<?php
namespace Barkios\core;
use PDO;
use PDOException;

/**
 * Clase Database
 * Implementa el patrón Singleton para la conexión PDO a la base de datos.
 */
class Database {
    /** @var Database Instancia única de la clase */
    private static $instance = null;
    /** @var PDO Conexión PDO */
    private $pdo;

    /**
     * Constructor privado: inicializa la conexión PDO.
     */
    private function __construct() {
        try {
            $this->pdo = new PDO(
                'mysql:host=barkios-db;dbname=barkios_db;charset=utf8',
                'barkios_admin',
                'barkios_pass123',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            // Manejo de error de conexión
            die("Error de conexión: " . $e->getMessage());
        }
    }

    /**
     * Devuelve la instancia única de Database.
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Devuelve la conexión PDO.
     * @return PDO
     */
    public function getConnection() {
        return $this->pdo;
    }
}
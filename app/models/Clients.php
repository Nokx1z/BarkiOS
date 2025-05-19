<?php
require_once __DIR__.'/../core/Database.php';

class Clients {
    private $db;

    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }

    // Obtener todos los productos
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM clientes");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Agregar nuevo producto
    public function add($cedula, $nombre, $direccion, $telefono, $membresia) {
        $stmt = $this->db->prepare("
            INSERT INTO clientes (cedula, nombre, direccion, telefono, membresia)
            VALUES (:cedula, :nombre, :direccion, :telefono, :membresia)
        ");
        return $stmt->execute([
            ':cedula' => $cedula,
            ':nombre' => $nombre,
            ':direccion' => $direccion,
            ':telefono' => $telefono,
            ':membresia' => $membresia
        ]);
    }
public function truncate() {
    $stmt = $this->db->prepare("TRUNCATE TABLE clientes");
    return $stmt->execute();
}

    // Eliminar producto por ID
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM clientes WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
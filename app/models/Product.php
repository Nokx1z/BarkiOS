<?php
require_once __DIR__.'/../core/Database.php';

class Product {
    private $db;

    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }

    // Obtener todos los productos
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM productos");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Agregar nuevo producto
    public function add($nombre, $categoria, $precio) {
        $codigo = uniqid('BARKI-');
        $stmt = $this->db->prepare("
            INSERT INTO productos (codigo, nombre, categoria, precio)
            VALUES (:codigo, :nombre, :categoria, :precio)
        ");
        return $stmt->execute([
            ':codigo' => $codigo,
            ':nombre' => $nombre,
            ':categoria' => $categoria,
            ':precio' => $precio
        ]);
    }
public function truncate() {
    $stmt = $this->db->prepare("TRUNCATE TABLE productos");
    return $stmt->execute();
}

    // Eliminar producto por ID
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM productos WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
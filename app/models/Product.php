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

    // Verificar si un producto existe
    public function productExists($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM productos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetchColumn() > 0;
    }

    // Obtener un producto por ID
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM productos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Agregar nuevo producto
    public function add($id, $nombre, $tipo, $categoria, $precio) {
        if ($this->productExists($id)) {
            throw new Exception("Ya existe un producto con este ID");
        }

        $stmt = $this->db->prepare("
            INSERT INTO productos (id, nombre, tipo, categoria, precio)
            VALUES (:id, :nombre, :tipo, :categoria, :precio)
        ");
        return $stmt->execute([
            ':id' => $id,
            ':nombre' => $nombre,
            ':tipo' => $tipo,
            ':categoria' => $categoria,
            ':precio' => $precio
        ]);
    }

    // Actualizar producto existente
    public function update($id, $nombre, $tipo, $categoria, $precio) {
        if (!$this->productExists($id)) {
            throw new Exception("No existe un producto con este ID");
        }

        $stmt = $this->db->prepare("
            UPDATE productos 
            SET nombre = :nombre, 
                tipo = :tipo, 
                categoria = :categoria, 
                precio = :precio 
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id' => $id,
            ':nombre' => $nombre,
            ':tipo' => $tipo,
            ':categoria' => $categoria,
            ':precio' => $precio
        ]);
    }

    // Eliminar producto por ID
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM productos WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
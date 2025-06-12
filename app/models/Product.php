<?php
// filepath: c:\xampp\htdocs\BarkiOS\app\models\Product.php
namespace Barkios\models;
use Barkios\core\Database;
use PDO;
use Exception;

/**
 * Modelo Product
 * 
 * Proporciona métodos para gestionar productos en la base de datos,
 * incluyendo operaciones CRUD y utilidades de consulta.
 */
class Product extends Database {
    /**
     * Obtiene todos los productos registrados en la base de datos.
     * 
     * @return array Lista de productos (cada producto es un array asociativo).
     */
    public function getAll() {
        try {
            $stmt = $this->db->query("SELECT * FROM productos ORDER BY id ASC");
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Verifica si un producto existe por su ID.
     * 
     * @param int $id ID del producto.
     * @return bool True si existe, false si no.
     */
    public function productExists($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM productos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Obtiene un producto por su ID.
     * 
     * @param int $id ID del producto.
     * @return array|null Array asociativo con los datos del producto o null si no existe.
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM productos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Agrega un nuevo producto a la base de datos.
     * 
     * @param int $id
     * @param string $nombre
     * @param string $tipo
     * @param string $categoria
     * @param float $precio
     * @return bool True si se insertó correctamente, false en caso contrario.
     * @throws Exception Si el producto ya existe.
     */
    public function add($id, $nombre, $tipo, $categoria, $precio) {
        if ($this->productExists($id)) throw new Exception("Ya existe un producto con este ID");
        $stmt = $this->db->prepare("INSERT INTO productos (id, nombre, tipo, categoria, precio) VALUES (:id, :nombre, :tipo, :categoria, :precio)");
        return $stmt->execute([
            ':id' => $id,
            ':nombre' => $nombre,
            ':tipo' => $tipo,
            ':categoria' => $categoria,
            ':precio' => $precio
        ]);
    }

    /**
     * Actualiza un producto existente.
     * 
     * @param int $id
     * @param string $nombre
     * @param string $tipo
     * @param string $categoria
     * @param float $precio
     * @return bool True si se actualizó correctamente, false en caso contrario.
     * @throws Exception Si el producto no existe.
     */
    public function update($id, $nombre, $tipo, $categoria, $precio) {
        if (!$this->productExists($id)) throw new Exception("No existe un producto con este ID");
        $stmt = $this->db->prepare("UPDATE productos SET nombre = :nombre, tipo = :tipo, categoria = :categoria, precio = :precio WHERE id = :id");
        return $stmt->execute([
            ':id' => $id,
            ':nombre' => $nombre,
            ':tipo' => $tipo,
            ':categoria' => $categoria,
            ':precio' => $precio
        ]);
    }

    /**
     * Elimina un producto por su ID.
     * 
     * @param int $id ID del producto a eliminar.
     * @return bool True si se eliminó correctamente, false en caso contrario.
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM productos WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
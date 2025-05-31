<?php
namespace Barkios\models;

use Barkios\core\Database;
use PDO;
use Exception;
use PDOException;

/**
 * Modelo Product
 * 
 * Proporciona métodos para gestionar productos en la base de datos,
 * incluyendo operaciones CRUD y utilidades de consulta.
 */
class Product {
    /** @var PDO Conexión a la base de datos */
    private $db;

    /**
     * Constructor.
     * Inicializa la conexión a la base de datos usando el singleton Database.
     */
    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }

    /**
     * Obtiene todos los productos registrados en la base de datos.
     * 
     * @return array Lista de productos (cada producto es un array asociativo).
     */
    public function getAll() {
        try {
            // Verifica si la conexión está activa
            if (!$this->db) {
                error_log('[' . date('Y-m-d H:i:s') . '] Error: No hay conexión a la base de datos');
                return [];
            }
            // Verifica si la tabla existe
            $tableExists = $this->db->query("SHOW TABLES LIKE 'productos'")->rowCount() > 0;
            if (!$tableExists) {
                error_log('[' . date('Y-m-d H:i:s') . '] Error: La tabla "productos" no existe en la base de datos');
                return [];
            }
            // Ejecuta la consulta
            $stmt = $this->db->query("SELECT * FROM productos ORDER BY id ASC");
            if ($stmt === false) {
                $error = $this->db->errorInfo();
                error_log('[' . date('Y-m-d H:i:s') . '] Error en la consulta SQL: ' . print_r($error, true));
                return [];
            }
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log('[' . date('Y-m-d H:i:s') . '] Productos encontrados: ' . count($result));
            return $result;
        } catch (PDOException $e) {
            error_log('[' . date('Y-m-d H:i:s') . '] Error en Product::getAll(): ' . $e->getMessage());
            error_log('[' . date('Y-m-d H:i:s') . '] Archivo: ' . $e->getFile() . ' Línea: ' . $e->getLine());
            return [];
        } catch (Exception $e) {
            error_log('[' . date('Y-m-d H:i:s') . '] Error inesperado en Product::getAll(): ' . $e->getMessage());
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
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
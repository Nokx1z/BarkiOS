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
        try {
            // Verificar si la conexión está activa
            if (!$this->db) {
                error_log('[' . date('Y-m-d H:i:s') . '] Error: No hay conexión a la base de datos');
                return [];
            }
            
            // Verificar si la tabla existe
            $tableExists = $this->db->query("SHOW TABLES LIKE 'productos'")->rowCount() > 0;
            if (!$tableExists) {
                error_log('[' . date('Y-m-d H:i:s') . '] Error: La tabla "productos" no existe en la base de datos');
                return [];
            }
            
            // Ejecutar consulta
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
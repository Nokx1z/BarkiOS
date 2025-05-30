<?php
namespace Barkios\models;
use Barkios\core\Database;


use PDO;
use Exception;
use PDOException;
//require_once __DIR__ . '/../core/Database.php';

class Supplier {
    private $db;

    public function __construct()
    {
        $database = Database::getInstance();
        $this->db = $database->getConnection();   
    }
    // Obtener todos los proveedores
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM proveedores");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function supplierExists($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM proveedores WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetchColumn() > 0;
    }

    // Agregar nuevo proveedor
    public function add($id, $nombre_contacto, $nombre_empresa, $direccion, $tipo_rif) {
        if ($this->supplierExists($id)) {
            throw new Exception("Ya existe un proveedor con este RIF");
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO proveedores (id, nombre_contacto, nombre_empresa, direccion, tipo_rif)
                VALUES (:id, :nombre_contacto, :nombre_empresa, :direccion, :tipo_rif)
            ");
            return $stmt->execute([
                ':id' => $id,
                ':nombre_contacto' => $nombre_contacto,
                ':nombre_empresa' => $nombre_empresa,
                ':direccion' => $direccion,
                ':tipo_rif' => $tipo_rif
            ]);
        } catch (PDOException $e) {
            error_log('Error al agregar proveedor: ' . $e->getMessage());
            return false;
        }
    }

    // Eliminar proveedor por ID
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM proveedores WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
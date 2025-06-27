<?php
namespace Barkios\models;
use Barkios\core\Database;
use PDO;
use Exception;
use PDOException;


class Supplier extends Database{

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM proveedores");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

  
    public function supplierExists($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM proveedores WHERE proveedor_rif = :proveedor_rif");
        $stmt->execute([':id' => $id]);
        return $stmt->fetchColumn() > 0;
    }

 
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM proveedores WHERE proveedor_rif = :proveedor_rif");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    
    public function add($id, $nombre_contacto, $nombre_empresa, $direccion, $tipo_rif) {
        if ($this->supplierExists($id)) {
            throw new Exception("Ya existe un proveedor con este RIF");
        }
        try {
            $stmt = $this->db->prepare("
                INSERT INTO proveedores (proveedor_rif, nombre_empresa, nombre_contacto, telefono)
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


    public function update($id, $nombre_contacto, $nombre_empresa, $direccion, $tipo_rif) {
        if (!$this->supplierExists($id)) {
            throw new Exception("No existe un proveedor con este RIF");
        }
        $stmt = $this->db->prepare("
            UPDATE proveedores
            SET nombre_contacto = :nombre_contacto,
                nombre_empresa = :nombre_empresa,
                direccion = :direccion,
                tipo_rif = :tipo_rif
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id' => $id,
            ':nombre_contacto' => $nombre_contacto,
            ':nombre_empresa' => $nombre_empresa,
            ':direccion' => $direccion,
            ':tipo_rif' => $tipo_rif
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM proveedores WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}

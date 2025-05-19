<?php
require_once __DIR__ . '/../core/Database.php';

class SupplierModel {

    private function getDbConnection() {
        $database = Database::getInstance();
        return $database->getConnection();
    }

    // Obtener todos los proveedores
    public function getAll() {
        $db = $this->getDbConnection();
        $stmt = $db->query("SELECT * FROM proveedores");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Agregar nuevo proveedor
    public function add($tipo_rif, $rif, $direccion, $nombre_empresa, $nombre_contacto) {
        $db = $this->getDbConnection();
        $stmt = $db->prepare("
            INSERT INTO proveedores (tipo_rif, rif, direccion, nombre_empresa, nombre_contacto)
            VALUES (:tipo_rif, :rif, :direccion, :nombre_empresa, :nombre_contacto)
        ");
        return $stmt->execute([
            ':tipo_rif' => $tipo_rif,
            ':rif' => $rif,
            ':direccion' => $direccion,
            ':nombre_empresa' => $nombre_empresa,
            ':nombre_contacto' => $nombre_contacto
        ]);
    }

    // Vaciar la tabla de proveedores
    public function truncate() {
        $db = $this->getDbConnection();
        $stmt = $db->prepare("TRUNCATE TABLE proveedores");
        return $stmt->execute();
    }

    // Eliminar proveedor por ID
    public function delete($id) {
        $db = $this->getDbConnection();
        $stmt = $db->prepare("DELETE FROM proveedores WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
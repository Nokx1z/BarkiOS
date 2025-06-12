<?php
namespace Barkios\models;
use Barkios\core\Database;
use PDO;
use Exception;
use PDOException;

/**
 * Modelo Supplier
 * Encapsula la lógica de acceso a datos para la entidad Proveedor.
 */
class Supplier extends Database{
    /**
     * Obtiene todos los proveedores de la base de datos.
     * @return array Lista de proveedores.
     * 
     */
    

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM proveedores");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica si existe un proveedor por su ID (RIF).
     * @param int $id RIF del proveedor.
     * @return bool True si existe, false si no.
     */
    public function supplierExists($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM proveedores WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetchColumn() > 0;
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM proveedores WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Agrega un nuevo proveedor a la base de datos.
     * @param int $id RIF del proveedor.
     * @param string $nombre_contacto Nombre del contacto.
     * @param string $nombre_empresa Nombre de la empresa.
     * @param string $direccion Dirección del proveedor.
     * @param string $tipo_rif Tipo de RIF (J/G/C).
     * @return bool True si se insertó correctamente, false si hubo error.
     * @throws Exception Si el proveedor ya existe.
     */

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

    /**
     * Elimina un proveedor por su ID (RIF).
     * @param int $id RIF del proveedor.
     * @return bool True si se eliminó correctamente.
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM proveedores WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Actualiza los datos de un proveedor existente
     * 
     * @param int $id ID del proveedor a actualizar
     * @param string $nombre Nuevo nombre
     * @param string $direccion Nueva dirección
     * @param string $telefono Nuevo teléfono
     * @param string $email Nuevo email
     * @return bool True si se actualizó correctamente
     * @throws Exception Si el proveedor no existe o hay un error en la actualización
     */
    public function update($id, $nombre, $direccion, $telefono, $email) {
        if (!$this->supplierExists($id)) {
            throw new \Exception("No existe un proveedor con este ID");
        }
        $stmt = $this->db->prepare("
            UPDATE proveedores
            SET nombre = :nombre,
                direccion = :direccion,
                telefono = :telefono,
                email = :email
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id' => $id,
            ':nombre' => $nombre,
            ':direccion' => $direccion,
            ':telefono' => $telefono,
            ':email' => $email
        ]);
    }
}

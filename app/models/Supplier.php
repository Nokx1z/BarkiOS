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
class Supplier {
    /** @var PDO Instancia de conexión a la base de datos */
    private $db;

    /**
     * Constructor: inicializa la conexión a la base de datos.
     */
    public function __construct()
    {
        $database = Database::getInstance();
        $this->db = $database->getConnection();   
    }

    /**
     * Obtiene todos los proveedores de la base de datos.
     * @return array Lista de proveedores.
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
}
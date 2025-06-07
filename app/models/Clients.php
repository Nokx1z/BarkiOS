<?php
namespace Barkios\models;
use Barkios\core\Database;
use PDO;
use Exception;

/**
 * Clase Clients
 * 
 * Gestiona todas las operaciones relacionadas con los clientes en la base de datos.
 * Incluye funcionalidades para crear, leer, actualizar y eliminar clientes.
 */

class Clients extends Database{
    /**
     * Obtiene todos los clientes de la base de datos
     * 
     * @return array Lista de clientes con sus datos
     */
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM clientes");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica si un cliente existe por su cédula
     * 
     * @param string $cedula Cédula del cliente a verificar
     * @return bool True si el cliente existe, false en caso contrario
     */
    public function clientExists($cedula) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM clientes WHERE cedula = :cedula");
        $stmt->execute([':cedula' => $cedula]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Agrega un nuevo cliente a la base de datos
     * 
     * @param string $cedula Cédula del cliente
     * @param string $nombre Nombre completo del cliente
     * @param string $direccion Dirección del cliente
     * @param string $telefono Número de teléfono
     * @param string $membresia Tipo de membresía (regular/vip)
     * @return bool True si se agregó correctamente
     * @throws Exception Si el cliente ya existe o hay un error en la inserción
     */
    public function add($cedula, $nombre, $direccion, $telefono, $membresia) {
        if ($this->clientExists($cedula)) {
            throw new Exception("Ya existe un cliente con esta cédula");
        }

        $stmt = $this->db->prepare("
            INSERT INTO clientes (cedula, nombre, direccion, telefono, membresia)
            VALUES (:cedula, :nombre, :direccion, :telefono, :membresia)
        ");
        
        $result = $stmt->execute([
            ':cedula' => $cedula,
            ':nombre' => $nombre,
            ':direccion' => $direccion,
            ':telefono' => $telefono,
            ':membresia' => $membresia
        ]);

        if (!$result) {
            throw new Exception("Error al agregar el cliente");
        }

        return true;
    }
    /**
     * Elimina un cliente por su cédula
     * 
     * @param string $cedula Cédula del cliente a eliminar
     * @return bool True si se eliminó correctamente
     */
    public function delete($cedula) {
        $stmt = $this->db->prepare("DELETE FROM clientes WHERE cedula = :cedula");
        return $stmt->execute([':cedula' => $cedula]);
    }
}
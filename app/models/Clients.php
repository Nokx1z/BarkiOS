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
    /**
     * Actualiza los datos de un cliente existente
     * 
     * @param string $cedula Cédula del cliente a actualizar
     * @param string $nombre Nuevo nombre
     * @param string $direccion Nueva dirección
     * @param string $telefono Nuevo teléfono
     * @param string $membresia Nueva membresía
     * @return bool True si se actualizó correctamente
     * @throws Exception Si el cliente no existe o hay un error en la actualización
     */
    public function update($cedula, $nombre, $direccion, $telefono, $membresia) {
        if (!$this->clientExists($cedula)) throw new Exception("No existe un cliente con esta cédula");
        $stmt = $this->db->prepare("UPDATE clientes SET nombre = :nombre, direccion = :direccion, telefono = :telefono, membresia = :membresia WHERE cedula = :cedula");
        return $stmt->execute([
            ':cedula' => $cedula,
            ':nombre' => $nombre,
            ':direccion' => $direccion,
            ':telefono' => $telefono,
            ':membresia' => $membresia
        ]);
    }

    /**
     * Obtiene un cliente por su cédula
     * 
     * @param string $cedula Cédula del cliente a obtener
     * @return array|null Datos del cliente o null si no existe
     */
    public function getById($cedula) {
        $stmt = $this->db->prepare("SELECT * FROM clientes WHERE cedula = :cedula");
        $stmt->execute([':cedula' => $cedula]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
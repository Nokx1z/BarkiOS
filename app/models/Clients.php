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
class Clients extends Database {
    /**
     * Obtiene todos los productos registrados en la base de datos.
     * 
     * @return array Lista de productos (cada producto es un array asociativo).
     */
    public function getAll() {
        try {
            $stmt = $this->db->query("SELECT * FROM clientes ORDER BY cliente_ced ASC");
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
    public function clientExists($cedula) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM clientes WHERE cliente_ced = :cliente_ced");
        $stmt->execute([':cliente_ced' => $cedula]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Obtiene un producto por su ID.
     * 
     * @param int $id ID del producto.
     * @return array|null Array asociativo con los datos del producto o null si no existe.
     */
    public function getById($cedula) {
        $stmt = $this->db->prepare("SELECT * FROM clientes WHERE cliente_ced = :cliente_ced");
        $stmt->execute([':cliente_ced' => $cedula]);
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
    public function add($cedula, $nombre, $direccion, $telefono, $membresia) {
        if ($this->clientExists($cedula)) {
            throw new Exception("Ya existe un cliente con esta cédula");
        }

        $stmt = $this->db->prepare("
            INSERT INTO clientes (cliente_ced, nombre_cliente, direccion, telefono, tipo)
            VALUES (:cliente_ced, :nombre_cliente, :direccion, :telefono, :tipo)
        ");

        return $stmt->execute([
            ':cliente_ced' => $cedula,
            ':nombre_cliente' => $nombre,
            ':direccion' => $direccion,
            ':telefono' => $telefono,
            ':tipo' => $membresia
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
    public function update($cedula, $nombre, $direccion, $telefono, $membresia) {
        if (!$this->clientExists($cedula)) throw new Exception("No existe un cliente con esta cedula");
        $stmt = $this->db->prepare("UPDATE clientes SET nombre_cliente = :nombre_cliente, direccion = :direccion, telefono = :telefono, tipo = :tipo WHERE cedula = :cedula");
        return $stmt->execute([
            ':cliente_ced' => $cedula,
            ':nombre_cliente' => $nombre,
            ':direccion' => $direccion,
            ':telefono' => $telefono,
            ':tipo' => $membresia
        ]);
    }

    /**
     * Elimina un producto por su ID.
     * 
     * @param int $id ID del producto a eliminar.
     * @return bool True si se eliminó correctamente, false en caso contrario.
     */
    public function delete($cedula) {
        $stmt = $this->db->prepare("DELETE FROM clientes WHERE cedula = :cedula");
        return $stmt->execute([':cedula' => $cedula]);
    }
}
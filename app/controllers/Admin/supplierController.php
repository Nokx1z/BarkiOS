<?php
namespace Barkios\controllers\Admin;
use Barkios\models\Supplier;
use Exception;
//require_once __DIR__.'/../../models/Supplier.php';

class SupplierController {
    private $supplierModel;

    public function __construct() {
        $this->supplierModel = new Supplier();
    }

    public function handleRequest() {
        // Habilitar reporte de errores
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        $action = $_GET['action'] ?? '';
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        // Registrar la solicitud
        error_log("Solicitud recibida - Acción: $action, Método: " . $_SERVER['REQUEST_METHOD'] . ", AJAX: " . ($isAjax ? 'Sí' : 'No'));
        
        try {
            // Manejar solicitudes AJAX
            if ($isAjax) {
                header('Content-Type: application/json');
                
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add_ajax') {
                    error_log("Invocando handleAddSupplierAjax");
                    $this->handleAddSupplierAjax();
                } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete_ajax') {
                    error_log("Invocando handleDeleteSupplierAjax");
                    $this->handleDeleteSupplierAjax();
                } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get_suppliers') {
                    error_log("Invocando getSuppliersAjax");
                    $this->getSuppliersAjax();
                } else {
                    error_log("Acción AJAX no reconocida: $action");
                    throw new Exception('Acción no válida');
                }
            } else {
                // Manejar solicitudes de página regulares
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
                    $this->handleAddSupplier();
                } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete') {
                    $this->handleDeleteSupplier();
                }
            }
        } catch (Exception $e) {
            $errorMsg = 'Error en handleRequest: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine();
            error_log($errorMsg);
            
            if ($isAjax) {
                http_response_code(500);
                echo json_encode([
                    'success' => false, 
                    'message' => 'Error en el servidor: ' . $e->getMessage(),
                    'debug' => [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ]
                ]);
                exit();
            } else {
                die("Error: " . $e->getMessage());
            }
        }
    }

    public function getSupplierr() {
        return $this->supplierModel->getAll();
    }

    private function handleAddSupplierAjax() {
        try {
            $required = ['id', 'nombre_contacto', 'nombre_empresa', 'direccion', 'tipo_rif'];
            $data = [];
            
            // Validar campos requeridos
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("El campo $field es requerido");
                }
                $data[$field] = trim($_POST[$field]);
            }

            // Validar formato del RIF
            $rif = (int) $data['id'];
            if (strlen($data['id']) !== 9) {
                throw new Exception("El RIF debe tener exactamente 9 dígitos");
            }
            
            // Verificar si el proveedor ya existe
            if ($this->supplierModel->supplierExists($rif)) {
                throw new Exception('El RIF ingresado ya está registrado.');
            }

            // Agregar el proveedor
            $result = $this->supplierModel->add(
                $rif,
                $data['nombre_contacto'],
                $data['nombre_empresa'],
                $data['direccion'],
                $data['tipo_rif']
            );

            if ($result === false) {
                throw new Exception('No se pudo agregar el proveedor. Inténtalo de nuevo.');
            }

            echo json_encode([
                'success' => true,
                'message' => 'Proveedor agregado correctamente'
            ]);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit();
    }

    private function handleDeleteSupplierAjax() {
        try {
            if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
                throw new Exception('ID de proveedor inválido');
            }

            $id = (int)$_POST['id'];
            
            // Verificar si el proveedor existe antes de intentar eliminarlo
            if (!$this->supplierModel->supplierExists($id)) {
                throw new Exception('El proveedor que intentas eliminar no existe');
            }
            
            $success = $this->supplierModel->delete($id);

            if (!$success) {
                throw new Exception('No se pudo eliminar el proveedor. Inténtalo de nuevo.');
            }

            echo json_encode([
                'success' => true,
                'message' => 'Proveedor eliminado correctamente'
            ]);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit();
    }

    private function getSuppliersAjax() {
        try {
            $suppliers = $this->supplierModel->getAll();
            if ($suppliers === false) {
                throw new Exception('Error al cargar los proveedores');
            }
            echo json_encode($suppliers);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit();
    }

    private function handleAddSupplier() {
        $required = ['id', 'nombre_contacto', 'nombre_empresa', 'direccion', 'tipo_rif'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("El campo $field es requerido");
            }
        }

        $rif = (int) $_POST['id'];
        $nombre_contacto = htmlspecialchars(trim($_POST['nombre_contacto']));
        $nombre_empresa = htmlspecialchars(trim($_POST['nombre_empresa']));
        $direccion = htmlspecialchars(trim($_POST['direccion']));
        $tipo_rif = htmlspecialchars(trim($_POST['tipo_rif']));

        // ✅ Verifica si ya existe antes de insertar
        if ($this->supplierModel->supplierExists($rif)) {
            header("Location: supplier-admin.php?error=rif_duplicado&rif=" . urlencode($rif));
            exit();
        }

        // ✅ Si no existe, lo insertas
        $success = $this->supplierModel->add($rif, $nombre_contacto, $nombre_empresa, $direccion, $tipo_rif);

        if ($success) {
            header("Location: supplier-admin.php?success=add");
            exit();
        }
    }

    private function handleDeleteSupplier() {
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            throw new Exception("ID de producto inválido");
        }

        $success = $this->supplierModel->delete((int)$_GET['id']);

        if ($success) {
        header('Location: supplier-admin.php?success=delete');
            exit();
        }
    }
}

// Initialize the controller and handle the request
$controller = new SupplierController();
$controller->handleRequest();

// Only set $supplierr if this is a regular page load (not AJAX)
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    $supplierr = $controller->getSupplierr();
}
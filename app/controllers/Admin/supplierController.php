<?php
use Barkios\models\Supplier;

$supplierModel = new Supplier();

error_reporting(E_ALL);
ini_set('display_errors', 1);

handleRequest($supplierModel);
/**
 * Acción principal: muestra la vista de administración de proveedores.
 */
function index() {
    require __DIR__ . '/../../views/admin/supplier-admin.php';
}

function handleRequest($supplierModel) {
    $action = $_GET['action'] ?? '';
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
             strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    
    // Log de la solicitud recibida
    error_log("Solicitud recibida - Acción: $action, Método: " . $_SERVER['REQUEST_METHOD'] . ", AJAX: " . ($isAjax ? 'Sí' : 'No'));
    
    try {
        // Solicitudes AJAX
        if ($isAjax) {
            header('Content-Type: application/json');
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add_ajax') {
                error_log("Invocando handleAddSupplierAjax");
                handleAddSupplierAjax($supplierModel);
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete_ajax') {
                error_log("Invocando handleDeleteSupplierAjax");
                handleDeleteSupplierAjax($supplierModel);
            } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get_suppliers') {
                error_log("Invocando getSuppliersAjax");
                getSuppliersAjax($supplierModel);
            } else {
                error_log("Acción AJAX no reconocida: $action");
                throw new Exception('Acción no válida');
            }
        } else {
            // Solicitudes de página normales
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
                handleAddSupplier($supplierModel);
            } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete') {
                handleDeleteSupplier($supplierModel);
            }
        }
    } catch (Exception $e) {
        // Manejo de errores global
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

/**
 * Devuelve todos los proveedores (para uso interno y vistas).
 * @return array
 */
function getSupplierr($supplierModel) {
    return $supplierModel->getAll();;
}

/**
 * Maneja la adición de un proveedor desde formulario regular.
 * Redirige según éxito o error.
 * @throws Exception Si falta algún campo o hay duplicado.
 */
function handleAddSupplier($supplierModel) {
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
    // Verifica duplicados
    if ($supplierModel->supplierExists($rif)) {
        header("Location: supplier-admin.php?error=rif_duplicado&rif=" . urlencode($rif));
        exit();
    }
    // Inserta proveedor
    $success = $supplierModel->add($rif, $nombre_contacto, $nombre_empresa, $direccion, $tipo_rif);
    if ($success) {
        header("Location: supplier-admin.php?success=add");
        exit();
    }
}

/**
 * Maneja la eliminación de un proveedor por GET.
 * @throws Exception Si el ID es inválido.
 */
function handleDeleteSupplier($supplierModel) {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception("ID de producto inválido");
    }
    $success = $supplierModel->delete((int)$_GET['id']);
    if ($success) {
        header('Location: supplier-admin.php?success=delete');
        exit();
    }
}
/**
 * Alias público para agregar proveedor vía AJAX.
 */
function add_ajax($supplierModel) {
    $supplierModel->handleAddSupplierAjax();
}

/**
 * Alias público para eliminar proveedor vía AJAX.
 */
function  delete_ajax($supplierModel){
    $supplierModel->handleDeleteSupplierAjax();
}

/**
 * Maneja la adición de proveedor vía AJAX.
 * Valida campos y responde en JSON.
 */
function handleAddSupplierAjax($supplierModel) {
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
        
        // Verificar duplicado
        if ($supplierModel->supplierExists($rif)) {
            throw new Exception('El RIF ingresado ya está registrado.');
        }
        // Agregar proveedor
        $result = $supplierModel->add(
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

    /**
     * Maneja la eliminación de proveedor vía AJAX.
     * Valida existencia y responde en JSON.
     */
function handleDeleteSupplierAjax($supplierModel) {
    try {
        if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
            throw new Exception('ID de proveedor inválido');
        }
        $id = (int)$_POST['id'];
        
        // Verificar existencia
        if (!$supplierModel->supplierExists($id)) {
            throw new Exception('El proveedor que intentas eliminar no existe');
        }
        
        $success = $supplierModel->delete($id);
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

    /**
     * Devuelve todos los proveedores en formato JSON (AJAX).
     */
function getSuppliersAjax($supplierModel) {
     try {
         $suppliers = $supplierModel->getAll();
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
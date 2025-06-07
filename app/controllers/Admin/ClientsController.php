<?php
namespace Barkios\controllers\Admin;

use Barkios\models\Clients;
use Exception;

/**
 * Controlador para la gestión de clientes en el área de administración
 * 
 * Maneja las operaciones CRUD para los clientes, incluyendo:
 * - Listado de clientes
 * - Agregar nuevos clientes
 * - Eliminar clientes existentes
 */
class ClientsController {
    /** @var Clients Instancia del modelo de clientes */
    private $clientsModel;

    /**
     * Constructor de la clase
     * 
     * Inicializa una nueva instancia del modelo de clientes
     */
    public function __construct() {
        $this->clientsModel = Clients::getInstance();
    }

    /**
     * Maneja las peticiones entrantes y las redirige al método correspondiente
     * 
     * Detecta el tipo de petición (GET/POST) y la acción solicitada,
     * luego llama al método correspondiente para manejar la acción
     */
    public function handleRequest() {
        $action = $_GET['action'] ?? '';
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
                $this->handleAddclients();
            } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete') {
                $this->handleDeleteclients();
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'truncate') {
                $this->handleTruncate();
            } else if ($isAjax) {
                // Si es AJAX pero no coincide con ninguna acción, devolver error
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Acción no válida']);
                exit();
            }
        } catch (Exception $e) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                exit();
            } else {
                die("Error: " . $e->getMessage());
            }
        }
    }

    /**
     * Obtiene todos los clientes
     * 
     * @return array Lista de clientes
     */
    public function getclientss() {
        return $this->clientsModel->getAll();
    }

    /**
     * Maneja la adición de un nuevo cliente
     * 
     * Procesa el formulario de agregar cliente, valida los datos
     * y devuelve una respuesta JSON si es una petición AJAX
     * o redirige a la página correspondiente en caso contrario
     */
    private function handleAddclients() {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        
        $response = ['success' => false, 'message' => ''];
        
        try {
            if (ob_get_length()) ob_clean();
            $required = ['cedula', 'nombre', 'direccion', 'telefono', 'membresia'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("El campo $field es requerido");
                }
            }

            $cedula = htmlspecialchars(trim($_POST['cedula']));
            $nombre = htmlspecialchars(trim($_POST['nombre']));
            $direccion = htmlspecialchars(trim($_POST['direccion']));
            $telefono = htmlspecialchars(trim($_POST['telefono']));
            $membresia = htmlspecialchars(trim($_POST['membresia']));

            // Verifica si ya existe antes de insertar
            if ($this->clientsModel->clientExists($cedula)) {
                throw new Exception("Ya existe un cliente con esta cédula");
            }

            // Insertar el cliente
            $success = $this->clientsModel->add($cedula, $nombre, $direccion, $telefono, $membresia);

            if ($success) {
                $response['success'] = true;
                $response['message'] = 'Cliente agregado correctamente';
                $response['cliente'] = [
                    'cedula' => $cedula,
                    'nombre' => $nombre,
                    'direccion' => $direccion,
                    'telefono' => $telefono,
                    'membresia' => $membresia
                ];
            } else {
                throw new Exception("Error al agregar el cliente");
            }
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }
        
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        } else {
            if ($response['success']) {
                header("Location: clients-admin.php?success=add");
            } else {
                header("Location: clients-admin.php?error=" . urlencode($response['message']) . "&cedula=" . urlencode($_POST['cedula'] ?? ''));
            }
            exit();
        }
    }

    /**
     * Maneja la eliminación de un cliente
     * 
     * Procesa la solicitud de eliminación de un cliente por su cédula
     * Devuelve una respuesta JSON si es una petición AJAX
     * o redirige a la página correspondiente en caso contrario
     */
    private function handleDeleteclients() {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        
        $response = ['success' => false, 'message' => ''];
        
        try {
            if (ob_get_length()) ob_clean();
            if (!isset($_GET['cedula']) || empty($_GET['cedula'])) {
                throw new Exception("Cédula de cliente inválida");
            }

            $cedula = $_GET['cedula'];
            $success = $this->clientsModel->delete($cedula);

            if ($success) {
                $response['success'] = true;
                $response['message'] = 'Cliente eliminado correctamente';
                $response['cedula'] = $cedula;
            } else {
                throw new Exception("Error al eliminar el cliente");
            }
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }
        
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        } else {
            if ($response['success']) {
                header("Location: clients-admin.php?success=delete");
            } else {
                header("Location: clients-admin.php?error=" . urlencode($response['message']));
            }
            exit();
        }
    }
    
    /**
     * Maneja el vaciado de la tabla de clientes
     * 
     * Elimina todos los registros de clientes y redirige a la página principal
     * con un mensaje de éxito
     */
    private function handleTruncate() {
        $this->clientsModel->truncate();
        header("Location: clients-admin.php?success=add");
        exit();
    }

    /**
     * Muestra la vista principal de administración de clientes
     * 
     * Obtiene la lista de clientes y carga la vista correspondiente
     */
    public function index() {
        $products = $this->getclientss();
        require __DIR__ . '/../../views/admin/clients-admin.php';
    }
}

// Inicialización del controlador y manejo de la solicitud
$controller = new ClientsController();
$controller->handleRequest();
$clientss = $controller->getclientss();
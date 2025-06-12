<?php
use Barkios\models\Clients;
$clientsModel = new Clients();
error_reporting(E_ALL);
ini_set('display_errors', 1);

handleRequest($clientsModel);
/**
 * Controlador para la gestión de clientes en el área de administración
 * 
 * Maneja las operaciones CRUD para los clientes, incluyendo:
 * - Listado de clientes
 * - Agregar nuevos clientes
 * - Eliminar clientes existentes
 */
/**
 * Muestra la vista principal de administración de clientes
 */
function index() {
        $clientsModel = new Clients();
        $clientss = $clientsModel->getAll(); // Cambia getAll() por el método real si es diferente
    require __DIR__ . '/../../views/admin/clients-admin.php';
}
/**
 * Maneja las peticiones entrantes y las redirige al método correspondiente
 * 
 * Detecta el tipo de petición (GET/POST) y la acción solicitada,
 * luego llama al método correspondiente para manejar la acción
 */
 function handleRequest($clientsModel) {
    $action = $_GET['action'] ?? '';
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
             strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

    try {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
            handleAddclients($clientsModel);
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'edit') {
            handleEditclients($clientsModel); // <-- Agrega esta función para editar por POST normal
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'edit_ajax') {
            handleEditClientAjax($clientsModel); // <-- Ya tienes esta función para AJAX
        } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete') {
            handleDeleteclients($clientsModel);
        } else if ($isAjax) {
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
function getclientss($clientsModel) {
    return $clientsModel->getAll();
}

/**
 * Maneja la adición de un nuevo cliente
 * 
 * Procesa el formulario de agregar cliente, valida los datos
 * y devuelve una respuesta JSON si es una petición AJAX
 * o redirige a la página correspondiente en caso contrario
 */
function handleAddclients($clientsModel) {
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
        if ($clientsModel->clientExists($cedula)) {
            throw new Exception("Ya existe un cliente con esta cédula");
        }
        // Insertar el cliente
        $success = $clientsModel->add($cedula, $nombre, $direccion, $telefono, $membresia);
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
function handleDeleteclients($clientsModel) {
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
             strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    $response = ['success' => false, 'message' => ''];
    
    try {
        if (ob_get_length()) ob_clean();
        if (!isset($_GET['cedula']) || empty($_GET['cedula'])) {
            throw new Exception("Cédula de cliente inválida");
        }
        $cedula = $_GET['cedula'];
        $success = $clientsModel->delete($cedula);
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
 * Maneja la edición de un cliente existente
 * 
 * Procesa el formulario de edición de cliente, valida los datos
 * y devuelve una respuesta JSON si es una petición AJAX
 * o redirige a la página correspondiente en caso contrario
 */
function handleEditClientAjax($clientModel) {
    header('Content-Type: application/json; charset=utf-8');

    try {
        $required = ['cedula', 'nombre', 'direccion', 'telefono', 'membresia'];
        $data = [];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("El campo $field es requerido");
            }
            $data[$field] = htmlspecialchars(trim($_POST[$field]));
        }

        if (!$clientModel->clientExists($data['cedula'])) {
            throw new Exception("El cliente no existe");
        }

        $success = $clientModel->update(
            $data['cedula'],
            $data['nombre'],
            $data['direccion'],
            $data['telefono'],
            $data['membresia']
        );

        if ($success) {
            $client = $clientModel->getById($data['cedula']);
            echo json_encode([
                'success' => true,
                'message' => 'Cliente actualizado correctamente',
                'client' => $client // <-- clave correcta
            ]);
            exit();
        } else {
            throw new Exception("Error al actualizar el cliente");
        }
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
 * Maneja la edición de un cliente existente
 * 
 * Procesa el formulario de edición de cliente, valida los datos
 * y devuelve una respuesta JSON si es una petición AJAX
 * o redirige a la página correspondiente en caso contrario
 */
function handleEditclients($clientsModel) {
    $response = ['success' => false, 'message' => ''];
    try {
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

        if (!$clientsModel->clientExists($cedula)) {
            throw new Exception("El cliente no existe");
        }
        $success = $clientsModel->update($cedula, $nombre, $direccion, $telefono, $membresia);
        if ($success) {
            $response['success'] = true;
            $response['message'] = 'Cliente actualizado correctamente';
        } else {
            throw new Exception("Error al actualizar el cliente");
        }
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    if ($response['success']) {
        header("Location: clients-admin.php?success=edit");
    } else {
        header("Location: clients-admin.php?error=" . urlencode($response['message']) . "&cedula=" . urlencode($_POST['cedula'] ?? ''));
    }
    exit();
}

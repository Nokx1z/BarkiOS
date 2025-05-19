<?php
require_once __DIR__.'/../../models/Clients.php';

class ClientsController {
    private $clientsModel;

    public function __construct() {
        $this->clientsModel = new Clients();
    }

    public function handleRequest() {
        $action = $_GET['action'] ?? '';

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
                $this->handleAddclients();
            } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete') {
                $this->handleDeleteclients();
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'truncate') {
                $this->handleTruncate(); // Asegúrate de que esta línea exista
            }
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
    }

    public function getclientss() {
        return $this->clientsModel->getAll();
    }

    private function handleAddclients() {
        $required = ['cedula', 'nombre', 'direccion', 'telefono', 'membresia'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("El campo $field es requerido");
            }
        }

        $success = $this->clientsModel->add(
            htmlspecialchars(trim($_POST['cedula'])),
            htmlspecialchars(trim($_POST['nombre'])),
            htmlspecialchars(trim($_POST['direccion'])),
            htmlspecialchars(trim($_POST['telefono'])),
            htmlspecialchars(trim($_POST['membresia']))
        );

        if ($success) {
            header("Location: clients-admin.php?success=add");
            exit();
        }
    }

    private function handleDeleteclients() {
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            throw new Exception("ID de clientso inválido");
        }

        $success = $this->clientsModel->delete((int)$_GET['id']);

        if ($success) {
            header("Location: clients-admin.php?success=add");
            exit();
        }
    }
    
    private function handleTruncate() {
        $this->clientsModel->truncate();
        header("Location: clients-admin.php?success=add");
        exit();
    }
}

// Instanciar y ejecutar
$controller = new ClientsController();
$controller->handleRequest();
$clientss = $controller->getclientss();
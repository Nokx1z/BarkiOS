<?php
require_once __DIR__.'/../../models/SupplierModel.php';

class SupplierController {
    private $supplierModel;

    public function __construct() {
        $this->supplierModel = new SupplierModel();
    }

    public function handleRequest() {
        $action = $_GET['action'] ?? '';

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add_supplier') {
                $this->handleAddSupplier();
            } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete') {
                $this->handleDeleteSupplier();
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'truncate') {
                $this->handleTruncate(); 
            }
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
    }

    public function getProducts() {
        return $this->supplierModel->getAll();
    }

    private function handleAddSupplier() {
        $required = ['tipo_rif', 'rif', 'direccion', 'nombre_empresa', 'nombre_contacto'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("El campo $field es requerido");
            }
        }

        $success = $this->supplierModel->add(
            htmlspecialchars(trim($_POST['tipo_rif'])),
            htmlspecialchars(trim($_POST['rif'])),
            htmlspecialchars(trim($_POST['direccion'])),
            htmlspecialchars(trim($_POST['nombre_empresa'])),
            htmlspecialchars(trim($_POST['nombre_contacto']))
        );

        if ($success) {
            header('Location: ../app/views/admin/supplier-admin.php?success=add_supplier');
            exit();
        }
    }

    private function handleDeleteSupplier() {
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            throw new Exception("ID de producto inválido");
        }

        $success = $this->supplierModel->delete((int)$_GET['id']);

        if ($success) {
        header('Location: ../app/views/admin/supplier-admin.php?success=add_supplier');
            exit();
        }
    }
    
    private function handleTruncate() {
        $this->supplierModel->truncate();
        header('Location: ../app/views/admin/supplier-admin.php?success=add_supplier');
        exit();
    }
}

// Instanciar y ejecutar
$controller = new SupplierController();
$controller->handleRequest();
$products = $controller->getProducts();
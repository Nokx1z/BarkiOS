<?php
require_once __DIR__.'/../../models/Product.php';

class ProductsController {
    private $productModel;

    public function __construct() {
        $this->productModel = new Product();
    }

    public function handleRequest() {
        $action = $_GET['action'] ?? '';

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
                $this->handleAddProduct();
            } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete') {
                $this->handleDeleteProduct();
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'truncate') {
                $this->handleTruncate(); // Asegúrate de que esta línea exista
            }
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
    }

    public function getProducts() {
        return $this->productModel->getAll();
    }

    private function handleAddProduct() {
        $required = [
            'nombre', 'categoria', 'precio'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("El campo $field es requerido");
            }
        }

     $success = $this->productModel->add(
        htmlspecialchars(trim($_POST['id'])),
        htmlspecialchars(trim($_POST['nombre'])),
        htmlspecialchars(trim($_POST['tipo'])),
        htmlspecialchars(trim($_POST['categoria'])),
        (float)$_POST['precio']
        );

        if ($success) {
            header("Location: index.php?success=add");
            exit();
        }
    }

    private function handleDeleteProduct() {
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            throw new Exception("ID de producto inválido");
        }

        $success = $this->productModel->delete((int)$_GET['id']);

        if ($success) {
            header("Location: index.php?success=add");
            exit();
        }
    }
    
    private function handleTruncate() {
        $this->productModel->truncate();
        header("Location: index.php?success=add");
        exit();
    }
}

// Instanciar y ejecutar
$controller = new ProductsController();
$controller->handleRequest();
$products = $controller->getProducts();
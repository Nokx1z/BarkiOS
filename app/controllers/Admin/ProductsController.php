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
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'edit') {
                $this->handleEditProduct();
            } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete') {
                $this->handleDeleteProduct();
            }
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
    }

    public function getProducts() {
        return $this->productModel->getAll();
    }

    public function getProductById($id) {
        return $this->productModel->getById($id);
    }

    private function handleAddProduct() {
        $required = ['id', 'nombre', 'tipo', 'categoria', 'precio'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("El campo $field es requerido");
            }
        }

        $id = (int) $_POST['id'];
        $nombre = htmlspecialchars(trim($_POST['nombre']));
        $tipo = htmlspecialchars(trim($_POST['tipo']));
        $categoria = htmlspecialchars(trim($_POST['categoria']));
        $precio = (float)$_POST['precio'];

        if ($this->productModel->productExists($id)) {
            header("Location: products-admin.php?error=id_duplicado&id=" . urlencode($id));
            exit();
        }

        $success = $this->productModel->add($id, $nombre, $tipo, $categoria, $precio);

        if ($success) {
            header("Location: products-admin.php?success=add");
            exit();
        }
    }

    private function handleEditProduct() {
        $required = ['id', 'nombre', 'tipo', 'categoria', 'precio'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("El campo $field es requerido");
            }
        }

        $id = (int) $_POST['id'];
        $nombre = htmlspecialchars(trim($_POST['nombre']));
        $tipo = htmlspecialchars(trim($_POST['tipo']));
        $categoria = htmlspecialchars(trim($_POST['categoria']));
        $precio = (float)$_POST['precio'];

        if (!$this->productModel->productExists($id)) {
            header("Location: products-admin.php?error=producto_no_existe&id=" . urlencode($id));
            exit();
        }

        $success = $this->productModel->update($id, $nombre, $tipo, $categoria, $precio);

        if ($success) {
            header("Location: products-admin.php?success=edit");
            exit();
        }
    }

    private function handleDeleteProduct() {
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            throw new Exception("ID de producto inválido");
        }

        $success = $this->productModel->delete((int)$_GET['id']);

        if ($success) {
            header("Location: products-admin.php?success=delete");
            exit();
        }
    }
}

// Instanciar y ejecutar
$controller = new ProductsController();
$controller->handleRequest();
$products = $controller->getProducts();
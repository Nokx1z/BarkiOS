<?php
require_once __DIR__ . '/controllers/Admin/ProductsController.php';

$controller = new ProductsController();
$controller->handleRequest();

$action = $_GET['action'] ?? null;

switch ($action) {
    case 'delete':
        if (isset($_GET['id'])) {
            $controller->deleteProduct($_GET['id']);
            header('Location: index.php?success=delete');
            exit;
        }
        break;

    case 'add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->addProduct($_POST['nombre'], $_POST['categoria'], $_POST['precio']);
            header('Location: index.php?success=add');
            exit;
        }
        break;

    default:
        $products = $controller->getProducts();
        include __DIR__ . '/views/Admin/products-admin.php';
        break;
}
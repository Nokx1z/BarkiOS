<?php
// filepath: c:\xampp\htdocs\BarkiOS\app\controllers\Admin\ProductsController.php
use Barkios\models\Product;
$productModel = new Product();

function index() {
   return null;
}
handleRequest($productModel);

function handleRequest($productModel) {
    $action = $_GET['action'] ?? '';
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    try {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            switch ("{$_SERVER['REQUEST_METHOD']}_$action") {
                case 'POST_add_ajax':    handleAddEditAjax($productModel, 'add'); break;
                case 'POST_edit_ajax':   handleAddEditAjax($productModel, 'edit'); break;
                case 'POST_delete_ajax': handleDeleteAjax($productModel); break;
                case 'GET_get_products': getProductsAjax($productModel); break;
                default:                 echo json_encode(['success'=>false,'message'=>'Acción inválida']); exit();
            }
        } else {
            switch ("{$_SERVER['REQUEST_METHOD']}_$action") {
                case 'POST_add':    handleAddEdit($productModel, 'add'); break;
                case 'POST_edit':   handleAddEdit($productModel, 'edit'); break;
                case 'GET_delete':  handleDelete($productModel); break;
                default:            require __DIR__ . '/../../views/admin/products-admin.php';
            }
        }
    } catch (Exception $e) {
        if ($isAjax) {
            http_response_code(500);
            echo json_encode(['success'=>false, 'message'=>$e->getMessage()]);
        } else {
            die("Error: " . $e->getMessage());
        }
        exit();
    }
}

function handleAddEdit($productModel, $mode) {
    $fields = ['id','nombre','tipo','categoria','precio'];
    foreach ($fields as $f) {
        if (empty($_POST[$f])) throw new Exception("El campo $f es requerido");
    }
    $id = trim($_POST['id']);
    $nombre = trim($_POST['nombre']);
    $tipo = trim($_POST['tipo']);
    $categoria = trim($_POST['categoria']);
    $precio = (float)$_POST['precio'];

    if ($mode === 'add') {
        if ($productModel->productExists($id)) {
            header("Location: products-admin.php?error=id_duplicado&id=$id"); exit();
        }
        $productModel->add($id, $nombre, $tipo, $categoria, $precio);
        header("Location: products-admin.php?success=add"); exit();
    } else {
        $productModel->update($id, $nombre, $tipo, $categoria, $precio);
        header("Location: products-admin.php?success=edit"); exit();
    }
}

function handleDelete($productModel) {
    if (!isset($_GET['id']));
    $productModel->delete((int)$_GET['id']);
    header("Location: products-admin.php?success=delete"); exit();
}

function handleAddEditAjax($productModel, $mode) {
    $fields = ['id','nombre','tipo','categoria','precio'];
    $data = [];
    foreach ($fields as $f) {
        if (empty($_POST[$f])) throw new Exception("El campo $f es requerido");
        $data[$f] = $f === 'precio' ? (float)$_POST[$f] : trim($_POST[$f]);
    }
    if ($mode === 'add') {
        if ($productModel->productExists($data['id'])) throw new Exception("ID duplicado");
        $productModel->add(...array_values($data));
        $msg = 'Producto agregado';
    } else {
        if (!$productModel->productExists($data['id'])) throw new Exception("No existe el producto");
        $productModel->update(...array_values($data));
        $msg = 'Producto actualizado';
    }
    $product = $productModel->getById($data['id']);
    echo json_encode(['success'=>true, 'message'=>$msg, 'product'=>$product]); exit();
}

function handleDeleteAjax($productModel) {
    if (empty($_POST['id']) || !is_numeric($_POST['id'])) throw new Exception("ID inválido");
    $id = (int)$_POST['id'];
    if (!$productModel->productExists($id)) throw new Exception("No existe el producto");
    $productModel->delete($id);
    echo json_encode(['success'=>true, 'message'=>'Producto eliminado', 'productId'=>$id]); exit();
}

function getProductsAjax($productModel) {
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $product = $productModel->getById((int)$_GET['id']);
        if (!$product) throw new Exception("No existe el producto");
        echo json_encode(['success'=>true, 'products'=>[$product]]); exit();
    }
    $products = $productModel->getAll();
    echo json_encode(['success'=>true, 'products'=>$products, 'count'=>count($products)]); exit();
}
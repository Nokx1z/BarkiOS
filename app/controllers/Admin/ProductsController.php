<?php
use Barkios\models\Product;
$productModel = Product::getInstance();
error_reporting(E_ALL);
ini_set('display_errors', 1);

handleRequest($productModel);
/**
 * index
 * 
 * Acción principal.
 * Muestra la vista de administración de productos.
 * 
 * Palabras clave: vista, administración, productos.
 * 
 * @return void
 */
function index() {
    require __DIR__ . '/../../views/admin/products-admin.php';
}

/**
 * handleRequest
 * 
 * Enrutador principal de solicitudes.
 * Determina el tipo de solicitud (AJAX o normal) y la acción a ejecutar.
 * 
 * Palabras clave: enrutamiento, AJAX, POST, GET, acción, logging, manejo de errores.
 * 
 * @param Product $productModel Instancia del modelo de productos.
 * @return void
 */
function handleRequest($productModel) {
    $action = $_GET['action'] ?? '';
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    
    // Logging de la solicitud recibida
    error_log("Solicitud recibida - Acción: $action, Método: " . $_SERVER['REQUEST_METHOD'] . ", AJAX: " . ($isAjax ? 'Sí' : 'No'));
    
    try {
        // Solicitudes AJAX
        if ($isAjax) {
            header('Content-Type: application/json');
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add_ajax') {
                handleAddProductAjax($productModel);
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'edit_ajax') {
                handleEditProductAjax($productModel);
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete_ajax') {
                handleDeleteProductAjax($productModel);
            } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get_products') {
                getProductsAjax($productModel);
            } else {
                index();
            }
        } else {
            // Solicitudes normales (no AJAX)
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
                handleAddProduct($productModel);
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'edit') {
                handleEditProduct($productModel);
            } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete') {
                handleDeleteProduct($productModel);
            }
        }
    } catch (Exception $e) {
        // Manejo global de errores
        if (ob_get_length()) ob_clean();

        if ($isAjax) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Error en el servidor: ' . $e->getMessage(),
                'debug' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            ]);
        } else {
            die("Error: " . $e->getMessage());
        }
        exit();
    }
}

/**
* Obtiene todos los productos registrados.
* 
* @return array Lista de productos.
*/
function getProducts($productModel) {
    return $productModel->getAll();
}

/**
* Maneja la adición de un nuevo producto (petición normal).
* Valida los datos recibidos, verifica duplicados y agrega el producto.
* Responde en JSON si es AJAX, o redirige en caso contrario.
* 
* @return void
*/
function handleAddProduct($productModel) {
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
    $precio = (float) $_POST['precio'];

    // Verifica duplicados
    if ($productModel->productExists($id)) {
        header("Location: products-admin.php?error=id_duplicado&id=" . urlencode($id));
        exit();
    }

    // Inserta producto
    $success = $productModel->add($id, $nombre, $tipo, $categoria, $precio);
    if ($success) {
        header("Location: products-admin.php?success=add");
        exit();
    }
}
/**
* Maneja la eliminación de un producto (petición normal).
* Valida el ID recibido y elimina el producto si existe.
* Redirige según el resultado.
* 
* @return void
*/
function handleDeleteProduct($productModel) {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception("ID de producto inválido");
    }

    $success = $productModel->delete((int)$_GET['id']);

    if ($success) {
        header("Location: products-admin.php?success=delete");
        exit();
    }
}
/**
* Maneja la edición de un producto existente (petición normal).
* Valida los datos recibidos, verifica existencia y actualiza el producto.
* Redirige según el resultado.
* 
* @return void
*/
function handleEditProduct($productModel) {
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
    $precio = (float) $_POST['precio'];

    $success = $productModel->update($id, $nombre, $tipo, $categoria, $precio);

    if ($success) {
            header("Location: products-admin.php?success=edit");
    } else {
            header("Location: products-admin.php?error=edit_failed&id=" . urlencode($id));
    }
        exit;
}
/**
* Maneja la adición de un nuevo producto vía AJAX.
* Valida y sanitiza los datos recibidos, verifica duplicados y agrega el producto.
* Devuelve una respuesta JSON con el resultado.
* 
* @return void
*/

function handleAddProductAjax($productModel) {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json; charset=utf-8');

    try {
        $required = ['id', 'nombre', 'tipo', 'categoria', 'precio'];
        $data = [];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("El campo $field es requerido");
            }
            $data[$field] = trim($_POST[$field]);
        }

        if ($productModel->productExists($data['id'])) {
            throw new Exception("Ya existe un producto con este ID");
        }

        $success = $productModel->add(
            $data['id'],
            $data['nombre'],
            $data['tipo'],
            $data['categoria'],
            $data['precio']
        );

        if (!$success) {
            throw new Exception("Error al agregar el producto a la base de datos");
        }

        $product = $productModel->getById($data['id']);
        if (!$product) {
            throw new Exception("No se pudo recuperar el producto recién agregado");
        }

        $response = [
            'success' => true,
            'message' => 'Producto agregado correctamente',
            'product' => $product
        ];

        echo json_encode($response);
        exit();

    } catch (Exception $e) {
        http_response_code(400);
        $response = [
            'success' => false,
            'message' => $e->getMessage()
        ];
        echo json_encode($response);
        exit();
    }
}

/**
* Maneja la edición de un producto vía AJAX.
* Valida y sanitiza los datos recibidos, verifica existencia y actualiza el producto.
* Devuelve una respuesta JSON con el resultado.
* 
* @return void
*/
function handleEditProductAjax($productModel) {
    header('Content-Type: application/json; charset=utf-8');

    try {
        $required = ['id', 'nombre', 'tipo', 'categoria', 'precio'];
        $data = [];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("El campo $field es requerido");
            }
            $data[$field] = $field === 'id' ? (int)$_POST[$field] :
                          ($field === 'precio' ? (float)$_POST[$field] :
                          htmlspecialchars(trim($_POST[$field])));
        }

        if (!$productModel->productExists($data['id'])) {
            throw new Exception("El producto no existe");
        }

        $success = $productModel->update(
            $data['id'],
            $data['nombre'],
            $data['tipo'],
            $data['categoria'],
            $data['precio']
        );

        if ($success) {
            $product = $productModel->getById($data['id']);
            echo json_encode([
                'success' => true,
                'message' => 'Producto actualizado correctamente',
                'product' => $product
            ]);
        } else {
            throw new Exception("Error al actualizar el producto");
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
* Maneja la eliminación de un producto vía AJAX.
* Valida el ID recibido, verifica existencia y elimina el producto.
* Devuelve una respuesta JSON con el resultado.
* 
* @return void
*/
function handleDeleteProductAjax($productModel) {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json; charset=utf-8');

    try {
        if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
            throw new Exception("ID de producto inválido");
        }

        $id = (int)$_POST['id'];

        if (!$productModel->productExists($id)) {
            throw new Exception("El producto no existe");
        }

        $success = $productModel->delete($id);

        if ($success) {
            $response = [
                'success' => true,
                'message' => 'Producto eliminado correctamente',
                'productId' => $id
            ];
            echo json_encode($response);
            exit();
        } else {
            throw new Exception("Error al eliminar el producto");
        }
    } catch (Exception $e) {
        http_response_code(500);
        $response = [
            'success' => false,
            'message' => $e->getMessage()
        ];
        echo json_encode($response);
        exit();
    }
}


/**
* Devuelve la lista de productos o un producto específico en formato JSON para AJAX.
* Si se recibe un ID por GET, devuelve solo ese producto. Si no, devuelve todos.
* Incluye manejo de errores y logging.
* 
* @return void
*/
function getProductsAjax($productModel) {
    header('Content-Type: application/json; charset=utf-8');

    try {
        error_log('[' . date('Y-m-d H:i:s') . '] getProductsAjax - Iniciando...');

        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $productId = (int)$_GET['id'];
            error_log('[' . date('Y-m-d H:i:s') . '] getProductsAjax - Solicitando producto ID: ' . $productId);

            $product = $productModel->getById($productId);

            if (!$product) {
                throw new Exception('El producto solicitado no existe');
            }

            $response = [
                'success' => true,
                'products' => [$product]
            ];

            error_log('[' . date('Y-m-d H:i:s') . '] getProductsAjax - Producto encontrado: ' . json_encode($product));
        } else {
            error_log('[' . date('Y-m-d H:i:s') . '] getProductsAjax - Solicitando todos los productos');

            $products = $productModel->getAll();
            error_log('[' . date('Y-m-d H:i:s') . '] getProductsAjax - Productos encontrados: ' . count($products));

            $response = [
                'success' => true,
                'products' => $products,
                'count' => count($products)
            ];

            error_log('[' . date('Y-m-d H:i:s') . '] getProductsAjax - Total de productos: ' . count($products));
        }

        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    } catch (Exception $e) {
        error_log('[' . date('Y-m-d H:i:s') . '] getProductsAjax - ERROR: ' . $e->getMessage());
        error_log('[' . date('Y-m-d H:i:s') . '] getProductsAjax - Archivo: ' . $e->getFile() . ' Línea: ' . $e->getLine());

        http_response_code(500);

        $response = [
            'success' => false,
            'message' => 'Error al obtener los productos: ' . $e->getMessage(),
            'timestamp' => date('Y-m-d H:i:s'),
            'request' => [
                'method' => $_SERVER['REQUEST_METHOD'],
                'params' => $_GET
            ]
        ];

        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    exit();
}

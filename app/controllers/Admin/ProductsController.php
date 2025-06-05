<?php
namespace Barkios\controllers\Admin;

use Barkios\models\Product;
use Exception;

/**
 * Controlador para la gestión de productos en el área de administración.
 * Permite realizar operaciones CRUD sobre los productos, tanto mediante peticiones normales
 * como a través de AJAX. Incluye manejo de errores y respuestas en formato JSON para AJAX.
 */
class ProductsController {
    /** @var Product Instancia del modelo de productos */
    private $productModel;

    /**
     * Constructor de la clase.
     * Inicializa el modelo de productos.
     */
    public function __construct() {
        $this->productModel = new Product();
    }

    /**
     * Enrutador principal del controlador.
     * Detecta el tipo de petición (AJAX o normal) y la acción solicitada,
     * luego llama al método correspondiente para manejar la acción.
     * 
     * @return void
     */
    public function handleRequest() {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $action = $_GET['action'] ?? '';
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        ob_start();

        try {
            if ($isAjax) {
                header('Content-Type: application/json');
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add_ajax') {
                    $this->handleAddProductAjax();
                } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'edit_ajax') {
                    $this->handleEditProductAjax();
                } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete_ajax') {
                    $this->handleDeleteProductAjax();
                } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get_products') {
                    $this->getProductsAjax();
                } else {
                    $this->index();
                }
            } else {
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
                    $this->handleAddProduct();
                } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'edit') {
                    $this->handleEditProduct();
                } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete') {
                    $this->handleDeleteProduct();
                }
            }
        } catch (Exception $e) {
            // Manejo global de errores para todas las acciones
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
    public function getProducts() {
        return $this->productModel->getAll();
    }

    /**
     * Maneja la adición de un nuevo producto (petición normal).
     * Valida los datos recibidos, verifica duplicados y agrega el producto.
     * Responde en JSON si es AJAX, o redirige en caso contrario.
     * 
     * @return void
     */
    private function handleAddProduct() {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        $required = ['id', 'nombre', 'tipo', 'categoria', 'precio'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                if ($isAjax) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => "El campo $field es requerido"]);
                    exit;
                } else {
                    header("Location: products-admin.php?error=campo_requerido");
                    exit;
                }
            }
        }

        $id = (int) $_POST['id'];
        $nombre = htmlspecialchars(trim($_POST['nombre']));
        $tipo = htmlspecialchars(trim($_POST['tipo']));
        $categoria = htmlspecialchars(trim($_POST['categoria']));
        $precio = (float)$_POST['precio'];

        if ($this->productModel->productExists($id)) {
            if ($isAjax) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Ya existe un producto con este ID"]);
                exit;
            } else {
                header("Location: products-admin.php?error=id_duplicado&id=" . urlencode($id));
                exit;
            }
        }

        $success = $this->productModel->add($id, $nombre, $tipo, $categoria, $precio);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => $success]);
            exit;
        } else {
            if ($success) {
                header("Location: products-admin.php?success=add");
                exit;
            } else {
                header("Location: products-admin.php?error=add_failed");
                exit;
            }
        }
    }

    /**
     * Maneja la edición de un producto existente (petición normal).
     * Valida los datos recibidos, verifica existencia y actualiza el producto.
     * Redirige según el resultado.
     * 
     * @return void
     */
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

    /**
     * Maneja la eliminación de un producto (petición normal).
     * Valida el ID recibido y elimina el producto si existe.
     * Redirige según el resultado.
     * 
     * @return void
     */
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

    /**
     * Punto de entrada para agregar productos vía AJAX.
     * Llama internamente al método privado que maneja la lógica.
     * 
     * @return void
     */
    public function add_ajax() {
        $this->handleAddProductAjax();
    }

    /**
     * Punto de entrada para eliminar productos vía AJAX.
     * Llama internamente al método privado que maneja la lógica.
     * 
     * @return void
     */
    public function delete_ajax() {
        $this->handleDeleteProductAjax();
    }

    /**
     * Maneja la adición de un nuevo producto vía AJAX.
     * Valida y sanitiza los datos recibidos, verifica duplicados y agrega el producto.
     * Devuelve una respuesta JSON con el resultado.
     * 
     * @return void
     */
    private function handleAddProductAjax() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        try {
            $required = ['id', 'nombre', 'tipo', 'categoria', 'precio'];
            $data = [];
            foreach ($required as $field) {
                if (!isset($_POST[$field]) || (is_string($_POST[$field]) && trim($_POST[$field]) === '')) {
                    throw new Exception("El campo $field es requerido");
                }
                $data[$field] = $field === 'id' ? (int)$_POST[$field] :
                              ($field === 'precio' ? (float)$_POST[$field] :
                              htmlspecialchars(trim($_POST[$field])));
            }

            if ($this->productModel->productExists($data['id'])) {
                throw new Exception("Ya existe un producto con este ID");
            }

            $success = $this->productModel->add(
                $data['id'],
                $data['nombre'],
                $data['tipo'],
                $data['categoria'],
                $data['precio']
            );

            if (!$success) {
                throw new Exception("Error al agregar el producto a la base de datos");
            }

            $product = $this->productModel->getById($data['id']);
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
    private function handleEditProductAjax() {
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

        if (!$this->productModel->productExists($data['id'])) {
            throw new Exception("El producto no existe");
        }

        $success = $this->productModel->update(
            $data['id'],
            $data['nombre'],
            $data['tipo'],
            $data['categoria'],
            $data['precio']
        );

        if ($success) {
            $product = $this->productModel->getById($data['id']);
            echo json_encode([
                'success' => true,
                'message' => 'Producto actualizado correctamente',
                'product' => $product
            ]);
        } else {
            throw new Exception("Error al actualizar el producto");
        }
    }

    /**
     * Maneja la eliminación de un producto vía AJAX.
     * Valida el ID recibido, verifica existencia y elimina el producto.
     * Devuelve una respuesta JSON con el resultado.
     * 
     * @return void
     */
    private function handleDeleteProductAjax() {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        try {
            if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
                throw new Exception("ID de producto inválido");
            }

            $id = (int)$_POST['id'];

            if (!$this->productModel->productExists($id)) {
                throw new Exception("El producto no existe");
            }

            $success = $this->productModel->delete($id);

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
    private function getProductsAjax() {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');

        try {
            error_log('[' . date('Y-m-d H:i:s') . '] getProductsAjax - Iniciando...');

            if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                $productId = (int)$_GET['id'];
                error_log('[' . date('Y-m-d H:i:s') . '] getProductsAjax - Solicitando producto ID: ' . $productId);

                $product = $this->productModel->getById($productId);

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

                $products = $this->productModel->getAll();
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

    /**
     * Muestra la vista principal de administración de productos.
     * Obtiene la lista de productos y carga la vista correspondiente.
     * 
     * @return void
     */
    public function index() {
        $products = $this->getProducts();
        require __DIR__ . '/../../views/admin/products-admin.php';
    }
}

// Inicialización del controlador y manejo de la solicitud
$controller = new ProductsController();
$controller->handleRequest();

// Obtenemos los productos para la carga inicial
$products = $controller->getProducts();
// El controlador debe ser instanciado y ejecutado desde el archivo que lo requiera

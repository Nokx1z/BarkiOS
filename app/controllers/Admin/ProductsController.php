<?php
namespace Barkios\controllers\Admin;
use Barkios\models\Product;
use Exception;
//require_once __DIR__.'/../../models/Product.php';

class ProductsController {
    private $productModel;

    public function __construct() {
        $this->productModel = new Product();
    }

    public function handleRequest() {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        $action = $_GET['action'] ?? '';
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        // Start output buffering to catch any accidental output
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
                    throw new Exception('Acción no válida');
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
            // Clean any output buffer
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
                // For non-AJAX requests, output the error
                die("Error: " . $e->getMessage());
            }
            exit();
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
    
    // AJAX Handlers
    private function handleAddProductAjax() {
        // Clear any previous output
        if (ob_get_length()) ob_clean();
        
        // Set JSON header
        header('Content-Type: application/json');
        
        try {
            $required = ['id', 'nombre', 'tipo', 'categoria', 'precio'];
            $data = [];
            
            // Validate required fields
            foreach ($required as $field) {
                if (!isset($_POST[$field]) || (is_string($_POST[$field]) && trim($_POST[$field]) === '')) {
                    throw new Exception("El campo $field es requerido");
                }
                
                // Sanitize input
                $data[$field] = $field === 'id' ? (int)$_POST[$field] : 
                              ($field === 'precio' ? (float)$_POST[$field] : 
                              htmlspecialchars(trim($_POST[$field])));
            }
            
            // Check if product already exists
            if ($this->productModel->productExists($data['id'])) {
                throw new Exception("Ya existe un producto con este ID");
            }
            
            // Add the product
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
            
            // Get the newly added product
            $product = $this->productModel->getById($data['id']);
            
            if (!$product) {
                throw new Exception("No se pudo recuperar el producto recién agregado");
            }
            
            // Return success response
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
    
    public function index() {
        $products = $this->getProducts();
        require __DIR__ . '/../../views/admin/products-admin.php';
    }
    
    private function handleDeleteProductAjax() {
        // Clear any previous output
        if (ob_get_length()) ob_clean();
        
        // Set JSON header
        header('Content-Type: application/json');
        
        try {
            if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
                throw new Exception("ID de producto inválido");
            }
            
            $id = (int)$_POST['id'];
            
            if (!$this->productModel->productExists($id)) {
                throw new Exception("El producto no existe");
            }
            
            $product = $this->productModel->getById($id);
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
    
    private function getProductsAjax() {
        // Limpiar cualquier salida previa
        while (ob_get_level()) ob_end_clean();
        
        // Establecer cabeceras para JSON
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            // Registrar la solicitud
            error_log('[' . date('Y-m-d H:i:s') . '] getProductsAjax - Iniciando...');
            
            // Verificar si se solicitó un ID de producto específico
            if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                $productId = (int)$_GET['id'];
                error_log('[' . date('Y-m-d H:i:s') . '] getProductsAjax - Solicitando producto ID: ' . $productId);
                
                $product = $this->productModel->getById($productId);
                
                if (!$product) {
                    throw new Exception('El producto solicitado no existe');
                }
                
                $response = [
                    'success' => true,
                    'products' => [$product] // Devolver como array para consistencia
                ];
                
                error_log('[' . date('Y-m-d H:i:s') . '] getProductsAjax - Producto encontrado: ' . json_encode($product));
            } else {
                // Obtener todos los productos si no se especifica un ID
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
            
            // Enviar la respuesta
            echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            // Registrar el error
            error_log('[' . date('Y-m-d H:i:s') . '] getProductsAjax - ERROR: ' . $e->getMessage());
            error_log('[' . date('Y-m-d H:i:s') . '] getProductsAjax - Archivo: ' . $e->getFile() . ' Línea: ' . $e->getLine());
            
            // Configurar código de estado HTTP
            http_response_code(500);
            
            // Crear respuesta de error
            $response = [
                'success' => false,
                'message' => 'Error al obtener los productos: ' . $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s'),
                'request' => [
                    'method' => $_SERVER['REQUEST_METHOD'],
                    'params' => $_GET
                ]
            ];
            
            // Enviar respuesta de error
            echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
        
        // Asegurarse de que no se envíe nada más
        exit();
    }
}

// El controlador debe ser instanciado y ejecutado desde el archivo que lo requiera
// Por ejemplo, desde products-admin.php
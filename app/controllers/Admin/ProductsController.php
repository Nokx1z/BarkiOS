<?php
namespace Barkios\controllers\Admin;
use Barkios\models\Product;
use Exception;
//require_once __DIR__.'/../../models/Product.php';

class ProductsController {
    private $productModel;
    // ───── Constructor ─────
    public function __construct() {
        $this->productModel = new Product();
    }
    // ───── Enrutador principal ─────
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
                    //Si no hace una acción 
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
            // Limpia cualquier contenido que haya sido enviado al buffer de salida
            if (ob_get_length()) ob_clean();
            
            if ($isAjax) {
                // Si la petición es AJAX, responde con un JSON y código HTTP 500 (error del servidor)
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                     // Mensaje de error para el usuario 
                    'message' => 'Error en el servidor: ' . $e->getMessage(),
                    // Información de depuración (archivo, línea y traza del error)
                    'debug' => [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ]
                ]);
            } else {
                 // Si NO es una petición AJAX, muestra el error directamente y detiene la ejecución
                die("Error: " . $e->getMessage());
            }
            exit();
        }
    }
    // ───── Métodos de Productos (NO AJAX) ─────
    public function getProducts() {
        return $this->productModel->getAll();
    }

    // ───── Métodos para añadir producto ─────
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

    // ───── Métodos para editar producto ─────
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
    
    //Este metodo es el que me permite cargar el ajax desde la vista 
    public function add_ajax() {
        $this->handleAddProductAjax();
    }

    public function  delete_ajax(){
        $this->handleDeleteProductAjax();
    }
    // ───── Métodos AJAX ─────
    // Maneja la petición AJAX para agregar un producto
    private function handleAddProductAjax() {
        // Limpia cualquier salida previa en el buffer para evitar mezclar datos
        if (ob_get_length()) ob_clean();
        
        // Establece la cabecera para indicar que la respuesta será JSON
        header('Content-Type: application/json');
        
        try {
            $required = ['id', 'nombre', 'tipo', 'categoria', 'precio'];
            $data = [];
            
            // Define los campos requeridos para agregar un producto
            foreach ($required as $field) {
                if (!isset($_POST[$field]) || (is_string($_POST[$field]) && trim($_POST[$field]) === '')) {
                    throw new Exception("El campo $field es requerido");
                }
                
<<<<<<< HEAD
                
=======
            // Valida que todos los campos requeridos estén presentes y no vacíos
>>>>>>> 843b0adaefa3c766ca7189cd7eb045b4dd642ba8
                $data[$field] = $field === 'id' ? (int)$_POST[$field] : 
                              ($field === 'precio' ? (float)$_POST[$field] : 
                              htmlspecialchars(trim($_POST[$field])));
            }
            $cedula = (int) $data['id'];
            
<<<<<<< HEAD
            // Check if product already exists
            if ($this->productModel->productExists($cedula)) {
=======
            // Sanitiza y convierte los datos según el tipo de campo
            if ($this->productModel->productExists($data['id'])) {
>>>>>>> 843b0adaefa3c766ca7189cd7eb045b4dd642ba8
                throw new Exception("Ya existe un producto con este ID");
            }
            
            // Intenta agregar el producto a la base de datos
            $success = $this->productModel->add(
                $data['id'], 
                $data['nombre'], 
                $data['tipo'], 
                $data['categoria'], 
                $data['precio']
            );
            // Si la inserción falla, lanza una excepción            
            if (!$success) {
                throw new Exception("Error al agregar el producto a la base de datos");
            }
            
            // Recupera el producto recién agregado para devolverlo en la respuesta
            $product = $this->productModel->getById($data['id']);
            
            // Si no se puede recuperar el producto, lanza una excepción       
            if (!$product) {
                throw new Exception("No se pudo recuperar el producto recién agregado");
            }
            
            // Construye la respuesta de éxito
            $response = [
                'success' => true, 
                'message' => 'Producto agregado correctamente',
                'product' => $product
            ];
            
            // Devuelve la respuesta en formato JSON y termina la ejecución
            echo json_encode($response);
            exit();
            
        } catch (Exception $e) {
            // Si ocurre un error, responde con código 400 y el mensaje de error
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
    
    private function handleDeleteProductAjax() {
        // Limpia cualquier salida previa en el buffer para evitar mezclar datos
        if (ob_get_length()) ob_clean();
        
        // Establece la cabecera para indicar que la respuesta será JSON
        header('Content-Type: application/json');
        
        try {
            // Verifica que se haya enviado un ID válido por POST
            if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
                throw new Exception("ID de producto inválido");
            }
            
            // Convierte el ID a entero
            $id = (int)$_POST['id'];
            
            // Verifica que el producto exista antes de intentar eliminarlo
            if (!$this->productModel->productExists($id)) {
                throw new Exception("El producto no existe");
            }
            
            // (Opcional) Obtiene el producto antes de eliminarlo (no se usa en la respuesta)
            $product = $this->productModel->getById($id);

            // Intenta eliminar el producto
            $success = $this->productModel->delete($id);
            
            if ($success) {
                 // Si la eliminación fue exitosa, responde con éxito y el ID eliminado
                $response = [
                    'success' => true, 
                    'message' => 'Producto eliminado correctamente',
                    'productId' => $id
                ];
                
                echo json_encode($response);
                exit();
            } else {
                // Si falla la eliminación, lanza una excepción
                throw new Exception("Error al eliminar el producto");
            }
        } catch (Exception $e) {
            // Si ocurre un error, responde con código 500 y el mensaje de error
            http_response_code(500);
            $response = [
                'success' => false,
                'message' => $e->getMessage()
            ];
            
            echo json_encode($response);
            exit();
        }
    }

    // ───── Utilidades ─────
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
    
    public function index() {
        $products = $this->getProducts();
        require __DIR__ . '/../../views/admin/products-admin.php';
    }
}

$controller = new ProductsController();

$controller->handleRequest();

// Obtenemos los productos para la carga inicial
$products = $controller->getProducts();
// El controlador debe ser instanciado y ejecutado desde el archivo que lo requiera

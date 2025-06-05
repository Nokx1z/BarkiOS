<?php
namespace Barkios\controllers;

/**
 * FrontController
 * Punto de entrada principal para el ruteo de la aplicación.
 * Analiza la URL, determina el controlador y la acción a ejecutar,
 * y maneja la carga dinámica de controladores y métodos.
 */
class FrontController {
    /** @var string Nombre del controlador */
    private $controller;
    /** @var string Nombre de la acción */
    private $action;
    /** @var array Parámetros adicionales */
    private $params = [];

    /**
     * Constructor: analiza la URL y carga el controlador correspondiente.
     */
    public function __construct() {
        $this->parseUrl();
        $this->loadController();
    }

    /**
     * Analiza la URL para determinar controlador, acción y parámetros.
     * Soporta rutas amigables y parámetros GET.
     */
    private function parseUrl() {
        if (isset($_GET['controller']) && isset($_GET['action'])) {
            $this->controller = $this->sanitize($_GET['controller']);
            $this->action = $this->sanitize($_GET['action']);
            // Parámetros opcionales por GET
            $this->params = $_GET['params'] ?? [];
        } else {
            // Analiza la URI para rutas amigables
            $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
            $scriptName = $_SERVER['SCRIPT_NAME'];

            $path = str_replace(dirname($scriptName), '', $requestUri);
            $path = preg_replace('#^/index\.php#', '', $path);
            $path = trim(parse_url($path, PHP_URL_PATH), '/');

            $segments = array_filter(explode('/', $path));

            // Si la ruta inicia con 'admin', la elimina
            if (isset($segments[0]) && strtolower($segments[0]) === 'admin') {
                array_shift($segments);
            }

            $this->controller = $this->sanitize($segments[0] ?? 'products');
            $this->action = $this->sanitize($segments[1] ?? 'index');
            $this->params = array_slice($segments, 2);
        }
    }

    /**
     * Sanitiza el nombre de controlador o acción para evitar inyección.
     * @param string $input
     * @return string
     */
    private function sanitize($input) {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $input);
    }

    /**
     * Carga el archivo y la clase del controlador, y ejecuta la acción.
     * Maneja errores de controlador o acción inexistentes.
     */
    private function loadController() {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        $controllerName = ucfirst($this->controller) . 'Controller';
        $controllerFile = __DIR__ . '/Admin/' . $controllerName . '.php';

        // Verifica existencia del archivo controlador
        if (!file_exists($controllerFile)) {
            if ($isAjax) {
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => "Error 404: Controlador no encontrado ($controllerFile)"
                ]);
                exit();
            } else {
                die("Error 404: Controlador no encontrado ($controllerFile)");
            }
        }

        require_once $controllerFile;
        $fullClassName = 'Barkios\\controllers\\Admin\\' . $controllerName;

        // Verifica existencia de la clase
        if (!class_exists($fullClassName)) {
            if ($isAjax) {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => "Error 500: Clase del controlador no existe ($controllerName)"
                ]);
                exit();
            } else {
                die("Error 500: Clase del controlador no existe ($controllerName)");
            }
        }

        $controller = new $fullClassName();

        // Verifica existencia del método (acción)
        if (!method_exists($controller, $this->action)) {
            if ($isAjax) {
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => "Error 404: Acción '{$this->action}' no encontrada."
                ]);
                exit();
            } else {
                die("Error 404: Acción '{$this->action}' no encontrada.");
            }
        }

        // Ejecuta la acción con los parámetros
        call_user_func_array([$controller, $this->action], $this->params);
    }
}

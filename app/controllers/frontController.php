<?php
namespace Barkios\controllers;
class FrontController {
    private $controller;
    private $action;
    private $params = [];

    public function __construct() {
        $this->parseUrl();
        $this->loadController();
    }

    private function parseUrl() {
        if (isset($_GET['controller']) && isset($_GET['action'])) {
            $this->controller = $this->sanitize($_GET['controller']);
            $this->action = $this->sanitize($_GET['action']);
            // Opcional params de GET
            $this->params = $_GET['params'] ?? [];
        } else {
            // Tu código actual para leer segmentos
            $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
            $scriptName = $_SERVER['SCRIPT_NAME'];

            $path = str_replace(dirname($scriptName), '', $requestUri);
            $path = preg_replace('#^/index\.php#', '', $path);
            $path = trim(parse_url($path, PHP_URL_PATH), '/');

            $segments = array_filter(explode('/', $path));

            if (isset($segments[0]) && strtolower($segments[0]) === 'admin') {
                array_shift($segments);
            }

            $this->controller = $this->sanitize($segments[0] ?? 'products');
            $this->action = $this->sanitize($segments[1] ?? 'index');
            $this->params = array_slice($segments, 2);
        }
    }



    private function sanitize($input) {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $input);
    }

    private function loadController() {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        $controllerName = ucfirst($this->controller) . 'Controller';
        $controllerFile = __DIR__ . '/Admin/' . $controllerName . '.php';

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

        call_user_func_array([$controller, $this->action], $this->params);
    }

}

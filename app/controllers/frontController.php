<?php
namespace Barkios\controllers;

class FrontController {
    private $controller = 'products';
    private $action = 'index';
    private $params = [];
    private $isAdmin = false;

    public function __construct() {
        $this->parseUrl();
        $this->loadController();
    }

    private function parseUrl() {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath = str_replace('\\', '/', dirname($scriptName));
        $path = str_replace($basePath, '', $requestUri);
        $path = preg_replace('#^/index\.php#', '', $path);
        $path = trim(parse_url($path, PHP_URL_PATH), '/');
        $segments = $path ? explode('/', $path) : [];

        // Detectar si es admin
        if (isset($segments[0]) && strtolower($segments[0]) === 'admin') {
            $this->isAdmin = true;
            array_shift($segments);
        }

        // Asignar controlador y acción
        if (isset($segments[0]) && $segments[0] !== '') {
            $this->controller = $this->sanitize($segments[0]);
        }
        if (isset($segments[1]) && $segments[1] !== '') {
            $this->action = $this->sanitize($segments[1]);
        }

        // El resto son parámetros
        $this->params = array_slice($segments, 2);

        // Permitir override por GET (útil para AJAX)
        if (isset($_GET['controller'])) {
            $this->controller = $this->sanitize($_GET['controller']);
        }
        if (isset($_GET['action'])) {
            $this->action = $this->sanitize($_GET['action']);
        }
    }

    private function sanitize($input) {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $input);
    }

    private function loadController() {
        $controllerName = ucfirst($this->controller) . 'Controller';
        $controllerDir = $this->isAdmin ? '/Admin/' : '/';
        $controllerFile = __DIR__ . $controllerDir . $controllerName . '.php';

        if (!file_exists($controllerFile)) {
            http_response_code(404);
            die("Error 404: Controlador no encontrado ($controllerFile)");
        }

        require_once $controllerFile;
        $namespace = 'Barkios\\controllers' . ($this->isAdmin ? '\\Admin\\' : '\\');
        $fullClassName = $namespace . $controllerName;

        if (!class_exists($fullClassName)) {
            http_response_code(500);
            die("Error 500: Clase del controlador no existe ($fullClassName)");
        }

        $controller = new $fullClassName();

        if (!method_exists($controller, $this->action)) {
            http_response_code(404);
            die("Error 404: Acción '{$this->action}' no encontrada.");
        }

        call_user_func_array([$controller, $this->action], $this->params);
    }
}

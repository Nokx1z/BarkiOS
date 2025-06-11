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
            $this->params = $_GET['params'] ?? [];
        } else {
            $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
            $scriptName = $_SERVER['SCRIPT_NAME'];

            $basepath = str_replace('/index.php', '', $scriptName);
            $path = str_replace($basepath, '', $requestUri);
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

        $controllerName = ucfirst($this->controller) . 'Controller'; // Ej: ProductsController
        $controllerFile = __DIR__ . "/Admin/{$controllerName}.php";

        if (!file_exists($controllerFile)) {
            return $this->renderNotFound("Controlador no encontrado: {$controllerFile}", $isAjax);
        }

        require_once $controllerFile;

        // Asume que las funciones están definidas globalmente (no dentro de clases)
        $functionName = $this->action; // Ej: index, store, update, etc.

        if (!function_exists($functionName)) {
            return $this->renderNotFound("Función '{$functionName}' no encontrada en $controllerFile", $isAjax);
        }

        call_user_func_array($functionName, $this->params);
    }

    private function renderNotFound($message, $isAjax = false) {
        if ($isAjax) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $message]);
        } else {
            http_response_code(404);
            echo "<h1>Error 404</h1><p>$message</p>";
        }
        exit();
    }
}
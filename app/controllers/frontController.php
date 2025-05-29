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
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $scriptName = $_SERVER['SCRIPT_NAME']; // Ej: /app/index.php

        // Elimina /app/index.php del inicio de la URI
        $path = str_replace(dirname($scriptName), '', $requestUri);
        $path = preg_replace('#^/index\.php#', '', $path); // elimina index.php
        $path = trim(parse_url($path, PHP_URL_PATH), '/'); // elimina ?params y slashes

        $segments = explode('/', $path);

        // Ignorar 'admin' si es el primer segmento
        if (isset($segments[0]) && strtolower($segments[0]) === 'admin') {
            array_shift($segments); // quita 'admin'
        }

        $this->controller = $this->sanitize($segments[0] ?? 'products');
        $this->action = $this->sanitize($segments[1] ?? 'index');
        $this->params = array_slice($segments, 2);
    }


    private function sanitize($input) {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $input);
    }

    private function loadController() {
        $controllerName = ucfirst($this->controller) . 'Controller';
        $controllerFile = __DIR__ . '/Admin/' . $controllerName . '.php';

        if (!file_exists($controllerFile)) {
            die("Error 404: Controlador no encontrado ($controllerFile)");
        }

        require_once $controllerFile;
        $fullClassName = 'Barkios\\controllers\\Admin\\' . $controllerName;

        if (!class_exists($fullClassName)) {
            die("Error 500: Clase del controlador no existe ($controllerName)");
        }

        $controller = new $fullClassName();

        if (!method_exists($controller, $this->action)) {
            die("Error 404: Acción '{$this->action}' no encontrada.");
        }

        call_user_func_array([$controller, $this->action], $this->params);
    }
}

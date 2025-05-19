<?php
class FrontController {
    private $url;
    private $controller;
    private $action;
    private $params;

    public function __construct() {
        $this->parseUrl();
        $this->loadController();
    }

    private function parseUrl() {
        $request = $_SERVER['REQUEST_URI'] ?? '/';
        $parsedUrl = parse_url($request);
        $path = trim($parsedUrl['path'] ?? '', '/');
        $query = $parsedUrl['query'] ?? '';
        
        // Extraer partes de la URL (formato: /controller/action/param1/param2)
        $parts = explode('/', $path);
        $this->controller = $this->sanitize($parts[0] ?? 'home');
        $this->action = $this->sanitize($parts[1] ?? 'index');
        $this->params = array_slice($parts, 2);
        
        // Parsear query string adicional
        parse_str($query, $queryParams);
        $this->params = array_merge($this->params, $queryParams);
    }

    private function sanitize($input) {
        return preg_replace('/[^a-zA-Z0-9_-]/', '', $input);
    }

    private function loadController() {
        $controllerFile = __DIR__.'/../../app/controllers/Admin/'.ucfirst($this->controller).'Controller.php';
        
        if (!file_exists($controllerFile)) {
            die('Error 404: Controlador no encontrado');
        }

        require_once $controllerFile;
        
        $controllerClass = 'App\\Controllers\\Admin\\'.ucfirst($this->controller).'Controller';
        if (!class_exists($controllerClass)) {
            die('Error 500: Clase del controlador no existe');
        }

        $controller = new $controllerClass();
        
        if (!method_exists($controller, $this->action)) {
            die('Error 404: Acción no encontrada');
        }

        call_user_func_array([$controller, $this->action], $this->params);
    }
}

// Uso
new FrontController();
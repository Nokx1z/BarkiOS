<?php
class frontController{
    public $url;

    public function __construct($url)
    {
        $this->url = $this->sanitizeUrl($url);
        $this->loadPage();
    }

    private function sanitizeUrl($url) {
        // Limpia la URL permitiendo solo letras, números y guiones
        return preg_replace('/[^a-zA-Z0-9-]/', '', $url);
    }

    private function loadPage(){
        // Ruta ABSOLUTA a los controladores
        $controllersPath = __DIR__.'/../../app/controllers/';
        
        // Primero intenta cargar el controlador solicitado
        $requestedController = $controllersPath.$this->url.'Controller.php';
        if (file_exists($requestedController)) {
            require($requestedController);
            return;
        }
        
        // Si no existe, carga el homeController
        $defaultController = $controllersPath.'homeController.php';
        if (file_exists($defaultController)) {
            require($defaultController);
            return;
        }
        
        // Si no existe ninguno, muestra error
        die('Error 004: No se encontró ningún controlador (ni '.$this->url.'Controller.php ni homeController.php)');
    }
}
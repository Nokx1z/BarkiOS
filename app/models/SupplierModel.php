<?php
require_once __DIR__.'/../core/Database.php';

// Obtener conexión a la base de datos
function getDbConnection() {
    $database = Database::getInstance();
    return $database->getConnection();
}

// Obtener todos los productos
function getAllProducts() {
    $db = getDbConnection();
    $stmt = $db->query("SELECT * FROM productos");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Agregar nuevo producto
function addProduct($nombre, $categoria, $precio) {
    $db = getDbConnection();
    $codigo = uniqid('BARKI-');
    $stmt = $db->prepare("
        INSERT INTO productos (codigo, nombre, categoria, precio)
        VALUES (:codigo, :nombre, :categoria, :precio)
    ");
    return $stmt->execute([
        ':codigo' => $codigo,
        ':nombre' => $nombre,
        ':categoria' => $categoria,
        ':precio' => $precio
    ]);
}

// Vaciar la tabla de productos
function truncateProducts() {
    $db = getDbConnection();
    $stmt = $db->prepare("TRUNCATE TABLE productos");
    return $stmt->execute();
}

// Eliminar producto por ID
function deleteProduct($id) {
    $db = getDbConnection();
    $stmt = $db->prepare("DELETE FROM productos WHERE id = :id");
    return $stmt->execute([':id' => $id]);
}
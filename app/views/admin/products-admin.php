<?php
// Incluimos el controlador y obtenemos los productos
require_once __DIR__.'/../../controllers/Admin/ProductsController.php';

$controller = new ProductsController();

$action = $_GET['action'] ?? null;
$productToEdit = null;

switch ($action) {
    case 'delete':
        if (isset($_GET['id'])) {
            $controller->deleteProduct($_GET['id']);
            header('Location: products-admin.php?success=delete');
            exit;
        }
        break;

    case 'add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->addProduct($_POST['id'], $_POST['nombre'], $_POST['tipo'], $_POST['categoria'], $_POST['precio']);
            header('Location: products-admin.php?success=add');
            exit;
        }
        break;

    case 'edit':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->updateProduct($_POST['id'], $_POST['nombre'], $_POST['tipo'], $_POST['categoria'], $_POST['precio']);
            header('Location: products-admin.php?success=edit');
            exit;
        } elseif (isset($_GET['edit_id'])) {
            $productToEdit = $controller->getProductById($_GET['edit_id']);
        }
        break;

    default:
        $products = $controller->getProducts();
        break;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Garage Barki</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Favicon -->
    <link rel="shortcut icon" href="../../../public/assets/icons/Logo - Garage Barki.webp" type="image/x-icon">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../../../public/assets/css/admin-styles.css">
</head>
<body>

        <nav class="sidebar" id="sidebar">
        <div class="sidebar-sticky">
            <div class="sidebar-header">
                <h3>GARAGE<span>BARKI</span></h3>
                <p class="mb-0">Panel de Administración</p>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="#Profe_No_Hago_Nada">
                        <i class="fas fa-tachometer-alt"></i>
                        Inicio
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="/app/views/admin/products-admin.php">
                        <i class="fas fa-tshirt"></i>
                        Productos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/app/views/admin/supplier-admin.php">
                        <i class="fas fa-shopping-cart"></i>
                        Proveedores
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/app/views/admin/clients-admin.php">
                        <i class="fas fa-users"></i>
                        Clientes
                    </a>
                </li>
            </ul>
        </div>
    </nav>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="display-6 fw-bold text-dark">Productos</h1>
        </div>
        <button class="btn btn-primary rounded-pill px-4 me-3" data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="fas fa-plus me-1"></i> Agregar producto
        </button>
        
        <!-- Mensajes de éxito/error -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success mt-3">
                <?php 
                switch($_GET['success']) {
                    case 'add': echo 'Producto agregado correctamente'; break;
                    case 'edit': echo 'Producto actualizado correctamente'; break;
                    case 'delete': echo 'Producto eliminado correctamente'; break;
                }
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger mt-3">
                <?php 
                if ($_GET['error'] === 'id_duplicado') {
                    $id = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : '[ID no proporcionado]';
                    echo "Error: El ID <strong>$id</strong> ya está registrado.";
                } elseif ($_GET['error'] === 'producto_no_existe') {
                    echo "Error: El producto que intentas editar no existe.";
                }
                ?>
            </div>
        <?php endif; ?>

        <!-- Tabla de Productos -->
        <div class="card mt-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-hover text-center">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($products)): ?>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($product['id'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($product['nombre'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($product['tipo'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($product['categoria'] ?? '') ?></td>
                                        <td>$<?= number_format($product['precio'] ?? 0, 2) ?></td>
                                        <td>
                                            <a href="products-admin.php?action=edit&edit_id=<?= $product['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary me-2"
                                               data-bs-toggle="modal" 
                                               data-bs-target="#editProductModal">
                                                <i class="fas fa-edit"></i> Editar
                                            </a>
                                            <a href="products-admin.php?action=delete&id=<?= $product['id'] ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('¿Estás seguro de eliminar este producto?')">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <div class="alert alert-info mb-0">No hay productos disponibles</div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Añadir Producto -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
 <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">Añadir Nuevo Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="products-admin.php?action=add" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Codigo</label>
                            <input type="text" class="form-control" 
                                name="id" 
                                placeholder="Ingrese código del producto" 
                                inputmode="numeric"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" 
                                name="nombre" 
                                placeholder="Ingrese nombre del producto" 
                                pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$"
                                oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '');"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Categoría</label>
                            <select class="form-select" name="categoria" required>
                                <option value="Formal">Formal</option>
                                <option value="Casual">Casual</option>
                                <option value="Ujum">Ujum</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipo de prenda</label>
                            <select class="form-select" name="tipo" required>
                                <option value="vestidos">Vestidos</option>
                                <option value="blusas">Blusas</option>
                                <option value="pantalones">Pantalones</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Precio</label>
                            <input type="number" step="0.01" class="form-control" name="precio" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Editar Producto -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProductModalLabel">Editar Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="products-admin.php?action=edit" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" value="<?= $productToEdit['id'] ?? '' ?>">
                    <div class="mb-3">
                        <label class="form-label">Codigo</label>
                            <input type="text" class="form-control" 
                                name="id" 
                                placeholder="Ingrese código del producto" 
                                inputmode="numeric"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                required>
                        
                        <label class="form-label">Nombre</label>
                        <input type="text" class="form-control" 
                            name="nombre" 
                            value="<?= htmlspecialchars($productToEdit['nombre'] ?? '') ?>" 
                            placeholder="Ingrese nombre del producto" 
                            pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$"
                            oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '');"
                            required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Categoría</label>
                        <select class="form-select" name="categoria" required>
                            <option value="Formal" <?= ($productToEdit['categoria'] ?? '') === 'Formal' ? 'selected' : '' ?>>Formal</option>
                            <option value="Casual" <?= ($productToEdit['categoria'] ?? '') === 'Casual' ? 'selected' : '' ?>>Casual</option>
                            <option value="Ujum" <?= ($productToEdit['categoria'] ?? '') === 'Ujum' ? 'selected' : '' ?>>Ujum</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo de prenda</label>
                        <select class="form-select" name="tipo" required>
                            <option value="vestidos" <?= ($productToEdit['tipo'] ?? '') === 'vestidos' ? 'selected' : '' ?>>Vestidos</option>
                            <option value="blusas" <?= ($productToEdit['tipo'] ?? '') === 'blusas' ? 'selected' : '' ?>>Blusas</option>
                            <option value="pantalones" <?= ($productToEdit['tipo'] ?? '') === 'pantalones' ? 'selected' : '' ?>>Pantalones</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Precio</label>
                        <input type="number" step="0.01" class="form-control" 
                            name="precio" 
                            value="<?= htmlspecialchars($productToEdit['precio'] ?? '') ?>" 
                            required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Script para manejar la apertura del modal de edición con los datos correctos
document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editProductModal');
    
    editModal.addEventListener('show.bs.modal', function(event) {
        // El botón que activó el modal
        const button = event.relatedTarget;
        // Extraer info de los atributos data-*
        const editUrl = button.getAttribute('href');
        
        // Hacer una petición para obtener los datos del producto
        if(editUrl.includes('edit_id')) {
            window.location.href = editUrl;
        }
    });
});
</script>
</body>
</html>
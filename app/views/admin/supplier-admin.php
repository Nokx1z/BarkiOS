<?php
// Incluimos el controlador y obtenemos los productos
require_once __DIR__.'/../../controllers/Admin/supplierController.php';
$controller = new SupplierController();
$controller->handleRequest(); // maneja add/delete/truncate
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
    <link rel="shortcut icon" href="/assets/icons/Logo - Garage Barki.webp" type="image/x-icon">
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
                    <a class="nav-link" href="dashboard.html">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="productos.html">
                        <i class="fas fa-tshirt"></i>
                        Productos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="ordenes.html">
                        <i class="fas fa-shopping-cart"></i>
                        Órdenes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-users"></i>
                        Clientes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-chart-bar"></i>
                        Reportes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-cog"></i>
                        Configuración
                    </a>
                </li>
            </ul>
            <div class="sidebar-footer">
                <a class="nav-link" href="login.html">
                    <i class="fas fa-sign-out-alt"></i>
                    Cerrar Sesión
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="display-6 fw-bold text-dark">GARAGE<span class="text-dark">BARKI</span></h1>
            </div>
            <button class="btn btn-primary rounded-pill px-4 me-3" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="fas fa-plus me-1"></i> Añadir Producto
            </button>
            
        <form action="index.php?action=truncate" method="POST" 
            onsubmit="return confirm('¿Estás seguro de que deseas eliminar TODOS los productos y reiniciar el ID?');" 
            style="display:inline-block;">
            <button type="submit" class="btn btn-danger rounded-pill px-4">
            <i class="fas fa-trash-alt me-1"></i> Reiniciar Tabla
            </button>
        </form>
            <!-- Mensajes de éxito/error -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success mt-3">
                    <?php 
                    switch($_GET['success']) {
                        case 'add': echo 'Producto agregado correctamente'; break;
                        case 'delete': echo 'Producto eliminado correctamente'; break;
                    }
                    ?>
                </div>
            <?php endif; ?>

            <!-- Tabla de Productos -->
            <div class="card mt-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Categoría</th>
                                    <th>Precio</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($products)): ?>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($product['nombre_contacto'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($product['nombre_empresa'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($product['direccion'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($product['rif'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($product['tipo_rif'] ?? '') ?></td>
                                            <td>
                                                <a href="index.php?action=delete&id=<?= $product['id'] ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('¿Estás seguro de eliminar este producto?')">
                                                    <i class="fas fa-trash"></i> Eliminar
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">
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
                <form action="../../index.php?action=add_supplier" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre del Proveedor</label>
                            <input type="text" class="form-control" name="nombre_contacto" placeholder="Ingrese nombre" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Empresa</label>
                            <input type="text" class="form-control" name="nombre_empresa" placeholder="Ingrese nombre" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rif</label>
                            <input type="text" class="form-control" name="rif" placeholder="Ingrese rif" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipo del rif</label>
                            <select class="form-select" name="tipo_rif" required>
                                <option value="J">J</option>
                                <option value="V">V</option>
                                <option value="E">E</option>
                                <option value="P">P</option>
                                <option value="G">G</option>
                                <option value="C">C</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Direccion</label>
                            <input type="text" class="form-control" name="direccion" placeholder="Ingrese direccion" required>
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




    

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
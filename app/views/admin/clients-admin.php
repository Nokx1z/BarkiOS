<?php
// Incluimos el controlador y obtenemos los productos
require_once __DIR__.'/../../controllers/Admin/ClientsController.php';

$controller = new ClientsController();

$action = $_GET['action'] ?? null;

switch ($action) {
    case 'delete':
        if (isset($_GET['id'])) {
            $controller->deleteClient($_GET['id']);
            header('Location: clients-admin.php?success=delete');
            exit;
        }
        break;

    case 'add':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->addClient($_POST['cedula'], $_POST['nombre'], $_POST['dirección'], $_POST['telefono'], $_POST['membresia']);
            header('Location: clients-admin.php?success=add');
            exit;
        }
        break;

    default:
        $clientes = $controller->getclientss();
        break;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - Garage Barki</title>
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
    <link rel="shortcut icon" href="../../../public/assets/icons/Logo - Garage Barki.webp" type="image/x-icon">
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
                    <a class="nav-link" href="client$clientos.html">
                        <i class="fas fa-tshirt"></i>
                        client$clientos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="ordenes.html">
                        <i class="fas fa-shopping-cart"></i>
                        Órdenes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="#">
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
            <button class="btn btn-primary rounded-pill px-4 me-3" data-bs-toggle="modal" data-bs-target="#addclient$clientModal">
                <i class="fas fa-plus me-1"></i> Añadir Cliente
            </button>
            
        <form action="clients-admin.php?action=truncate" method="POST" 
            onsubmit="return confirm('¿Estás seguro de que deseas eliminar TODOS los clientes y reiniciar el ID?');" 
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
                        case 'add': echo 'Cliente agregado correctamente'; break;
                        case 'delete': echo 'Cliente eliminado correctamente'; break;
                    }
                    ?>
                </div>
            <?php endif; ?>

            <!-- Tabla de Clientes -->
            <div class="card mt-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Cédula</th>
                                    <th>Nombre</th>
                                    <th>Dirección</th>
                                    <th>Número de Teléfono</th>
                                    <th>Membresía</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($clientss)): ?>
                                    <?php foreach ($clientss as $client): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($client['cedula'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($client['nombre'] ?? '') ?></td>
                                            <td>$<?= htmlspecialchars($client['direccion'] ?? '') ?></td>
                                            <td>$<?= number_format($client['telefono'] ?? '') ?></td>
                                            <td>$<?= htmlspecialchars($client['membresia'] ?? '') ?></td>
                                            <td>
                                                <a href="index.php?action=delete&id=<?= $client['cedula'] ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('¿Estás seguro de eliminar a este cliente?')">
                                                    <i class="fas fa-trash"></i> Eliminar
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            <div class="alert alert-info mb-0">No hay clientes disponibles</div>
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

    <!-- Modal para Añadir Clientes -->
    <div class="modal fade" id="addclient$clientModal" tabindex="-1" aria-labelledby="addclient$clientModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addclient$clientModalLabel">Añadir Nuevo Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="clients-admin.php?action=add" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Cédula</label>
                            <input type="text" class="form-control" name="cedula" placeholder="Ej: V30803977" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="nombre" placeholder="Ingrese su nombre completo" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dirección</label>
                            <input type="text" class="form-control" name="direccion" placeholder="Ej: Av. Leones, Edif. Los Leones, Piso 3, Barquisimeto" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control" name="telefono" placeholder="Ej: 04245555555" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Membresía</label>
                            <select class="form-select" name="membresia" required>
                                <option value="regular">Regular</option>
                                <option value="vip">VIP</option>
                            </select>
                        </div>
                        <!--<div class="mb-3">
                            <label class="form-label">Precio</label>
                            <input type="number" step="0.01" class="form-control" name="precio" required>
                        </div>-->
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
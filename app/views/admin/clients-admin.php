<?php
/**
 * Vista de administración de clientes
 * 
 * Esta vista muestra la interfaz de usuario para gestionar los clientes del sistema.
 * Incluye un formulario para agregar nuevos clientes y una tabla para visualizar
 * y eliminar los clientes existentes.
 */

// Asegurarse de que $clientss esté definido

?>
<?= require_once __DIR__ . '/../partials/header-admin.php'; ?>
<!-- Barra lateral de navegación -->
<?= require_once __DIR__ . '/../partials/navbar-admin.php'; ?>  
   
    <!-- Contenido principal de la página -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="display-6 fw-bold text-dark">Clientes</h1>
            </div>
            <button class="btn btn-primary rounded-pill px-4 me-3" data-bs-toggle="modal" data-bs-target="#addClientModal">
                <i class="fas fa-plus me-1"></i> Añadir Cliente
            </button>
            

            <!-- Sección para mostrar mensajes de éxito o error -->
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

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger mt-3">
                    <?php 
                    if ($_GET['error'] === 'cedula_duplicada') {
            $cedula = isset($_GET['cedula']) ? htmlspecialchars($_GET['cedula']) : '';
            echo "Error: La cédula $cedula ya está registrada.";
                    }
                    ?>
    </div>
<?php endif; ?>

            <!-- Tabla que muestra el listado de clientes -->
            <div class="card mt-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                <table class="table table-hover align-middle" id="clientesTable">
                    <thead>
                        <tr>
                            <th class="text-center">Cédula</th>
                            <th>Nombre</th>
                            <th>Dirección</th>
                            <th class="text-end">Teléfono</th>
                            <th class="text-center">Membresía</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="clientesTableBody">
                        <?php if (!empty($clientss)): ?>
                            <?php foreach ($clientss as $client): ?>
                                <tr id="cliente-<?= htmlspecialchars($client['cedula']) ?>">
                                    <td class="text-center"><?= htmlspecialchars($client['cedula'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($client['nombre'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($client['direccion'] ?? '') ?></td>
                                    <td class="text-end"><?= preg_replace('/(\d{4})(\d{7})/', '$1-$2', $client['telefono'] ?? '') ?></td>
                                    <td class="text-center"><?= htmlspecialchars($client['membresia'] ?? '') ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-danger btn-eliminar" 
                                                data-cedula="<?= htmlspecialchars($client['cedula']) ?>"
                                                data-nombre="<?= htmlspecialchars($client['nombre']) ?>">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr id="no-clientes">
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

    <!-- Modal para el formulario de agregar nuevo cliente -->
    <div class="modal fade" id="addClientModal" tabindex="-1" aria-labelledby="addClientModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addClientModalLabel">Añadir Nuevo Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formAgregarCliente">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Cédula</label>
                            <input type="text" 
                                class="form-control" 
                                name="cedula" 
                                placeholder="Ej: V30803977" 
                                pattern="\d{6,8}" 
                                maxlength="8" minlength="6"
                                inputmode="numeric"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,8);"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" 
                                name="nombre" 
                                placeholder="Ingrese su nombre completo" 
                                pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$"
                                oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '');"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dirección</label>
                            <input type="text" class="form-control" name="direccion" placeholder="Ej: Av. Leones, Edif. Los Leones, Piso 3, Barquisimeto" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" 
                                class="form-control" 
                                name="telefono" 
                                placeholder="Ej: 04245555555" 
                                pattern="\d{11}" 
                                maxlength="11" minlength="11"
                                inputmode="numeric"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,11);"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Membresía</label>
                            <select class="form-select" name="membresia" required>
                                <option value="regular">Regular</option>
                                <option value="vip">VIP</option>
                            </select>
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
    
    

    <!-- Bootstrap JS Bundle with Popper -->
    <!-- SweetAlert2 para alertas bonitas -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script para manejar AJAX -->
    <script src="/BarkiOS/public/assets/js/clients-admin.js"></script>
</body>
</html>
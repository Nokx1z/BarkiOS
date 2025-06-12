<?php $pageTitle = "Proveedores | Garage Barki"; ?>
<?php require_once __DIR__ . '/../partials/header-admin.php'; ?>

<?= require_once __DIR__ . '/../partials/header-admin.php'; ?>
<!-- Barra lateral de navegación -->
<?= require_once __DIR__ . '/../partials/navbar-admin.php'; ?> 

    <!-- Contenido principal -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="display-6 fw-bold text-dark">Proveedores</h1>
            </div>
            <button class="btn btn-primary rounded-pill px-4 me-3" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                <i class="fas fa-plus me-1"></i> Agregar proveedor
            </button>
            
            <!-- Contenedor para mensajes de éxito/error -->
            <div id="message-container" class="mt-3"></div>

            <!-- Tabla de proveedores -->
            <div class="card mt-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <!-- Spinner de carga mientras se obtienen los datos -->
                        <div id="table-loading" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2 text-muted">Cargando proveedores...</p>
                        </div>
                        <!-- Tabla de proveedores (rellenada por PHP y AJAX) -->
                        <table class="table table-hover align-middle text-center d-none" id="suppliers-table">
                            <thead class="table-light">
                                <tr>
                                    <th>RIF</th>
                                    <th>Nombre del Contacto</th>
                                    <th>Empresa</th>
                                    <th>Dirección</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Renderizado inicial por PHP -->
                                <?php if (!empty($supplier)): ?>
                                    <?php foreach ($supplier as $supplier): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($supplier['tipo_rif'] ?? '') ?>-<?= htmlspecialchars($supplier['id'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($supplier['nombre_contacto'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($supplier['nombre_empresa'] ?? '') ?></td>
                                            <td><?= htmlspecialchars($supplier['direccion'] ?? '') ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary edit-supplier"
                                                        data-id="<?= $supplier['id'] ?>"
                                                        data-nombre_contacto="<?= htmlspecialchars($supplier['nombre_contacto']) ?>"
                                                        data-nombre_empresa="<?= htmlspecialchars($supplier['nombre_empresa']) ?>"
                                                        data-tipo_rif="<?= htmlspecialchars($supplier['tipo_rif']) ?>"
                                                        data-direccion="<?= htmlspecialchars($supplier['direccion']) ?>">
                                                    <i class="fas fa-edit"></i> Editar
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger delete-supplier" 
                                                        data-id="<?= $supplier['id'] ?>"
                                                        data-nombre="<?= htmlspecialchars($supplier['nombre_contacto']) ?>">
                                                    <i class="fas fa-trash"></i> Eliminar
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            <div class="alert alert-info mb-0">No hay proveedores disponibles</div>
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

    <!-- Modal para añadir proveedor -->
    <div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSupplierModalLabel">Añadir Nuevo Proveedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- Formulario para agregar proveedor -->
                <form id="addSupplierForm">
                    <div class="modal-body">
                        <!-- Campos del formulario: nombre, empresa, rif, tipo rif, dirección -->
                        <div class="mb-3">
                            <label class="form-label">Nombre del Proveedor</label>
                            <input type="text" class="form-control" 
                                name="nombre_contacto" 
                                placeholder="Ingrese nombre" 
                                pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$"
                                oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '');"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Empresa</label>
                            <input type="text" class="form-control" name="nombre_empresa" placeholder="Ingrese nombre" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rif</label>
                            <input type="text" class="form-control" 
                                name="id" 
                                placeholder="Ingrese rif" 
                                pattern="\d{9}" 
                                maxlength="9" minlength="9"
                                inputmode="numeric"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,9);"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipo del rif</label>
                            <select class="form-select" name="tipo_rif" required>
                                <option value="J">J</option>
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

    <!-- Modal para editar proveedor -->
    <div class="modal fade" id="editSupplierModal" tabindex="-1" aria-labelledby="editSupplierModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editSupplierModalLabel">Editar Proveedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form id="editSupplierForm">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit-id">
                        <div class="mb-3">
                            <label class="form-label">Nombre del Proveedor</label>
                            <input type="text" class="form-control" name="nombre_contacto" id="edit-nombre_contacto" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Empresa</label>
                            <input type="text" class="form-control" name="nombre_empresa" id="edit-nombre_empresa" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipo del rif</label>
                            <select class="form-select" name="tipo_rif" id="edit-tipo_rif" required>
                                <option value="J">J</option>
                                <option value="G">G</option>
                                <option value="C">C</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rif</label>
                            <input type="text" class="form-control" name="id" id="edit-id-visible" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dirección</label>
                            <input type="text" class="form-control" name="direccion" id="edit-direccion" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts para AJAX, Bootstrap y SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="/BarkiOs/public/assets/js/suppliers-admin.js"></script>
</body>
</html>
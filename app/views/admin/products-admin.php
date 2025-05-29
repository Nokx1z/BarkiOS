<?php
// Incluimos el controlador
//require_once __DIR__.'/../../controllers/Admin/ProductsController.php';
require_once(__DIR__ . '/../../../vendor/autoload.php');
use Barkios\controllers\Admin\ProductsController;
// Inicializamos el controlador y manejamos la petición
$controller = new ProductsController();
$controller->handleRequest();

// Obtenemos los productos para la carga inicial
$products = $controller->getProducts();
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
        
        <!-- Mensajes de éxito/error dinámicos -->
        <div id="alertContainer" class="mt-3"></div>

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
                        <tbody id="productsTableBody">
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                </td>
                            </tr>
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
                <form id="addProductForm">
                    <div class="modal-body">
                        <div id="addProductErrors" class="alert alert-danger d-none"></div>
                        <div class="mb-3">
                            <label class="form-label">Código</label>
                            <input type="text" class="form-control" 
                                id="productId"
                                name="id" 
                                placeholder="Ingrese código del producto" 
                                inputmode="numeric"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                required>
                            <div class="invalid-feedback">Por favor ingrese un código válido</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" 
                                id="productName"
                                name="nombre" 
                                placeholder="Ingrese nombre del producto" 
                                pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$"
                                oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '');"
                                required>
                            <div class="invalid-feedback">Por favor ingrese un nombre válido (solo letras y espacios)</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Categoría</label>
                            <select class="form-select" id="productCategory" name="categoria" required>
                                <option value="">Seleccione una categoría</option>
                                <option value="Formal">Formal</option>
                                <option value="Casual">Casual</option>
                                <option value="Ujum">Ujum</option>
                            </select>
                            <div class="invalid-feedback">Por favor seleccione una categoría</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipo de prenda</label>
                            <select class="form-select" id="productType" name="tipo" required>
                                <option value="">Seleccione un tipo</option>
                                <option value="vestidos">Vestidos</option>
                                <option value="blusas">Blusas</option>
                                <option value="pantalones">Pantalones</option>
                            </select>
                            <div class="invalid-feedback">Por favor seleccione un tipo de prenda</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Precio</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" class="form-control" 
                                    id="productPrice" 
                                    name="precio" 
                                    min="0" 
                                    required>
                                <div class="invalid-feedback">Por favor ingrese un precio válido</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="addProductBtn">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            <span class="btn-text">Guardar</span>
                        </button>
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
            <form id="editProductForm">
                <input type="hidden" name="id" id="editProductId">
                <div class="modal-body">
                    <div id="editProductErrors" class="alert alert-danger d-none"></div>
                    <div class="mb-3">
                        <label class="form-label">Código</label>
                        <input type="text" class="form-control" 
                            id="editProductIdDisplay"
                            disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" class="form-control" 
                            name="nombre" 
                            id="editProductName"
                            placeholder="Ingrese nombre del producto" 
                            pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$"
                            oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '');"
                            required>
                        <div class="invalid-feedback">Por favor ingrese un nombre válido (solo letras y espacios)</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Categoría</label>
                        <select class="form-select" name="categoria" id="editProductCategory" required>
                            <option value="">Seleccione una categoría</option>
                            <option value="Formal">Formal</option>
                            <option value="Casual">Casual</option>
                            <option value="Ujum">Ujum</option>
                        </select>
                        <div class="invalid-feedback">Por favor seleccione una categoría</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo de prenda</label>
                        <select class="form-select" name="tipo" id="editProductType" required>
                            <option value="">Seleccione un tipo</option>
                            <option value="vestidos">Vestidos</option>
                            <option value="blusas">Blusas</option>
                            <option value="pantalones">Pantalones</option>
                        </select>
                        <div class="invalid-feedback">Por favor seleccione un tipo de prenda</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Precio</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" 
                                step="0.01" 
                                class="form-control" 
                                name="precio" 
                                id="editProductPrice" 
                                min="0"
                                required>
                            <div class="invalid-feedback">Por favor ingrese un precio válido</div>
                        </div>
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
<!-- SweetAlert2 para alertas bonitas -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips de Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Variables globales
    const productsTableBody = document.getElementById('productsTableBody');
    const alertContainer = document.getElementById('alertContainer');
    
    // Cargar productos al iniciar la página
    loadProducts();
    
    // Manejador para el formulario de agregar producto
    const addProductForm = document.getElementById('addProductForm');
    if (addProductForm) {
        addProductForm.addEventListener('submit', handleAddProduct);
    }
    
    // Manejador para el formulario de editar producto
    const editProductForm = document.getElementById('editProductForm');
    if (editProductForm) {
        editProductForm.addEventListener('submit', handleUpdateProduct);
    }
    
    // Manejador para el modal de agregar producto
    const addProductModalElement = document.getElementById('addProductModal');
    if (addProductModalElement) {
        addProductModalElement.addEventListener('show.bs.modal', function() {
            // Limpiar el formulario
            const form = document.getElementById('addProductForm');
            if (form) {
                form.reset();
                // Remover clases de validación
                form.classList.remove('was-validated');
            }
            
            // Restablecer el botón de guardar
            const saveButton = form ? form.querySelector('button[type="submit"]') : null;
            if (saveButton) {
                saveButton.disabled = false;
                saveButton.innerHTML = '<i class="fas fa-save me-2"></i>Guardar Producto';
            }
            
            // Limpiar mensajes de error
            const errorContainer = document.getElementById('addProductError');
            if (errorContainer) {
                errorContainer.innerHTML = '';
                errorContainer.classList.add('d-none');
            }
        });
    }
    
    // Manejador para el modal de edición
    const editModalElement = document.getElementById('editProductModal');
    
    if (!editModalElement) {
        console.error('No se encontró el elemento del modal de edición');
    } else {
        // Inicializar el modal de Bootstrap
        const editModal = new bootstrap.Modal(editModalElement);
        
        // Manejador para cuando se muestra el modal
        editModalElement.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            if (!button) {
                console.error('No se encontró el botón relacionado');
                return;
            }
            
            const productId = button.getAttribute('data-product-id');
            if (productId) {
                loadProductForEdit(productId);
            } else {
                console.error('No se encontró el ID del producto');
            }
        });
        
        // Manejador para cuando se oculta el modal
        editModalElement.addEventListener('hidden.bs.modal', function() {
            // Limpiar el formulario
            const form = document.getElementById('editProductForm');
            if (form) {
                form.reset();
            }
            // Limpiar errores
            const errorContainer = document.getElementById('editProductErrors');
            if (errorContainer) {
                errorContainer.classList.add('d-none');
                errorContainer.innerHTML = '';
            }
        });
        
        // Hacer el modal disponible globalmente si es necesario
        window.editModal = editModal;
    }
    
    // Función para cargar productos
    function loadProducts() {
        console.log('Iniciando carga de productos...');
        
        // Mostrar indicador de carga
        productsTableBody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center">
                    <div class="d-flex justify-content-center align-items-center" style="min-height: 100px;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando productos...</span>
                        </div>
                        <span class="ms-2">Cargando productos...</span>
                    </div>
                </td>
            </tr>`;
        
        // Usar la ruta actual para la petición
        const currentPath = window.location.pathname;
        const url = new URL(window.location.origin + currentPath);
        url.searchParams.append('action', 'get_products');
        url.searchParams.append('_', new Date().getTime()); // Evitar caché
        
        console.log('Realizando petición a:', url.toString());
        
        fetch(url.toString(), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache',
                'Expires': '0'
            },
            cache: 'no-store'
        })
        .then(response => {
            console.log('Respuesta recibida, estado:', response.status);
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Error en la respuesta:', text);
                    throw new Error(`Error ${response.status}: ${text || 'Error al cargar los productos'}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Datos recibidos:', data);
            
            if (!data) {
                throw new Error('No se recibieron datos del servidor');
            }
            
            if (data.success === false) {
                throw new Error(data.message || 'Error al cargar los productos');
            }
            
            if (data.products && Array.isArray(data.products)) {
                if (data.products.length > 0) {
                    renderProducts(data.products);
                } else {
                    productsTableBody.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center">
                                <div class="alert alert-info mb-0">No hay productos registrados</div>
                            </td>
                        </tr>`;
                }
            } else {
                throw new Error('Formato de datos inválido');
            }
        })
        .catch(error => {
            console.error('Error al cargar los productos:', error);
            productsTableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-danger">
                        <div class="alert alert-danger">
                            <strong>Error:</strong> ${error.message || 'No se pudieron cargar los productos'}
                            <button class="btn btn-sm btn-outline-danger ms-2" onclick="loadProducts()">
                                <i class="fas fa-sync-alt"></i> Reintentar
                            </button>
                        </div>
                    </td>
                </tr>`;
        });
    }
    
    // Función para renderizar productos en la tabla
    function renderProducts(products) {
        console.log('Renderizando productos:', products); // Para depuración
        
        if (!products || products.length === 0) {
            productsTableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center">
                        <div class="alert alert-info mb-0">No hay productos disponibles</div>
                    </td>
                </tr>`;
            return;
        }
        
        // Limpiar la tabla
        productsTableBody.innerHTML = '';
        
        let html = '';
        products.forEach(product => {
            html += `
                <tr data-product-id="${product.id}">
                    <td>${product.id}</td>
                    <td>${escapeHtml(product.nombre || '')}</td>
                    <td>${escapeHtml(product.tipo || '')}</td>
                    <td>${escapeHtml(product.categoria || '')}</td>
                    <td>$${parseFloat(product.precio || 0).toFixed(2)}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-2 btn-edit" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editProductModal"
                                data-product-id="${product.id}">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-delete" 
                                data-product-id="${product.id}"
                                data-product-name="${escapeHtml(product.nombre || '')}">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </td>
                </tr>`;
        });
        
        productsTableBody.innerHTML = html;
        
        // Agregar manejadores de eventos a los botones de eliminar
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', handleDeleteProduct);
        });
    }
    
    // Manejador para agregar producto
    function handleAddProduct(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        const submitButton = form.querySelector('button[type="submit"]');
        const spinner = submitButton ? submitButton.querySelector('.spinner-border') : null;
        const buttonText = submitButton ? submitButton.querySelector('.btn-text') : null;
        
        // Validar formulario
        if (!validateForm(form)) {
            return false;
        }
        
        // Mostrar spinner y deshabilitar botón
        if (submitButton) {
            submitButton.disabled = true;
            if (spinner) spinner.classList.remove('d-none');
            if (buttonText) buttonText.textContent = 'Guardando...';
        }
        
        // Crear objeto con los datos del formulario
        const formDataObj = {};
        formData.forEach((value, key) => {
            formDataObj[key] = value;
        });
        
        // Enviar datos por AJAX
        fetch('products-admin.php?action=add_ajax', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams(formDataObj).toString(),
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`HTTP error! status: ${response.status}, body: ${text}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (!data) {
                throw new Error('No se recibió una respuesta válida del servidor');
            }
            
            // Cerrar el modal y limpiar
            const modalElement = document.getElementById('addProductModal');
            if (modalElement) {
                const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                modal.hide();
                
                // Limpiar el formulario
                const form = document.getElementById('addProductForm');
                if (form) form.reset();
                
                // Restablecer el botón
                if (submitButton) {
                    submitButton.disabled = false;
                    if (spinner) spinner.classList.add('d-none');
                    if (buttonText) buttonText.textContent = 'Guardar Producto';
                }
                
                // Eliminar el backdrop manualmente si existe
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
                
                // Restaurar el scroll del body
                document.body.style.overflow = 'auto';
                document.body.style.paddingRight = '0';
            }
            
            // Mostrar mensaje de éxito
            showAlert('Producto agregado correctamente', 'success');
            
            // Recargar la lista de productos
            loadProducts();
        })
        .catch(error => {
            console.error('Error al agregar el producto:', error);
            
            // Mostrar mensaje de error
            showAlert('Error al agregar el producto: ' + error.message, 'danger');
            
            // Restablecer el botón
            if (submitButton) {
                submitButton.disabled = false;
                if (spinner) spinner.classList.add('d-none');
                if (buttonText) buttonText.textContent = 'Guardar Producto';
            }
        });
    }
    
    function loadProductForEdit(productId) {
        // Limpiar errores previos
        document.getElementById('editProductErrors').classList.add('d-none');
        
        // Mostrar indicador de carga
        const editButton = document.querySelector(`button[data-product-id="${productId}"][data-bs-target="#editProductModal"]`);
        const originalHtml = editButton ? editButton.innerHTML : '';
        if (editButton) {
            editButton.disabled = true;
            editButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cargando...';
        }
        
        fetch(`products-admin.php?action=get_products&id=${productId}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Cache-Control': 'no-cache',
                'Pragma': 'no-cache'
            },
            cache: 'no-cache'
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`HTTP error! status: ${response.status}, body: ${text}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (!data) {
                throw new Error('No se recibió una respuesta válida del servidor');
            }
            
            if (data.success && data.products && data.products.length > 0) {
                const product = data.products[0];
                
                // Llenar el formulario con los datos del producto
                const form = document.getElementById('editProductForm');
                form.reset(); // Limpiar el formulario primero
                
                document.getElementById('editProductId').value = product.id;
                document.getElementById('editProductIdDisplay').value = product.id;
                document.getElementById('editProductName').value = product.nombre || '';
                document.getElementById('editProductType').value = product.tipo || '';
                document.getElementById('editProductCategory').value = product.categoria || '';
                document.getElementById('editProductPrice').value = product.precio || '';
                
                // Mostrar el modal
                const editModal = new bootstrap.Modal(document.getElementById('editProductModal'));
                editModal.show();
            } else {
                throw new Error(data.message || 'No se encontró el producto');
            }
        })
        .catch(error => {
            console.error('Error al cargar el producto:', error);
            showAlert('Error al cargar los datos del producto: ' + error.message, 'danger');
        })
        .finally(() => {
            // Restaurar el botón
            if (editButton) {
                editButton.disabled = false;
                editButton.innerHTML = originalHtml;
            }
            const modal = bootstrap.Modal.getInstance(document.getElementById('editProductModal'));
            modal.hide();
        });
    }
    
    // Manejador para actualizar producto
    function handleUpdateProduct(e) {
        e.preventDefault();
        
        const form = e.target;
        const submitButton = form.querySelector('button[type="submit"]');
        let spinner = null;
        let buttonText = null;
        
        if (submitButton) {
            spinner = submitButton.querySelector('.spinner-border');
            buttonText = submitButton.querySelector('.btn-text');
            // Deshabilitar botón y mostrar spinner
            submitButton.disabled = true;
            if (spinner) spinner.classList.remove('d-none');
            if (buttonText) buttonText.textContent = 'Guardando...';
        }
        
        const errorContainer = document.getElementById('editProductErrors');
        
        // Validar formulario
        if (!validateForm(form)) {
            if (submitButton) {
                submitButton.disabled = false;
                if (spinner) spinner.classList.add('d-none');
                if (buttonText) buttonText.textContent = 'Guardar Cambios';
            }
            return false;
        }
        
        // Limpiar errores previos
        if (errorContainer) {
            errorContainer.classList.add('d-none');
            errorContainer.innerHTML = '';
        }
        
        // Crear objeto con los datos del formulario
        const formData = new URLSearchParams();
        formData.append('id', form.querySelector('#editProductId').value);
        formData.append('nombre', form.querySelector('#editProductName').value);
        formData.append('tipo', form.querySelector('#editProductType').value);
        formData.append('categoria', form.querySelector('#editProductCategory').value);
        formData.append('precio', form.querySelector('#editProductPrice').value);
        
        console.log('Enviando datos:', Object.fromEntries(formData)); // Para depuración
        
        // Usar la ruta actual para la petición
        const currentPath = window.location.pathname;
        const url = new URL(window.location.origin + currentPath);
        url.searchParams.append('action', 'edit_ajax');
        
        console.log('Enviando datos a:', url.toString());
        
        // Enviar la petición
        fetch(url.toString(), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest',
                'Cache-Control': 'no-cache, no-store, must-revalidate',
                'Pragma': 'no-cache',
                'Expires': '0'
            },
            body: formData.toString(),
            cache: 'no-store',
            credentials: 'same-origin'
        })
        .then(async response => {
            console.log('Respuesta recibida, estado:', response.status);
            const responseText = await response.text();
            
            if (!response.ok) {
                console.error('Error en la respuesta:', responseText);
                throw new Error(`Error ${response.status}: ${responseText || 'Error al actualizar el producto'}`);
            }
            
            try {
                // Intentar parsear la respuesta como JSON
                const data = JSON.parse(responseText);
                console.log('Datos recibidos:', data);
                
                if (!data) {
                    throw new Error('No se recibieron datos del servidor');
                }
                
                if (data.success) {
                    // Mostrar mensaje de éxito
                    showAlert('Producto actualizado correctamente', 'success');
                    
                    // Cerrar el modal y eliminar el backdrop
                    const modalElement = document.getElementById('editProductModal');
                    if (modalElement) {
                        const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                        modal.hide();
                        
                        // Eliminar el backdrop manualmente si existe
                        const backdrop = document.querySelector('.modal-backdrop');
                        if (backdrop) {
                            backdrop.remove();
                        }
                        
                        // Restaurar el scroll del body
                        document.body.style.overflow = 'auto';
                        document.body.style.paddingRight = '0';
                    }
                    
                    // Recargar la lista de productos
                    loadProducts();
                    
                    return data;
                } else {
                    // Mostrar mensaje de error del servidor
                    const errorMessage = data.message || 'Error al actualizar el producto';
                    showAlert(errorMessage, 'danger');
                }
                
            } catch (error) {
                // Si hay un error al parsear, pero la respuesta fue exitosa, asumir que se actualizó correctamente
                if (response.ok && responseText.includes('success')) {
                    console.log('Respuesta no es JSON pero parece exitosa, actualizando interfaz...');
                    
                    // Cerrar el modal y eliminar el backdrop
                    const modalElement = document.getElementById('editProductModal');
                    if (modalElement) {
                        const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                        modal.hide();
                        
                        // Eliminar el backdrop manualmente si existe
                        const backdrop = document.querySelector('.modal-backdrop');
                        if (backdrop) {
                            backdrop.remove();
                        }
                        
                        // Restaurar el scroll del body
                        document.body.style.overflow = 'auto';
                        document.body.style.paddingRight = '0';
                    }
                    
                    // Mostrar mensaje de éxito
                    showAlert('Producto actualizado correctamente', 'success');
                    
                    // Recargar la lista de productos
                    loadProducts();
                    
                    return { success: true };
                }
                
                console.error('Error al procesar la respuesta:', error, '\nRespuesta recibida:', responseText);
                showAlert('Error al procesar la respuesta del servidor', 'danger');
            }
        })
        .catch(error => {
            console.error('Error al actualizar el producto:', error);
            
            // Mostrar mensaje de error
            let errorMessage = 'Error al procesar la solicitud';
            if (error.message.includes('JSON.parse')) {
                errorMessage = 'Error al procesar la respuesta del servidor';
            } else if (error.message) {
                errorMessage = error.message;
            }
            
            if (errorContainer) {
                errorContainer.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ${errorMessage}
                    </div>`;
                errorContainer.classList.remove('d-none');
                // Hacer scroll al mensaje de error
                errorContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                showAlert(errorMessage, 'danger');
            }
        })
        .finally(() => {
            // Restaurar el botón
            if (submitButton) {
                submitButton.disabled = false;
                if (spinner) spinner.classList.add('d-none');
                if (buttonText) buttonText.textContent = 'Guardar Cambios';
            }
        });
    }
    
    // Manejador para eliminar producto
    function handleDeleteProduct(e) {
        const button = e.currentTarget;
        const productId = button.getAttribute('data-product-id');
        const productName = button.getAttribute('data-product-name');
        
        // Mostrar confirmación
        Swal.fire({
            title: '¿Estás seguro?',
            html: `¿Deseas eliminar el producto <strong>${escapeHtml(productName)}</strong>?<br><br><span class="text-danger">Esta acción no se puede deshacer.</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash me-1"></i> Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar indicador de carga
                button.disabled = true;
                const originalHtml = button.innerHTML;
                button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Eliminando...';
                
                // Enviar solicitud de eliminación
                const formData = new URLSearchParams();
                formData.append('id', productId);
                
                fetch('products-admin.php?action=delete_ajax', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`HTTP error! status: ${response.status}, body: ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data) {
                        throw new Error('No se recibió una respuesta válida del servidor');
                    }
                    
                    if (data.success) {
                        // Eliminar la fila de la tabla
                        const row = button.closest('tr');
                        if (row) {
                            row.style.transition = 'opacity 0.3s';
                            row.style.opacity = '0';
                            
                            setTimeout(() => {
                                row.remove();
                                
                                // Verificar si la tabla quedó vacía
                                if (productsTableBody && productsTableBody.querySelectorAll('tr').length === 0) {
                                    productsTableBody.innerHTML = `
                                        <tr>
                                            <td colspan="6" class="text-center">
                                                <div class="alert alert-info mb-0">No hay productos disponibles</div>
                                            </td>
                                        </tr>`;
                                }
                                
                                // Mostrar mensaje de éxito
                                showAlert(data.message || 'Producto eliminado correctamente', 'success');
                            }, 300);
                        } else {
                            // Si no se pudo encontrar la fila, recargar la tabla
                            loadProducts();
                            showAlert(data.message || 'Producto eliminado correctamente', 'success');
                        }
                    } else {
                        throw new Error(data.message || 'Error al eliminar el producto');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    const errorMessage = error.message.includes('JSON.parse') 
                        ? 'Error al procesar la respuesta del servidor' 
                        : error.message;
                    
                    showAlert(errorMessage || 'Error al eliminar el producto', 'danger');
                    if (button) {
                        button.disabled = false;
                        button.innerHTML = originalHtml;
                    }
                });
            }
        });
    }
    
    // Función para validar formularios
    function validateForm(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input, select, textarea');
        
        // Limpiar validaciones previas
        inputs.forEach(input => {
            input.classList.remove('is-invalid');
            if (input.nextElementSibling && input.nextElementSibling.classList.contains('invalid-feedback')) {
                input.nextElementSibling.textContent = '';
            }
        });
        
        // Validar cada campo
        inputs.forEach(input => {
            if (input.hasAttribute('required') && !input.value.trim()) {
                markAsInvalid(input, 'Este campo es obligatorio');
                isValid = false;
            } else if (input.type === 'email' && input.value && !isValidEmail(input.value)) {
                markAsInvalid(input, 'Por favor ingrese un correo electrónico válido');
                isValid = false;
            } else if (input.type === 'number' && input.hasAttribute('min') && parseFloat(input.value) < parseFloat(input.getAttribute('min'))) {
                markAsInvalid(input, `El valor mínimo permitido es ${input.getAttribute('min')}`);
                isValid = false;
            } else if (input.pattern && !new RegExp(input.pattern).test(input.value) && input.value) {
                markAsInvalid(input, 'El formato no es válido');
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    // Función para marcar un campo como inválido
    function markAsInvalid(input, message) {
        input.classList.add('is-invalid');
        if (input.nextElementSibling && input.nextElementSibling.classList.contains('invalid-feedback')) {
            input.nextElementSibling.textContent = message;
        }
    }
    
    // Función para mostrar errores de formulario
    function showFormErrors(containerId, errors) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        let errorHtml = '';
        
        if (typeof errors === 'string') {
            errorHtml = `<p class="mb-0">${escapeHtml(errors)}</p>`;
        } else if (errors.message) {
            errorHtml = `<p class="mb-0">${escapeHtml(errors.message)}</p>`;
        } else {
            errorHtml = '<ul class="mb-0">';
            for (const field in errors) {
                if (Array.isArray(errors[field])) {
                    errors[field].forEach(error => {
                        errorHtml += `<li>${escapeHtml(error)}</li>`;
                    });
                } else {
                    errorHtml += `<li>${escapeHtml(errors[field])}</li>`;
                }
            }
            errorHtml += '</ul>';
        }
        
        container.innerHTML = errorHtml;
        container.classList.remove('d-none');
    }
    
    // Función para mostrar alertas
    function showAlert(message, type = 'info') {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>`;
        
        // Insertar la alerta al principio del contenedor
        alertContainer.insertAdjacentHTML('afterbegin', alertHtml);
        
        // Eliminar la alerta después de 5 segundos
        setTimeout(() => {
            const alert = alertContainer.querySelector('.alert');
            if (alert) {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }
        }, 5000);
    }
    
    // Función auxiliar para escapar HTML
    function escapeHtml(unsafe) {
        if (unsafe === null || unsafe === undefined) return '';
        return String(unsafe)
            .split('&').join('&amp;')
            .split('<').join('&lt;')
            .split('>').join('&gt;')
            .split('"').join('&quot;')
            .split("'").join('&#039;');
    }
    
    // Función para validar email
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(String(email).toLowerCase());
    }

    // Función para inicializar tooltips
    function initTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        return tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Inicializar tooltips
    initTooltips();

    // Cerrar el manejador DOMContentLoaded
});
</script>
</body>
</html>
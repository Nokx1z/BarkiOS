    $(document).ready(function() {
        // Inicializar tooltips de Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Función para mostrar mensajes
        function showMessage(type, message) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const icon = type === 'success' ? 'check-circle' : 'exclamation-triangle';
            
            // Cerrar cualquier mensaje existente
            $('.alert').alert('close');
            
            const alert = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    <i class="fas fa-${icon} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>`;
                
            $('#message-container').html(alert);
            
            // Ocultar el mensaje después de 5 segundos
            setTimeout(() => {
                $('.alert').fadeOut('slow');
            }, 5000);
        }

        // Función para actualizar la tabla de proveedores
        function updateSuppliersTable() {
            const $table = $('#suppliers-table');
            const $loading = $('#table-loading');
            const $tbody = $table.find('tbody');
            
            // Mostrar cargador y ocultar tabla
            $loading.removeClass('d-none');
            $table.addClass('d-none');
            
            $.ajax({
                url: 'index.php?controller=supplier&action=get_suppliers',
                type: 'GET',
                dataType: 'json',
                success: function(suppliers) {
                    $tbody.empty();
                    
                    if (suppliers.length > 0) {
                        suppliers.forEach(supplier => {
                            const row = `
                                <tr>
                                    <td>${supplier.tipo_rif}-${supplier.id}</td>
                                    <td>${supplier.nombre_contacto}</td>
                                    <td>${supplier.nombre_empresa}</td>
                                    <td>${supplier.direccion}</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary edit-supplier"
                                                data-id="${supplier.id}"
                                                data-nombre_contacto="${supplier.nombre_contacto}"
                                                data-nombre_empresa="${supplier.nombre_empresa}"
                                                data-tipo_rif="${supplier.tipo_rif}"
                                                data-direccion="${supplier.direccion}">
                                            <i class="fas fa-edit"></i> Editar
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger delete-supplier" 
                                                data-id="${supplier.id}"
                                                data-nombre="${supplier.nombre_contacto}"
                                                data-bs-toggle="tooltip"
                                                title="Eliminar proveedor">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </td>
                                </tr>`;
                            $tbody.append(row);
                        });
                    } else {
                        $tbody.html(`
                            <tr>
                                <td colspan="5" class="text-center">
                                    <div class="alert alert-info mb-0">No hay proveedores registrados</div>
                                </td>
                            </tr>`);
                    }
                    
                    // Ocultar cargador y mostrar tabla
                    $loading.addClass('d-none');
                    $table.removeClass('d-none');
                    
                    // Reinicializar tooltips
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                        new bootstrap.Tooltip(tooltipTriggerEl);
                    });
                    
                    // Reasignar eventos de eliminación
                    $('.delete-supplier').on('click', handleDeleteSupplier);
                },
                error: function(xhr, status, error) {
                    console.error('Error al cargar proveedores:', { status, error, response: xhr.responseText });
                    $loading.addClass('d-none');
                    $table.removeClass('d-none');
                    $tbody.html('<tr><td colspan="5" class="text-center text-danger">Error al cargar los datos. Intenta recargar la página.</td></tr>');
                    showMessage('error', 'Error al cargar los proveedores. Por favor, recarga la página.');
                }
            });
        }

        // Función para manejar el envío del formulario
        function handleFormSubmit(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $submitBtn = $form.find('button[type="submit"]');
            const originalBtnText = $submitBtn.html();
            
            // Validar el formulario
            const formData = {
                id: $form.find('input[name="id"]').val().trim(),
                nombre_contacto: $form.find('input[name="nombre_contacto"]').val().trim(),
                nombre_empresa: $form.find('input[name="nombre_empresa"]').val().trim(),
                direccion: $form.find('input[name="direccion"]').val().trim(),
                tipo_rif: $form.find('select[name="tipo_rif"]').val()
            };
            
            // Configurar la URL con el parámetro de acción
            const url = 'index.php?controller=supplier&action=add_ajax';
            
            // Validación del lado del cliente
            if (!formData.id || formData.id.length !== 9) {
                showMessage('error', 'El RIF debe tener exactamente 9 dígitos');
                return false;
            }
            
            if (!formData.nombre_contacto) {
                showMessage('error', 'El nombre del contacto es obligatorio');
                return false;
            }
            
            // Deshabilitar el botón y mostrar spinner
            $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...');
            
            console.log('Enviando datos:', formData); // Depuración
            
            // Enviar la solicitud AJAX
            console.log('Enviando a:', url, 'Datos:', formData);
            
            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    console.log('Respuesta recibida:', response); // Depuración
                    
                    if (response && response.success) {
                        showMessage('success', 'Proveedor agregado correctamente');
                        $('#addSupplierModal').modal('hide');
                        updateSuppliersTable();
                        $form[0].reset();
                    } else {
                        const errorMsg = response && response.message ? response.message : 'Error desconocido al agregar el proveedor';
                        showMessage('error', errorMsg);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error en la solicitud AJAX:', { status, error, response: xhr.responseText });
                    
                    let errorMessage = 'Error en la solicitud al servidor';
                    
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response && response.message) {
                            errorMessage = response.message;
                        } else if (xhr.status === 0) {
                            errorMessage = 'No se pudo conectar con el servidor. Verifica tu conexión a Internet.';
                        } else if (xhr.status === 500) {
                            errorMessage = 'Error interno del servidor. Por favor, contacta al administrador.';
                        }
                    } catch (e) {
                        console.error('Error al analizar la respuesta del servidor:', e);
                    }
                    
                    showMessage('error', errorMessage);
                },
                complete: function() {
                    $submitBtn.prop('disabled', false).html(originalBtnText);
                }
            });
        }
        
        // Asignar el manejador de eventos al formulario
        $('#addSupplierForm').on('submit', handleFormSubmit);

        // Cerrar el modal y limpiar el formulario
        $('#addSupplierModal').on('hidden.bs.modal', function () {
            $('#addSupplierForm')[0].reset();
            $('#addSupplierForm .is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();
        });
        

        // Función para manejar la eliminación de proveedores
        function handleDeleteSupplier() {
            const button = $(this);
            const supplierId = button.data('id');
            const supplierName = button.data('nombre');
            
            if (!supplierId || !supplierName) {
                console.error('Datos de proveedor inválidos:', { id: supplierId, name: supplierName });
                showMessage('error', 'Datos de proveedor inválidos');
                return;
            }
            
            console.log('Iniciando eliminación del proveedor:', { id: supplierId, name: supplierName });
            
            Swal.fire({
                title: '¿Estás seguro?',
                html: `¿Deseas eliminar al proveedor <strong>${supplierName}</strong>?<br>Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash me-1"></i> Sí, eliminar',
                cancelButtonText: '<i class="fas fa-times me-1"></i> Cancelar',
                reverseButtons: true,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve) => {
                        console.log('Enviando solicitud de eliminación para el proveedor ID:', supplierId);
                        
const deleteUrl = `index.php?controller=supplier&action=delete_ajax`;
                        console.log('Enviando eliminación a:', deleteUrl, 'ID:', supplierId);
                        
                        $.ajax({
                            url: deleteUrl,
                            type: 'POST',
                            data: {
                                id: supplierId
                            },
                            dataType: 'json',
                            success: function(response) {
                                console.log('Respuesta de eliminación recibida:', response);
                                resolve(response);
                            },
                            error: function(xhr, status, error) {
                                console.error('Error en la solicitud de eliminación:', { status, error, response: xhr.responseText });
                                let errorMsg = 'Error en la solicitud de eliminación';
                                
                                try {
                                    const response = JSON.parse(xhr.responseText);
                                    if (response && response.message) {
                                        errorMsg = response.message;
                                    }
                                } catch (e) {
                                    console.error('Error al analizar la respuesta de error:', e);
                                }
                                
                                resolve({ 
                                    success: false, 
                                    message: errorMsg 
                                });
                            }
                        });
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    if (result.value && result.value.success) {
                        showMessage('success', 'Proveedor eliminado correctamente');
                        updateSuppliersTable();
                    } else {
                        const errorMsg = result.value && result.value.message 
                            ? result.value.message 
                            : 'Error al eliminar el proveedor';
                        showMessage('error', errorMsg);
                    }
                }
            });
        }

        // Asignar el manejador de eventos a los botones de eliminar
        $(document).on('click', '.delete-supplier', handleDeleteSupplier);
        
        // Cargar la tabla inicialmente
        updateSuppliersTable();
    });
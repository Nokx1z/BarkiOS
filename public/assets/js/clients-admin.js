document.addEventListener('DOMContentLoaded', function() {
            // Inicializar tooltips de Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Inicializar el modal
            var addClientModal = document.getElementById('addClientModal');
            if (addClientModal) {
                addClientModal.addEventListener('hidden.bs.modal', function () {
                    // Limpiar el formulario cuando se cierre el modal
                    var form = document.getElementById('formAgregarCliente');
                    if (form) {
                        form.reset();
                        // Limpiar mensajes de error
                        var errorElements = form.querySelectorAll('.is-invalid');
                        errorElements.forEach(function(element) {
                            element.classList.remove('is-invalid');
                        });
                        var errorMessages = form.querySelectorAll('.invalid-feedback');
                        errorMessages.forEach(function(element) {
                            element.remove();
                        });
                    }
                });
            }
            // Agregar cliente con AJAX
            const formAgregar = document.getElementById('formAgregarCliente');
            if (formAgregar) {
                formAgregar.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Limpiar errores anteriores
                    const errorMessages = formAgregar.querySelectorAll('.invalid-feedback');
                    errorMessages.forEach(el => el.remove());
                    const invalidInputs = formAgregar.querySelectorAll('.is-invalid');
                    invalidInputs.forEach(el => el.classList.remove('is-invalid'));
                    
                    const formData = new FormData(this);
                    
                    // Validación básica del lado del cliente
                    let hasErrors = false;
                    const requiredFields = formAgregar.querySelectorAll('[required]');
                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            field.classList.add('is-invalid');
                            const errorDiv = document.createElement('div');
                            errorDiv.className = 'invalid-feedback';
                            errorDiv.textContent = 'Este campo es obligatorio';
                            field.parentNode.appendChild(errorDiv);
                            hasErrors = true;
                        }
                    });
                    
                    if (hasErrors) {
                        return; // Detener el envío si hay errores de validación
                    }
                    
                    // Mostrar loading
                    const submitButton = formAgregar.querySelector('button[type="submit"]');
                    const originalButtonText = submitButton.innerHTML;
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...';
                    
                    fetch('index.php?action=add', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error en la red');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Cerrar el modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById('addClientModal'));
                            modal.hide();
                            
                            // Mostrar mensaje de éxito
                            Swal.fire({
                                icon: 'success',
                                title: '¡Éxito!',
                                text: data.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            
                            // Agregar la nueva fila a la tabla
                            agregarFilaCliente(data.cliente);
                        } else {
                            throw new Error(data.message || 'Error al procesar la solicitud');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        let errorMessage = 'Ocurrió un error al procesar la solicitud';
                        
                        if (error.message.includes('Failed to fetch')) {
                            errorMessage = 'Error de conexión. Por favor, verifica tu conexión a internet.';
                        } else if (error.message) {
                            errorMessage = error.message;
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage,
                            confirmButtonText: 'Aceptar'
                        });
                    })
                    .finally(() => {
                        // Restaurar el botón
                        if (submitButton) {
                            submitButton.disabled = false;
                            submitButton.innerHTML = originalButtonText;
                        }
                    });
                });
            }
            
            // Eliminar cliente con AJAX
            document.addEventListener('click', function(e) {
                const btnEliminar = e.target.closest('.btn-eliminar');
                if (!btnEliminar) return;

                const boton = btnEliminar;
                const cedula = boton.getAttribute('data-cedula');
                const nombre = boton.getAttribute('data-nombre');
                
                Swal.fire({
                    title: '¿Estás seguro?',
                    html: `¿Deseas eliminar al cliente <strong>${nombre}</strong> con cédula <strong>${cedula}</strong>?<br><br><span class="text-danger">Esta acción no se puede deshacer.</span>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-trash me-1"></i> Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return fetch(`clients-admin.php?action=delete&cedula=${cedula}`, {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Error en la red');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (!data.success) {
                                throw new Error(data.message || 'Error al eliminar el cliente');
                            }
                            return data;
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                })
                .then((result) => {
                    if (result.isConfirmed) {
                        // Animación de eliminación
                        const fila = document.getElementById(`cliente-${cedula}`);
                        if (fila) {
                            fila.style.transition = 'all 0.3s ease-out';
                            fila.style.opacity = '0';
                            setTimeout(() => {
                                fila.remove();
                                
                                // Verificar si la tabla quedó vacía
                                const tbody = document.getElementById('clientesTableBody');
                                if (tbody.children.length === 0) {
                                    tbody.innerHTML = `
                                        <tr id="no-clientes">
                                            <td colspan="6" class="text-center">
                                                <div class="alert alert-info mb-0">No hay clientes disponibles</div>
                                            </td>
                                        </tr>`;
                                }
                                
                                // Mostrar mensaje de éxito
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Eliminado!',
                                    text: result.value.message || 'El cliente ha sido eliminado correctamente',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            }, 300);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error al eliminar:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        html: `<p>${error.message || 'Ocurrió un error al intentar eliminar el cliente'}</p>`,
                        confirmButtonText: 'Aceptar'
                    });
                });
            });
            
            // Función para agregar una nueva fila a la tabla
            function agregarFilaCliente(cliente) {
                const tbody = document.getElementById('clientesTableBody');
                const filaVacia = document.getElementById('no-clientes');
                if (filaVacia) filaVacia.remove();

                const nuevaFila = document.createElement('tr');
                nuevaFila.id = `cliente-${cliente.cedula}`;
                nuevaFila.innerHTML = `
                    <td class="text-center">${cliente.cedula}</td>
                    <td>${cliente.nombre}</td>
                    <td>${cliente.direccion}</td>
                    <td class="text-end">${formatearTelefono(cliente.telefono)}</td>
                    <td class="text-center">${cliente.membresia}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary btn-editar"
                            data-cedula="${cliente.cedula}"
                            data-nombre="${cliente.nombre}"
                            data-direccion="${cliente.direccion}"
                            data-telefono="${cliente.telefono}"
                            data-membresia="${cliente.membresia}">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-eliminar"
                            data-cedula="${cliente.cedula}"
                            data-nombre="${cliente.nombre}">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </td>
                `;
                tbody.insertBefore(nuevaFila, tbody.firstChild);
            }
            
            // Función para formatear el número de teléfono
            function formatearTelefono(telefono) {
                if (!telefono) return '';
                const telefonoStr = String(telefono);
                if (telefonoStr.length === 11) {
                    return telefonoStr.replace(/(\d{4})(\d{7})/, '$1-$2');
                }
                return telefonoStr;
            }
        });
        
        // Función para llenar el modal de edición y mostrarlo
        document.addEventListener('click', function(e) {
            const btnEditar = e.target.closest('.btn-editar');
            if (!btnEditar) return;

            // Llenar los campos del modal de edición
            document.getElementById('edit-cedula').value = btnEditar.getAttribute('data-cedula');
            document.getElementById('edit-nombre').value = btnEditar.getAttribute('data-nombre');
            document.getElementById('edit-direccion').value = btnEditar.getAttribute('data-direccion');
            document.getElementById('edit-telefono').value = btnEditar.getAttribute('data-telefono');
            document.getElementById('edit-membresia').value = btnEditar.getAttribute('data-membresia');

            // Mostrar el modal
            const modal = new bootstrap.Modal(document.getElementById('editClientModal'));
            modal.show();
        });

        // Enviar formulario de edición por AJAX
        const editClientForm = document.getElementById('editClientForm');
        if (editClientForm) {
            editClientForm.addEventListener('submit', function(e) {
                e.preventDefault();

                // Mostrar loading en el botón
                const submitButton = editClientForm.querySelector('button[type="submit"]');
                const originalButtonText = submitButton.innerHTML;
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...';

                fetch('clients-admin.php?action=edit_ajax', {
                    method: 'POST',
                    body: new URLSearchParams(new FormData(editClientForm)),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        // Cerrar el modal
                        bootstrap.Modal.getInstance(document.getElementById('editClientModal')).hide();
                        // Mostrar mensaje de éxito
                        Swal.fire({
                            icon: 'success',
                            title: '¡Actualizado!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        // Actualizar la fila en la tabla
                        actualizarFilaCliente(data.client);
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                    }
                })
                .catch(() => {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Error al actualizar' });
                })
                .finally(() => {
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                });
            });
        }

        // Función para actualizar la fila del cliente editado
        function actualizarFilaCliente(cliente) {
            const fila = document.getElementById(`cliente-${cliente.cedula}`);
            if (!fila) return;
            fila.innerHTML = `
                <td class="text-center">${cliente.cedula}</td>
                <td>${cliente.nombre}</td>
                <td>${cliente.direccion}</td>
                <td class="text-end">${formatearTelefono(cliente.telefono)}</td>
                <td class="text-center">${cliente.membresia}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-primary btn-editar"
                        data-cedula="${cliente.cedula}"
                        data-nombre="${cliente.nombre}"
                        data-direccion="${cliente.direccion}"
                        data-telefono="${cliente.telefono}"
                        data-membresia="${cliente.membresia}">
                        <i class="fas fa-edit"></i> Editar
                    </button>
                    <button class="btn btn-sm btn-outline-danger btn-eliminar"
                        data-cedula="${cliente.cedula}"
                        data-nombre="${cliente.nombre}">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </td>
            `;
        }
        
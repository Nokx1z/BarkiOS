document.addEventListener('DOMContentLoaded', () => {
    const productsTableBody = document.getElementById('productsTableBody');
    const alertContainer = document.getElementById('alertContainer');
    const addProductForm = document.getElementById('addProductForm');
    const editProductForm = document.getElementById('editProductForm');

    // Utilidades
    const escapeHtml = str => String(str ?? '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    const showAlert = (msg, type = 'info') => {
        alertContainer.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${msg}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
        setTimeout(() => alertContainer.innerHTML = '', 4000);
    };

    // CRUD AJAX
    function fetchProducts() {
        productsTableBody.innerHTML = `<tr><td colspan="6" class="text-center">
            <div class="spinner-border text-primary"></div> Cargando...</td></tr>`;
        fetch(window.location.pathname + '?action=get_products', {headers: {'X-Requested-With':'XMLHttpRequest'}})
        .then(r => r.json()).then(data => {
            if (!data.products?.length) return productsTableBody.innerHTML =
                `<td colspan="6" class="text-center">
                                            <div class="alert alert-info mb-0">No hay productos disponibles</div>
                                        </td>`;
            productsTableBody.innerHTML = data.products.map(p => `
    <tr id="producto-${escapeHtml(p.id)}">
        <td>${escapeHtml(p.id)}</td>
        <td>${escapeHtml(p.nombre)}</td>
        <td>${escapeHtml(p.tipo)}</td>
        <td>${escapeHtml(p.categoria)}</td>
        <td>$${parseFloat(p.precio).toFixed(2)}</td>
        <td>
            <button class="btn btn-sm btn-outline-primary btn-edit"
                data-id="${escapeHtml(p.id)}"
                data-nombre="${escapeHtml(p.nombre)}"
                data-categoria="${escapeHtml(p.categoria)}"
                data-tipo="${escapeHtml(p.tipo)}"
                data-precio="${p.precio}">
                <i class="fas fa-edit"></i> Editar
            </button>
            <button class="btn btn-sm btn-outline-danger btn-delete"
                data-product-id="${escapeHtml(p.id)}"
                data-product-name="${escapeHtml(p.nombre)}">
                <i class="fas fa-trash"></i> Eliminar
            </button>
        </td>
    </tr>
`).join('');
            document.querySelectorAll('.btn-delete').forEach(btn => btn.onclick = handleDelete);
            document.querySelectorAll('.btn-edit').forEach(btn => btn.onclick = () => loadProductForEdit(btn));
        }).catch(() => showAlert('Error al cargar productos', 'danger'));
    }

    function handleAdd(e) {
        e.preventDefault();
        const fd = new URLSearchParams(new FormData(addProductForm));
        fetch('index.php?controller=products&action=add_ajax', {
            method: 'POST', headers: {'X-Requested-With':'XMLHttpRequest','Content-Type':'application/x-www-form-urlencoded'},
            body: fd
        }).then(r => r.json()).then(data => {
            if (data.success) {
                showAlert('Producto agregado', 'success');
                addProductForm.reset();
                bootstrap.Modal.getInstance(document.getElementById('addProductModal')).hide();
                fetchProducts();
            } else showAlert(data.message, 'danger');
        }).catch(() => showAlert('Error al agregar', 'danger'));
    }

function loadProductForEdit(btn) {
    document.getElementById('editProductId').value = btn.getAttribute('data-id') || '';
    document.getElementById('editProductName').value = btn.getAttribute('data-nombre') || '';
    document.getElementById('editProductCategory').value = btn.getAttribute('data-categoria') || '';
    document.getElementById('editProductType').value = btn.getAttribute('data-tipo') || '';
    document.getElementById('editProductPrice').value = btn.getAttribute('data-precio') || '';
    const modal = new bootstrap.Modal(document.getElementById('editProductModal'));
    modal.show();
}

    function handleEdit(e) {
        e.preventDefault();
        const fd = new URLSearchParams(new FormData(editProductForm));
        fetch(window.location.pathname + '?action=edit_ajax', {
            method: 'POST', headers: {'X-Requested-With':'XMLHttpRequest','Content-Type':'application/x-www-form-urlencoded'},
            body: fd
        }).then(r => r.json()).then(data => {
            if (data.success) {
                showAlert('Producto actualizado', 'success');
                bootstrap.Modal.getInstance(document.getElementById('editProductModal')).hide();
                fetchProducts();
            } else showAlert(data.message, 'danger');
        }).catch(() => showAlert('Error al actualizar', 'danger'));
    }

    function handleDelete(e) {
        const id = e.currentTarget.dataset.productId;
        const name = e.currentTarget.dataset.productName;
        Swal.fire({
            title: '¿Eliminar producto?',
            html: `¿Deseas eliminar <strong>${escapeHtml(name)}</strong>?`,
            icon: 'warning', showCancelButton: true,
            confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar'
        }).then(res => {
            if (res.isConfirmed) {
                fetch('index.php?controller=products&action=delete_ajax', {
                    method: 'POST',
                    headers: {'X-Requested-With':'XMLHttpRequest','Content-Type':'application/x-www-form-urlencoded'},
                    body: `id=${encodeURIComponent(id)}`
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        showAlert('Producto eliminado', 'success');
                        fetchProducts();
                    } else showAlert(data.message, 'danger');
                }).catch(() => showAlert('Error al eliminar', 'danger'));
            }
        });
    }

    // Inicialización
    if (addProductForm) addProductForm.onsubmit = handleAdd;
    if (editProductForm) editProductForm.onsubmit = handleEdit;
    fetchProducts();
});
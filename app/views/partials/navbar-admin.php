<nav class="sidebar" id="sidebar">
    <div class="sidebar-sticky">
        <div class="sidebar-header">
            <h3>GARAGE<span>BARKI</span></h3>
            <p class="mb-0">Panel de Administración</p>
        </div>
        <ul class="nav flex-column">
            <?php 
            // Obtener la ruta actual
            $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $menuItems = [
                'inicio' => [
                    'url' => '/app/admin/products/',
                    'icon' => 'fa-tachometer-alt',
                    'text' => 'Inicio',
                    'match' => ['/app/', '/app/admin/products/']
                ],
                'productos' => [
                    'url' => '/app/admin/products/',
                    'icon' => 'fa-tshirt',
                    'text' => 'Productos'
                ],
                'proveedores' => [
                    'url' => '/app/admin/supplier/',
                    'icon' => 'fa-shopping-cart',
                    'text' => 'Proveedores'
                ],
                'clientes' => [
                    'url' => '/app/admin/clients/',
                    'icon' => 'fa-users',
                    'text' => 'Clientes'
                ]
            ];
            foreach ($menuItems as $key => $item): 
                $isActive = '';
                // Manejar lógica especial para el ítem de inicio
                if ($key === 'inicio') {
                    $isActive = in_array($currentPath, $item['match']) ? 'active' : '';
                } else {
                    // Para los demás ítems, verificar si la ruta actual contiene la URL del ítem
                    $isActive = (strpos($currentPath, $item['url']) !== false) ? 'active' : '';
                }
            ?>
                <li class="nav-item">
                    <a class="nav-link <?= $isActive ?>" href="<?= $item['url'] ?>">
                        <i class="fas <?= $item['icon'] ?>"></i>
                        <?= $item['text'] ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</nav>

<script>
// Función para resaltar el elemento del menú al hacer clic
document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Remover la clase 'active' de todos los enlaces
            navLinks.forEach(l => l.classList.remove('active'));
            // Agregar la clase 'active' al enlace clickeado
            this.classList.add('active');
        });
    });
});
</script>
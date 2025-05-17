<?php
namespace App\Controllers\Front;

class ProductController
{
    public function showProducts()
    {
        // 1. Datos de ejemplo (luego vendrán de la base de datos)
        $products = [
            [
                'id' => 1,
                'name' => 'Vestido Elegante',
                'price' => 189.99,
                'category' => 'vestidos',
                'image' => 'vestido.jpg',
                'color' => 'Negro',
                'is_exclusive' => true
            ],
            [
                'id' => 2,
                'name' => 'Blusa Clásica',
                'price' => 89.99,
                'category' => 'blusas',
                'image' => 'blusa.jpg',
                'color' => 'Blanco',
                'is_exclusive' => false
            ]
            // Agrega más productos según necesites
        ];

        $categories = [
            ['id' => 1, 'name' => 'Vestidos', 'slug' => 'vestidos'],
            ['id' => 2, 'name' => 'Blusas', 'slug' => 'blusas'],
            ['id' => 3, 'name' => 'Pantalones', 'slug' => 'pantalones']
        ];

        // 2. Procesar filtros (simulando el JS en PHP)
        $filteredProducts = $this->applyFilters($products, $_GET);

        // 3. Pasar datos a la vista
        $data = [
            'products' => $filteredProducts,
            'categories' => $categories,
            'selected_category' => $_GET['categoria'] ?? null,
            'max_price' => $_GET['max_price'] ?? 500,
            'selected_colors' => isset($_GET['colors']) ? explode(',', $_GET['colors']) : [],
            'sort_by' => $_GET['sort'] ?? 'relevance'
        ];

        // 4. Cargar vista
        require __DIR__ . '/../../views/front/productos.php';
    }

    private function applyFilters($products, $filters)
    {
        // Filtrado por categoría
        if (!empty($filters['categoria'])) {
            $products = array_filter($products, function($product) use ($filters) {
                return $product['category'] === $filters['categoria'];
            });
        }

        // Filtrado por precio máximo
        $maxPrice = $filters['max_price'] ?? 500;
        $products = array_filter($products, function($product) use ($maxPrice) {
            return $product['price'] <= $maxPrice;
        });

        // Filtrado por color
        if (!empty($filters['colors'])) {
            $colors = explode(',', $filters['colors']);
            $products = array_filter($products, function($product) use ($colors) {
                return in_array($product['color'], $colors);
            });
        }

        // Ordenación
        $sortBy = $filters['sort'] ?? 'relevance';
        usort($products, function($a, $b) use ($sortBy) {
            if ($sortBy === 'price-low') {
                return $a['price'] <=> $b['price'];
            } elseif ($sortBy === 'price-high') {
                return $b['price'] <=> $a['price'];
            }
            return 0;
        });

        return $products;
    }

    public function quickView($productId)
    {
        // Lógica para mostrar vista rápida (puedes implementarlo similar a showProducts)
        // Esto sería llamado via AJAX desde tu JavaScript
    }

    public function addToCart()
    {
        // Lógica para añadir al carrito
        session_start();
        $productId = $_POST['product_id'];
        
        // Aquí iría la lógica real de tu carrito
        $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + 1;
        
        echo json_encode(['success' => true]);
    }
}
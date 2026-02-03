<?php
$pageTitle = 'My3DStore - Catálogo de Productos';
$useTailwindBody = true; // Activar clases Tailwind para esta página
$loadStatic3D = true; // Cargar modelos 3D estáticos
include __DIR__ . '/../../includes/header.php';

// Obtener valores de filtros actuales
$currentPrice = $_GET['price'] ?? '';
$currentMaterial = $_GET['material'] ?? '';
$currentCategory = $_GET['category'] ?? '';
$currentSearch = $_GET['search'] ?? '';
?>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #475569;
    }
</style>

<main class="max-w-7xl mx-auto px-6 py-8 flex flex-col md:flex-row gap-8">
    <aside class="w-full md:w-64 space-y-8">
        <form method="GET" action="/My3DStore/" id="filterForm">
            <input type="hidden" name="action" value="products">
            <?php if ($currentSearch): ?>
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($currentSearch); ?>">
            <?php endif; ?>
            
            <!-- Filtro de Precio -->
            <div>
                <h3 class="text-lg font-bold mb-4">Precio</h3>
                <div class="space-y-4">
                    <div class="flex justify-between text-sm">
                        <span>0 €</span>
                        <span>500 €</span>
                    </div>
                    <input 
                        type="range" 
                        min="0" 
                        max="500" 
                        value="<?php echo $currentPrice ? explode('-', $currentPrice)[0] ?? 0 : 0; ?>"
                        class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-primary"
                        id="priceRange"
                    />
                    <div class="space-y-2 mt-4 text-sm">
                        <label class="flex items-center space-x-2 cursor-pointer hover:text-primary transition-colors">
                            <input 
                                class="text-primary focus:ring-primary border-slate-300" 
                                name="price" 
                                type="radio" 
                                value="0-5"
                                <?php echo $currentPrice === '0-5' ? 'checked' : ''; ?>
                                onchange="document.getElementById('filterForm').submit();"
                            />
                            <span>De 0 a 5 €</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer hover:text-primary transition-colors">
                            <input 
                                class="text-primary focus:ring-primary border-slate-300" 
                                name="price" 
                                type="radio" 
                                value="5-15"
                                <?php echo $currentPrice === '5-15' ? 'checked' : ''; ?>
                                onchange="document.getElementById('filterForm').submit();"
                            />
                            <span>De 5 a 15 €</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer hover:text-primary transition-colors">
                            <input 
                                class="text-primary focus:ring-primary border-slate-300" 
                                name="price" 
                                type="radio" 
                                value="15-20"
                                <?php echo $currentPrice === '15-20' ? 'checked' : ''; ?>
                                onchange="document.getElementById('filterForm').submit();"
                            />
                            <span>De 15 a 20 €</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer hover:text-primary transition-colors">
                            <input 
                                class="text-primary focus:ring-primary border-slate-300" 
                                name="price" 
                                type="radio" 
                                value="20+"
                                <?php echo $currentPrice === '20+' ? 'checked' : ''; ?>
                                onchange="document.getElementById('filterForm').submit();"
                            />
                            <span>Más de 20 €</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer hover:text-primary transition-colors">
                            <input 
                                class="text-primary focus:ring-primary border-slate-300" 
                                name="price" 
                                type="radio" 
                                value=""
                                <?php echo !$currentPrice ? 'checked' : ''; ?>
                                onchange="document.getElementById('filterForm').submit();"
                            />
                            <span>Todos</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <!-- Filtro de Materiales -->
            <div>
                <h3 class="text-lg font-bold mb-4">Materiales</h3>
                <div class="space-y-2 text-sm">
                    <label class="flex items-center space-x-2 cursor-pointer hover:text-primary transition-colors">
                        <input 
                            class="rounded text-primary focus:ring-primary border-slate-300" 
                            type="checkbox" 
                            name="material" 
                            value="PLA"
                            <?php echo $currentMaterial === 'PLA' ? 'checked' : ''; ?>
                            onchange="updateMaterialFilter(this);"
                        />
                        <span>Plástico</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer hover:text-primary transition-colors">
                        <input 
                            class="rounded text-primary focus:ring-primary border-slate-300" 
                            type="checkbox" 
                            name="material" 
                            value="Metal"
                            <?php echo $currentMaterial === 'Metal' ? 'checked' : ''; ?>
                            onchange="updateMaterialFilter(this);"
                        />
                        <span>Metal</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer hover:text-primary transition-colors">
                        <input 
                            class="rounded text-primary focus:ring-primary border-slate-300" 
                            type="checkbox" 
                            name="material" 
                            value="Madera"
                            <?php echo $currentMaterial === 'Madera' ? 'checked' : ''; ?>
                            onchange="updateMaterialFilter(this);"
                        />
                        <span>Madera</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer hover:text-primary transition-colors">
                        <input 
                            class="rounded text-primary focus:ring-primary border-slate-300" 
                            type="checkbox" 
                            name="material" 
                            value="Ceramica"
                            <?php echo $currentMaterial === 'Ceramica' ? 'checked' : ''; ?>
                            onchange="updateMaterialFilter(this);"
                        />
                        <span>Cerámica</span>
                    </label>
                </div>
            </div>
        </form>
    </aside>
    
    <section class="flex-1">
        <?php if (empty($products)): ?>
            <div class="text-center py-12">
                <p class="text-slate-500 text-lg">No se encontraron productos.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($products as $product): 
                    $productImage = !empty($product['image_url']) 
                        ? htmlspecialchars($product['image_url']) 
                        : 'https://via.placeholder.com/400x400?text=3D+Product';
                    $productPrice = formatPrice($product['price']);
                ?>
                    <div class="bg-card-light dark:bg-card-dark rounded-xl overflow-hidden shadow-md group transition-all hover:shadow-xl hover:-translate-y-1">
                        <div class="relative overflow-hidden aspect-square bg-[#003d7e]">
                            <a href="/My3DStore/?action=product&id=<?php echo $product['id']; ?>">
                                <div class="static-3d-viewer w-full h-full" data-auto-rotate="true" data-rotation-speed="0.5"></div>
                            </a>
                            <div class="absolute bottom-4 left-0 right-0 flex justify-center space-x-2 px-2">
                                <?php if ($product['stock'] > 0 && isLoggedIn()): ?>
                                    <form method="POST" action="/My3DStore/?action=cart-add" class="flex-1">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button 
                                            type="submit" 
                                            class="bg-primary hover:bg-blue-600 text-white text-[10px] uppercase font-bold py-2 px-3 rounded shadow-lg transition-colors w-full"
                                        >
                                            Añadir a la cesta
                                        </button>
                                    </form>
                                    <a 
                                        href="/My3DStore/?action=customize&product_id=<?php echo $product['id']; ?>"
                                        class="bg-footer-dark hover:bg-blue-900 text-white text-[10px] uppercase font-bold py-2 px-3 rounded shadow-lg transition-colors flex-1 text-center"
                                    >
                                        Personalizar
                                    </a>
                                <?php elseif ($product['stock'] > 0): ?>
                                    <a 
                                        href="/My3DStore/?action=login"
                                        class="bg-primary hover:bg-blue-600 text-white text-[10px] uppercase font-bold py-2 px-3 rounded shadow-lg transition-colors flex-1 text-center"
                                    >
                                        Añadir a la cesta
                                    </a>
                                    <a 
                                        href="/My3DStore/?action=login"
                                        class="bg-footer-dark hover:bg-blue-900 text-white text-[10px] uppercase font-bold py-2 px-3 rounded shadow-lg transition-colors flex-1 text-center"
                                    >
                                        Personalizar
                                    </a>
                                <?php else: ?>
                                    <button 
                                        disabled
                                        class="bg-slate-400 text-white text-[10px] uppercase font-bold py-2 px-3 rounded shadow-lg flex-1 cursor-not-allowed"
                                    >
                                        Agotado
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="p-4 text-center">
                            <a href="/My3DStore/?action=product&id=<?php echo $product['id']; ?>">
                                <h4 class="font-bold text-slate-800 dark:text-slate-100"><?php echo htmlspecialchars($product['name']); ?></h4>
                                <p class="text-primary font-bold mt-1"><?php echo $productPrice; ?></p>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initStatic3DViewers();
    
    // Función para manejar checkboxes de materiales (solo uno seleccionado a la vez)
    function updateMaterialFilter(checkbox) {
        const checkboxes = document.querySelectorAll('input[name="material"]');
        if (checkbox.checked) {
            // Desmarcar los demás
            checkboxes.forEach(cb => {
                if (cb !== checkbox) {
                    cb.checked = false;
                }
            });
            // Enviar formulario
            document.getElementById('filterForm').submit();
        } else {
            // Si se desmarca, quitar el filtro
            checkbox.checked = false;
            document.getElementById('filterForm').submit();
        }
    }
    window.updateMaterialFilter = updateMaterialFilter;
});
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
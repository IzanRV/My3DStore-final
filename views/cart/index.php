<?php
$pageTitle = 'Cesta de Compra - My3DStore';
$useTailwindBody = true; // Activar clases Tailwind para esta página
// No cargar visor 3D en cesta (solo en ficha de producto) para evitar límite WebGL
include __DIR__ . '/../../includes/header.php';
?>

<style>
    body { font-family: 'Inter', system-ui, sans-serif; }
</style>

<main class="container mx-auto px-4 py-8 flex-grow">
    <h1 class="text-2xl font-bold mb-8">Tu Cesta de Compra</h1>
    
    <?php if (empty($items)): ?>
        <div class="text-center py-12">
            <p class="text-slate-500 text-lg mb-4">Tu carrito está vacío.</p>
            <a href="/My3DStore/?action=products" class="inline-block bg-primary hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-xl transition-colors">
                Ver Productos
            </a>
        </div>
    <?php else: ?>
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Lista de productos en el carrito -->
            <div class="flex-1 space-y-4">
                <?php foreach ($items as $item): 
                    $itemTotal = $item['price'] * $item['quantity'];
                    $productImage = !empty($item['image_url']) 
                        ? htmlspecialchars($item['image_url']) 
                        : 'https://via.placeholder.com/400x400?text=3D+Product';
                    
                    // Construir descripción del producto
                    $description = [];
                    if (!empty($item['material'])) {
                        $description[] = 'Material: ' . htmlspecialchars($item['material']);
                    }
                    if (!empty($item['dimensions'])) {
                        $description[] = 'Tamaño: ' . htmlspecialchars($item['dimensions']);
                    }
                    if (!empty($item['weight'])) {
                        $description[] = 'Peso: ' . htmlspecialchars($item['weight']);
                    }
                    $productDescription = !empty($description) ? implode(' | ', $description) : 'Producto personalizado';
                ?>
                    <div class="bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row gap-4">
                        <!-- Modelo 3D del producto -->
                        <div class="w-full sm:w-32 h-32 bg-slate-100 dark:bg-slate-700 rounded-lg overflow-hidden flex items-center justify-center flex-shrink-0 text-primary">
                            <span class="material-icons-outlined text-4xl">view_in_ar</span>
                        </div>
                        
                        <!-- Información del producto -->
                        <div class="flex-1 flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start">
                                    <h3 class="font-bold text-lg"><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <form method="POST" action="/My3DStore/?action=cart-remove" class="inline">
                                        <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                        <button 
                                            type="submit" 
                                            class="text-slate-400 hover:text-red-500 transition-colors"
                                            onclick="return confirm('¿Estás seguro de que quieres eliminar este producto?');"
                                        >
                                            <span class="material-icons-outlined">delete</span>
                                        </button>
                                    </form>
                                </div>
                                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
                                    <?php echo $productDescription; ?>
                                </p>
                            </div>
                            
                            <!-- Controles de cantidad y precio -->
                            <div class="flex justify-between items-center mt-4">
                                <!-- Selector de cantidad -->
                                <div class="flex items-center border border-slate-200 dark:border-slate-600 rounded-lg">
                                    <form method="POST" action="/My3DStore/?action=cart-update" class="flex items-center" id="quantityForm<?php echo $item['product_id']; ?>">
                                        <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                        <button 
                                            type="button"
                                            class="px-3 py-1 hover:bg-slate-50 dark:hover:bg-slate-700 border-r border-slate-200 dark:border-slate-600"
                                            onclick="updateQuantity(<?php echo $item['product_id']; ?>, -1, <?php echo $item['stock']; ?>)"
                                        >
                                            -
                                        </button>
                                        <input 
                                            type="number" 
                                            name="quantity" 
                                            value="<?php echo $item['quantity']; ?>" 
                                            min="1" 
                                            max="<?php echo $item['stock']; ?>"
                                            class="px-4 py-1 font-medium w-16 text-center border-0 focus:ring-0 focus:outline-none bg-transparent"
                                            id="quantity<?php echo $item['product_id']; ?>"
                                            onchange="document.getElementById('quantityForm<?php echo $item['product_id']; ?>').submit();"
                                        />
                                        <button 
                                            type="button"
                                            class="px-3 py-1 hover:bg-slate-50 dark:hover:bg-slate-700 border-l border-slate-200 dark:border-slate-600"
                                            onclick="updateQuantity(<?php echo $item['product_id']; ?>, 1, <?php echo $item['stock']; ?>)"
                                        >
                                            +
                                        </button>
                                    </form>
                                </div>
                                
                                <!-- Precio total del item -->
                                <span class="font-bold text-lg text-primary">
                                    <?php echo formatPrice($itemTotal); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Resumen del pedido -->
            <div class="lg:w-96">
                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl shadow-md border border-slate-200 dark:border-slate-700 sticky top-8">
                    <h2 class="text-xl font-bold mb-6">Resumen del pedido</h2>
                    
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>Subtotal</span>
                            <span><?php echo formatPrice($total); ?></span>
                        </div>
                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>Envío</span>
                            <span class="text-green-500">Gratis</span>
                        </div>
                        <div class="pt-3 border-t border-slate-100 dark:border-slate-700 flex justify-between font-bold text-xl">
                            <span>Total</span>
                            <span class="text-primary"><?php echo formatPrice($total); ?></span>
                        </div>
                    </div>
                    
                    <a 
                        href="/My3DStore/?action=checkout" 
                        class="w-full bg-primary hover:bg-blue-700 text-white font-bold py-4 rounded-xl transition-colors shadow-lg shadow-blue-500/20 mb-4 block text-center"
                    >
                        Tramitar pedido
                    </a>
                    
                    <div class="flex items-center justify-center gap-2 text-xs text-slate-400">
                        <span class="material-icons-outlined text-sm">lock</span>
                        <span>Pago 100% seguro y encriptado</span>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initStatic3DViewers();
    
    function updateQuantity(productId, change, maxStock) {
        const quantityInput = document.getElementById('quantity' + productId);
        let currentQuantity = parseInt(quantityInput.value) || 1;
        let newQuantity = currentQuantity + change;
        
        // Validar límites
        if (newQuantity < 1) {
            newQuantity = 1;
        }
        if (newQuantity > maxStock) {
            newQuantity = maxStock;
            alert('No hay suficiente stock disponible');
        }
        
        // Actualizar el valor
        quantityInput.value = newQuantity;
        
        // Enviar el formulario
        document.getElementById('quantityForm' + productId).submit();
    }
    window.updateQuantity = updateQuantity;
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

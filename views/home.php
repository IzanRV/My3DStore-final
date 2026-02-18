<?php
$pageTitle = 'My3DStore - Impresión 3D Personalizada';
$useTailwindBody = true; // Activar clases Tailwind para el body en la página principal
$loadStatic3D = true; // Visor 3D en tarjetas (imagen por defecto, botón Ver en 3D)
// Obtener productos destacados para el hero
$featuredProduct = null;
$plasticProducts = [];
$metalProducts = [];

if (isset($productsByMaterial) && !empty($productsByMaterial)) {
    // Buscar un producto destacado (puede ser el primero disponible)
    foreach ($productsByMaterial as $material => $products) {
        if (!empty($products) && !$featuredProduct) {
            $featuredProduct = $products[0];
        }
        
        // Separar productos por tipo para las secciones
        $materialLower = strtolower($material);
        if (strpos($materialLower, 'pla') !== false || strpos($materialLower, 'petg') !== false || strpos($materialLower, 'abs') !== false || strpos($materialLower, 'tpu') !== false) {
            $plasticProducts = array_merge($plasticProducts, array_slice($products, 0, 3));
        } elseif (strpos($materialLower, 'metal') !== false || strpos($materialLower, 'acero') !== false || strpos($materialLower, 'aluminio') !== false) {
            $metalProducts = array_merge($metalProducts, array_slice($products, 0, 2));
        }
    }
    
    // Si no hay productos de plástico/metal específicos, usar los primeros disponibles
    if (empty($plasticProducts) && !empty($productsByMaterial)) {
        $firstMaterial = array_key_first($productsByMaterial);
        $plasticProducts = array_slice($productsByMaterial[$firstMaterial] ?? [], 0, 3);
    }
    if (empty($metalProducts) && !empty($productsByMaterial)) {
        $materials = array_keys($productsByMaterial);
        if (count($materials) > 1) {
            $secondMaterial = $materials[1];
            $metalProducts = array_slice($productsByMaterial[$secondMaterial] ?? [], 0, 2);
        }
    }
}

// Función helper para obtener URL de imagen
function getProductImage($product) {
    if (!empty($product['image_url'])) {
        return htmlspecialchars($product['image_url']);
    }
    // Imagen por defecto si no hay
    return 'https://via.placeholder.com/400x400?text=3D+Product';
}

// Función helper para formatear precio
function formatPriceDisplay($price) {
    return number_format($price, 2, ',', '.') . ' €';
}

// Incluir header
include __DIR__ . '/../includes/header.php';
?>
<section class="relative overflow-hidden pt-12 pb-20 px-4">
<div class="max-w-7xl mx-auto flex flex-col lg:flex-row items-center gap-12">
<div class="flex-1 space-y-8 z-10 text-center lg:text-left">
<div class="inline-flex items-center gap-2 px-3 py-1 bg-primary/10 text-primary rounded-full text-sm font-semibold">
<span class="flex h-2 w-2 rounded-full bg-primary animate-pulse"></span>
                        Nuevo: Materiales de Madera Real
                    </div>
<h1 class="text-5xl lg:text-7xl font-extrabold leading-tight tracking-tight">
                        Crea lo que <span class="text-primary">imagines</span> en 3D
                    </h1>
<p class="text-lg text-slate-600 dark:text-slate-400 max-w-xl mx-auto lg:mx-0">
                        Personaliza tus propios diseños o elige de nuestro catálogo curado de productos impresos con la más alta calidad y materiales sostenibles.
                    </p>
<div class="flex flex-wrap items-center justify-center lg:justify-start gap-4 pt-4">
<a href="/My3DStore/?action=products" class="px-8 py-4 bg-primary text-white font-bold rounded-2xl hover:shadow-xl hover:-translate-y-1 transition-all">
                            Explorar catálogo
                        </a>
<a href="/My3DStore/?action=customize" class="px-8 py-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 font-bold rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-all flex items-center gap-2">
<span class="material-icons-outlined">auto_fix_high</span>
                            Diseñar con IA
                        </a>
</div>
<div class="flex items-center justify-center lg:justify-start gap-8 pt-8 grayscale opacity-60">
<img alt="Partner Logo" class="h-6" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCs7cjQbrg8Inr0LboJfC3hUgC1b8t81H6as7fbgnvGYH2DeXmzY5HNgCM1WLF74r8WischCEETkZ578usQxqPDl3Gbgb1_dCFWYye38QRfy46LK9geV6D7Fi0FFwP1LOUkq2EldtolFVG0zIeQpwlJjZ6YSPVWhMtx4Zs0SS71jVkGxObWuPbLbmDklK0zljWrOaiR22VnzMM2LM92WY80plBbVFA5jxN-HJBXXSrS2tkJL8gYwhghjMNHWcjxNsqNzm4oxuMa8Gs"/>
<img alt="Partner Logo" class="h-6" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAsrbxPaEea8P6b2_9U827FEaqIDYDyPwOfBesCnkODG-MoW3QZO7bty8lE8qlzAq0kD3Jwy3HyLiUN2uaHL3UKyy6-YSxq3EOuy-NgJrsMBgf0BSZFbbsl-kjTND8Xcfju8nritiGEIJmdH2qpHeJsS9JsRmDwySP-J27AjR4T-Fe8qCuTU2X-GOn_nlTAGrJm_pfGMz2kA-P18EfQOXt1_IHz6QrAVUXlC4a6AesKFZKlgkNMYC92cwqN51kBJEzYkPVe2DccYOQ"/>
<img alt="Partner Logo" class="h-6" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBnBdG0RDzP1scxjTEu97wpufAwmSCD815c9kXZE3Yha7gX97pbOLJnjkH4ilsTp7LlgTyvqqSsfciP04pNc0X8TSzkGfEOM_34ldtzAlGw97TqQxusTw59RGXBuVw5tuGfSyZ_0o0zbWMh5R2sxzl4qgFPHF_6z8eBxeUjG0SbrxDvARHvupwfwMShSV3C9MRvM-betLjRGjC8aX8HBVKwrowvMmcHkXiloYGTnCnNGm_LlJqyuJWItqNrYuZTy-vH5WtUwIgdy2c"/>
</div>
</div>
<div class="flex-1 relative">
<div class="absolute inset-0 bg-primary/20 blur-[100px] rounded-full scale-150"></div>
<?php if ($featuredProduct):
    $p = $featuredProduct;
    if (!empty($p['image_url'])) {
        $iu = trim($p['image_url']);
        if (strpos($iu, 'http') === 0) { $heroImage = htmlspecialchars($iu); } else {
            $rel = $iu; if (strpos($rel, '/') === 0) $rel = ltrim($rel, '/');
            if (preg_match('#^My3DStore/public/(.*)#', $rel, $m)) $rel = $m[1];
            $heroImage = htmlspecialchars(asset($rel));
        }
    } else { $heroImage = 'https://via.placeholder.com/400x400?text=3D+Product'; }
    $heroHasModel = !empty($p['stl_url']) || (!empty($p['dimensions']) && (strpos($p['dimensions'], '.stl') !== false || strpos($p['dimensions'], '.glb') !== false));
    $heroModelPath = $heroHasModel ? productModelAsset($p) : '';
    $heroFallbackPath = asset('glb/pato.glb');
?>
<div class="relative bg-white dark:bg-card-dark p-4 rounded-[2.5rem] shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden transform lg:rotate-3 hover:rotate-0 transition-transform duration-500 product-card" data-product-url="<?php echo htmlspecialchars(url('product', ['id' => $p['id']])); ?>">
<div class="relative w-full h-[500px] rounded-[2rem] overflow-hidden bg-[#003d7e]">
<a href="<?php echo htmlspecialchars(url('product', ['id' => $p['id']])); ?>" class="card-image-link block w-full h-full absolute inset-0 z-0">
<div class="card-image relative w-full h-full <?php echo $heroHasModel ? '' : 'flex items-center justify-center'; ?>" style="<?php echo $heroHasModel ? '' : 'min-height:200px'; ?>">
<?php if ($heroImage): ?>
<img src="<?php echo $heroImage; ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" class="w-full h-full object-cover" loading="eager" onerror="this.style.display='none'; this.nextElementSibling && (this.nextElementSibling.style.display='flex');">
<span class="material-icons-outlined text-white/90 text-6xl absolute inset-0 flex items-center justify-center bg-[#003d7e]" style="display:none">view_in_ar</span>
<?php else: ?>
<span class="material-icons-outlined text-white/90 text-6xl">view_in_ar</span>
<?php endif; ?>
</div>
</a>
<div class="card-3d absolute inset-0 w-full h-full min-h-0 hidden z-10 pointer-events-auto bg-[#e2e8f0] dark:bg-slate-800 overflow-hidden rounded-[2rem]">
<?php if ($heroHasModel): ?>
<div class="static-3d-viewer w-full h-full overflow-hidden" style="width:100%;height:100%;min-height:0;" data-model-path="<?php echo htmlspecialchars($heroModelPath); ?>" data-fallback-model-path="<?php echo htmlspecialchars($heroFallbackPath); ?>" data-auto-rotate="true" data-rotation-speed="0.5"></div>
<?php endif; ?>
</div>
<?php if ($heroHasModel): ?>
<button type="button" class="card-toggle-3d absolute top-4 right-4 z-20 px-3 py-1.5 rounded-lg bg-white/90 dark:bg-slate-800/90 text-sm font-medium shadow hover:bg-white dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200" title="Ver modelo 3D">Ver en 3D</button>
<?php endif; ?>
</div>
<div class="absolute bottom-10 left-10 right-10 z-20 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md p-6 rounded-3xl border border-white/20">
<a href="<?php echo htmlspecialchars(url('product', ['id' => $p['id']])); ?>" class="block">
<div class="flex justify-between items-center">
<div>
<h3 class="font-bold text-lg"><?php echo htmlspecialchars($p['name']); ?></h3>
<p class="text-sm opacity-70"><?php echo htmlspecialchars($p['material'] ?? 'Material Premium'); ?> • <?php echo htmlspecialchars($p['category'] ?? 'Diseño Exclusivo'); ?></p>
</div>
<div class="text-right">
<span class="text-primary font-black text-2xl"><?php echo formatPriceDisplay($p['price']); ?></span>
</div>
</div>
</a>
</div>
</div>
<?php else: ?>
<div class="relative bg-white dark:bg-card-dark p-4 rounded-[2.5rem] shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden transform lg:rotate-3 hover:rotate-0 transition-transform duration-500">
<div class="product-preview-placeholder w-full h-[500px] rounded-[2rem] flex items-center justify-center bg-slate-100 dark:bg-slate-800 text-primary">
<span class="material-icons-outlined text-6xl">view_in_ar</span>
</div>
<div class="absolute bottom-10 left-10 right-10 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md p-6 rounded-3xl border border-white/20">
<div class="flex justify-between items-center">
<div>
<h3 class="font-bold text-lg">Vaso Espiralé</h3>
<p class="text-sm opacity-70">Diseño Orgánico • PLA Mate</p>
</div>
<div class="text-right">
<span class="text-primary font-black text-2xl">10 €</span>
</div>
</div>
</div>
</div>
<?php endif; ?>
</div>
</div>
</section>
<section class="py-16 bg-slate-50 dark:bg-slate-900/50">
<div class="max-w-7xl mx-auto px-4 space-y-16">
<?php if (!empty($plasticProducts)): ?>
<div>
<div class="flex items-center justify-between mb-8">
<div>
<h2 class="text-2xl font-bold flex items-center gap-2">
<span class="material-icons-outlined text-primary">category</span>
                                Ofertas de productos de plástico
                            </h2>
<p class="text-slate-500 mt-1">Nuestra selección más versátil y colorida</p>
</div>
<a href="/My3DStore/?action=products&material=PLA" class="text-primary font-semibold hover:underline flex items-center gap-1">
                            Ver todo <span class="material-icons-outlined text-sm">arrow_forward</span>
</a>
</div>
<div class="flex gap-6 overflow-x-auto pb-6 custom-scrollbar snap-x">
<?php foreach ($plasticProducts as $product):
    if (!empty($product['image_url'])) {
        $iu = trim($product['image_url']);
        if (strpos($iu, 'http') === 0) { $productImage = htmlspecialchars($iu); } else {
            $rel = $iu; if (strpos($rel, '/') === 0) $rel = ltrim($rel, '/');
            if (preg_match('#^My3DStore/public/(.*)#', $rel, $m)) $rel = $m[1];
            $productImage = htmlspecialchars(asset($rel));
        }
    } else { $productImage = 'https://via.placeholder.com/400x400?text=3D+Product'; }
    $hasModel = !empty($product['stl_url']) || (!empty($product['dimensions']) && (strpos($product['dimensions'], '.stl') !== false || strpos($product['dimensions'], '.glb') !== false));
    $modelPath = $hasModel ? productModelAsset($product) : '';
    $fallbackPath = asset('glb/pato.glb');
?>
<div class="min-w-[300px] snap-start bg-white dark:bg-card-dark p-4 rounded-3xl border border-slate-200 dark:border-slate-800 group cursor-pointer hover:shadow-xl transition-all product-card" data-product-url="<?php echo htmlspecialchars(url('product', ['id' => $product['id']])); ?>">
<div class="aspect-square bg-[#003d7e] rounded-2xl overflow-hidden mb-4 relative">
<a href="<?php echo htmlspecialchars(url('product', ['id' => $product['id']])); ?>" class="card-image-link block w-full h-full absolute inset-0 z-0">
<div class="card-image relative w-full h-full <?php echo $hasModel ? '' : 'flex items-center justify-center'; ?>" style="<?php echo $hasModel ? '' : 'min-height:200px'; ?>">
<?php if ($productImage): ?>
<img src="<?php echo $productImage; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-cover" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling && (this.nextElementSibling.style.display='flex');">
<span class="material-icons-outlined text-white/90 text-5xl absolute inset-0 flex items-center justify-center bg-[#003d7e]" style="display:none">view_in_ar</span>
<?php else: ?>
<span class="material-icons-outlined text-white/90 text-5xl">view_in_ar</span>
<?php endif; ?>
</div>
</a>
<div class="card-3d absolute inset-0 w-full h-full min-h-0 hidden z-10 pointer-events-auto bg-[#e2e8f0] dark:bg-slate-800 overflow-hidden rounded-2xl">
<?php if ($hasModel): ?>
<div class="static-3d-viewer w-full h-full overflow-hidden" style="width:100%;height:100%;min-height:0;" data-model-path="<?php echo htmlspecialchars($modelPath); ?>" data-fallback-model-path="<?php echo htmlspecialchars($fallbackPath); ?>" data-auto-rotate="true" data-rotation-speed="0.5"></div>
<?php endif; ?>
</div>
<?php if ($hasModel): ?>
<button type="button" class="card-toggle-3d absolute top-2 right-2 z-20 px-2 py-1 rounded-lg bg-white/90 dark:bg-slate-800/90 text-xs font-medium shadow hover:bg-white dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200" title="Ver modelo 3D">Ver en 3D</button>
<?php endif; ?>
</div>
<a href="<?php echo htmlspecialchars(url('product', ['id' => $product['id']])); ?>" class="block">
<h3 class="font-bold mb-1"><?php echo htmlspecialchars($product['name']); ?></h3>
<p class="text-sm text-slate-500">Desde <?php echo formatPriceDisplay($product['price']); ?></p>
</a>
</div>
<?php endforeach; ?>
</div>
</div>
<?php endif; ?>
<?php if (!empty($metalProducts)): ?>
<div>
<div class="flex items-center justify-between mb-8">
<div>
<h2 class="text-2xl font-bold flex items-center gap-2">
<span class="material-icons-outlined text-primary">hardware</span>
                                Ofertas de productos de metal
                            </h2>
<p class="text-slate-500 mt-1">Durabilidad excepcional y acabados premium</p>
</div>
<a href="/My3DStore/?action=products&material=Metal" class="text-primary font-semibold hover:underline flex items-center gap-1">
                            Ver todo <span class="material-icons-outlined text-sm">arrow_forward</span>
</a>
</div>
<div class="flex gap-6 overflow-x-auto pb-6 custom-scrollbar snap-x">
<?php foreach ($metalProducts as $product):
    if (!empty($product['image_url'])) {
        $iu = trim($product['image_url']);
        if (strpos($iu, 'http') === 0) { $productImage = htmlspecialchars($iu); } else {
            $rel = $iu; if (strpos($rel, '/') === 0) $rel = ltrim($rel, '/');
            if (preg_match('#^My3DStore/public/(.*)#', $rel, $m)) $rel = $m[1];
            $productImage = htmlspecialchars(asset($rel));
        }
    } else { $productImage = 'https://via.placeholder.com/400x400?text=3D+Product'; }
    $hasModel = !empty($product['stl_url']) || (!empty($product['dimensions']) && (strpos($product['dimensions'], '.stl') !== false || strpos($product['dimensions'], '.glb') !== false));
    $modelPath = $hasModel ? productModelAsset($product) : '';
    $fallbackPath = asset('glb/pato.glb');
?>
<div class="min-w-[300px] snap-start bg-white dark:bg-card-dark p-4 rounded-3xl border border-slate-200 dark:border-slate-800 group cursor-pointer hover:shadow-xl transition-all product-card" data-product-url="<?php echo htmlspecialchars(url('product', ['id' => $product['id']])); ?>">
<div class="aspect-square bg-[#003d7e] rounded-2xl overflow-hidden mb-4 relative">
<a href="<?php echo htmlspecialchars(url('product', ['id' => $product['id']])); ?>" class="card-image-link block w-full h-full absolute inset-0 z-0">
<div class="card-image relative w-full h-full <?php echo $hasModel ? '' : 'flex items-center justify-center'; ?>" style="<?php echo $hasModel ? '' : 'min-height:200px'; ?>">
<?php if ($productImage): ?>
<img src="<?php echo $productImage; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-cover" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling && (this.nextElementSibling.style.display='flex');">
<span class="material-icons-outlined text-white/90 text-5xl absolute inset-0 flex items-center justify-center bg-[#003d7e]" style="display:none">view_in_ar</span>
<?php else: ?>
<span class="material-icons-outlined text-white/90 text-5xl">view_in_ar</span>
<?php endif; ?>
</div>
</a>
<div class="card-3d absolute inset-0 w-full h-full min-h-0 hidden z-10 pointer-events-auto bg-[#e2e8f0] dark:bg-slate-800 overflow-hidden rounded-2xl">
<?php if ($hasModel): ?>
<div class="static-3d-viewer w-full h-full overflow-hidden" style="width:100%;height:100%;min-height:0;" data-model-path="<?php echo htmlspecialchars($modelPath); ?>" data-fallback-model-path="<?php echo htmlspecialchars($fallbackPath); ?>" data-auto-rotate="true" data-rotation-speed="0.5"></div>
<?php endif; ?>
</div>
<?php if ($hasModel): ?>
<button type="button" class="card-toggle-3d absolute top-2 right-2 z-20 px-2 py-1 rounded-lg bg-white/90 dark:bg-slate-800/90 text-xs font-medium shadow hover:bg-white dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200" title="Ver modelo 3D">Ver en 3D</button>
<?php endif; ?>
</div>
<a href="<?php echo htmlspecialchars(url('product', ['id' => $product['id']])); ?>" class="block">
<h3 class="font-bold mb-1"><?php echo htmlspecialchars($product['name']); ?></h3>
<p class="text-sm text-slate-500">Desde <?php echo formatPriceDisplay($product['price']); ?></p>
</a>
</div>
<?php endforeach; ?>
</div>
</div>
<?php endif; ?>
<?php if (empty($plasticProducts) && empty($metalProducts) && !empty($productsByMaterial)): ?>
<?php foreach ($productsByMaterial as $material => $products): ?>
<div>
<div class="flex items-center justify-between mb-8">
<div>
<h2 class="text-2xl font-bold flex items-center gap-2">
<span class="material-icons-outlined text-primary">category</span>
                                Productos en <?php echo htmlspecialchars($material); ?>
                            </h2>
<p class="text-slate-500 mt-1">Nuestra selección de productos</p>
</div>
<a href="/My3DStore/?action=products&material=<?php echo urlencode($material); ?>" class="text-primary font-semibold hover:underline flex items-center gap-1">
                            Ver todo <span class="material-icons-outlined text-sm">arrow_forward</span>
</a>
</div>
<div class="flex gap-6 overflow-x-auto pb-6 custom-scrollbar snap-x">
<?php foreach (array_slice($products, 0, 6) as $product):
    if (!empty($product['image_url'])) {
        $iu = trim($product['image_url']);
        if (strpos($iu, 'http') === 0) { $productImage = htmlspecialchars($iu); } else {
            $rel = $iu; if (strpos($rel, '/') === 0) $rel = ltrim($rel, '/');
            if (preg_match('#^My3DStore/public/(.*)#', $rel, $m)) $rel = $m[1];
            $productImage = htmlspecialchars(asset($rel));
        }
    } else { $productImage = 'https://via.placeholder.com/400x400?text=3D+Product'; }
    $hasModel = !empty($product['stl_url']) || (!empty($product['dimensions']) && (strpos($product['dimensions'], '.stl') !== false || strpos($product['dimensions'], '.glb') !== false));
    $modelPath = $hasModel ? productModelAsset($product) : '';
    $fallbackPath = asset('glb/pato.glb');
?>
<div class="min-w-[300px] snap-start bg-white dark:bg-card-dark p-4 rounded-3xl border border-slate-200 dark:border-slate-800 group cursor-pointer hover:shadow-xl transition-all product-card" data-product-url="<?php echo htmlspecialchars(url('product', ['id' => $product['id']])); ?>">
<div class="aspect-square bg-[#003d7e] rounded-2xl overflow-hidden mb-4 relative">
<a href="<?php echo htmlspecialchars(url('product', ['id' => $product['id']])); ?>" class="card-image-link block w-full h-full absolute inset-0 z-0">
<div class="card-image relative w-full h-full <?php echo $hasModel ? '' : 'flex items-center justify-center'; ?>" style="<?php echo $hasModel ? '' : 'min-height:200px'; ?>">
<?php if ($productImage): ?>
<img src="<?php echo $productImage; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="w-full h-full object-cover" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling && (this.nextElementSibling.style.display='flex');">
<span class="material-icons-outlined text-white/90 text-5xl absolute inset-0 flex items-center justify-center bg-[#003d7e]" style="display:none">view_in_ar</span>
<?php else: ?>
<span class="material-icons-outlined text-white/90 text-5xl">view_in_ar</span>
<?php endif; ?>
</div>
</a>
<div class="card-3d absolute inset-0 w-full h-full min-h-0 hidden z-10 pointer-events-auto bg-[#e2e8f0] dark:bg-slate-800 overflow-hidden rounded-2xl">
<?php if ($hasModel): ?>
<div class="static-3d-viewer w-full h-full overflow-hidden" style="width:100%;height:100%;min-height:0;" data-model-path="<?php echo htmlspecialchars($modelPath); ?>" data-fallback-model-path="<?php echo htmlspecialchars($fallbackPath); ?>" data-auto-rotate="true" data-rotation-speed="0.5"></div>
<?php endif; ?>
</div>
<?php if ($hasModel): ?>
<button type="button" class="card-toggle-3d absolute top-2 right-2 z-20 px-2 py-1 rounded-lg bg-white/90 dark:bg-slate-800/90 text-xs font-medium shadow hover:bg-white dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200" title="Ver modelo 3D">Ver en 3D</button>
<?php endif; ?>
</div>
<a href="<?php echo htmlspecialchars(url('product', ['id' => $product['id']])); ?>" class="block">
<h3 class="font-bold mb-1"><?php echo htmlspecialchars($product['name']); ?></h3>
<p class="text-sm text-slate-500">Desde <?php echo formatPriceDisplay($product['price']); ?></p>
</a>
</div>
<?php endforeach; ?>
</div>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>
</section>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.card-3d').forEach(function(el) {
        el.addEventListener('click', function(ev) { ev.stopPropagation(); });
        el.addEventListener('mousedown', function(ev) { ev.stopPropagation(); });
        el.addEventListener('touchstart', function(ev) { ev.stopPropagation(); }, { passive: true });
    });

    function closeCard3D(card) {
        if (!card) return;
        var card3d = card.querySelector('.card-3d');
        var imageLink = card.querySelector('.card-image-link');
        var imageWrap = card.querySelector('.card-image');
        var btn = card.querySelector('.card-toggle-3d');
        if (card._viewerInstance && typeof disposeStatic3DViewer === 'function') {
            disposeStatic3DViewer(card._viewerInstance);
            card._viewerInstance = null;
        }
        if (card3d) card3d.classList.add('hidden');
        if (imageLink) imageLink.style.pointerEvents = '';
        if (imageWrap) imageWrap.style.visibility = '';
        if (btn) btn.textContent = 'Ver en 3D';
    }

    document.querySelectorAll('.product-card').forEach(function(card) {
        card.addEventListener('click', function(e) {
            var productUrl = card.dataset.productUrl;
            if (!productUrl) return;
            if (e.target.closest('button') || e.target.closest('form') || e.target.closest('a')) return;
            e.preventDefault();
            window.location.href = productUrl;
        });
    });

    document.querySelectorAll('.card-toggle-3d').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var card = this.closest('.product-card');
            var imageLink = card.querySelector('.card-image-link');
            var imageWrap = card.querySelector('.card-image');
            var card3d = card.querySelector('.card-3d');

            if (card3d.classList.contains('hidden')) {
                document.querySelectorAll('.product-card').forEach(function(other) {
                    if (other !== card && other.querySelector('.card-3d') && !other.querySelector('.card-3d').classList.contains('hidden')) {
                        closeCard3D(other);
                    }
                });

                card3d.classList.remove('hidden');
                if (imageLink) imageLink.style.pointerEvents = 'none';
                if (imageWrap) imageWrap.style.visibility = 'hidden';
                this.textContent = 'Ver imagen';

                function waitForSize(el, cb, tries) {
                    if (tries === undefined) tries = 60;
                    if (!el) return cb();
                    var r = el.getBoundingClientRect();
                    if (r.width > 10 && r.height > 10) return cb();
                    if (tries <= 0) return cb();
                    requestAnimationFrame(function() { waitForSize(el, cb, tries - 1); });
                }

                var viewerDiv = card3d.querySelector('.static-3d-viewer');
                if (viewerDiv && !card._viewerInstance) {
                    viewerDiv.style.position = 'absolute';
                    viewerDiv.style.inset = '0';
                    viewerDiv.style.width = '100%';
                    viewerDiv.style.height = '100%';

                    waitForSize(viewerDiv, function() {
                        if (!card._viewerInstance && viewerDiv.parentNode) {
                            var viewer = typeof initOneStatic3DViewer === 'function' ? initOneStatic3DViewer(viewerDiv) : null;
                            if (viewer) {
                                card._viewerInstance = viewer;
                                setTimeout(function() { if (viewer.onWindowResize) viewer.onWindowResize(); }, 0);
                                setTimeout(function() { if (viewer.onWindowResize) viewer.onWindowResize(); }, 150);
                            }
                        }
                    });
                }
            } else {
                closeCard3D(card);
            }
        });
    });
});
</script>
<?php
// Incluir footer
include __DIR__ . '/../includes/footer.php';
?>

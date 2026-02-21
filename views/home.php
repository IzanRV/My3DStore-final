<?php
$pageTitle = 'My3DStore - Impresión 3D Personalizada';
$useTailwindBody = true; // Activar clases Tailwind para el body en la página principal
$loadStatic3D = true; // Visor 3D en tarjetas (imagen por defecto, botón Ver en 3D)
// Obtener productos destacados para el hero
$featuredProduct = null;
$plasticProducts = [];
$metalProducts = [];
$woodProducts = [];
$ceramicProducts = [];

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
        } elseif (strpos($materialLower, 'madera') !== false || strpos($materialLower, 'wood') !== false) {
            $woodProducts = array_merge($woodProducts, array_slice($products, 0, 3));
        } elseif (strpos($materialLower, 'ceramica') !== false || strpos($materialLower, 'cerámica') !== false) {
            $ceramicProducts = array_merge($ceramicProducts, array_slice($products, 0, 3));
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
    if (empty($woodProducts) && !empty($productsByMaterial)) {
        $materials = array_keys($productsByMaterial);
        foreach ($materials as $mat) {
            if (stripos($mat, 'madera') !== false) {
                $woodProducts = array_slice($productsByMaterial[$mat] ?? [], 0, 3);
                break;
            }
        }
    }
    if (empty($ceramicProducts) && !empty($productsByMaterial)) {
        $materials = array_keys($productsByMaterial);
        foreach ($materials as $mat) {
            if (stripos($mat, 'ceramica') !== false || stripos($mat, 'cerámica') !== false) {
                $ceramicProducts = array_slice($productsByMaterial[$mat] ?? [], 0, 3);
                break;
            }
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
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; }
</style>
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
    $heroHasModel = !empty($p['stl_url']) && (strpos($p['stl_url'], '.stl') !== false || strpos($p['stl_url'], '.glb') !== false);
    $heroShow3dFirst = $heroHasModel && empty(trim($p['image_url'] ?? ''));
    $heroModelPath = $heroHasModel ? productModelAsset($p) : '';
    $heroFallbackPath = asset('glb/pato.glb');
?>
<div class="relative bg-white dark:bg-card-dark p-4 rounded-[2.5rem] shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden transform lg:rotate-3 hover:rotate-0 transition-transform duration-500 product-card" data-product-url="<?php echo htmlspecialchars(url('product', ['id' => $p['id']])); ?>">
<div class="relative w-full h-[500px] rounded-[2rem] overflow-hidden bg-[#003d7e]">
<a href="<?php echo htmlspecialchars(url('product', ['id' => $p['id']])); ?>" class="card-image-link block w-full h-full absolute inset-0 z-0"<?php if ($heroShow3dFirst): ?> style="pointer-events:none"<?php endif; ?>>
<div class="card-image relative w-full h-full <?php echo $heroShow3dFirst ? 'hidden' : ($heroHasModel ? '' : 'flex items-center justify-center'); ?>" style="<?php echo $heroHasModel ? '' : 'min-height:200px'; ?>">
<?php if ($heroImage && !$heroShow3dFirst): ?>
<img src="<?php echo $heroImage; ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" class="w-full h-full object-cover" loading="eager" onerror="this.style.display='none'; this.nextElementSibling && (this.nextElementSibling.style.display='flex');">
<span class="material-icons-outlined text-white/90 text-6xl absolute inset-0 flex items-center justify-center bg-[#003d7e]" style="display:none">view_in_ar</span>
<?php elseif (!$heroShow3dFirst): ?>
<span class="material-icons-outlined text-white/90 text-6xl">view_in_ar</span>
<?php endif; ?>
</div>
</a>
<div class="card-3d absolute inset-0 w-full h-full min-h-0 z-10 pointer-events-auto bg-[#e2e8f0] dark:bg-slate-800 overflow-hidden rounded-[2rem] <?php echo $heroShow3dFirst ? '' : 'hidden'; ?>">
<?php if ($heroHasModel): ?>
<div class="static-3d-viewer w-full h-full overflow-hidden" style="width:100%;height:100%;min-height:0;" data-model-path="<?php echo htmlspecialchars($heroModelPath); ?>" data-fallback-model-path="<?php echo htmlspecialchars($heroFallbackPath); ?>" data-auto-rotate="true" data-rotation-speed="0.5"<?php if (!empty($p['color'])): ?> data-color="<?php echo htmlspecialchars($p['color']); ?>"<?php endif; ?><?php if (isset($p['dim_x']) && $p['dim_x'] !== null && $p['dim_x'] !== ''): ?> data-dim-x="<?php echo htmlspecialchars($p['dim_x']); ?>" data-dim-y="<?php echo htmlspecialchars($p['dim_y'] ?? $p['dim_x']); ?>" data-dim-z="<?php echo htmlspecialchars($p['dim_z'] ?? $p['dim_x']); ?>"<?php endif; ?><?php if (!empty($p['logo_url'])): ?> data-logo-url="<?php echo htmlspecialchars(asset($p['logo_url'])); ?>"<?php endif; ?><?php if (!empty($p['logo_side'])): ?> data-logo-side="<?php echo htmlspecialchars($p['logo_side']); ?>"<?php endif; ?>></div>
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
<p class="text-sm opacity-70"><?php echo htmlspecialchars($p['material'] ?? 'Material Premium'); ?><?php if (!empty($p['author'])): ?> • <?php echo htmlspecialchars($p['author']); ?><?php endif; ?></p>
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
    $cardImages = productImageAssets($product);
    $cardModels = productModelAssets($product);
    $cardMedia = [];
    foreach ($cardImages as $u) { $cardMedia[] = ['type' => 'image', 'url' => $u]; }
    foreach ($cardModels as $u) { $cardMedia[] = ['type' => 'model', 'url' => $u]; }
    if (empty($cardMedia)) { $cardMedia[] = ['type' => 'image', 'url' => 'https://via.placeholder.com/400x400?text=3D+Product']; }
    $hasModel = count($cardModels) > 0;
    $show3dByDefault = (count($cardImages) === 0 && $hasModel);
    $productImage = $cardMedia[0]['type'] === 'image' ? $cardMedia[0]['url'] : (count($cardImages) ? $cardImages[0] : 'https://via.placeholder.com/400x400?text=3D+Product');
    $modelPath = $hasModel ? $cardModels[0] : '';
    $fallbackPath = asset('glb/pato.glb');
    $cardMediaJson = htmlspecialchars(json_encode($cardMedia), ENT_QUOTES, 'UTF-8');
?>
<div class="w-[300px] flex-shrink-0 snap-start bg-white dark:bg-card-dark rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden group cursor-pointer hover:shadow-xl transition-all product-card" data-product-url="<?php echo htmlspecialchars(url('product', ['id' => $product['id']])); ?>" data-product-media="<?php echo $cardMediaJson; ?>"<?php if ($show3dByDefault): ?> data-show-3d-first="1"<?php endif; ?>>
<div class="relative w-full h-[300px] bg-[#003d7e] overflow-hidden">
<a href="<?php echo htmlspecialchars(url('product', ['id' => $product['id']])); ?>" class="card-image-link block w-full h-full absolute inset-0 z-0"<?php if ($show3dByDefault): ?> style="pointer-events:none"<?php endif; ?>>
<div class="card-image relative w-full h-full flex items-center justify-center <?php echo $show3dByDefault ? 'hidden' : ''; ?>" style="min-height:200px;">
<img src="<?php echo htmlspecialchars($productImage); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="card-carousel-img w-full h-full object-cover" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling && (this.nextElementSibling.style.display='flex');">
<span class="material-icons-outlined text-white/90 text-5xl absolute inset-0 flex items-center justify-center bg-[#003d7e]" style="display:none">view_in_ar</span>
</div>
</a>
<div class="card-3d absolute inset-0 w-full h-full min-h-0 z-10 bg-[#e2e8f0] dark:bg-slate-800 overflow-hidden <?php echo $show3dByDefault ? '' : 'hidden'; ?>">
<div class="card-static-3d-viewer w-full h-full overflow-hidden" style="width:100%;height:100%;min-height:0;" data-model-path="<?php echo htmlspecialchars($modelPath); ?>" data-fallback-model-path="<?php echo htmlspecialchars($fallbackPath); ?>" data-auto-rotate="true" data-rotation-speed="0.5"<?php if (!empty($product['color'])): ?> data-color="<?php echo htmlspecialchars($product['color']); ?>"<?php endif; ?><?php if (isset($product['dim_x']) && $product['dim_x'] !== null && $product['dim_x'] !== ''): ?> data-dim-x="<?php echo htmlspecialchars($product['dim_x']); ?>" data-dim-y="<?php echo htmlspecialchars($product['dim_y'] ?? $product['dim_x']); ?>" data-dim-z="<?php echo htmlspecialchars($product['dim_z'] ?? $product['dim_x']); ?>"<?php endif; ?><?php if (!empty($product['logo_url'])): ?> data-logo-url="<?php echo htmlspecialchars(asset($product['logo_url'])); ?>"<?php endif; ?><?php if (!empty($product['logo_side'])): ?> data-logo-side="<?php echo htmlspecialchars($product['logo_side']); ?>"<?php endif; ?>></div>
</div>
<?php if ($hasModel): ?>
<button type="button" class="card-toggle-3d absolute top-4 right-4 z-20 px-3 py-1.5 rounded-lg bg-white/90 dark:bg-slate-800/90 text-sm font-medium shadow hover:bg-white dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200" title="Vista 3D"><?php echo $show3dByDefault ? 'Ver imagen' : 'Ver en 3D'; ?></button>
<?php endif; ?>
<?php if (count($cardMedia) > 1): ?>
<button type="button" class="card-carousel-prev absolute left-1 top-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-white/90 dark:bg-slate-800/90 shadow flex items-center justify-center text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-700 transition-colors" aria-label="Anterior">
<span class="material-icons-outlined text-lg">chevron_left</span>
</button>
<button type="button" class="card-carousel-next absolute right-1 top-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-white/90 dark:bg-slate-800/90 shadow flex items-center justify-center text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-700 transition-colors" aria-label="Siguiente">
<span class="material-icons-outlined text-lg">chevron_right</span>
</button>
<div class="card-carousel-dot absolute bottom-1 left-1/2 -translate-x-1/2 z-20 w-fit text-[10px] text-white font-medium rounded px-2 py-0.5 bg-black/60 shadow-sm">1/<?php echo count($cardMedia); ?></div>
<?php endif; ?>
</div>
<div class="p-4 h-[88px] flex flex-col justify-center">
<a href="<?php echo htmlspecialchars(url('product', ['id' => $product['id']])); ?>" class="block">
<h3 class="font-bold mb-1 line-clamp-2 text-slate-800 dark:text-slate-100"><?php echo htmlspecialchars($product['name']); ?></h3>
<p class="text-sm text-slate-500">Desde <?php echo formatPriceDisplay($product['price']); ?></p>
</a>
</div>
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
    $cardImages = productImageAssets($product);
    $cardModels = productModelAssets($product);
    $cardMedia = [];
    foreach ($cardImages as $u) { $cardMedia[] = ['type' => 'image', 'url' => $u]; }
    foreach ($cardModels as $u) { $cardMedia[] = ['type' => 'model', 'url' => $u]; }
    if (empty($cardMedia)) { $cardMedia[] = ['type' => 'image', 'url' => 'https://via.placeholder.com/400x400?text=3D+Product']; }
    $hasModel = count($cardModels) > 0;
    $show3dByDefault = (count($cardImages) === 0 && $hasModel);
    $productImage = $cardMedia[0]['type'] === 'image' ? $cardMedia[0]['url'] : (count($cardImages) ? $cardImages[0] : 'https://via.placeholder.com/400x400?text=3D+Product');
    $modelPath = $hasModel ? $cardModels[0] : '';
    $fallbackPath = asset('glb/pato.glb');
    $cardMediaJson = htmlspecialchars(json_encode($cardMedia), ENT_QUOTES, 'UTF-8');
?>
<div class="w-[300px] flex-shrink-0 snap-start bg-white dark:bg-card-dark rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden group cursor-pointer hover:shadow-xl transition-all product-card" data-product-url="<?php echo htmlspecialchars(url('product', ['id' => $product['id']])); ?>" data-product-media="<?php echo $cardMediaJson; ?>"<?php if ($show3dByDefault): ?> data-show-3d-first="1"<?php endif; ?>>
<div class="relative w-full h-[300px] bg-[#003d7e] overflow-hidden">
<a href="<?php echo htmlspecialchars(url('product', ['id' => $product['id']])); ?>" class="card-image-link block w-full h-full absolute inset-0 z-0"<?php if ($show3dByDefault): ?> style="pointer-events:none"<?php endif; ?>>
<div class="card-image relative w-full h-full flex items-center justify-center <?php echo $show3dByDefault ? 'hidden' : ''; ?>" style="min-height:200px;">
<img src="<?php echo htmlspecialchars($productImage); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="card-carousel-img w-full h-full object-cover" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling && (this.nextElementSibling.style.display='flex');">
<span class="material-icons-outlined text-white/90 text-5xl absolute inset-0 flex items-center justify-center bg-[#003d7e]" style="display:none">view_in_ar</span>
</div>
</a>
<div class="card-3d absolute inset-0 w-full h-full min-h-0 z-10 bg-[#e2e8f0] dark:bg-slate-800 overflow-hidden <?php echo $show3dByDefault ? '' : 'hidden'; ?>">
<div class="card-static-3d-viewer w-full h-full overflow-hidden" style="width:100%;height:100%;min-height:0;" data-model-path="<?php echo htmlspecialchars($modelPath); ?>" data-fallback-model-path="<?php echo htmlspecialchars($fallbackPath); ?>" data-auto-rotate="true" data-rotation-speed="0.5"<?php if (!empty($product['color'])): ?> data-color="<?php echo htmlspecialchars($product['color']); ?>"<?php endif; ?><?php if (isset($product['dim_x']) && $product['dim_x'] !== null && $product['dim_x'] !== ''): ?> data-dim-x="<?php echo htmlspecialchars($product['dim_x']); ?>" data-dim-y="<?php echo htmlspecialchars($product['dim_y'] ?? $product['dim_x']); ?>" data-dim-z="<?php echo htmlspecialchars($product['dim_z'] ?? $product['dim_x']); ?>"<?php endif; ?><?php if (!empty($product['logo_url'])): ?> data-logo-url="<?php echo htmlspecialchars(asset($product['logo_url'])); ?>"<?php endif; ?><?php if (!empty($product['logo_side'])): ?> data-logo-side="<?php echo htmlspecialchars($product['logo_side']); ?>"<?php endif; ?>></div>
</div>
<?php if ($hasModel): ?>
<button type="button" class="card-toggle-3d absolute top-4 right-4 z-20 px-3 py-1.5 rounded-lg bg-white/90 dark:bg-slate-800/90 text-sm font-medium shadow hover:bg-white dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200" title="Vista 3D"><?php echo $show3dByDefault ? 'Ver imagen' : 'Ver en 3D'; ?></button>
<?php endif; ?>
<?php if (count($cardMedia) > 1): ?>
<button type="button" class="card-carousel-prev absolute left-1 top-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-white/90 dark:bg-slate-800/90 shadow flex items-center justify-center text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-700 transition-colors" aria-label="Anterior">
<span class="material-icons-outlined text-lg">chevron_left</span>
</button>
<button type="button" class="card-carousel-next absolute right-1 top-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-white/90 dark:bg-slate-800/90 shadow flex items-center justify-center text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-700 transition-colors" aria-label="Siguiente">
<span class="material-icons-outlined text-lg">chevron_right</span>
</button>
<div class="card-carousel-dot absolute bottom-1 left-1/2 -translate-x-1/2 z-20 w-fit text-[10px] text-white font-medium rounded px-2 py-0.5 bg-black/60 shadow-sm">1/<?php echo count($cardMedia); ?></div>
<?php endif; ?>
</div>
<div class="p-4 h-[88px] flex flex-col justify-center">
<a href="<?php echo htmlspecialchars(url('product', ['id' => $product['id']])); ?>" class="block">
<h3 class="font-bold mb-1 line-clamp-2 text-slate-800 dark:text-slate-100"><?php echo htmlspecialchars($product['name']); ?></h3>
<p class="text-sm text-slate-500">Desde <?php echo formatPriceDisplay($product['price']); ?></p>
</a>
</div>
</div>
<?php endforeach; ?>
</div>
</div>
<?php endif; ?>
<?php if (!empty($woodProducts)): ?>
<div>
<div class="flex items-center justify-between mb-8">
<div>
<h2 class="text-2xl font-bold flex items-center gap-2">
<span class="material-icons-outlined text-primary">forest</span>
                                Ofertas de productos de madera
                            </h2>
<p class="text-slate-500 mt-1">Diseños naturales y sostenibles</p>
</div>
<a href="/My3DStore/?action=products&material=Madera" class="text-primary font-semibold hover:underline flex items-center gap-1">
                            Ver todo <span class="material-icons-outlined text-sm">arrow_forward</span>
</a>
</div>
<div class="flex gap-6 overflow-x-auto pb-6 custom-scrollbar snap-x">
<?php foreach ($woodProducts as $product):
    $cardImages = productImageAssets($product);
    $cardModels = productModelAssets($product);
    $cardMedia = [];
    foreach ($cardImages as $u) { $cardMedia[] = ['type' => 'image', 'url' => $u]; }
    foreach ($cardModels as $u) { $cardMedia[] = ['type' => 'model', 'url' => $u]; }
    if (empty($cardMedia)) { $cardMedia[] = ['type' => 'image', 'url' => 'https://via.placeholder.com/400x400?text=3D+Product']; }
    $hasModel = count($cardModels) > 0;
    $show3dByDefault = (count($cardImages) === 0 && $hasModel);
    $productImage = $cardMedia[0]['type'] === 'image' ? $cardMedia[0]['url'] : (count($cardImages) ? $cardImages[0] : 'https://via.placeholder.com/400x400?text=3D+Product');
    $modelPath = $hasModel ? $cardModels[0] : '';
    $fallbackPath = asset('glb/pato.glb');
    $cardMediaJson = htmlspecialchars(json_encode($cardMedia), ENT_QUOTES, 'UTF-8');
?>
<div class="w-[300px] flex-shrink-0 snap-start bg-white dark:bg-card-dark rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden group cursor-pointer hover:shadow-xl transition-all product-card" data-product-url="<?php echo htmlspecialchars(url('product', ['id' => $product['id']])); ?>" data-product-media="<?php echo $cardMediaJson; ?>"<?php if ($show3dByDefault): ?> data-show-3d-first="1"<?php endif; ?>>
<div class="relative w-full h-[300px] bg-[#003d7e] overflow-hidden">
<a href="<?php echo htmlspecialchars(url('product', ['id' => $product['id']])); ?>" class="card-image-link block w-full h-full absolute inset-0 z-0"<?php if ($show3dByDefault): ?> style="pointer-events:none"<?php endif; ?>>
<div class="card-image relative w-full h-full flex items-center justify-center <?php echo $show3dByDefault ? 'hidden' : ''; ?>" style="min-height:200px;">
<img src="<?php echo htmlspecialchars($productImage); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="card-carousel-img w-full h-full object-cover" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling && (this.nextElementSibling.style.display='flex');">
<span class="material-icons-outlined text-white/90 text-5xl absolute inset-0 flex items-center justify-center bg-[#003d7e]" style="display:none">view_in_ar</span>
</div>
</a>
<div class="card-3d absolute inset-0 w-full h-full min-h-0 z-10 bg-[#e2e8f0] dark:bg-slate-800 overflow-hidden <?php echo $show3dByDefault ? '' : 'hidden'; ?>">
<div class="card-static-3d-viewer w-full h-full overflow-hidden" style="width:100%;height:100%;min-height:0;" data-model-path="<?php echo htmlspecialchars($modelPath); ?>" data-fallback-model-path="<?php echo htmlspecialchars($fallbackPath); ?>" data-auto-rotate="true" data-rotation-speed="0.5"<?php if (!empty($product['color'])): ?> data-color="<?php echo htmlspecialchars($product['color']); ?>"<?php endif; ?><?php if (isset($product['dim_x']) && $product['dim_x'] !== null && $product['dim_x'] !== ''): ?> data-dim-x="<?php echo htmlspecialchars($product['dim_x']); ?>" data-dim-y="<?php echo htmlspecialchars($product['dim_y'] ?? $product['dim_x']); ?>" data-dim-z="<?php echo htmlspecialchars($product['dim_z'] ?? $product['dim_x']); ?>"<?php endif; ?><?php if (!empty($product['logo_url'])): ?> data-logo-url="<?php echo htmlspecialchars(asset($product['logo_url'])); ?>"<?php endif; ?><?php if (!empty($product['logo_side'])): ?> data-logo-side="<?php echo htmlspecialchars($product['logo_side']); ?>"<?php endif; ?>></div>
</div>
<?php if ($hasModel): ?>
<button type="button" class="card-toggle-3d absolute top-4 right-4 z-20 px-3 py-1.5 rounded-lg bg-white/90 dark:bg-slate-800/90 text-sm font-medium shadow hover:bg-white dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200" title="Vista 3D"><?php echo $show3dByDefault ? 'Ver imagen' : 'Ver en 3D'; ?></button>
<?php endif; ?>
<?php if (count($cardMedia) > 1): ?>
<button type="button" class="card-carousel-prev absolute left-1 top-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-white/90 dark:bg-slate-800/90 shadow flex items-center justify-center text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-700 transition-colors" aria-label="Anterior">
<span class="material-icons-outlined text-lg">chevron_left</span>
</button>
<button type="button" class="card-carousel-next absolute right-1 top-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-white/90 dark:bg-slate-800/90 shadow flex items-center justify-center text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-700 transition-colors" aria-label="Siguiente">
<span class="material-icons-outlined text-lg">chevron_right</span>
</button>
<div class="card-carousel-dot absolute bottom-1 left-1/2 -translate-x-1/2 z-20 w-fit text-[10px] text-white font-medium rounded px-2 py-0.5 bg-black/60 shadow-sm">1/<?php echo count($cardMedia); ?></div>
<?php endif; ?>
</div>
<div class="p-4 h-[88px] flex flex-col justify-center">
<a href="<?php echo htmlspecialchars(url('product', ['id' => $product['id']])); ?>" class="block">
<h3 class="font-bold mb-1 line-clamp-2 text-slate-800 dark:text-slate-100"><?php echo htmlspecialchars($product['name']); ?></h3>
<p class="text-sm text-slate-500">Desde <?php echo formatPriceDisplay($product['price']); ?></p>
</a>
</div>
</div>
<?php endforeach; ?>
</div>
</div>
<?php endif; ?>
<?php if (!empty($ceramicProducts)): ?>
<div>
<div class="flex items-center justify-between mb-8">
<div>
<h2 class="text-2xl font-bold flex items-center gap-2">
<span class="material-icons-outlined text-primary">palette</span>
                                Ofertas de productos de cerámica
                            </h2>
<p class="text-slate-500 mt-1">Acabados elegantes y artesanales</p>
</div>
<a href="/My3DStore/?action=products&material=Ceramica" class="text-primary font-semibold hover:underline flex items-center gap-1">
                            Ver todo <span class="material-icons-outlined text-sm">arrow_forward</span>
</a>
</div>
<div class="flex gap-6 overflow-x-auto pb-6 custom-scrollbar snap-x">
<?php foreach ($ceramicProducts as $product):
    $cardImages = productImageAssets($product);
    $cardModels = productModelAssets($product);
    $cardMedia = [];
    foreach ($cardImages as $u) { $cardMedia[] = ['type' => 'image', 'url' => $u]; }
    foreach ($cardModels as $u) { $cardMedia[] = ['type' => 'model', 'url' => $u]; }
    if (empty($cardMedia)) { $cardMedia[] = ['type' => 'image', 'url' => 'https://via.placeholder.com/400x400?text=3D+Product']; }
    $hasModel = count($cardModels) > 0;
    $show3dByDefault = (count($cardImages) === 0 && $hasModel);
    $productImage = $cardMedia[0]['type'] === 'image' ? $cardMedia[0]['url'] : (count($cardImages) ? $cardImages[0] : 'https://via.placeholder.com/400x400?text=3D+Product');
    $modelPath = $hasModel ? $cardModels[0] : '';
    $fallbackPath = asset('glb/pato.glb');
    $cardMediaJson = htmlspecialchars(json_encode($cardMedia), ENT_QUOTES, 'UTF-8');
?>
<div class="w-[300px] flex-shrink-0 snap-start bg-white dark:bg-card-dark rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden group cursor-pointer hover:shadow-xl transition-all product-card" data-product-url="<?php echo htmlspecialchars(url('product', ['id' => $product['id']])); ?>" data-product-media="<?php echo $cardMediaJson; ?>"<?php if ($show3dByDefault): ?> data-show-3d-first="1"<?php endif; ?>>
<div class="relative w-full h-[300px] bg-[#003d7e] overflow-hidden">
<a href="<?php echo htmlspecialchars(url('product', ['id' => $product['id']])); ?>" class="card-image-link block w-full h-full absolute inset-0 z-0"<?php if ($show3dByDefault): ?> style="pointer-events:none"<?php endif; ?>>
<div class="card-image relative w-full h-full flex items-center justify-center <?php echo $show3dByDefault ? 'hidden' : ''; ?>" style="min-height:200px;">
<img src="<?php echo htmlspecialchars($productImage); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="card-carousel-img w-full h-full object-cover" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling && (this.nextElementSibling.style.display='flex');">
<span class="material-icons-outlined text-white/90 text-5xl absolute inset-0 flex items-center justify-center bg-[#003d7e]" style="display:none">view_in_ar</span>
</div>
</a>
<div class="card-3d absolute inset-0 w-full h-full min-h-0 z-10 bg-[#e2e8f0] dark:bg-slate-800 overflow-hidden <?php echo $show3dByDefault ? '' : 'hidden'; ?>">
<div class="card-static-3d-viewer w-full h-full overflow-hidden" style="width:100%;height:100%;min-height:0;" data-model-path="<?php echo htmlspecialchars($modelPath); ?>" data-fallback-model-path="<?php echo htmlspecialchars($fallbackPath); ?>" data-auto-rotate="true" data-rotation-speed="0.5"<?php if (!empty($product['color'])): ?> data-color="<?php echo htmlspecialchars($product['color']); ?>"<?php endif; ?><?php if (isset($product['dim_x']) && $product['dim_x'] !== null && $product['dim_x'] !== ''): ?> data-dim-x="<?php echo htmlspecialchars($product['dim_x']); ?>" data-dim-y="<?php echo htmlspecialchars($product['dim_y'] ?? $product['dim_x']); ?>" data-dim-z="<?php echo htmlspecialchars($product['dim_z'] ?? $product['dim_x']); ?>"<?php endif; ?><?php if (!empty($product['logo_url'])): ?> data-logo-url="<?php echo htmlspecialchars(asset($product['logo_url'])); ?>"<?php endif; ?><?php if (!empty($product['logo_side'])): ?> data-logo-side="<?php echo htmlspecialchars($product['logo_side']); ?>"<?php endif; ?>></div>
</div>
<?php if ($hasModel): ?>
<button type="button" class="card-toggle-3d absolute top-4 right-4 z-20 px-3 py-1.5 rounded-lg bg-white/90 dark:bg-slate-800/90 text-sm font-medium shadow hover:bg-white dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200" title="Vista 3D"><?php echo $show3dByDefault ? 'Ver imagen' : 'Ver en 3D'; ?></button>
<?php endif; ?>
<?php if (count($cardMedia) > 1): ?>
<button type="button" class="card-carousel-prev absolute left-1 top-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-white/90 dark:bg-slate-800/90 shadow flex items-center justify-center text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-700 transition-colors" aria-label="Anterior">
<span class="material-icons-outlined text-lg">chevron_left</span>
</button>
<button type="button" class="card-carousel-next absolute right-1 top-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-white/90 dark:bg-slate-800/90 shadow flex items-center justify-center text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-700 transition-colors" aria-label="Siguiente">
<span class="material-icons-outlined text-lg">chevron_right</span>
</button>
<div class="card-carousel-dot absolute bottom-1 left-1/2 -translate-x-1/2 z-20 w-fit text-[10px] text-white font-medium rounded px-2 py-0.5 bg-black/60 shadow-sm">1/<?php echo count($cardMedia); ?></div>
<?php endif; ?>
</div>
<div class="p-4 h-[88px] flex flex-col justify-center">
<a href="<?php echo htmlspecialchars(url('product', ['id' => $product['id']])); ?>" class="block">
<h3 class="font-bold mb-1 line-clamp-2 text-slate-800 dark:text-slate-100"><?php echo htmlspecialchars($product['name']); ?></h3>
<p class="text-sm text-slate-500">Desde <?php echo formatPriceDisplay($product['price']); ?></p>
</a>
</div>
</div>
<?php endforeach; ?>
</div>
</div>
<?php endif; ?>
<?php if (empty($plasticProducts) && empty($metalProducts) && empty($woodProducts) && empty($ceramicProducts) && !empty($productsByMaterial)): ?>
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
    $cardImages = productImageAssets($product);
    $cardModels = productModelAssets($product);
    $cardMedia = [];
    foreach ($cardImages as $u) { $cardMedia[] = ['type' => 'image', 'url' => $u]; }
    foreach ($cardModels as $u) { $cardMedia[] = ['type' => 'model', 'url' => $u]; }
    if (empty($cardMedia)) { $cardMedia[] = ['type' => 'image', 'url' => 'https://via.placeholder.com/400x400?text=3D+Product']; }
    $hasModel = count($cardModels) > 0;
    $show3dByDefault = (count($cardImages) === 0 && $hasModel);
    $productImage = $cardMedia[0]['type'] === 'image' ? $cardMedia[0]['url'] : (count($cardImages) ? $cardImages[0] : 'https://via.placeholder.com/400x400?text=3D+Product');
    $modelPath = $hasModel ? $cardModels[0] : '';
    $fallbackPath = asset('glb/pato.glb');
    $cardMediaJson = htmlspecialchars(json_encode($cardMedia), ENT_QUOTES, 'UTF-8');
?>
<div class="w-[300px] flex-shrink-0 snap-start bg-white dark:bg-card-dark rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden group cursor-pointer hover:shadow-xl transition-all product-card" data-product-url="<?php echo htmlspecialchars(url('product', ['id' => $product['id']])); ?>" data-product-media="<?php echo $cardMediaJson; ?>"<?php if ($show3dByDefault): ?> data-show-3d-first="1"<?php endif; ?>>
<div class="relative w-full h-[300px] bg-[#003d7e] overflow-hidden">
<a href="<?php echo htmlspecialchars(url('product', ['id' => $product['id']])); ?>" class="card-image-link block w-full h-full absolute inset-0 z-0"<?php if ($show3dByDefault): ?> style="pointer-events:none"<?php endif; ?>>
<div class="card-image relative w-full h-full flex items-center justify-center <?php echo $show3dByDefault ? 'hidden' : ''; ?>" style="min-height:200px;">
<img src="<?php echo htmlspecialchars($productImage); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="card-carousel-img w-full h-full object-cover" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling && (this.nextElementSibling.style.display='flex');">
<span class="material-icons-outlined text-white/90 text-5xl absolute inset-0 flex items-center justify-center bg-[#003d7e]" style="display:none">view_in_ar</span>
</div>
</a>
<div class="card-3d absolute inset-0 w-full h-full min-h-0 z-10 bg-[#e2e8f0] dark:bg-slate-800 overflow-hidden <?php echo $show3dByDefault ? '' : 'hidden'; ?>">
<div class="card-static-3d-viewer w-full h-full overflow-hidden" style="width:100%;height:100%;min-height:0;" data-model-path="<?php echo htmlspecialchars($modelPath); ?>" data-fallback-model-path="<?php echo htmlspecialchars($fallbackPath); ?>" data-auto-rotate="true" data-rotation-speed="0.5"<?php if (!empty($product['color'])): ?> data-color="<?php echo htmlspecialchars($product['color']); ?>"<?php endif; ?><?php if (isset($product['dim_x']) && $product['dim_x'] !== null && $product['dim_x'] !== ''): ?> data-dim-x="<?php echo htmlspecialchars($product['dim_x']); ?>" data-dim-y="<?php echo htmlspecialchars($product['dim_y'] ?? $product['dim_x']); ?>" data-dim-z="<?php echo htmlspecialchars($product['dim_z'] ?? $product['dim_x']); ?>"<?php endif; ?><?php if (!empty($product['logo_url'])): ?> data-logo-url="<?php echo htmlspecialchars(asset($product['logo_url'])); ?>"<?php endif; ?><?php if (!empty($product['logo_side'])): ?> data-logo-side="<?php echo htmlspecialchars($product['logo_side']); ?>"<?php endif; ?>></div>
</div>
<?php if ($hasModel): ?>
<button type="button" class="card-toggle-3d absolute top-4 right-4 z-20 px-3 py-1.5 rounded-lg bg-white/90 dark:bg-slate-800/90 text-sm font-medium shadow hover:bg-white dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200" title="Vista 3D"><?php echo $show3dByDefault ? 'Ver imagen' : 'Ver en 3D'; ?></button>
<?php endif; ?>
<?php if (count($cardMedia) > 1): ?>
<button type="button" class="card-carousel-prev absolute left-1 top-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-white/90 dark:bg-slate-800/90 shadow flex items-center justify-center text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-700 transition-colors" aria-label="Anterior">
<span class="material-icons-outlined text-lg">chevron_left</span>
</button>
<button type="button" class="card-carousel-next absolute right-1 top-1/2 -translate-y-1/2 z-20 w-8 h-8 rounded-full bg-white/90 dark:bg-slate-800/90 shadow flex items-center justify-center text-slate-700 dark:text-slate-200 hover:bg-white dark:hover:bg-slate-700 transition-colors" aria-label="Siguiente">
<span class="material-icons-outlined text-lg">chevron_right</span>
</button>
<div class="card-carousel-dot absolute bottom-1 left-1/2 -translate-x-1/2 z-20 w-fit text-[10px] text-white font-medium rounded px-2 py-0.5 bg-black/60 shadow-sm">1/<?php echo count($cardMedia); ?></div>
<?php endif; ?>
</div>
<div class="p-4 h-[88px] flex flex-col justify-center">
<a href="<?php echo htmlspecialchars(url('product', ['id' => $product['id']])); ?>" class="block">
<h3 class="font-bold mb-1 line-clamp-2 text-slate-800 dark:text-slate-100"><?php echo htmlspecialchars($product['name']); ?></h3>
<p class="text-sm text-slate-500">Desde <?php echo formatPriceDisplay($product['price']); ?></p>
</a>
</div>
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
    var MAX_VIEWERS = 6;
    var activeViewerCards = [];

    function toAbsoluteUrl(url) {
        if (!url || url.indexOf('http') === 0) return url;
        var origin = window.location.origin;
        return origin + (url.charAt(0) === '/' ? url : '/' + url);
    }

    function ensureViewerForCard(card, modelUrl, callback) {
        var card3d = card.querySelector('.card-3d');
        var viewerDiv = card3d ? card3d.querySelector('.card-static-3d-viewer') : null;
        if (!viewerDiv) return callback(null);
        if (card._viewerInstance) {
            if (card._viewerInstance.loadModelFromUrl) card._viewerInstance.loadModelFromUrl(toAbsoluteUrl(modelUrl));
            return callback(card._viewerInstance);
        }
        while (activeViewerCards.length >= MAX_VIEWERS && activeViewerCards.length > 0) {
            var old = activeViewerCards.shift();
            if (old._viewerInstance && typeof disposeStatic3DViewer === 'function') {
                disposeStatic3DViewer(old._viewerInstance);
                old._viewerInstance = null;
            }
        }
        viewerDiv.dataset.modelPath = modelUrl;
        var viewer = typeof initOneStatic3DViewer === 'function' ? initOneStatic3DViewer(viewerDiv) : null;
        if (viewer) {
            card._viewerInstance = viewer;
            activeViewerCards.push(card);
            setTimeout(function() { if (viewer.onWindowResize) viewer.onWindowResize(); }, 0);
        }
        callback(viewer);
    }

    function showCardSlide(card, index) {
        var mediaJson = card.dataset.productMedia;
        if (!mediaJson) return;
        var media = [];
        try { media = JSON.parse(mediaJson); } catch (e) { return; }
        if (media.length === 0) return;
        index = (index + media.length) % media.length;
        card._carouselIndex = index;
        var item = media[index];
        var imageLink = card.querySelector('.card-image-link');
        var imageWrap = card.querySelector('.card-image');
        var imgEl = card.querySelector('.card-carousel-img');
        var card3d = card.querySelector('.card-3d');
        var dotEl = card.querySelector('.card-carousel-dot');

        if (item.type === 'image') {
            if (imageLink) imageLink.style.pointerEvents = '';
            if (imageWrap) imageWrap.style.visibility = 'visible';
            if (imgEl) imgEl.src = toAbsoluteUrl(item.url);
            if (card3d) card3d.classList.add('hidden');
        } else {
            if (imageLink) imageLink.style.pointerEvents = 'none';
            if (imageWrap) imageWrap.style.visibility = 'hidden';
            if (card3d) card3d.classList.remove('hidden');
            ensureViewerForCard(card, item.url, function() {
                if (dotEl) dotEl.textContent = (index + 1) + '/' + media.length;
            });
        }
        if (dotEl) dotEl.textContent = (index + 1) + '/' + media.length;
    }

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

    // Inicializar visor 3D del hero (producto destacado arriba) si está visible
    (function initHeroViewer() {
        var heroCard3d = document.querySelector('.card-3d:not(.hidden) .static-3d-viewer');
        if (!heroCard3d) return;
        var heroCard = heroCard3d.closest('.product-card');
        if (!heroCard || heroCard._viewerInstance) return;
        while (activeViewerCards.length >= MAX_VIEWERS && activeViewerCards.length > 0) {
            var old = activeViewerCards.shift();
            if (old._viewerInstance && typeof disposeStatic3DViewer === 'function') {
                disposeStatic3DViewer(old._viewerInstance);
                old._viewerInstance = null;
            }
        }
        var viewer = typeof initOneStatic3DViewer === 'function' ? initOneStatic3DViewer(heroCard3d) : null;
        if (viewer) {
            heroCard._viewerInstance = viewer;
            activeViewerCards.push(heroCard);
            setTimeout(function() { if (viewer.onWindowResize) viewer.onWindowResize(); }, 0);
        }
    })();

    document.querySelectorAll('.product-card').forEach(function(card) {
        card.addEventListener('click', function(e) {
            var productUrl = card.dataset.productUrl;
            if (!productUrl) return;
            if (e.target.closest('button') || e.target.closest('form') || e.target.closest('a')) return;
            e.preventDefault();
            window.location.href = productUrl;
        });
    });

    // Carrusel (todos los modelos + imagen) en tarjetas con data-product-media
    document.querySelectorAll('.product-card').forEach(function(card) {
        var mediaJson = card.dataset.productMedia;
        if (!mediaJson) return;
        card._carouselIndex = 0;
        var btnPrev = card.querySelector('.card-carousel-prev');
        var btnNext = card.querySelector('.card-carousel-next');
        if (btnPrev) btnPrev.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            showCardSlide(card, (card._carouselIndex || 0) - 1);
        });
        if (btnNext) btnNext.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            showCardSlide(card, (card._carouselIndex || 0) + 1);
        });
        try {
            var media = JSON.parse(mediaJson);
            if (media.length > 0 && media[0].type === 'model') {
                card._carouselIndex = 0;
                var imageWrap = card.querySelector('.card-image');
                var card3d = card.querySelector('.card-3d');
                if (imageWrap) imageWrap.style.visibility = 'hidden';
                if (card.querySelector('.card-image-link')) card.querySelector('.card-image-link').style.pointerEvents = 'none';
                if (card3d) card3d.classList.remove('hidden');
                ensureViewerForCard(card, media[0].url, function() {});
            }
        } catch (err) {}
    });

    // Botón "Ver en 3D" / "Ver imagen": tarjetas con carrusel (data-product-media)
    document.querySelectorAll('.card-toggle-3d').forEach(function(btn) {
        var card = btn.closest('.product-card');
        var mediaJson = card && card.dataset.productMedia;
        if (mediaJson) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var card = this.closest('.product-card');
                var card3d = card.querySelector('.card-3d');
                var media = [];
                try { media = JSON.parse(card.dataset.productMedia); } catch (err) { return; }
                var modelIndex = -1, imageIndex = -1;
                for (var i = 0; i < media.length; i++) {
                    if (media[i].type === 'model' && modelIndex < 0) modelIndex = i;
                    if (media[i].type === 'image' && imageIndex < 0) imageIndex = i;
                }
                if (card3d && card3d.classList.contains('hidden')) {
                    if (modelIndex >= 0) {
                        showCardSlide(card, modelIndex);
                        btn.textContent = 'Ver imagen';
                        setTimeout(function() {
                            if (card._viewerInstance && card._viewerInstance.onWindowResize) card._viewerInstance.onWindowResize();
                        }, 150);
                    }
                } else {
                    if (imageIndex >= 0) showCardSlide(card, imageIndex);
                    else if (media.length > 0) showCardSlide(card, 0);
                    btn.textContent = 'Ver en 3D';
                }
            });
            return;
        }
        // Hero (sin carrusel): toggle 3D con .static-3d-viewer
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var card = this.closest('.product-card');
            var imageLink = card.querySelector('.card-image-link');
            var imageWrap = card.querySelector('.card-image');
            var card3d = card.querySelector('.card-3d');

            if (card3d && card3d.classList.contains('hidden')) {
                document.querySelectorAll('.product-card').forEach(function(other) {
                    if (other !== card && other.querySelector('.card-3d') && !other.querySelector('.card-3d').classList.contains('hidden')) {
                        closeCard3D(other);
                    }
                });
                card3d.classList.remove('hidden');
                if (imageLink) imageLink.style.pointerEvents = 'none';
                if (imageWrap) imageWrap.style.visibility = 'hidden';
                this.textContent = 'Ver imagen';
                var viewerDiv = card3d.querySelector('.static-3d-viewer');
                if (viewerDiv && !card._viewerInstance) {
                    viewerDiv.style.position = 'absolute';
                    viewerDiv.style.inset = '0';
                    viewerDiv.style.width = '100%';
                    viewerDiv.style.height = '100%';
                    function waitForSize(el, cb, tries) {
                        if (tries === undefined) tries = 60;
                        if (!el) return cb();
                        var r = el.getBoundingClientRect();
                        if (r.width > 10 && r.height > 10) return cb();
                        if (tries <= 0) return cb();
                        requestAnimationFrame(function() { waitForSize(el, cb, tries - 1); });
                    }
                    waitForSize(viewerDiv, function() {
                        if (!card._viewerInstance && viewerDiv.parentNode) {
                            var viewer = typeof initOneStatic3DViewer === 'function' ? initOneStatic3DViewer(viewerDiv) : null;
                            if (viewer) {
                                card._viewerInstance = viewer;
                                setTimeout(function() { if (viewer.onWindowResize) viewer.onWindowResize(); }, 0);
                            }
                        }
                    });
                }
            } else if (card3d) {
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

<?php
require_once __DIR__ . '/functions.php';
startSession();
$user = getUser();
$cartCount = getCartCount();
$pageTitle = $pageTitle ?? 'My3DStore';
$currentAction = $_GET['action'] ?? '';
$navHome = ($currentAction === '');
$navProducts = ($currentAction === 'products' || $currentAction === 'product');
$navCustomize = ($currentAction === 'customize');
$navCart = ($currentAction === 'cart' || $currentAction === 'checkout');
$navAccount = ($currentAction === 'account' || $currentAction === 'login');
?>
<!DOCTYPE html>
<html class="light" lang="es">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <!-- CSS personalizado original para páginas existentes - cargado después para tener prioridad -->
    <link rel="stylesheet" href="<?php echo htmlspecialchars(asset('css/style.css')); ?>">
    <script>
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            colors: {
              primary: "#0056b3",
              "background-light": "#f3f4f6",
              "background-dark": "#021533",
              "card-light": "#ffffff",
              "card-dark": "#0a1f44",
              "footer-dark": "#002b5c",
              "accent-blue": "#3b82f6",
            },
            fontFamily: {
              display: ["Outfit", "sans-serif"],
            },
            borderRadius: {
              DEFAULT: "0.75rem",
            },
          },
        },
      };
    </script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .hero-gradient {
            background: linear-gradient(135deg, #002d5c 0%, #0056b3 100%);
        }
        .custom-scrollbar::-webkit-scrollbar {
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #334155;
        }
        /* Mobile Menu Styles */
        .mobile-menu {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            max-width: 320px;
            height: 100vh;
            background: #002d5c;
            z-index: 100;
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
            overflow-y: auto;
        }
        .mobile-menu.open {
            transform: translateX(0);
        }
        .mobile-menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            z-index: 99;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease-in-out, visibility 0.3s ease-in-out;
        }
        .mobile-menu-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        .menu-item-icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .material-icon-blue {
            width: 8px;
            height: 8px;
            background: #3b82f6;
            border-radius: 2px;
        }
        .material-icon-brown {
            width: 8px;
            height: 8px;
            background: #8b4513;
            border-radius: 2px;
        }
        .material-icon-metal {
            width: 8px;
            height: 8px;
            background: #64748b;
            border-radius: 2px;
        }
        .material-icon-ceramic {
            width: 8px;
            height: 8px;
            background: #ffffff;
            border-radius: 50%;
        }
    </style>
    <!-- Three.js para visualización 3D (solo se carga cuando es necesario) -->
    <?php if (isset($loadSTLViewer) && $loadSTLViewer): ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/mrdoob/three.js@r128/examples/js/loaders/STLLoader.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/mrdoob/three.js@r128/examples/js/loaders/GLTFLoader.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/mrdoob/three.js@r128/examples/js/controls/OrbitControls.js"></script>
    <?php endif; ?>
    <!-- Three.js para modelos 3D estáticos (GLB y STL por producto) -->
    <?php if (isset($loadStatic3D) && $loadStatic3D): ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/mrdoob/three.js@r128/examples/js/loaders/STLLoader.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/mrdoob/three.js@r128/examples/js/loaders/GLTFLoader.js"></script>
    <script src="<?php echo htmlspecialchars(asset('js/static-3d-viewer.js')); ?>"></script>
    <?php endif; ?>
</head>
<body class="<?php echo isset($useTailwindBody) && $useTailwindBody ? 'bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 transition-colors duration-300' : ''; ?>">
<header class="sticky top-0 z-50 bg-white/80 dark:bg-background-dark/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-800">
<div class="max-w-7xl mx-auto px-4 h-20 flex items-center justify-between gap-4">
<div class="flex items-center gap-6">
<button id="menuToggle" class="p-2.5 bg-primary/10 hover:bg-primary/20 text-primary rounded-xl transition-all duration-200 hover:scale-105 active:scale-95 shadow-sm hover:shadow-md" aria-label="Abrir menú">
<span class="material-icons-outlined text-primary">menu</span>
</button>
<a href="<?php echo url(''); ?>" class="flex items-center gap-2 flex-shrink-0">
<img src="<?php echo htmlspecialchars(asset('images/logo-icon.png')); ?>" alt="" class="h-10 w-10 object-contain" aria-hidden="true" />
<span class="text-xl font-bold tracking-tight hidden sm:inline">My3D<span class="text-primary">Store</span></span>
</a>
</div>
<div class="flex-1 max-w-xl hidden md:block">
<div class="relative group">
<form action="/My3DStore/" method="GET">
<input type="hidden" name="action" value="products">
<input class="w-full bg-slate-100 dark:bg-slate-800 border-none rounded-full py-2.5 pl-5 pr-12 focus:ring-2 focus:ring-primary/50 transition-all" placeholder="Buscar productos o materiales..." type="text" name="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"/>
<button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center bg-primary text-white rounded-full">
<span class="material-icons-outlined text-sm">search</span>
</button>
</form>
</div>
</div>
<nav class="flex items-center gap-1 sm:gap-4">
<a href="/My3DStore/" class="hidden lg:flex items-center gap-2 px-4 py-2 text-sm font-medium hover:text-primary transition-colors <?php echo $navHome ? 'text-primary' : 'text-slate-700 dark:text-slate-200'; ?>">
                    Inicio
                </a>
<a href="/My3DStore/?action=products" class="hidden lg:flex items-center gap-2 px-4 py-2 text-sm font-medium hover:text-primary transition-colors <?php echo $navProducts ? 'text-primary' : 'text-slate-700 dark:text-slate-200'; ?>">
                    Productos
                </a>
<a href="/My3DStore/?action=customize" class="hidden lg:flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-full transition-all <?php echo $navCustomize ? 'bg-primary/90 text-white shadow-inner ring-2 ring-primary/50' : 'bg-primary text-white hover:bg-primary/90 shadow-md'; ?>">
                    Personalización
                </a>
<div class="h-6 w-px bg-slate-200 dark:bg-slate-700 mx-2 hidden sm:block"></div>
<a href="/My3DStore/?action=cart" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full flex flex-col items-center <?php echo $navCart ? 'text-primary' : 'text-slate-700 dark:text-slate-200'; ?>">
<span class="material-icons-outlined">shopping_cart</span>
<span class="text-[10px] uppercase font-bold mt-0.5">Cesta</span>
</a>
<a href="/My3DStore/?action=<?php echo isLoggedIn() ? 'account' : 'login'; ?>" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full flex flex-col items-center <?php echo $navAccount ? 'text-primary' : 'text-slate-700 dark:text-slate-200'; ?>">
<span class="material-icons-outlined">person</span>
<span class="text-[10px] uppercase font-bold mt-0.5">Perfil</span>
</a>
</nav>
</div>
</header>

<!-- Mobile Menu Overlay -->
<div id="menuOverlay" class="mobile-menu-overlay"></div>

<!-- Mobile Menu -->
<nav id="mobileMenu" class="mobile-menu">
    <div class="flex flex-col h-full">
        <!-- Menu Header -->
        <div class="p-6 border-b border-white/20">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-white">Menú</h2>
                <button id="menuClose" class="p-2 hover:bg-white/10 rounded-full transition-colors" aria-label="Cerrar menú">
                    <span class="material-icons-outlined text-white">close</span>
                </button>
            </div>
            <div class="h-px bg-white/20"></div>
        </div>

        <!-- Menu Content -->
        <div class="flex-1 px-6 py-4 space-y-6">
            <!-- Materiales Section -->
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <span class="material-icons-outlined text-white">apps</span>
                    <h3 class="text-lg font-semibold text-white">Materiales</h3>
                </div>
                <div class="h-px bg-white/20 mb-3"></div>
                <div class="space-y-3 pl-8">
                    <a href="/My3DStore/?action=products&material=PLA" class="flex items-center gap-3 text-white hover:text-blue-300 transition-colors">
                        <div class="material-icon-blue"></div>
                        <span>Plástico</span>
                    </a>
                    <a href="/My3DStore/?action=products&material=Madera" class="flex items-center gap-3 text-white hover:text-blue-300 transition-colors">
                        <div class="material-icon-brown"></div>
                        <span>Madera</span>
                    </a>
                    <a href="/My3DStore/?action=products&material=Metal" class="flex items-center gap-3 text-white hover:text-blue-300 transition-colors">
                        <div class="material-icon-metal"></div>
                        <span>Metal</span>
                    </a>
                    <a href="/My3DStore/?action=products&material=Ceramica" class="flex items-center gap-3 text-white hover:text-blue-300 transition-colors">
                        <div class="material-icon-ceramic"></div>
                        <span>Cerámica</span>
                    </a>
                </div>
            </div>

            <!-- Standalone Menu Items -->
            <div class="space-y-3">
                <a href="/My3DStore/?action=products&filter=offers" class="flex items-center gap-3 text-white hover:text-blue-300 transition-colors">
                    <span class="material-icons-outlined">sell</span>
                    <span>Ofertas</span>
                </a>
                <a href="/My3DStore/?action=products&filter=new" class="flex items-center gap-3 text-white hover:text-blue-300 transition-colors">
                    <span class="material-icons-outlined">rocket_launch</span>
                    <span>Novedades</span>
                </a>
                <a href="/My3DStore/?action=customize" class="flex items-center gap-3 text-white hover:text-blue-300 transition-colors">
                    <span class="material-icons-outlined">auto_fix_high</span>
                    <span>Diseña con IA</span>
                </a>
            </div>

            <!-- Cuenta Section -->
            <div class="pt-4 border-t border-white/20">
                <a href="/My3DStore/?action=<?php echo isLoggedIn() ? 'account' : 'login'; ?>" class="flex items-center gap-3 text-white hover:text-blue-300 transition-colors">
                    <span class="material-icons-outlined">person</span>
                    <span>Cuenta</span>
                </a>
            </div>
        </div>

        <!-- Menu Footer -->
        <div class="p-6 border-t border-white/20">
            <a href="<?php echo url(''); ?>" class="flex items-center gap-3 mb-2">
                <img src="<?php echo htmlspecialchars(asset('images/logo-icon.png')); ?>" alt="" class="h-10 w-10 object-contain" aria-hidden="true" />
                <div>
                    <div class="text-white font-bold">My3D <span class="text-orange-400">Store</span></div>
                    <div class="text-white/70 text-sm">Impresión 3D</div>
                </div>
            </a>
        </div>
    </div>
</nav>

<main class="main-content">
    <?php
    $flash = getFlashMessage();
    if ($flash):
    ?>
        <div class="flash-message flash-<?php echo $flash['type']; ?> fixed top-24 left-1/2 transform -translate-x-1/2 z-50 px-6 py-3 rounded-xl shadow-lg <?php echo $flash['type'] === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'; ?>">
            <?php echo htmlspecialchars($flash['message']); ?>
        </div>
    <?php endif; ?>

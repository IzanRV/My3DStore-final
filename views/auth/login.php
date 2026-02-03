<?php
$pageTitle = 'Iniciar Sesión - My3DStore';
$useTailwindBody = true; // Activar clases Tailwind para esta página
include __DIR__ . '/../../includes/header.php';
?>

<style>
    body {
        font-family: 'Inter', sans-serif;
    }
</style>

<main class="flex items-center justify-center p-6 mt-12 md:mt-24 min-h-[calc(100vh-200px)]">
    <div class="w-full max-w-md bg-card-light dark:bg-card-dark p-8 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-800">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-primary/10 rounded-full mb-4">
                <span class="material-icons-outlined text-primary text-3xl">lock</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Bienvenido de nuevo</h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-2">Accede a tu cuenta de My3DStore</p>
        </div>
        
        <?php
        $flash = getFlashMessage();
        if ($flash):
        ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $flash['type'] === 'success' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300'; ?>">
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
        <?php endif; ?>
        
        <form action="/My3DStore/?action=login" class="space-y-6" method="POST">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="email">Usuario</label>
                <div class="relative">
                    <span class="material-icons-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">person</span>
                    <input 
                        class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-gray-900 dark:text-white transition-all outline-none" 
                        id="email" 
                        name="email" 
                        placeholder="Tu email" 
                        required="" 
                        type="email"
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                    />
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="password">Contraseña</label>
                <div class="relative">
                    <span class="material-icons-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">vpn_key</span>
                    <input 
                        class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-gray-900 dark:text-white transition-all outline-none" 
                        id="password" 
                        name="password" 
                        placeholder="••••••••" 
                        required="" 
                        type="password"
                    />
                </div>
            </div>
            
            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center text-gray-600 dark:text-gray-400 cursor-pointer">
                    <input 
                        class="rounded border-gray-300 text-primary focus:ring-primary mr-2" 
                        type="checkbox"
                        name="remember"
                    />
                    Recordarme
                </label>
                <a class="text-primary hover:underline font-medium" href="/My3DStore/?action=forgot-password">¿He olvidado mi contraseña?</a>
            </div>
            
            <button class="w-full bg-primary hover:bg-blue-700 text-white font-semibold py-3 rounded-lg shadow-lg shadow-primary/30 transition-all transform active:scale-[0.98]" type="submit">
                Iniciar sesión
            </button>
        </form>
        
        <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-800">
            <p class="text-center text-gray-600 dark:text-gray-400 text-sm mb-4">¿No tienes una cuenta aún?</p>
            <a href="/My3DStore/?action=register" class="w-full bg-transparent border-2 border-primary text-primary hover:bg-primary hover:text-white font-semibold py-2.5 rounded-lg transition-all block text-center">
                Crear cuenta
            </a>
        </div>
    </div>
</main>

<button class="fixed bottom-6 right-6 p-3 bg-white dark:bg-gray-800 rounded-full shadow-2xl border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-yellow-400 flex items-center justify-center transition-all hover:scale-110 active:scale-90 z-50" onclick="document.documentElement.classList.toggle('dark')" aria-label="Toggle dark mode">
    <span class="material-icons-outlined">dark_mode</span>
</button>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

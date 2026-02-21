    </main>

    <footer class="bg-primary text-white py-20">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid lg:grid-cols-2 gap-16">
                <div class="space-y-8">
                    <h2 class="text-4xl font-bold leading-tight">¿Tienes dudas, ideas o necesitas ayuda?</h2>
                    <p class="text-blue-100 text-lg">
                        En My3DStore estamos aquí para escucharte. Ya sea que estés empezando en el mundo de la impresión 3D o seas un experto buscando asesoría técnica, nuestro equipo está listo para ayudarte.
                    </p>
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <span class="material-icons-outlined text-accent-blue bg-white rounded-lg p-1">extension</span>
                            <p>¿Necesitas ayuda para elegir o personalizar tus modelos 3D impresos?</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="material-icons-outlined text-accent-blue bg-white rounded-lg p-1">square_foot</span>
                            <p>¿Buscas asesoría sobre calidad, acabados o aplicaciones de tus piezas impresas?</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="material-icons-outlined text-accent-blue bg-white rounded-lg p-1">redeem</span>
                            <p>¿Quieres encargar productos 3D personalizados para tu proyecto o negocio?</p>
                        </div>
                    </div>
                    <div class="pt-8 space-y-3 border-t border-white/20">
                        <div class="flex items-center gap-3">
                            <span class="material-icons-outlined">email</span>
                            <span>contacto@my3dstore.com</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="material-icons-outlined">phone</span>
                            <span>+34 912 345 678</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="material-icons-outlined">schedule</span>
                            <span>Lunes a viernes, de 9:00 a 18:00</span>
                        </div>
                    </div>
                </div>
                <div class="bg-white/10 backdrop-blur-md p-8 rounded-[2rem] border border-white/20">
                    <form class="space-y-6" method="POST" action="<?php echo htmlspecialchars(url('contact')); ?>">
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Nombre</label>
                            <input class="w-full bg-white/5 border-white/20 rounded-xl focus:ring-accent-blue focus:border-accent-blue placeholder-white/40" placeholder="Tu nombre" type="text" name="name" required/>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Correo electrónico</label>
                                <input class="w-full bg-white/5 border-white/20 rounded-xl focus:ring-accent-blue focus:border-accent-blue placeholder-white/40" placeholder="ejemplo@correo.com" type="email" name="email" required/>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Teléfono</label>
                                <input class="w-full bg-white/5 border-white/20 rounded-xl focus:ring-accent-blue focus:border-accent-blue placeholder-white/40" placeholder="+34 000 000 000" type="tel" name="phone"/>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Asunto</label>
                            <input class="w-full bg-white/5 border-white/20 rounded-xl focus:ring-accent-blue focus:border-accent-blue placeholder-white/40" placeholder="¿Cómo podemos ayudarte?" type="text" name="subject" required/>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium">Descripción</label>
                            <textarea class="w-full bg-white/5 border-white/20 rounded-xl focus:ring-accent-blue focus:border-accent-blue placeholder-white/40" placeholder="Cuéntanos más detalles..." rows="4" name="message" required></textarea>
                        </div>
                        <button type="submit" class="w-full py-4 bg-white text-primary font-bold rounded-xl hover:bg-blue-50 transition-colors">
                            Enviar mensaje
                        </button>
                    </form>
                </div>
            </div>
            <div class="mt-20 pt-8 border-t border-white/10 flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-blue-100/60">
                <div class="flex items-center gap-2">
                    <span class="material-icons-outlined text-lg">3d_rotation</span>
                    <span>© <?php echo date('Y'); ?> My3DStore. Todos los derechos reservados.</span>
                </div>
                <div class="flex gap-8">
                    <a class="hover:text-white" href="#">Términos</a>
                    <a class="hover:text-white" href="#">Privacidad</a>
                    <a class="hover:text-white" href="#">Cookies</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Optional: Simple toggle for dark mode for testing (can be triggered via console or adding a button)
        function toggleDarkMode() {
            document.documentElement.classList.toggle('dark');
        }

        // Mobile Menu Toggle
        const menuToggle = document.getElementById('menuToggle');
        const menuClose = document.getElementById('menuClose');
        const mobileMenu = document.getElementById('mobileMenu');
        const menuOverlay = document.getElementById('menuOverlay');

        function openMenu() {
            if (mobileMenu) {
                mobileMenu.classList.add('open');
            }
            if (menuOverlay) {
                menuOverlay.classList.add('active');
            }
            document.body.style.overflow = 'hidden';
        }

        function closeMenu() {
            if (mobileMenu) {
                mobileMenu.classList.remove('open');
            }
            if (menuOverlay) {
                menuOverlay.classList.remove('active');
            }
            document.body.style.overflow = '';
        }

        if (menuToggle) {
            menuToggle.addEventListener('click', openMenu);
        }

        if (menuClose) {
            menuClose.addEventListener('click', closeMenu);
        }

        if (menuOverlay) {
            menuOverlay.addEventListener('click', closeMenu);
        }

        // Close menu when clicking on a link
        if (mobileMenu) {
            const menuLinks = mobileMenu.querySelectorAll('a');
            menuLinks.forEach(link => {
                link.addEventListener('click', () => {
                    setTimeout(closeMenu, 100);
                });
            });
        }

        // Auto-hide flash messages
        const flashMessage = document.querySelector('.flash-message');
        if (flashMessage) {
            setTimeout(() => {
                flashMessage.style.opacity = '0';
                flashMessage.style.transition = 'opacity 0.5s';
                setTimeout(() => flashMessage.remove(), 500);
            }, 5000);
        }
    </script>
</body>
</html>

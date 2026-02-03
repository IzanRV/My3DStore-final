// Componente para renderizar modelos 3D estáticos
class Static3DViewer {
    constructor(containerId, options = {}) {
        this.container = typeof containerId === 'string' 
            ? document.getElementById(containerId) 
            : containerId;
        
        if (!this.container) {
            console.error('Container not found:', containerId);
            return;
        }

        this.options = {
            modelPath: options.modelPath || '/My3DStore/public/glb/pato.glb',
            backgroundColor: options.backgroundColor || 0xf8fafc,
            autoRotate: options.autoRotate !== false, // Rotación automática por defecto
            rotationSpeed: options.rotationSpeed || 0.5,
            initialRotation: options.initialRotation || { x: 0, y: Math.PI / 2, z: 0 },
            ...options
        };

        this.scene = null;
        this.camera = null;
        this.renderer = null;
        this.model = null;
        this.animationId = null;

        this.init();
    }

    init() {
        // Crear escena
        this.scene = new THREE.Scene();
        this.scene.background = new THREE.Color(this.options.backgroundColor);

        // Crear cámara
        const width = this.container.clientWidth;
        const height = this.container.clientHeight || 400;
        
        this.camera = new THREE.PerspectiveCamera(50, width / height, 0.1, 1000);
        this.camera.position.set(0, 0, 100);

        // Crear renderer
        this.renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
        this.renderer.setSize(width, height);
        this.renderer.setPixelRatio(window.devicePixelRatio);
        this.renderer.outputEncoding = THREE.sRGBEncoding;
        this.renderer.toneMapping = THREE.ACESFilmicToneMapping;
        this.renderer.toneMappingExposure = 1.0;
        this.container.appendChild(this.renderer.domElement);

        // Iluminación
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.8);
        this.scene.add(ambientLight);

        const directionalLight1 = new THREE.DirectionalLight(0xffffff, 1.0);
        directionalLight1.position.set(1, 1, 1);
        this.scene.add(directionalLight1);

        const directionalLight2 = new THREE.DirectionalLight(0xffffff, 0.5);
        directionalLight2.position.set(-1, -1, -1);
        this.scene.add(directionalLight2);

        const directionalLight3 = new THREE.DirectionalLight(0xffffff, 0.3);
        directionalLight3.position.set(0, 1, 0);
        this.scene.add(directionalLight3);

        // Manejar redimensionamiento
        window.addEventListener('resize', () => this.onWindowResize());

        // Cargar modelo
        this.loadModel();
    }

    loadModel() {
        const loader = new THREE.GLTFLoader();
        
        loader.load(
            this.options.modelPath,
            (gltf) => {
                this.model = gltf.scene;
                
                // Aplicar rotación inicial
                if (this.options.initialRotation) {
                    const rot = this.options.initialRotation;
                    this.model.rotation.x = rot.x || 0;
                    this.model.rotation.y = rot.y || 0;
                    this.model.rotation.z = rot.z || 0;
                }
                
                // Calcular escala
                const box = new THREE.Box3().setFromObject(this.model);
                const size = box.getSize(new THREE.Vector3());
                const maxDim = Math.max(size.x, size.y, size.z);
                
                if (maxDim > 0) {
                    const scale = 50 / maxDim;
                    this.model.scale.multiplyScalar(scale);
                    
                    // Centrar modelo
                    const center = box.getCenter(new THREE.Vector3());
                    this.model.position.sub(center.multiplyScalar(scale));
                }

                this.scene.add(this.model);
                this.fitCameraToModel();
                this.animate();
            },
            (progress) => {
                // Mostrar progreso si es necesario
            },
            (error) => {
                console.error('Error loading GLB:', error);
            }
        );
    }

    fitCameraToModel() {
        if (!this.model) return;

        const box = new THREE.Box3().setFromObject(this.model);
        const size = box.getSize(new THREE.Vector3());
        const center = box.getCenter(new THREE.Vector3());

        const maxDim = Math.max(size.x, size.y, size.z);
        const distance = maxDim * 2.5;

        this.camera.position.set(
            center.x + distance * 0.5,
            center.y + distance * 0.5,
            center.z + distance * 0.5
        );
        this.camera.lookAt(center);
    }

    animate() {
        this.animationId = requestAnimationFrame(() => this.animate());
        
        // Rotación automática si está habilitada
        if (this.options.autoRotate && this.model) {
            this.model.rotation.y += 0.01 * this.options.rotationSpeed;
        }
        
        this.renderer.render(this.scene, this.camera);
    }

    onWindowResize() {
        const width = this.container.clientWidth;
        const height = this.container.clientHeight;
        
        this.camera.aspect = width / height;
        this.camera.updateProjectionMatrix();
        this.renderer.setSize(width, height);
    }

    destroy() {
        if (this.animationId) {
            cancelAnimationFrame(this.animationId);
        }
        if (this.renderer) {
            this.renderer.dispose();
        }
        if (this.model) {
            this.scene.remove(this.model);
            this.model.traverse((child) => {
                if (child.geometry) child.geometry.dispose();
                if (child.material) {
                    if (Array.isArray(child.material)) {
                        child.material.forEach(mat => {
                            if (mat.map) mat.map.dispose();
                            mat.dispose();
                        });
                    } else {
                        if (child.material.map) child.material.map.dispose();
                        child.material.dispose();
                    }
                }
            });
        }
    }
}

// Función helper para inicializar múltiples visores estáticos
function initStatic3DViewers() {
    const viewers = document.querySelectorAll('.static-3d-viewer');
    const viewerInstances = [];
    
    viewers.forEach((container, index) => {
        const viewer = new Static3DViewer(container, {
            autoRotate: container.dataset.autoRotate !== 'false',
            rotationSpeed: parseFloat(container.dataset.rotationSpeed) || 0.5
        });
        viewerInstances.push(viewer);
    });
    
    return viewerInstances;
}

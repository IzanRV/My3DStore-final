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
            modelPath: options.modelPath || (this.container && this.container.dataset.modelPath) || '/public/glb/pato.glb',
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
        this._loadId = 0;

        this.init();
    }

    init() {
        // Crear escena
        this.scene = new THREE.Scene();
        this.scene.background = new THREE.Color(this.options.backgroundColor);

        // Tamaño del contenedor: clientWidth/Height pueden ser 0 si estaba oculto; usar getBoundingClientRect
        let w = this.container.clientWidth;
        let h = this.container.clientHeight;
        if (w <= 0 || h <= 0) {
            const rect = this.container.getBoundingClientRect();
            w = rect.width;
            h = rect.height;
        }
        if (w <= 0 || h <= 0) {
            w = w <= 0 ? 200 : w;
            h = h <= 0 ? 200 : h;
        }
        const width = Math.max(Math.floor(w), 1);
        const height = Math.max(Math.floor(h), 1);

        this.camera = new THREE.PerspectiveCamera(50, width / height, 0.1, 1000);
        this.camera.position.set(0, 0, 100);

        // Crear renderer
        this.renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
        this.renderer.setSize(width, height);
        this.renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        this.renderer.outputEncoding = THREE.sRGBEncoding;
        this.renderer.toneMapping = THREE.ACESFilmicToneMapping;
        this.renderer.toneMappingExposure = 1.0;
        this.renderer.domElement.style.display = 'block';
        this.renderer.domElement.style.position = 'absolute';
        this.renderer.domElement.style.left = '0';
        this.renderer.domElement.style.top = '0';
        this.renderer.domElement.style.width = '100%';
        this.renderer.domElement.style.height = '100%';
        this.renderer.domElement.style.pointerEvents = 'auto';
        this.container.style.position = 'relative';
        this.container.style.overflow = 'hidden';
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

        // Redimensionar cuando cambie la ventana o el propio contenedor (p. ej. tras el layout)
        window.addEventListener('resize', () => this.onWindowResize());
        this._resizeObserver = new ResizeObserver(() => {
            this.onWindowResize();
        });
        this._resizeObserver.observe(this.container);

        // Iniciar bucle de render (así se ve el fondo aunque el modelo falle o tarde)
        this.animate();

        // Cargar modelo
        this.loadModel();
    }

    loadModel() {
        const rawPath = (this.options.modelPath || '').trim();
        const path = rawPath.replace(/\?.*$/, '').replace(/#.*$/, '').trim();
        const fallbackPath = (this.container && this.container.dataset.fallbackModelPath) || this.options.fallbackModelPath || '';

        this._loadId = (this._loadId || 0) + 1;
        const currentLoadId = this._loadId;

        const isCurrentLoad = () => currentLoadId === this._loadId;

        // URL absoluta para fetch (conservar query string por si lleva cache buster)
        const absoluteUrl = rawPath && !rawPath.startsWith('http')
            ? (window.location.origin + (rawPath.startsWith('/') ? rawPath : '/' + rawPath))
            : rawPath;

        const tryFallback = () => {
            if (fallbackPath && fallbackPath !== path && !this._fallbackTried) {
                this._fallbackTried = true;
                this.options.modelPath = fallbackPath;
                this.loadModel();
            }
        };

        const onLoadError = (loaderName) => {
            return (error) => {
                console.warn(loaderName + ' failed:', path, error);
                tryFallback();
            };
        };

        const isStl = /\.stl$/i.test(path);

        const loadFromBlob = (buffer) => {
            if (!buffer || buffer.byteLength === 0) return;
            // Si la respuesta parece HTML (404/error del servidor), usar fallback
            const first = new Uint8Array(buffer, 0, Math.min(100, buffer.byteLength));
            const start = String.fromCharCode.apply(null, first);
            if (start.trim().startsWith('<') || start.trim().startsWith('<!')) {
                tryFallback();
                return;
            }
            const blob = new Blob([buffer]);
            const blobUrl = URL.createObjectURL(blob);
            const revoke = () => { setTimeout(() => URL.revokeObjectURL(blobUrl), 2000); };

            if (isStl && typeof THREE.STLLoader !== 'undefined') {
                const loader = new THREE.STLLoader();
                loader.load(blobUrl,
                    (geometry) => {
                        revoke();
                        if (!isCurrentLoad()) return;
                        const material = new THREE.MeshPhongMaterial({
                            color: 0x0056b3,
                            specular: 0x222222,
                            shininess: 30
                        });
                        this.model = new THREE.Mesh(geometry, material);
                        this._centerAndScaleModel();
                        this.scene.add(this.model);
                        this.fitCameraToModel();
                    },
                    undefined,
                    (err) => { revoke(); onLoadError('STL')(err); }
                );
            } else {
                const loader = new THREE.GLTFLoader();
                loader.load(blobUrl,
                    (gltf) => {
                        revoke();
                        if (!isCurrentLoad()) return;
                        this.model = gltf.scene;
                        if (this.options.initialRotation) {
                            const rot = this.options.initialRotation;
                            this.model.rotation.x = rot.x || 0;
                            this.model.rotation.y = rot.y || 0;
                            this.model.rotation.z = rot.z || 0;
                        }
                        this._centerAndScaleModel();
                        this.scene.add(this.model);
                        this.fitCameraToModel();
                    },
                    undefined,
                    (err) => { revoke(); onLoadError('GLB')(err); }
                );
            }
        };

        const loadDirect = () => {
            // Carga directa con el loader (por si fetch falla por CORS o ruta)
            const urlToLoad = absoluteUrl || path;
            if (isStl && typeof THREE.STLLoader !== 'undefined') {
                const loader = new THREE.STLLoader();
                loader.load(urlToLoad,
                    (geometry) => {
                        if (!isCurrentLoad()) return;
                        const material = new THREE.MeshPhongMaterial({
                            color: 0x0056b3,
                            specular: 0x222222,
                            shininess: 30
                        });
                        this.model = new THREE.Mesh(geometry, material);
                        this._centerAndScaleModel();
                        this.scene.add(this.model);
                        this.fitCameraToModel();
                    },
                    undefined,
                    onLoadError('STL')
                );
            } else {
                const loader = new THREE.GLTFLoader();
                loader.load(urlToLoad,
                    (gltf) => {
                        if (!isCurrentLoad()) return;
                        this.model = gltf.scene;
                        if (this.options.initialRotation) {
                            const rot = this.options.initialRotation;
                            this.model.rotation.x = rot.x || 0;
                            this.model.rotation.y = rot.y || 0;
                            this.model.rotation.z = rot.z || 0;
                        }
                        this._centerAndScaleModel();
                        this.scene.add(this.model);
                        this.fitCameraToModel();
                    },
                    undefined,
                    onLoadError('GLB')
                );
            }
        };

        fetch(absoluteUrl, { method: 'GET', credentials: 'same-origin', cache: 'no-store' })
            .then((res) => {
                if (!res.ok) {
                    tryFallback();
                    return null;
                }
                const ct = (res.headers.get('content-type') || '').toLowerCase();
                if (ct.indexOf('text/html') !== -1) {
                    tryFallback();
                    return null;
                }
                return res.arrayBuffer();
            })
            .then((buffer) => {
                if (buffer) loadFromBlob(buffer);
            })
            .catch(() => {
                // Si fetch falla (red, CORS, ruta), intentar carga directa con el loader; si falla, onLoadError hará tryFallback
                loadDirect();
            });
    }

    /**
     * Quita el modelo actual de la escena y carga otro por URL (para carrusel de modelos).
     * @param {string} modelUrl URL absoluta o relativa del .stl o .glb
     */
    loadModelFromUrl(modelUrl) {
        if (!modelUrl || !this.scene) return;
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
                        if (child.material && child.material.map) child.material.map.dispose();
                        if (child.material) child.material.dispose();
                    }
                }
            });
            this.model = null;
        }
        this._fallbackTried = false;
        modelUrl = (modelUrl || '').replace(/^\s+|\s+$/g, '');
        // Cache buster para que cada modelo se pida de nuevo y no se use la caché del anterior
        modelUrl = modelUrl + (modelUrl.indexOf('?') >= 0 ? '&' : '?') + '_=' + Date.now();
        this.options.modelPath = modelUrl;
        this.loadModel();
    }

    _centerAndScaleModel() {
        if (!this.model) return;
        const box = new THREE.Box3().setFromObject(this.model);
        const size = box.getSize(new THREE.Vector3());
        const maxDim = Math.max(size.x, size.y, size.z);
        if (maxDim > 0) {
            const scale = 50 / maxDim;
            this.model.scale.multiplyScalar(scale);
            const center = box.getCenter(new THREE.Vector3());
            this.model.position.sub(center.multiplyScalar(scale));
        }
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
        if (!this.container || !this.camera || !this.renderer) return;
        let w = this.container.clientWidth;
        let h = this.container.clientHeight;
        if (w <= 0 || h <= 0) {
            const rect = this.container.getBoundingClientRect();
            w = rect.width;
            h = rect.height;
        }
        const width = Math.max(Math.floor(w), 1);
        const height = Math.max(Math.floor(h), 1);
        this.camera.aspect = width / height;
        this.camera.updateProjectionMatrix();
        this.renderer.setSize(width, height);
    }

    destroy() {
        if (this._resizeObserver && this.container) {
            this._resizeObserver.unobserve(this.container);
        }
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

// Máximo de visores a crear (evitar "Too many WebGL contexts", límite ~8 en muchos navegadores)
var STATIC_3D_VIEWER_MAX = 6;

// Función helper para inicializar múltiples visores estáticos
function initStatic3DViewers() {
    const viewers = document.querySelectorAll('.static-3d-viewer');
    const toInit = Array.from(viewers).slice(0, STATIC_3D_VIEWER_MAX);
    const viewerInstances = [];
    
    toInit.forEach((container) => {
        const viewer = new Static3DViewer(container, {
            modelPath: container.dataset.modelPath || '/public/glb/pato.glb',
            autoRotate: container.dataset.autoRotate !== 'false',
            rotationSpeed: parseFloat(container.dataset.rotationSpeed) || 0.5
        });
        viewerInstances.push(viewer);
    });
    
    return viewerInstances;
}

function initOneStatic3DViewer(container) {
    if (!container) return null;
    try {
        return new Static3DViewer(container, {
            modelPath: container.dataset.modelPath || '/public/glb/pato.glb',
            autoRotate: container.dataset.autoRotate !== 'false',
            rotationSpeed: parseFloat(container.dataset.rotationSpeed) || 0.5
        });
    } catch (e) {
        console.error('initOneStatic3DViewer', e);
        return null;
    }
}

function disposeStatic3DViewer(instance) {
    if (instance && typeof instance.destroy === 'function') instance.destroy();
}

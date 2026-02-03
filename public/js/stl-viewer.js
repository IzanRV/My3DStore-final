// Visor STL usando Three.js
class STLViewer {
    constructor(containerId, options = {}) {
        this.container = document.getElementById(containerId);
        if (!this.container) {
            console.error('Container not found:', containerId);
            return;
        }

        this.options = {
            backgroundColor: options.backgroundColor || 0xf0f0f0,
            modelColor: options.modelColor || 0x00ff00,
            wireframe: options.wireframe || false,
            showGrid: options.showGrid !== false,
            showAxes: options.showAxes !== false,
            initialRotation: options.initialRotation || { x: 0, y: 0, z: 0 }, // Rotación inicial en radianes
            ...options
        };

        this.scene = null;
        this.camera = null;
        this.renderer = null;
        this.controls = null;
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
        const height = this.container.clientHeight || 600;
        
        this.camera = new THREE.PerspectiveCamera(75, width / height, 0.1, 1000);
        this.camera.position.set(0, 0, 100);

        // Crear renderer con soporte para colores sRGB
        this.renderer = new THREE.WebGLRenderer({ antialias: true });
        this.renderer.setSize(width, height);
        this.renderer.setPixelRatio(window.devicePixelRatio);
        this.renderer.outputEncoding = THREE.sRGBEncoding; // Mejor representación de colores
        this.renderer.toneMapping = THREE.ACESFilmicToneMapping;
        this.renderer.toneMappingExposure = 1.0;
        this.container.appendChild(this.renderer.domElement);

        // Controles de órbita
        this.controls = new THREE.OrbitControls(this.camera, this.renderer.domElement);
        this.controls.enableDamping = true;
        this.controls.dampingFactor = 0.05;
        this.controls.rotateSpeed = 0.5;
        this.controls.zoomSpeed = 1.2;
        this.controls.panSpeed = 0.8;

        // Iluminación mejorada para mostrar colores del GLB
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.8);
        this.scene.add(ambientLight);

        const directionalLight1 = new THREE.DirectionalLight(0xffffff, 1.0);
        directionalLight1.position.set(1, 1, 1);
        this.scene.add(directionalLight1);

        const directionalLight2 = new THREE.DirectionalLight(0xffffff, 0.5);
        directionalLight2.position.set(-1, -1, -1);
        this.scene.add(directionalLight2);
        
        // Luz adicional para mejor iluminación
        const directionalLight3 = new THREE.DirectionalLight(0xffffff, 0.3);
        directionalLight3.position.set(0, 1, 0);
        this.scene.add(directionalLight3);

        // Grid
        if (this.options.showGrid) {
            const gridHelper = new THREE.GridHelper(200, 20, 0x888888, 0xcccccc);
            this.scene.add(gridHelper);
        }

        // Ejes
        if (this.options.showAxes) {
            const axesHelper = new THREE.AxesHelper(50);
            this.scene.add(axesHelper);
        }

        // Manejar redimensionamiento
        window.addEventListener('resize', () => this.onWindowResize());

        // Iniciar animación
        this.animate();
    }

    loadSTL(url, onProgress = null) {
        const loader = new THREE.STLLoader();
        
        loader.load(
            url,
            (geometry) => {
                // Calcular centro y normalizar
                geometry.computeVertexNormals();
                geometry.center();

                // Crear material
                const material = new THREE.MeshPhongMaterial({
                    color: this.options.modelColor,
                    specular: 0x111111,
                    shininess: 200,
                    wireframe: this.options.wireframe
                });

                // Crear mesh
                this.model = new THREE.Mesh(geometry, material);
                
                // Aplicar rotación inicial si está definida
                if (this.options.initialRotation) {
                    const rot = this.options.initialRotation;
                    this.model.rotation.x = rot.x || 0;
                    this.model.rotation.y = rot.y || 0;
                    this.model.rotation.z = rot.z || 0;
                }
                
                // Calcular escala para que quepa en la vista
                const box = new THREE.Box3().setFromObject(this.model);
                const size = box.getSize(new THREE.Vector3());
                const maxDim = Math.max(size.x, size.y, size.z);
                const scale = 50 / maxDim;
                this.model.scale.multiplyScalar(scale);

                // Centrar modelo
                const center = box.getCenter(new THREE.Vector3());
                this.model.position.sub(center.multiplyScalar(scale));

                this.scene.add(this.model);

                // Ajustar cámara
                this.fitCameraToModel();

                if (onProgress) {
                    onProgress({ success: true, geometry });
                }
            },
            (progress) => {
                if (onProgress) {
                    onProgress({ 
                        progress: (progress.loaded / progress.total) * 100,
                        loaded: progress.loaded,
                        total: progress.total
                    });
                }
            },
            (error) => {
                console.error('Error loading STL:', error);
                if (onProgress) {
                    onProgress({ success: false, error });
                }
            }
        );
    }

    loadGLB(url, onProgress = null) {
        const loader = new THREE.GLTFLoader();
        
        loader.load(
            url,
            (gltf) => {
                // GLB puede contener múltiples objetos, usar la escena completa
                this.model = gltf.scene;
                
                // Aplicar rotación inicial si está definida
                if (this.options.initialRotation) {
                    const rot = this.options.initialRotation;
                    this.model.rotation.x = rot.x || 0;
                    this.model.rotation.y = rot.y || 0;
                    this.model.rotation.z = rot.z || 0;
                }
                
                // Calcular escala para que quepa en la vista
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

                // Ajustar cámara
                this.fitCameraToModel();

                if (onProgress) {
                    onProgress({ success: true, gltf });
                }
            },
            (progress) => {
                if (onProgress) {
                    const xhr = progress;
                    if (xhr.lengthComputable) {
                        onProgress({ 
                            progress: (xhr.loaded / xhr.total) * 100,
                            loaded: xhr.loaded,
                            total: xhr.total
                        });
                    } else {
                        onProgress({ 
                            progress: 0,
                            loaded: xhr.loaded,
                            total: 0
                        });
                    }
                }
            },
            (error) => {
                console.error('Error loading GLB:', error);
                if (onProgress) {
                    onProgress({ success: false, error });
                }
            }
        );
    }

    fitCameraToModel() {
        if (!this.model) return;

        const box = new THREE.Box3().setFromObject(this.model);
        const size = box.getSize(new THREE.Vector3());
        const center = box.getCenter(new THREE.Vector3());

        const maxDim = Math.max(size.x, size.y, size.z);
        const distance = maxDim * 2;

        this.camera.position.set(
            center.x + distance * 0.5,
            center.y + distance * 0.5,
            center.z + distance * 0.5
        );
        this.camera.lookAt(center);
        this.controls.target.copy(center);
        this.controls.update();
    }

    setColor(color) {
        // Para STL: cambiar color del material
        if (this.model && this.model.material) {
            this.model.material.color.set(color);
        }
        // Para GLB: recorrer todos los objetos y cambiar color si no tienen textura
        if (this.model && this.model.traverse) {
            this.model.traverse((child) => {
                if (child.isMesh && child.material) {
                    // Solo cambiar color si no tiene textura (para preservar colores originales del GLB)
                    if (!child.material.map) {
                        if (Array.isArray(child.material)) {
                            child.material.forEach(mat => {
                                if (!mat.map) mat.color.set(color);
                            });
                        } else {
                            child.material.color.set(color);
                        }
                    }
                }
            });
        }
    }

    setWireframe(wireframe) {
        this.options.wireframe = wireframe;
        if (this.model && this.model.material) {
            this.model.material.wireframe = wireframe;
        }
    }

    resetView() {
        if (this.model) {
            // Restaurar rotación inicial si existe
            if (this.options.initialRotation) {
                const rot = this.options.initialRotation;
                this.model.rotation.x = rot.x || 0;
                this.model.rotation.y = rot.y || 0;
                this.model.rotation.z = rot.z || 0;
            }
            this.fitCameraToModel();
        } else {
            this.camera.position.set(0, 0, 100);
            this.controls.target.set(0, 0, 0);
            this.controls.update();
        }
    }

    onWindowResize() {
        const width = this.container.clientWidth;
        const height = this.container.clientHeight || 600;

        this.camera.aspect = width / height;
        this.camera.updateProjectionMatrix();
        this.renderer.setSize(width, height);
    }

    animate() {
        this.animationId = requestAnimationFrame(() => this.animate());
        
        if (this.controls) {
            this.controls.update();
        }
        
        if (this.renderer && this.scene && this.camera) {
            this.renderer.render(this.scene, this.camera);
        }
    }

    dispose() {
        if (this.animationId) {
            cancelAnimationFrame(this.animationId);
        }

        if (this.model) {
            this.scene.remove(this.model);
            
            // Limpiar recursos (soporta tanto STL como GLB)
            if (this.model.traverse) {
                // GLB: recorrer y limpiar todos los objetos
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
            } else {
                // STL: limpiar directamente
                if (this.model.geometry) this.model.geometry.dispose();
                if (this.model.material) this.model.material.dispose();
            }
        }

        if (this.renderer) {
            this.renderer.dispose();
            if (this.container && this.renderer.domElement) {
                this.container.removeChild(this.renderer.domElement);
            }
        }
    }
}


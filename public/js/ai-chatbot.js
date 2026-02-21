class AIChatbot {
    constructor(containerId, viewerInstance, options = {}) {
        this.container = document.getElementById(containerId);
        this.viewer = viewerInstance;
        this.isOpen = false;
        this.messages = [];
        this.currentJobId = null;
        this.hasModel = false;
        this.pollInterval = null;
        this.apiUrl = (window.BASE_PATH || '/') + 'api/ai3d.php';
        this.options = options || {};

        this.init();
    }

    init() {
        this.createChatUI();
        this.loadChatHistory();
        if (this.options.openByDefault && !this.isOpen) {
            setTimeout(() => this.toggleChat(), 100);
        }
    }

    createChatUI() {

        if (document.getElementById('ai-chatbot-container') ||
            document.getElementById('ai-chatbot-toggle-btn')) {
            return;
        }

        const chatHTML = `
            <div class="ai-chatbot-container" id="ai-chatbot-container">
                <div class="ai-chatbot-header" id="ai-chatbot-toggle">
                    <div class="ai-chatbot-header-content">
                        <span class="ai-chatbot-icon">🤖</span>
                        <span class="ai-chatbot-title">Asistente IA 3D</span>
                    </div>
                    <button class="ai-chatbot-close" id="ai-chatbot-close">×</button>
                </div>
                <div class="ai-chatbot-body" id="ai-chatbot-body">
                    <div class="ai-chatbot-messages" id="ai-chatbot-messages">
                        <div class="ai-chatbot-welcome">
                            <p>👋 ¡Hola! Soy tu asistente de IA para generar modelos 3D.</p>
                            <p>Puedes pedirme que genere objetos como:</p>
                            <ul>
                                <li>"Genera un vaso rojo"</li>
                                <li>"Crea una silla de madera"</li>
                                <li>"Haz un cubo azul"</li>
                            </ul>
                        </div>
                    </div>
                    <div class="ai-chatbot-input-container">
                        <input 
                            type="text" 
                            id="ai-chatbot-input" 
                            class="ai-chatbot-input" 
                            placeholder="Describe el objeto 3D que quieres crear..."
                        />
                        <button id="ai-chatbot-send" class="ai-chatbot-send-btn">Enviar</button>
                    </div>
                </div>
            </div>
            <button class="ai-chatbot-toggle-btn" id="ai-chatbot-toggle-btn">
                <span class="ai-chatbot-toggle-icon">💬</span>
            </button>
        `;

        document.body.insertAdjacentHTML('beforeend', chatHTML);
        this.attachEvents();
    }

    attachEvents() {
        const toggleBtn = document.getElementById('ai-chatbot-toggle-btn');
        const closeBtn = document.getElementById('ai-chatbot-close');
        const sendBtn = document.getElementById('ai-chatbot-send');
        const input = document.getElementById('ai-chatbot-input');
        const container = document.getElementById('ai-chatbot-container');

        toggleBtn.addEventListener('click', () => this.toggleChat());
        closeBtn.addEventListener('click', () => this.toggleChat());

        sendBtn.addEventListener('click', () => this.sendMessage());
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.sendMessage();
            }
        });
    }

    toggleChat() {
        const container = document.getElementById('ai-chatbot-container');
        const toggleBtn = document.getElementById('ai-chatbot-toggle-btn');

        this.isOpen = !this.isOpen;

        if (this.isOpen) {
            container.classList.add('open');
            toggleBtn.style.display = 'none';
            document.getElementById('ai-chatbot-input').focus();
        } else {
            container.classList.remove('open');
            toggleBtn.style.display = 'flex';
        }
    }

    async sendMessage() {
        const input = document.getElementById('ai-chatbot-input');
        const message = input.value.trim();

        if (!message) return;

        // Agregar mensaje del usuario
        this.addMessage('user', message);
        input.value = '';

        // Detectar intención del mensaje
        const intent = this.detectIntent(message);

        if (intent === 'new') {
            await this.generateModel(message, 'new');
            return;
        }

        if (intent === 'modify') {
            if (this.hasModel) {
                const modified = this.applyLocalModification(message);
                if (!modified) {
                    this.addMessage('bot', 'He entendido que quieres modificar el modelo actual, pero aún no sé cómo aplicar esa modificación concreta.');
                }
            } else {
                // No hay modelo aún: tratamos como nuevo si tiene sentido
                this.addMessage('bot', 'Aún no hay un modelo cargado. Voy a generar uno nuevo a partir de tu descripción.');
                await this.generateModel(message, 'new');
            }
            return;
        }

        // Si no encaja claramente pero parece una generación, trátalo como nuevo
        if (this.isGenerationRequest(message)) {
            await this.generateModel(message, 'new');
        } else {
            this.addMessage('bot', 'Puedo ayudarte a generar y modificar modelos 3D. Por ejemplo, di "genera un cubo rojo" o "hazlo más grande".');
        }
    }

    isGenerationRequest(message) {
        // Detectar cualquier solicitud de generación de forma más flexible
        const lowerMessage = message.toLowerCase();

        // Palabras clave que indican generación
        const generationKeywords = [
            'genera', 'crea', 'haz', 'diseña', 'modelo', 'objeto', '3d',
            'hacer', 'construir', 'fabricar', 'producir', 'elaborar'
        ];

        // Si contiene alguna palabra clave, es una solicitud de generación
        const hasKeyword = generationKeywords.some(keyword => lowerMessage.includes(keyword));

        // También considerar mensajes cortos que describen objetos (ej: "un pato", "cubo rojo")
        // Si el mensaje es corto y no tiene palabras de pregunta, probablemente es una solicitud
        const isShortDescription = message.split(' ').length <= 5 &&
            !lowerMessage.includes('?') &&
            !lowerMessage.includes('qué') &&
            !lowerMessage.includes('cómo') &&
            !lowerMessage.includes('cuándo');

        return hasKeyword || isShortDescription;
    }

    detectIntent(message) {
        const lower = message.toLowerCase();

        const newKeywords = [
            'genera', 'crear', 'crea', 'nuevo', 'cambia por', 'reemplaza', 'reemplazar',
            'cargar', 'carga', 'mostrar', 'muestrame', 'ver un', 'ver una', 'empezar',
            'resetear', 'limpiar', 'subir', 'importar', 'abrir'
        ];

        const modifyKeywords = [
            'ajustar', 'ajusta', 'cambia', 'cambiar', 'poner', 'editar', 'transformar',
            'escalar', 'más grande', 'mas grande', 'agrandar', 'agrandalo', 'encoger',
            'más pequeño', 'mas pequeño', 'más alto', 'mas alto', 'más ancho', 'mas ancho',
            'pintar', 'píntalo', 'pintalo', 'color', 'material', 'textura', 'acabado'
        ];

        const hasNew = newKeywords.some(k => lower.includes(k));
        const hasModify = modifyKeywords.some(k => lower.includes(k));

        // Si hay palabras de ambos tipos, priorizamos "nuevo"
        if (hasNew) return 'new';
        if (hasModify) return 'modify';

        return 'other';
    }

    async generateModel(prompt, intent = 'new') {
        this.addMessage('bot', '🔄 Generando tu modelo 3D... Esto puede tomar unos momentos.');

        if (intent === 'new' && this.viewer && typeof this.viewer.clearModel === 'function') {
            this.viewer.clearModel();
            this.hasModel = false;
            this.currentJobId = null;
            // Mostrar el texto de carga mientras se genera el nuevo modelo
            const loadingElement = document.getElementById('preview-loading');
            if (loadingElement) {
                loadingElement.style.display = '';
            }
            if (this.options.onViewerCleared && typeof this.options.onViewerCleared === 'function') {
                this.options.onViewerCleared();
            }
        }
        try {
            this.lastPrompt = prompt || '';
            // Limpiar prompt - extraer solo la descripción del objeto
            const cleanPrompt = this.extractObjectDescription(prompt);

            console.log('Enviando solicitud de generación:', cleanPrompt);

            const response = await fetch(this.apiUrl + '?action=generateFromText', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    prompt: cleanPrompt,
                    output_format: 'stl',
                    model_type: 'shap-e',
                    quality: 'medium'
                })
            });

            // Verificar si la respuesta es JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Respuesta no JSON:', text);
                throw new Error('El servidor no respondió correctamente. Verifica que el servicio de IA esté corriendo.');
            }

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({ error: `Error HTTP: ${response.status}` }));
                throw new Error(errorData.error || `Error HTTP: ${response.status}`);
            }

            const data = await response.json();
            console.log('Respuesta del servidor:', data);

            if (data.success && data.job_id) {
                this.currentJobId = data.job_id;
                this.addMessage('bot', '✅ Generación iniciada. Redirigiendo para cargar el modelo en el visor...');
                var basePath = window.BASE_PATH || '/';
                var customizeUrl = basePath + '?action=customize&job_id=' + encodeURIComponent(data.job_id) + '&prompt=' + encodeURIComponent(this.lastPrompt || '');
                window.location.href = customizeUrl;
                return;
            } else {
                throw new Error(data.error || 'Error al iniciar la generación');
            }
        } catch (error) {
            console.error('Error completo:', error);
            this.addMessage('bot', `❌ Error: ${error.message}`);

            // Mensaje de ayuda adicional
            if (error.message.includes('servicio') || error.message.includes('servidor')) {
                this.addMessage('bot', '💡 Asegúrate de que el microservicio de IA esté corriendo en http://localhost:8000');
            }
        }
    }

    extractObjectDescription(message) {
        // Extraer la descripción del objeto del mensaje
        // Ejemplos:
        // "genera un pato" -> "a duck"
        // "crea un cubo rojo" -> "a red cube"
        // "haz una silla" -> "a chair"

        let cleanMessage = message.toLowerCase();

        // Remover palabras de acción
        const actionWords = ['genera', 'crea', 'haz', 'diseña', 'hacer', 'construir', 'fabricar'];
        actionWords.forEach(word => {
            cleanMessage = cleanMessage.replace(new RegExp(`\\b${word}\\b`, 'gi'), '');
        });

        // Remover artículos en español y agregar artículo en inglés
        cleanMessage = cleanMessage.replace(/\b(un|una|el|la|los|las)\b/gi, '');
        cleanMessage = cleanMessage.trim();

        // Agregar artículo en inglés al inicio
        if (cleanMessage && !cleanMessage.startsWith('a ') && !cleanMessage.startsWith('an ')) {
            // Determinar si usar "a" o "an"
            const firstLetter = cleanMessage.charAt(0);
            const useAn = ['a', 'e', 'i', 'o', 'u'].includes(firstLetter);
            cleanMessage = (useAn ? 'an ' : 'a ') + cleanMessage;
        }

        return cleanMessage || 'a 3d object';
    }

    async pollJobStatus(jobId) {
        const maxAttempts = 150; // 5 minutos máximo (2 segundos * 150)
        let attempts = 0;

        this.pollInterval = setInterval(async () => {
            attempts++;

            try {
                const response = await fetch(`${this.apiUrl}?action=getJobStatus&job_id=${jobId}`);

                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }

                const data = await response.json();

                if (data.status === 'completed') {
                    clearInterval(this.pollInterval);
                    this.addMessage('bot', '🎉 ¡Modelo generado exitosamente!');
                    await this.loadGeneratedModel(jobId);
                } else if (data.status === 'failed') {
                    clearInterval(this.pollInterval);
                    this.addMessage('bot', `❌ Error: ${data.error || 'Error desconocido'}`);
                } else if (attempts >= maxAttempts) {
                    clearInterval(this.pollInterval);
                    this.addMessage('bot', '⏱️ El proceso está tomando más tiempo del esperado. Por favor, verifica más tarde.');
                }
            } catch (error) {
                clearInterval(this.pollInterval);
                this.addMessage('bot', `❌ Error al verificar el estado: ${error.message}`);
            }
        }, 2000);
    }

    /**
     * Carga un job ya completado en el visor (p. ej. al llegar con job_id en la URL).
     * @param {string} jobId - ID del job
     * @param {string} prompt - Prompt usado (para "Añadir a catálogo")
     */
    loadJobInViewer(jobId, prompt) {
        this.lastPrompt = prompt || '';
        return this.loadGeneratedModel(jobId);
    }

    async loadGeneratedModel(jobId) {
        try {
            // Descargar el modelo
            const downloadUrl = `${this.apiUrl}?action=downloadModel&job_id=${jobId}`;

            // Cargar en el visor 3D
            if (this.viewer) {
                this.addMessage('bot', '📦 Cargando modelo en la vista previa...');

                // Hacer fetch del archivo
                const response = await fetch(downloadUrl);

                if (!response.ok) {
                    throw new Error('Error al descargar el modelo');
                }

                const blob = await response.blob();
                const url = URL.createObjectURL(blob);

                // Cargar en el visor
                this.viewer.loadSTL(url, (progress) => {
                    if (progress.success === true) {
                        this.addMessage('bot', '✅ Modelo cargado en la vista previa. ¡Puedes personalizarlo!');
                        this.hasModel = true;
                        // Ocultar el texto de carga
                        const loadingElement = document.getElementById('preview-loading');
                        if (loadingElement) {
                            loadingElement.style.display = 'none';
                        }
                        if (this.options.onModelReady && typeof this.options.onModelReady === 'function') {
                            this.options.onModelReady(jobId);
                        }
                        this.addCatalogButton(jobId, this.lastPrompt || '');
                        // Limpiar URL temporal después de un tiempo
                        setTimeout(() => URL.revokeObjectURL(url), 1000);
                    } else if (progress.success === false) {
                        this.addMessage('bot', '⚠️ Error al cargar el modelo en la vista previa.');
                    }
                    // Si progress.success es undefined, es un evento de progreso, no hacer nada
                });
            } else {
                this.addMessage('bot', '📥 Modelo generado. Puedes descargarlo desde el enlace.');
            }
        } catch (error) {
            this.addMessage('bot', `❌ Error al cargar el modelo: ${error.message}`);
        }
    }

    applyLocalModification(message) {
        if (!this.viewer) {
            this.addMessage('bot', '💡 Para modificar modelos 3D, ve a la página de Personalización donde podrás ver y editar tu modelo.');
            return true; // retornamos true para indicar que ya manejamos el mensaje
        }
        if (!this.hasModel) {
            return false;
        }

        const lower = message.toLowerCase();
        let applied = false;

        // Limpiar / resetear
        if (lower.includes('limpiar') || lower.includes('resetear')) {
            if (typeof this.viewer.clearModel === 'function') {
                this.viewer.clearModel();
                this.hasModel = false;
                this.currentJobId = null;
                // Mostrar el texto de carga cuando se limpia el visor
                const loadingElement = document.getElementById('preview-loading');
                if (loadingElement) {
                    loadingElement.style.display = '';
                }
                if (this.options.onViewerCleared && typeof this.options.onViewerCleared === 'function') {
                    this.options.onViewerCleared();
                }
                this.addMessage('bot', 'He limpiado el visor. Pídeme que genere un nuevo objeto 3D.');
                applied = true;
            }
        }

        // Escalado
        if (typeof this.viewer.scale === 'function') {
            if (lower.includes('más grande') || lower.includes('mas grande') || lower.includes('agrandar') || lower.includes('agrándalo') || lower.includes('agrandalo')) {
                this.viewer.scale(1.5);
                this.addMessage('bot', 'He escalado el modelo un 50% más grande.');
                applied = true;
            }

            if (lower.includes('más pequeño') || lower.includes('mas pequeño') || lower.includes('encoger')) {
                this.viewer.scale(0.7);
                this.addMessage('bot', 'He reducido el tamaño del modelo.');
                applied = true;
            }
        }

        // Cambio de color
        const colorMap = {
            'rojo': 0xff0000,
            'azul': 0x0066ff,
            'verde': 0x00ff00,
            'amarillo': 0xffff00,
            'negro': 0x000000,
            'blanco': 0xffffff
        };

        if (typeof this.viewer.setColor === 'function') {
            for (const [name, hex] of Object.entries(colorMap)) {
                if (lower.includes(name) || lower.includes('color ' + name) || lower.includes('pinta') && lower.includes(name)) {
                    this.viewer.setColor(hex);
                    this.addMessage('bot', `He cambiado el color del modelo a ${name}.`);
                    applied = true;
                    break;
                }
            }
        }

        return applied;
    }

    /**
     * Añade un mensaje con botón "Añadir a catálogo" que guarda el modelo como producto.
     */
    addCatalogButton(jobId, prompt) {
        const messagesContainer = document.getElementById('ai-chatbot-messages');
        const messageDiv = document.createElement('div');
        messageDiv.className = 'ai-chatbot-message ai-chatbot-message-bot';
        const timestamp = new Date().toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
        messageDiv.innerHTML = `
            <div class="ai-chatbot-message-content">
                <p style="margin-bottom: 8px;">¿Quieres añadir este modelo al catálogo de la tienda?</p>
                <button type="button" class="btn btn-primary btn-small ai-chatbot-catalog-btn">Añadir a catálogo</button>
            </div>
            <div class="ai-chatbot-message-time">${timestamp}</div>
        `;
        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;

        const btn = messageDiv.querySelector('.ai-chatbot-catalog-btn');
        btn.addEventListener('click', async () => {
            btn.disabled = true;
            btn.textContent = 'Añadiendo...';
            try {
                const formData = new FormData();
                formData.append('action', 'saveAsProduct');
                formData.append('job_id', jobId);
                formData.append('prompt', prompt || '');
                const response = await fetch(this.apiUrl + '?action=saveAsProduct', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json().catch(() => ({}));
                if (response.ok && data.success) {
                    btn.textContent = 'Añadido';
                    const link = document.createElement('a');
                    link.href = data.product_url;
                    link.target = '_blank';
                    link.rel = 'noopener';
                    link.textContent = 'Ver producto en la tienda';
                    link.className = 'ai-chatbot-product-link';
                    link.style.display = 'block';
                    link.style.marginTop = '8px';
                    messageDiv.querySelector('.ai-chatbot-message-content').appendChild(link);
                    this.addMessage('bot', '✅ Producto añadido al catálogo. Ya puedes verlo en la tienda.');
                } else if (response.status === 401) {
                    btn.textContent = 'Añadir a catálogo';
                    btn.disabled = false;
                    this.addMessage('bot', '⚠️ Inicia sesión para poder añadir el modelo al catálogo.');
                } else {
                    btn.textContent = 'Añadir a catálogo';
                    btn.disabled = false;
                    this.addMessage('bot', '❌ ' + (data.error || 'Error al añadir al catálogo.'));
                }
            } catch (err) {
                btn.textContent = 'Añadir a catálogo';
                btn.disabled = false;
                this.addMessage('bot', '❌ Error de conexión al añadir al catálogo.');
            }
        });
    }

    addMessage(sender, text) {
        const messagesContainer = document.getElementById('ai-chatbot-messages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `ai-chatbot-message ai-chatbot-message-${sender}`;

        const timestamp = new Date().toLocaleTimeString('es-ES', {
            hour: '2-digit',
            minute: '2-digit'
        });

        messageDiv.innerHTML = `
            <div class="ai-chatbot-message-content">
                ${text}
            </div>
            <div class="ai-chatbot-message-time">${timestamp}</div>
        `;

        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;

        // Guardar en historial
        this.messages.push({ sender, text, timestamp });
        this.saveChatHistory();
    }

    saveChatHistory() {
        localStorage.setItem('ai3d_chat_history', JSON.stringify(this.messages));
    }

    loadChatHistory() {
        const saved = localStorage.getItem('ai3d_chat_history');
        if (saved) {
            this.messages = JSON.parse(saved);
            // Mostrar últimos mensajes (opcional)
        }
    }
}

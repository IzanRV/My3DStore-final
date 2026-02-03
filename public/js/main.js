// JavaScript para My3DStore

document.addEventListener('DOMContentLoaded', function() {
    // Menú desplegable
    const menuToggle = document.getElementById('menuToggle');
    const dropdownMenu = document.getElementById('dropdownMenu');
    
    if (menuToggle && dropdownMenu) {
        menuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle('active');
        });
        
        // Cerrar menú al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (!dropdownMenu.contains(e.target) && !menuToggle.contains(e.target)) {
                dropdownMenu.classList.remove('active');
            }
        });
    }
    
    // Chatbot
    const chatbotToggle = document.getElementById('chatbotToggle');
    const chatbotWindow = document.getElementById('chatbotWindow');
    const chatbotClose = document.getElementById('chatbotClose');
    const chatbotSend = document.getElementById('chatbotSend');
    const chatbotInput = document.getElementById('chatbotInput');
    const chatbotMessages = document.getElementById('chatbotMessages');
    
    if (chatbotToggle && chatbotWindow) {
        chatbotToggle.addEventListener('click', function() {
            chatbotWindow.classList.toggle('active');
        });
    }
    
    if (chatbotClose) {
        chatbotClose.addEventListener('click', function() {
            chatbotWindow.classList.remove('active');
        });
    }
    
    if (chatbotSend && chatbotInput) {
        chatbotSend.addEventListener('click', function() {
            sendChatbotMessage();
        });
        
        chatbotInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendChatbotMessage();
            }
        });
    }
    
    function sendChatbotMessage() {
        const message = chatbotInput.value.trim();
        if (message) {
            // Añadir mensaje del usuario
            const userMessage = document.createElement('div');
            userMessage.className = 'chatbot-message user';
            userMessage.innerHTML = '<p>' + message + '</p>';
            chatbotMessages.appendChild(userMessage);
            
            // Limpiar input
            chatbotInput.value = '';
            
            // Scroll al final
            chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
            
            // Simular respuesta del bot (por ahora solo visual)
            setTimeout(function() {
                const botMessage = document.createElement('div');
                botMessage.className = 'chatbot-message bot';
                botMessage.innerHTML = '<p>Gracias por tu mensaje. Esta funcionalidad estará disponible pronto.</p>';
                chatbotMessages.appendChild(botMessage);
                chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
            }, 500);
        }
    }
    
    // Actualizar contador del carrito si existe
    updateCartCount();
    
    // Manejar formularios de cantidad
    const quantityForms = document.querySelectorAll('.quantity-form');
    quantityForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const input = this.querySelector('input[type="number"]');
            if (input && input.value <= 0) {
                e.preventDefault();
                alert('La cantidad debe ser mayor a 0');
            }
        });
    });
    
    // Auto-submit en selects de estado de pedido (admin)
    const statusSelects = document.querySelectorAll('select[name="status"]');
    statusSelects.forEach(select => {
        select.addEventListener('change', function() {
            if (confirm('¿Deseas actualizar el estado del pedido?')) {
                this.form.submit();
            } else {
                // Revertir el cambio
                this.blur();
            }
        });
    });
    
    // Validación de formularios
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#E74C3C';
                } else {
                    field.style.borderColor = '';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Por favor, completa todos los campos obligatorios');
            }
        });
    });
});

function updateCartCount() {
    // Esta función podría hacer una petición AJAX para actualizar el contador
    // Por ahora, el contador se actualiza en el servidor
}

// Confirmar eliminación
function confirmDelete(message) {
    return confirm(message || '¿Estás seguro de que deseas eliminar esto?');
}

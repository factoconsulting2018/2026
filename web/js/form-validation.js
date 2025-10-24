/**
 * SISTEMA DE VALIDACIÓN VISUAL DE FORMULARIOS
 * Proporciona feedback visual en tiempo real para campos de formulario
 */

class FormValidator {
    constructor(formSelector = 'form') {
        // Asegurar que formSelector sea un string válido
        if (typeof formSelector === 'string') {
            this.form = document.querySelector(formSelector);
        } else if (formSelector instanceof HTMLElement) {
            this.form = formSelector;
        } else {
            console.warn('⚠️ FormValidator: selector inválido, usando primer formulario');
            this.form = document.querySelector('form');
        }
        
        this.fields = [];
        this.requiredFields = [];
        this.validFields = [];
        this.invalidFields = [];
        this.init();
    }

    init() {
        if (!this.form) return;
        
        this.setupFields();
        // this.setupProgressBar(); // DESHABILITADO
        this.bindEvents();
        this.validateAll();
    }

    setupFields() {
        // Encontrar todos los campos de entrada
        const inputs = this.form.querySelectorAll('input, select, textarea');
        
        inputs.forEach(input => {
            if (input.type === 'hidden' || input.type === 'submit') return;
            
            const field = {
                element: input,
                label: this.findLabel(input),
                name: input.name || input.id,
                required: this.isRequired(input),
                valid: false,
                touched: false
            };
            
            this.fields.push(field);
            
            if (field.required) {
                this.requiredFields.push(field);
                if (field.label) {
                    field.label.classList.add('required');
                }
            }
        });
    }

    setupProgressBar() {
        // Crear barra de progreso si no existe - DESHABILITADO
        let progressContainer = this.form.querySelector('.form-progress');
        if (!progressContainer) {
            // Comentado para no mostrar el progreso del formulario
            /*
            progressContainer = document.createElement('div');
            progressContainer.className = 'form-progress';
            progressContainer.innerHTML = `
                <h6>Progreso del formulario</h6>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                </div>
                <small class="text-muted">Campos completados: <span class="completed-count">0</span> de <span class="total-count">0</span></small>
            `;
            
            // Insertar al inicio del formulario
            this.form.insertBefore(progressContainer, this.form.firstChild);
            */
        }
        
        // Comentado para evitar errores si no existe el contenedor
        /*
        this.progressBar = progressContainer.querySelector('.progress-bar');
        this.completedCount = progressContainer.querySelector('.completed-count');
        this.totalCount = progressContainer.querySelector('.total-count');
        
        this.totalCount.textContent = this.requiredFields.length;
        */
    }

    bindEvents() {
        this.fields.forEach(field => {
            const input = field.element;
            
            // Eventos para validación en tiempo real
            input.addEventListener('input', () => this.validateField(field));
            input.addEventListener('blur', () => this.validateField(field));
            input.addEventListener('change', () => this.validateField(field));
            
            // Evento para marcar como "touched"
            input.addEventListener('focus', () => {
                field.touched = true;
                this.highlightFieldGroup(field);
            });
            
            input.addEventListener('blur', () => {
                setTimeout(() => this.unhighlightFieldGroup(field), 200);
            });
        });

        // Validar al enviar el formulario
        this.form.addEventListener('submit', (e) => {
            if (!this.validateAll()) {
                e.preventDefault();
                this.showValidationErrors();
                return false;
            }
        });
    }

    validateField(field) {
        const input = field.element;
        const value = input.value.trim();
        
        // Remover clases anteriores
        input.classList.remove('empty', 'complete', 'error');
        if (field.label) {
            field.label.classList.remove('empty', 'complete', 'error');
        }
        
        // Validar campo
        let isValid = true;
        let message = '';
        
        if (field.required && !value) {
            isValid = false;
            message = 'Este campo es requerido';
        } else if (value) {
            // Validaciones específicas por tipo
            isValid = this.validateByType(input, value);
            if (!isValid) {
                message = this.getValidationMessage(input);
            }
        }
        
        // Aplicar estado visual
        if (field.required && !value) {
            input.classList.add('empty');
            if (field.label) field.label.classList.add('empty');
            field.valid = false;
        } else if (isValid && value) {
            input.classList.add('complete');
            if (field.label) field.label.classList.add('complete');
            field.valid = true;
        } else if (!isValid) {
            input.classList.add('error');
            if (field.label) field.label.classList.add('error');
            field.valid = false;
        }
        
        // Mostrar mensaje de validación
        this.showFieldMessage(field, message);
        
        // Actualizar contadores
        this.updateCounters();
        
        return isValid;
    }

    validateByType(input, value) {
        switch (input.type) {
            case 'email':
                return this.isValidEmail(value);
            case 'tel':
                return this.isValidPhone(value);
            case 'url':
                return this.isValidUrl(value);
            case 'number':
                return this.isValidNumber(input, value);
            case 'date':
                return this.isValidDate(value);
            case 'text':
                // Validaciones específicas por campo
                if (input.name && input.name.includes('placa')) {
                    return this.isValidPlaca(value);
                }
                if (input.name && input.name.includes('vin')) {
                    return this.isValidVIN(value);
                }
                return true;
            default:
                return true;
        }
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    isValidPhone(phone) {
        const phoneRegex = /^[\+]?[0-9\s\-\(\)]{7,}$/;
        return phoneRegex.test(phone);
    }

    isValidUrl(url) {
        try {
            new URL(url);
            return true;
        } catch {
            return false;
        }
    }

    isValidNumber(input, value) {
        const num = parseFloat(value);
        if (isNaN(num)) return false;
        
        if (input.min && num < parseFloat(input.min)) return false;
        if (input.max && num > parseFloat(input.max)) return false;
        
        return true;
    }

    isValidDate(date) {
        const dateObj = new Date(date);
        return dateObj instanceof Date && !isNaN(dateObj);
    }

    isValidPlaca(placa) {
        // Validar formato de placa costarricense (ABC-123 o ABC123)
        const placaRegex = /^[A-Z]{3}[-]?[0-9]{3}$/;
        return placaRegex.test(placa.toUpperCase());
    }

    isValidVIN(vin) {
        // VIN debe tener 17 caracteres alfanuméricos
        const vinRegex = /^[A-HJ-NPR-Z0-9]{17}$/;
        return vinRegex.test(vin.toUpperCase());
    }

    getValidationMessage(input) {
        switch (input.type) {
            case 'email':
                return 'Por favor ingresa un email válido';
            case 'tel':
                return 'Por favor ingresa un teléfono válido';
            case 'url':
                return 'Por favor ingresa una URL válida';
            case 'number':
                if (input.min && parseFloat(input.value) < parseFloat(input.min)) {
                    return `El valor mínimo es ${input.min}`;
                }
                if (input.max && parseFloat(input.value) > parseFloat(input.max)) {
                    return `El valor máximo es ${input.max}`;
                }
                return 'Por favor ingresa un número válido';
            case 'date':
                return 'Por favor ingresa una fecha válida';
            case 'text':
                // Mensajes específicos para campos de vehículos
                if (input.name && input.name.includes('placa')) {
                    return 'Formato de placa inválido. Use formato ABC-123 o ABC123';
                }
                if (input.name && input.name.includes('vin')) {
                    return 'VIN inválido. Debe tener 17 caracteres alfanuméricos';
                }
                return 'Valor inválido';
            default:
                return 'Valor inválido';
        }
    }

    validateAll() {
        let allValid = true;
        
        this.fields.forEach(field => {
            if (!this.validateField(field)) {
                allValid = false;
            }
        });
        
        return allValid;
    }

    showValidationErrors() {
        this.requiredFields.forEach(field => {
            if (!field.valid) {
                field.element.classList.add('error');
                if (field.label) field.label.classList.add('error');
                
                // Scroll al primer campo con error
                if (this.requiredFields.indexOf(field) === 0) {
                    field.element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    field.element.focus();
                }
            }
        });
        
        // Mostrar mensaje general
        this.showGlobalMessage('Por favor corrige los errores marcados en rojo', 'error');
    }

    showFieldMessage(field, message) {
        // Remover mensaje anterior
        const existingMessage = field.element.parentNode.querySelector('.validation-message');
        if (existingMessage) {
            existingMessage.remove();
        }
        
        if (message) {
            const messageEl = document.createElement('div');
            messageEl.className = 'validation-message ' + (field.valid ? 'complete' : 'error');
            messageEl.textContent = message;
            field.element.parentNode.appendChild(messageEl);
        }
    }

    showGlobalMessage(message, type = 'info') {
        // Remover mensaje anterior
        const existingMessage = this.form.querySelector('.global-validation-message');
        if (existingMessage) {
            existingMessage.remove();
        }
        
        const messageEl = document.createElement('div');
        messageEl.className = `global-validation-message alert alert-${type === 'error' ? 'danger' : 'info'}`;
        messageEl.innerHTML = `
            <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; margin-right: 8px;">
                ${type === 'error' ? 'error' : 'info'}
            </span>
            ${message}
        `;
        
        // Insertar después del progress bar
        const progressContainer = this.form.querySelector('.form-progress');
        if (progressContainer) {
            progressContainer.insertAdjacentElement('afterend', messageEl);
        } else {
            this.form.insertBefore(messageEl, this.form.firstChild);
        }
        
        // Auto-remover después de 5 segundos
        setTimeout(() => {
            if (messageEl.parentNode) {
                messageEl.remove();
            }
        }, 5000);
    }

    updateCounters() {
        // Comentado para no mostrar progreso del formulario
        /*
        const completed = this.requiredFields.filter(field => field.valid).length;
        const total = this.requiredFields.length;
        const percentage = total > 0 ? (completed / total) * 100 : 0;
        
        this.completedCount.textContent = completed;
        this.progressBar.style.width = percentage + '%';
        
        // Cambiar color según progreso
        this.progressBar.className = 'progress-bar';
        if (percentage === 100) {
            this.progressBar.classList.add('bg-success');
        } else if (percentage >= 50) {
            this.progressBar.classList.add('bg-warning');
        } else {
            this.progressBar.classList.add('bg-danger');
        }
        */
    }

    highlightFieldGroup(field) {
        const fieldGroup = field.element.closest('.form-group');
        if (fieldGroup) {
            fieldGroup.classList.add('highlight');
        }
    }

    unhighlightFieldGroup(field) {
        const fieldGroup = field.element.closest('.form-group');
        if (fieldGroup) {
            fieldGroup.classList.remove('highlight');
        }
    }

    findLabel(input) {
        // Buscar label asociado
        if (input.id) {
            const label = document.querySelector(`label[for="${input.id}"]`);
            if (label) return label;
        }
        
        // Buscar label padre
        const parentLabel = input.closest('label');
        if (parentLabel) return parentLabel;
        
        // Buscar label anterior
        let previous = input.previousElementSibling;
        while (previous) {
            if (previous.tagName === 'LABEL') return previous;
            previous = previous.previousElementSibling;
        }
        
        return null;
    }

    isRequired(input) {
        return input.hasAttribute('required') || 
               input.classList.contains('required') ||
               (input.name && input.name.includes('required'));
    }
}

// Auto-inicializar en todos los formularios cuando se carga el DOM - DESHABILITADO
/*
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        if (!form.querySelector('.form-validator-initialized')) {
            form.classList.add('form-validator-initialized');
            new FormValidator(form);
        }
    });
});
*/

// Exportar para uso manual
window.FormValidator = FormValidator;

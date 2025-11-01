/**
 * JavaScript simple para formularios de actualización de clientes
 * Este archivo se carga solo para actualizaciones
 */

(function() {
    'use strict';
    
    console.log('🚀 client-update.js cargado');
    
    function initUpdateForm() {
        console.log('✅ Inicializando formulario de actualización');
        
        const form = document.getElementById('client-form');
        const submitBtn = document.getElementById('guardar-cliente-btn');
        
        if (!form) {
            console.error('❌ Formulario no encontrado');
            return;
        }
        
        if (!submitBtn) {
            console.error('❌ Botón submit no encontrado');
            return;
        }
        
        console.log('✅ Formulario y botón encontrados');
        
        // Asegurar method="post"
        if (form.getAttribute('method') !== 'post') {
            form.setAttribute('method', 'post');
            console.log('🔧 Method cambiado a POST');
        }
        
        // Asegurar que el botón esté asociado al formulario
        if (!submitBtn.form) {
            console.log('🔧 Botón no está asociado al formulario - asociando manualmente');
            submitBtn.setAttribute('form', form.id);
        }
        
        // Listener SIMPLE al botón - FORZAR SUBMIT si no se asocia automáticamente
        submitBtn.addEventListener('click', function(e) {
            console.log('🖱️ CLICK EN BOTÓN GUARDAR CLIENTE');
            
            const currentForm = document.getElementById('client-form');
            if (!currentForm) {
                console.error('❌ Formulario no encontrado en click handler');
                e.preventDefault();
                return;
            }
            
            // Validar formulario por pestañas antes de enviar
            if (typeof validarFormularioPorPestanas === 'function') {
                if (!validarFormularioPorPestanas()) {
                    console.log('❌ Validación del formulario falló - PREVENIR ENVÍO');
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            }
            
            // Asegurar method="post"
            if (currentForm.getAttribute('method') !== 'post') {
                currentForm.setAttribute('method', 'post');
                console.log('🔧 Method cambiado a POST');
            }
            
            // Si el botón NO está asociado al formulario, enviar manualmente
            if (!submitBtn.form || submitBtn.form !== currentForm) {
                console.log('⚠️ Botón no asociado al formulario - enviando manualmente');
                e.preventDefault();
                e.stopPropagation();
                
                // Deshabilitar Yii2 ActiveForm completamente antes de enviar
                if (typeof $ !== 'undefined' && $.fn && $.fn.yiiActiveForm) {
                    const $form = $(currentForm);
                    const activeFormData = $form.data('yiiActiveForm');
                    if (activeFormData) {
                        activeFormData.settings.validateOnSubmit = false;
                        if (activeFormData.settings.submitHandler) {
                            delete activeFormData.settings.submitHandler;
                        }
                        $form.off('submit.yiiActiveForm');
                        activeFormData.validated = true;
                        console.log('✅ Yii2 ActiveForm deshabilitado antes de submit');
                    }
                }
                
                // Enviar formulario manualmente
                console.log('📤 Enviando formulario manualmente');
                currentForm.submit();
            } else {
                console.log('✅ Botón asociado al formulario - submit nativo permitido');
            }
        }, { capture: false, passive: false }); // NO passive para poder prevenir si es necesario
        
        // Remover required de campos ocultos que puedan bloquear el submit
        const hiddenRequiredInputs = form.querySelectorAll('input[required], textarea[required]');
        hiddenRequiredInputs.forEach(function(input) {
            // Verificar si el campo está visible
            const isVisible = input.offsetParent !== null && 
                            window.getComputedStyle(input).display !== 'none' &&
                            window.getComputedStyle(input).visibility !== 'hidden';
            
            if (!isVisible) {
                console.log('🔧 Removiendo required de campo oculto:', input.id || input.name);
                input.removeAttribute('required');
                // Guardar que era required para restaurarlo después si es necesario
                input.setAttribute('data-was-required', 'true');
            }
        });
        
        // Listener al formulario para log y verificar que se envía
        form.addEventListener('submit', function(e) {
            console.log('📤 EVENTO SUBMIT DEL FORMULARIO DETECTADO');
            
            // Validar formulario por pestañas antes de enviar
            if (typeof validarFormularioPorPestanas === 'function') {
                if (!validarFormularioPorPestanas()) {
                    console.log('❌ Validación del formulario falló - PREVENIR ENVÍO');
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            }
            
            console.log('📋 Method:', form.getAttribute('method'));
            console.log('📋 Action:', form.getAttribute('action') || form.action);
            console.log('📋 FormData keys:', Array.from(new FormData(form).keys()).join(', '));
            
            // NO prevenir - dejar que se envíe normalmente
            console.log('✅ Formulario se está enviando...');
        }, { capture: false, passive: false }); // Cambiar a false para poder prevenir
        
        console.log('✅ Listeners agregados - el formulario debería funcionar');
        
        // Test: verificar que el botón es clickeable
        console.log('🔍 Estado del botón:');
        console.log('  - Tipo:', submitBtn.type);
        console.log('  - Disabled:', submitBtn.disabled);
        console.log('  - Style display:', window.getComputedStyle(submitBtn).display);
        console.log('  - Style pointer-events:', window.getComputedStyle(submitBtn).pointerEvents);
        console.log('  - Form asociado:', submitBtn.form ? '✅ Sí' : '❌ No');
        console.log('  - Form ID:', submitBtn.form ? submitBtn.form.id : 'N/A');
        
        // Deshabilitar Yii2 ActiveForm si está activo
        setTimeout(function() {
            try {
                if (typeof $ !== 'undefined' && $.fn && $.fn.yiiActiveForm) {
                    const $form = $(form);
                    const activeFormData = $form.data('yiiActiveForm');
                    if (activeFormData) {
                        console.log('🔧 Deshabilitando Yii2 ActiveForm completamente');
                        activeFormData.settings.validateOnSubmit = false;
                        activeFormData.settings.validateOnChange = false;
                        activeFormData.settings.validateOnBlur = false;
                        if (activeFormData.settings.submitHandler) {
                            console.log('🔧 Eliminando submitHandler de ActiveForm');
                            delete activeFormData.settings.submitHandler;
                        }
                        // Remover todos los listeners de submit
                        $form.off('submit.yiiActiveForm');
                        $form.off('beforeSubmit.yiiActiveForm');
                        $form.off('beforeValidate.yiiActiveForm');
                        $form.off('afterValidate.yiiActiveForm');
                        activeFormData.validated = true;
                        console.log('✅ Yii2 ActiveForm deshabilitado completamente');
                    } else {
                        console.log('ℹ️ Yii2 ActiveForm no está activo');
                    }
                }
            } catch (err) {
                console.warn('⚠️ Error al deshabilitar ActiveForm:', err);
            }
        }, 200);
        
        // Listener adicional usando onclick como fallback (removido - el addEventListener es suficiente)
    }
    
    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initUpdateForm);
    } else {
        initUpdateForm();
    }
})();


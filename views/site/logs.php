<?php
/** @var yii\web\View $this */
/** @var string $content */
/** @var string $logFile */

$this->title = 'Logs de Error';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">
                <span class="material-symbols-outlined" style="font-size: 32px; vertical-align: middle; margin-right: 8px;">description</span>
                Logs de Error
            </h1>

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <span class="material-symbols-outlined" style="vertical-align: middle; margin-right: 8px;">info</span>
                        Archivo: <?= htmlspecialchars($logFile) ?>
                    </h5>
                </div>
                <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                    <?php if (!empty($content)): ?>
                        <pre style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; font-size: 12px; line-height: 1.5;"><?= htmlspecialchars($content) ?></pre>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <span class="material-symbols-outlined" style="vertical-align: middle; margin-right: 8px;">info</span>
                            No hay registros de log disponibles.
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <a href="<?= \yii\helpers\Url::to(['/site/index']) ?>" class="btn btn-secondary">
                        <span class="material-symbols-outlined" style="vertical-align: middle; margin-right: 4px;">arrow_back</span>
                        Volver
                    </a>
                    <button onclick="location.reload()" class="btn btn-primary">
                        <span class="material-symbols-outlined" style="vertical-align: middle; margin-right: 4px;">refresh</span>
                        Actualizar
                    </button>
                    <button onclick="captureLogs()" class="btn btn-success">
                        <span class="material-symbols-outlined" style="vertical-align: middle; margin-right: 4px;">camera_alt</span>
                        Capturar PNG
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
pre {
    margin: 0;
    white-space: pre-wrap;
    word-wrap: break-word;
}

#loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

#loading-content {
    background: white;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
}

.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 2s linear infinite;
    margin: 0 auto 10px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<!-- html2canvas library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
function captureLogs() {
    const overlay = document.getElementById('loading-overlay');
    const cardBody = document.querySelector('.card-body');
    
    // Mostrar overlay de carga
    overlay.style.display = 'flex';
    
    // Configurar html2canvas
    html2canvas(cardBody, {
        backgroundColor: '#ffffff',
        scale: 2, // Mayor resolución
        useCORS: true,
        allowTaint: true,
        scrollX: 0,
        scrollY: 0,
        width: cardBody.scrollWidth,
        height: cardBody.scrollHeight
    }).then(function(canvas) {
        // Crear enlace de descarga
        const link = document.createElement('a');
        link.download = 'logs_error_' + new Date().toISOString().slice(0, 19).replace(/:/g, '-') + '.png';
        link.href = canvas.toDataURL('image/png');
        
        // Descargar automáticamente
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Ocultar overlay
        overlay.style.display = 'none';
        
        // Mostrar mensaje de éxito
        alert('Captura de pantalla guardada exitosamente');
        
    }).catch(function(error) {
        console.error('Error al capturar:', error);
        overlay.style.display = 'none';
        alert('Error al capturar la pantalla: ' + error.message);
    });
}
</script>

<!-- Overlay de carga -->
<div id="loading-overlay">
    <div id="loading-content">
        <div class="spinner"></div>
        <p>Generando captura de pantalla...</p>
    </div>
</div>

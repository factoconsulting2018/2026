/*!
 * image-compress.js
 *
 * Comprime imágenes en el navegador antes de subirlas para evitar errores
 * 413 (Request Entity Too Large) de nginx/PHP y acelerar el upload.
 *
 * Activación: agregar `data-compress` al <input type="file">.
 * Atributos opcionales:
 *   data-max-side   = "1600"    -> tamaño máximo del lado más largo (px)
 *   data-quality    = "0.85"    -> calidad JPEG (0.0 - 1.0)
 *   data-threshold  = "500000"  -> bytes; no se comprime si está bajo este valor
 *   data-mime       = "image/jpeg"
 *
 * También exporta `window.ImageCompress = { attach, compressImage }` para
 * uso programático.
 */
(function () {
    'use strict';

    function bytesHuman(n) {
        if (n < 1024) return n + ' B';
        if (n < 1024 * 1024) return (n / 1024).toFixed(1) + ' KB';
        return (n / (1024 * 1024)).toFixed(2) + ' MB';
    }

    function readAsDataURL(file) {
        return new Promise(function (resolve, reject) {
            var reader = new FileReader();
            reader.onload = function () { resolve(reader.result); };
            reader.onerror = function () { reject(new Error('No se pudo leer la imagen.')); };
            reader.readAsDataURL(file);
        });
    }

    function loadImage(dataUrl) {
        return new Promise(function (resolve, reject) {
            var img = new Image();
            img.onload = function () { resolve(img); };
            img.onerror = function () { reject(new Error('No se pudo procesar la imagen.')); };
            img.src = dataUrl;
        });
    }

    function compressImage(file, opts) {
        opts = opts || {};
        var maxSide = opts.maxSide || 1600;
        var quality = typeof opts.quality === 'number' ? opts.quality : 0.85;
        var mime = opts.mime || 'image/jpeg';

        if (!file || !/^image\//.test(file.type)) return Promise.resolve(file);
        // No tocar GIF (perdería animación) ni SVG (vectorial).
        if (file.type === 'image/gif' || file.type === 'image/svg+xml') return Promise.resolve(file);

        return readAsDataURL(file)
            .then(loadImage)
            .then(function (img) {
                var w = img.naturalWidth || img.width;
                var h = img.naturalHeight || img.height;
                if (!w || !h) return file;
                var ratio = Math.min(1, maxSide / Math.max(w, h));
                var nw = Math.max(1, Math.round(w * ratio));
                var nh = Math.max(1, Math.round(h * ratio));

                var canvas = document.createElement('canvas');
                canvas.width = nw;
                canvas.height = nh;
                var ctx = canvas.getContext('2d');
                if (mime === 'image/jpeg') {
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, nw, nh);
                }
                ctx.drawImage(img, 0, 0, nw, nh);

                return new Promise(function (resolve) {
                    canvas.toBlob(function (blob) {
                        if (!blob) return resolve(file);
                        if (blob.size >= file.size) return resolve(file);
                        var ext = mime === 'image/jpeg' ? '.jpg' : (mime === 'image/webp' ? '.webp' : '.png');
                        var base = (file.name || 'imagen').replace(/\.[^.]+$/, '');
                        try {
                            var newFile = new File([blob], base + ext, { type: mime, lastModified: Date.now() });
                            resolve(newFile);
                        } catch (e) {
                            // Safari/Edge muy viejos podrían no soportar File(). Fallback con Blob.
                            blob.name = base + ext;
                            resolve(blob);
                        }
                    }, mime, quality);
                });
            })
            .catch(function (e) {
                console.warn('[image-compress] fallo, se sube original:', e);
                return file;
            });
    }

    function replaceFile(input, newFile) {
        try {
            var dt = new DataTransfer();
            dt.items.add(newFile);
            input.files = dt.files;
            return true;
        } catch (e) {
            console.warn('[image-compress] No se pudo reemplazar el archivo (sin soporte DataTransfer):', e);
            return false;
        }
    }

    function getInfoBox(input) {
        var holder = input.closest('.car-imagen-field') || input.parentElement;
        if (!holder) return null;
        var box = holder.querySelector('.image-compress-info');
        if (!box) {
            box = document.createElement('div');
            box.className = 'image-compress-info small mt-2';
            holder.appendChild(box);
        }
        return box;
    }

    function showInfo(input, msg, level) {
        var box = getInfoBox(input);
        if (!box) return;
        box.style.color = level === 'success'
            ? '#198754'
            : (level === 'error' ? '#dc3545' : '#6c757d');
        box.textContent = msg;
    }

    function attach(input) {
        if (!input || input.dataset.compressBound === '1') return;
        input.dataset.compressBound = '1';

        var opts = {
            maxSide: parseInt(input.dataset.maxSide || '1600', 10),
            quality: parseFloat(input.dataset.quality || '0.85'),
            threshold: parseInt(input.dataset.threshold || '500000', 10),
            mime: input.dataset.mime || 'image/jpeg'
        };

        input.addEventListener('change', function () {
            var file = input.files && input.files[0];
            if (!file) return;
            if (!/^image\//.test(file.type)) {
                showInfo(input, '', 'info');
                return;
            }

            var originalSize = file.size;
            if (file.type === 'image/gif' || file.type === 'image/svg+xml') {
                showInfo(input, file.type + ' detectado — no se comprime.', 'info');
                return;
            }
            if (originalSize < opts.threshold) {
                showInfo(input, '📁 Tamaño: ' + bytesHuman(originalSize) + ' — no requiere optimización.', 'info');
                return;
            }

            showInfo(input, '⏳ Optimizando imagen (' + bytesHuman(originalSize) + ')…', 'info');
            compressImage(file, opts).then(function (newFile) {
                replaceFile(input, newFile);
                if (newFile && newFile.size && newFile.size < originalSize) {
                    var pct = Math.round((1 - newFile.size / originalSize) * 100);
                    showInfo(
                        input,
                        '✅ Imagen optimizada: ' + bytesHuman(originalSize) + ' → ' + bytesHuman(newFile.size)
                        + ' (' + pct + '% menos).',
                        'success'
                    );
                } else {
                    showInfo(input, '📁 Imagen lista (' + bytesHuman(originalSize) + ').', 'info');
                }
            });
        });
    }

    function init() {
        document.querySelectorAll('input[type=file][data-compress]').forEach(attach);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window.ImageCompress = { attach: attach, compressImage: compressImage };
})();

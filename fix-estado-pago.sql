-- Script SQL para corregir el tamaño de la columna estado_pago en la tabla rentals
-- Ejecutar este script directamente en MySQL/MariaDB en producción

-- Verificar el tamaño actual de la columna (opcional, para diagnóstico)
-- SELECT COLUMN_NAME, COLUMN_TYPE, CHARACTER_MAXIMUM_LENGTH 
-- FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_SCHEMA = DATABASE() 
--   AND TABLE_NAME = 'rentals' 
--   AND COLUMN_NAME = 'estado_pago';

-- Corregir el tamaño de la columna estado_pago a VARCHAR(20)
ALTER TABLE `rentals` 
MODIFY COLUMN `estado_pago` VARCHAR(20) NOT NULL DEFAULT 'pendiente' 
COMMENT 'Estado de pago del alquiler';

-- Verificar que se aplicó correctamente
-- SELECT COLUMN_NAME, COLUMN_TYPE, CHARACTER_MAXIMUM_LENGTH 
-- FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_SCHEMA = DATABASE() 
--   AND TABLE_NAME = 'rentals' 
--   AND COLUMN_NAME = 'estado_pago';

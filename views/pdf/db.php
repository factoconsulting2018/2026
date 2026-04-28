<?php

/**
 * Punto único de configuración de BD (Yii2).
 *
 * Este archivo existía duplicado con credenciales y lógica propia; cualquier
 * inclusión legacy debe resolver contra `config/db.php` para evitar drift y
 * secretos duplicados.
 */
return require dirname(__DIR__, 2) . '/config/db.php';

<?php

// Cargar información de versión
$versionInfo = require __DIR__ . '/version.php';

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'appVersion' => $versionInfo['version'],
    'appBuild' => $versionInfo['build'],
    'appDescription' => $versionInfo['description'],
    /**
     * Orígenes permitidos para CORS (ApiController). Solo estos esquemas/host
     * recibirán Access-Control-Allow-Origin en respuestas y preflight OPTIONS.
     * Ajusta si usas otro subdominio o http en desarrollo local.
     */
    'corsAllowedOrigins' => [
        'https://factorentacar.com',
        'https://www.factorentacar.com',
        'https://app.factorentacar.com',
    ],
    /** Contraseña para eliminar un insidente (listado o ficha). */
    'incidentDeletePassword' => '3030',
];

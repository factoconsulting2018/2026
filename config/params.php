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
];

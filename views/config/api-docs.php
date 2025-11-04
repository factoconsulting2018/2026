<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Documentación de la API';
$this->params['breadcrumbs'][] = ['label' => 'Configuración', 'url' => ['config/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="api-docs">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-book"></i> Documentación de la API REST v1</h3>
                </div>
                <div class="card-body">
                    <!-- Introducción -->
                    <div class="mb-5">
                        <h4>Introducción</h4>
                        <p>La API REST de Facto Rent a Car permite acceder a los datos del sistema mediante endpoints estándar. Todas las peticiones requieren autenticación mediante API Key.</p>
                        
                        <div class="alert alert-info">
                            <strong>Base URL:</strong> <code>https://app.factorentacar.com/api/v1/</code>
                            <br><small class="text-muted">URL actual: <?= Yii::$app->request->hostInfo ?>/api/v1/</small>
                        </div>
                    </div>

                    <!-- Autenticación -->
                    <div class="mb-5">
                        <h4>Autenticación</h4>
                        <p>Todas las peticiones (excepto <code>/health</code>) requieren autenticación mediante API Key. Puedes proporcionar la key de dos formas:</p>
                        
                        <h5>1. Header HTTP</h5>
                        <pre class="bg-light p-3 rounded"><code>X-API-Key: tu_api_key_aqui</code></pre>
                        
                        <h5>2. Query Parameter</h5>
                        <pre class="bg-light p-3 rounded"><code>?api_key=tu_api_key_aqui</code></pre>
                        
                        <div class="alert alert-warning">
                            <strong>Nota:</strong> Si la API Key no es válida o está inactiva, recibirás un error 401 (Unauthorized).
                        </div>
                    </div>

                    <!-- Formato de Respuestas -->
                    <div class="mb-5">
                        <h4>Formato de Respuestas</h4>
                        
                        <h5>Respuesta Exitosa</h5>
                        <pre class="bg-light p-3 rounded"><code>{
  "success": true,
  "data": {...},
  "meta": {
    "page": 1,
    "per_page": 20,
    "total": 100,
    "total_pages": 5
  }
}</code></pre>
                        
                        <h5>Respuesta de Error</h5>
                        <pre class="bg-light p-3 rounded"><code>{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "Mensaje de error",
    "details": {...}
  }
}</code></pre>
                    </div>

                    <!-- Endpoints -->
                    <div class="mb-5">
                        <h4>Endpoints Disponibles</h4>

                        <!-- Health Check -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5><code>GET /api/v1/health</code> - Health Check</h5>
                            </div>
                            <div class="card-body">
                                <p>Verifica el estado de la API. No requiere autenticación.</p>
                                <p><strong>Ejemplo de respuesta:</strong></p>
                                <pre class="bg-light p-3 rounded"><code>{
  "success": true,
  "data": {
    "status": "ok",
    "timestamp": "2024-01-15 10:30:00",
    "version": "1.0"
  }
}</code></pre>
                            </div>
                        </div>

                        <!-- Clientes -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5>Clientes</h5>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li><code>GET /api/v1/clients</code> - Listar clientes (con filtros y paginación)</li>
                                    <li><code>GET /api/v1/clients/{id}</code> - Obtener cliente específico</li>
                                    <li><code>POST /api/v1/clients/create</code> - Crear cliente</li>
                                    <li><code>PUT /api/v1/clients/{id}/update</code> - Actualizar cliente</li>
                                    <li><code>DELETE /api/v1/clients/{id}/delete</code> - Eliminar cliente</li>
                                </ul>
                                
                                <h6>Filtros para GET /api/v1/clients:</h6>
                                <ul>
                                    <li><code>status</code> - Filtrar por estado (active, inactive)</li>
                                    <li><code>search</code> - Buscar por nombre, cédula o email</li>
                                    <li><code>page</code> - Número de página (default: 1)</li>
                                    <li><code>per_page</code> - Elementos por página (default: 20)</li>
                                </ul>
                                
                                <h6>Ejemplo de petición:</h6>
                                <pre class="bg-light p-3 rounded"><code>curl -X GET "https://app.factorentacar.com/api/v1/clients?status=active&page=1" \
  -H "X-API-Key: tu_api_key"</code></pre>
                                
                                <h6>Ejemplo de creación:</h6>
                                <pre class="bg-light p-3 rounded"><code>curl -X POST "https://app.factorentacar.com/api/v1/clients/create" \
  -H "X-API-Key: tu_api_key" \
  -H "Content-Type: application/json" \
  -d '{
    "full_name": "Juan Pérez",
    "cedula_fisica": "123456789",
    "email": "juan@example.com",
    "whatsapp": "50688888888",
    "status": "active"
  }'</code></pre>
                            </div>
                        </div>

                        <!-- Vehículos -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5>Vehículos</h5>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li><code>GET /api/v1/cars</code> - Listar vehículos</li>
                                    <li><code>GET /api/v1/cars/{id}</code> - Obtener vehículo específico</li>
                                    <li><code>GET /api/v1/cars/available</code> - Vehículos disponibles</li>
                                </ul>
                                
                                <h6>Filtros para GET /api/v1/cars:</h6>
                                <ul>
                                    <li><code>status</code> - Filtrar por estado (disponible, alquilado, mantenimiento, fuera_servicio)</li>
                                    <li><code>empresa</code> - Filtrar por empresa (Facto Rent a Car, Moviliza)</li>
                                    <li><code>search</code> - Buscar por nombre, placa o VIN</li>
                                </ul>
                                
                                <h6>Ejemplo de vehículos disponibles:</h6>
                                <pre class="bg-light p-3 rounded"><code>curl -X GET "https://app.factorentacar.com/api/v1/cars/available?fecha_inicio=2024-02-01&fecha_final=2024-02-05" \
  -H "X-API-Key: tu_api_key"</code></pre>
                            </div>
                        </div>

                        <!-- Reservas/Alquileres -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5>Reservas/Alquileres</h5>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li><code>GET /api/v1/rentals</code> - Listar reservas</li>
                                    <li><code>GET /api/v1/rentals/{id}</code> - Obtener reserva específica</li>
                                    <li><code>POST /api/v1/rentals/create</code> - Crear reserva</li>
                                    <li><code>PUT /api/v1/rentals/{id}/update</code> - Actualizar reserva</li>
                                </ul>
                                
                                <h6>Filtros para GET /api/v1/rentals:</h6>
                                <ul>
                                    <li><code>estado_pago</code> - Filtrar por estado de pago (pendiente, pagado, reservado, cancelado)</li>
                                    <li><code>start_date</code> - Fecha de inicio (formato: YYYY-MM-DD)</li>
                                    <li><code>end_date</code> - Fecha de fin (formato: YYYY-MM-DD)</li>
                                    <li><code>client_id</code> - Filtrar por ID de cliente</li>
                                    <li><code>car_id</code> - Filtrar por ID de vehículo</li>
                                </ul>
                                
                                <h6>Ejemplo de creación de reserva:</h6>
                                <pre class="bg-light p-3 rounded"><code>curl -X POST "https://app.factorentacar.com/api/v1/rentals/create" \
  -H "X-API-Key: tu_api_key" \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": 1,
    "car_id": 1,
    "fecha_inicio": "2024-02-01",
    "hora_inicio": "09:00",
    "cantidad_dias": 3,
    "precio_por_dia": 50000,
    "estado_pago": "pendiente"
  }'</code></pre>
                            </div>
                        </div>

                        <!-- Reportes -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5>Reportes</h5>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li><code>GET /api/v1/reports/sales</code> - Reporte de ventas</li>
                                    <li><code>GET /api/v1/reports/clients</code> - Reporte de clientes</li>
                                </ul>
                                
                                <h6>Parámetros para /api/v1/reports/sales:</h6>
                                <ul>
                                    <li><code>start_date</code> - Fecha de inicio (opcional)</li>
                                    <li><code>end_date</code> - Fecha de fin (opcional)</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Estadísticas -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5>Estadísticas</h5>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li><code>GET /api/v1/statistics</code> - Estadísticas generales</li>
                                    <li><code>GET /api/v1/statistics/metrics</code> - Métricas detalladas</li>
                                </ul>
                                
                                <h6>Ejemplo de respuesta de /api/v1/statistics:</h6>
                                <pre class="bg-light p-3 rounded"><code>{
  "success": true,
  "data": {
    "clients": {
      "total": 150,
      "active": 120
    },
    "cars": {
      "total": 25,
      "available": 15
    },
    "rentals": {
      "total": 500
    },
    "revenue": {
      "total": 25000000.00
    }
  }
}</code></pre>
                            </div>
                        </div>

                        <!-- Notas -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5>Notas</h5>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li><code>GET /api/v1/notes</code> - Listar notas</li>
                                    <li><code>POST /api/v1/notes/create</code> - Crear nota</li>
                                </ul>
                                
                                <h6>Filtros para GET /api/v1/notes:</h6>
                                <ul>
                                    <li><code>status</code> - Filtrar por estado (pending, processing, completed)</li>
                                    <li><code>page</code> - Número de página</li>
                                    <li><code>per_page</code> - Elementos por página</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Hacienda -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5>Consulta Hacienda</h5>
                            </div>
                            <div class="card-body">
                                <ul>
                                    <li><code>GET /api/v1/hacienda/{cedula}</code> - Consultar información tributaria</li>
                                </ul>
                                
                                <h6>Ejemplo:</h6>
                                <pre class="bg-light p-3 rounded"><code>curl -X GET "https://app.factorentacar.com/api/v1/hacienda/123456789" \
  -H "X-API-Key: tu_api_key"</code></pre>
                                
                                <h6>Ejemplo de respuesta:</h6>
                                <pre class="bg-light p-3 rounded"><code>{
  "success": true,
  "data": {
    "cedula": "123456789",
    "nombre": "JUAN PÉREZ GONZÁLEZ",
    "tipo_identificacion": "Física",
    "situacion_tributaria": "Al día",
    "regimen_tributario": "Simplificado",
    "actividad_economica": {
      "codigo": "1234",
      "descripcion": "Actividad económica"
    }
  }
}</code></pre>
                            </div>
                        </div>
                    </div>

                    <!-- Códigos de Error -->
                    <div class="mb-5">
                        <h4>Códigos de Error</h4>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Código HTTP</th>
                                    <th>Código de Error</th>
                                    <th>Descripción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>400</td>
                                    <td>VALIDATION_ERROR</td>
                                    <td>Error de validación en los datos enviados</td>
                                </tr>
                                <tr>
                                    <td>401</td>
                                    <td>UNAUTHORIZED</td>
                                    <td>API Key inválida o no proporcionada</td>
                                </tr>
                                <tr>
                                    <td>404</td>
                                    <td>NOT_FOUND</td>
                                    <td>Recurso no encontrado</td>
                                </tr>
                                <tr>
                                    <td>422</td>
                                    <td>VALIDATION_ERROR</td>
                                    <td>Error de validación en los datos enviados</td>
                                </tr>
                                <tr>
                                    <td>500</td>
                                    <td>SERVER_ERROR</td>
                                    <td>Error interno del servidor</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Ejemplos de Código -->
                    <div class="mb-5">
                        <h4>Ejemplos de Código</h4>

                        <h5>JavaScript (Fetch)</h5>
                        <pre class="bg-light p-3 rounded"><code>fetch('https://app.factorentacar.com/api/v1/clients', {
  headers: {
    'X-API-Key': 'tu_api_key_aqui'
  }
})
.then(response => response.json())
.then(data => {
  if (data.success) {
    console.log('Clientes:', data.data);
  } else {
    console.error('Error:', data.error);
  }
});</code></pre>

                        <h5>PHP (cURL)</h5>
                        <pre class="bg-light p-3 rounded"><code>$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://app.factorentacar.com/api/v1/clients');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-API-Key: tu_api_key_aqui'
]);

$response = curl_exec($ch);
$data = json_decode($response, true);
curl_close($ch);</code></pre>

                        <h5>Python (requests)</h5>
                        <pre class="bg-light p-3 rounded"><code>import requests

headers = {
    'X-API-Key': 'tu_api_key_aqui'
}

response = requests.get('https://app.factorentacar.com/api/v1/clients', headers=headers)
data = response.json()

if data['success']:
    print('Clientes:', data['data'])
else:
    print('Error:', data['error'])</code></pre>
                    </div>

                    <!-- Links -->
                    <div class="mb-3">
                        <a href="<?= Url::to(['config/index']) ?>" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Volver a Configuración
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


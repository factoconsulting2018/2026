<?php
/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'Dashboard de Estadísticas';
$this->params['breadcrumbs'][] = $this->title;

// Registrar Chart.js desde CDN
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', ['position' => $this::POS_HEAD]);
?>

<style>
.dashboard-container {
    padding: 20px;
    background: #f5f5f5;
    min-height: 100vh;
}

.dashboard-header {
    background: linear-gradient(135deg, #3fa9f5 0%, #1b305b 100%);
    color: white;
    padding: 24px;
    border-radius: 12px;
    margin-bottom: 24px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.dashboard-header h1 {
    margin: 0;
    font-size: 28px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 12px;
}

.dashboard-header .last-update {
    margin-top: 8px;
    font-size: 14px;
    opacity: 0.9;
}

/* KPI Cards */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 24px;
}

.kpi-card {
    background: white;
    padding: 24px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.kpi-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.kpi-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}

.kpi-card-title {
    font-size: 14px;
    color: #6c757d;
    font-weight: 500;
    text-transform: uppercase;
}

.kpi-card-icon {
    font-size: 32px;
    opacity: 0.8;
}

.kpi-card-value {
    font-size: 32px;
    font-weight: 700;
    color: #2c3e50;
    margin: 8px 0;
}

.kpi-card-footer {
    font-size: 12px;
    color: #6c757d;
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Charts Grid */
.charts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 24px;
    margin-bottom: 24px;
}

.chart-card {
    background: white;
    padding: 24px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.chart-card-header {
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid #e9ecef;
}

.chart-card-title {
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 8px;
}

.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
}

.chart-container.large {
    height: 400px;
}

/* Top Clients Table */
.table-card {
    background: white;
    padding: 24px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.table-card-header {
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid #e9ecef;
}

.table-card-title {
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 8px;
}

.top-clients-table {
    width: 100%;
    border-collapse: collapse;
}

.top-clients-table th {
    background: #f8f9fa;
    padding: 12px;
    text-align: left;
    font-weight: 600;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
}

.top-clients-table td {
    padding: 12px;
    border-bottom: 1px solid #e9ecef;
}

.top-clients-table tr:hover {
    background: #f8f9fa;
}

.rank-badge {
    display: inline-block;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3fa9f5, #1b305b);
    color: white;
    text-align: center;
    line-height: 28px;
    font-weight: 600;
    font-size: 12px;
}

.rank-badge.gold {
    background: linear-gradient(135deg, #ffd700, #ffed4e);
    color: #333;
}

.rank-badge.silver {
    background: linear-gradient(135deg, #c0c0c0, #e8e8e8);
    color: #333;
}

.rank-badge.bronze {
    background: linear-gradient(135deg, #cd7f32, #daa520);
    color: white;
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    flex-direction: column;
    gap: 16px;
}

.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3fa9f5;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loading-text {
    font-size: 16px;
    color: #6c757d;
    font-weight: 500;
}

.refresh-indicator {
    position: fixed;
    top: 80px;
    right: 20px;
    background: #28a745;
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 12px;
    display: none;
    align-items: center;
    gap: 8px;
    z-index: 1000;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.refresh-indicator.show {
    display: flex;
}

@media (max-width: 768px) {
    .charts-grid {
        grid-template-columns: 1fr;
    }
    
    .kpi-grid {
        grid-template-columns: 1fr;
    }
    
    .chart-container {
        height: 250px;
    }
}
</style>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>
            <span class="material-symbols-outlined">bar_chart</span>
            Dashboard de Estadísticas
        </h1>
        <div class="last-update">
            <span class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle;">schedule</span>
            <span id="last-update-time">Cargando...</span>
        </div>
    </div>

    <div class="refresh-indicator" id="refresh-indicator">
        <span class="material-symbols-outlined" style="font-size: 16px;">refresh</span>
        Actualizando datos...
    </div>

    <!-- KPI Cards -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-card-header">
                <div class="kpi-card-title">Total Alquileres</div>
                <span class="material-symbols-outlined kpi-card-icon" style="color: #3fa9f5;">receipt_long</span>
            </div>
            <div class="kpi-card-value" id="kpi-total-rentals">0</div>
            <div class="kpi-card-footer">
                <span class="material-symbols-outlined" style="font-size: 14px;">trending_up</span>
                Todos los registros
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-card-header">
                <div class="kpi-card-title">Ingresos Totales</div>
                <span class="material-symbols-outlined kpi-card-icon" style="color: #28a745;">attach_money</span>
            </div>
            <div class="kpi-card-value" id="kpi-total-revenue">₡0</div>
            <div class="kpi-card-footer">
                <span class="material-symbols-outlined" style="font-size: 14px;">trending_up</span>
                Total acumulado
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-card-header">
                <div class="kpi-card-title">Promedio por Alquiler</div>
                <span class="material-symbols-outlined kpi-card-icon" style="color: #ffc107;">calculate</span>
            </div>
            <div class="kpi-card-value" id="kpi-average-rental">₡0</div>
            <div class="kpi-card-footer">
                <span class="material-symbols-outlined" style="font-size: 14px;">trending_up</span>
                Promedio calculado
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-card-header">
                <div class="kpi-card-title">Clientes Activos</div>
                <span class="material-symbols-outlined kpi-card-icon" style="color: #17a2b8;">group</span>
            </div>
            <div class="kpi-card-value" id="kpi-active-clients">0</div>
            <div class="kpi-card-footer">
                <span class="material-symbols-outlined" style="font-size: 14px;">person</span>
                Clientes activos
            </div>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="charts-grid">
        <div class="chart-card">
            <div class="chart-card-header">
                <div class="chart-card-title">
                    <span class="material-symbols-outlined">show_chart</span>
                    Ventas Mensuales
                </div>
            </div>
            <div class="chart-container">
                <canvas id="monthlySalesChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-card-header">
                <div class="chart-card-title">
                    <span class="material-symbols-outlined">pie_chart</span>
                    Alquileres por Estado
                </div>
            </div>
            <div class="chart-container">
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-card-header">
                <div class="chart-card-title">
                    <span class="material-symbols-outlined">account_balance</span>
                    Ventas por Empresa
                </div>
            </div>
            <div class="chart-container">
                <canvas id="empresaChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Clients Table -->
    <div class="table-card">
        <div class="table-card-header">
            <div class="table-card-title">
                <span class="material-symbols-outlined">emoji_events</span>
                Top 10 Clientes
            </div>
        </div>
        <div style="overflow-x: auto;">
            <table class="top-clients-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Cliente</th>
                        <th>Total Alquileres</th>
                        <th>Ingresos Totales</th>
                        <th>Promedio por Alquiler</th>
                    </tr>
                </thead>
                <tbody id="top-clients-tbody">
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: #6c757d;">
                            Cargando datos...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Variables globales para los gráficos
let monthlySalesChart = null;
let statusChart = null;
let empresaChart = null;

// Configuración de actualización automática (cada 2 minutos)
const UPDATE_INTERVAL = 120000; // 2 minutos en milisegundos
let updateTimer = null;

// Función para formatear números como moneda
function formatCurrency(amount) {
    return '₡' + new Intl.NumberFormat('es-CR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
}

// Función para obtener datos de la API
async function fetchMetrics() {
    try {
        const response = await fetch('/api/reports/metrics');
        const result = await response.json();
        
        if (result.success) {
            return result.data;
        } else {
            throw new Error(result.error || 'Error al obtener métricas');
        }
    } catch (error) {
        console.error('Error fetching metrics:', error);
        throw error;
    }
}

async function fetchTopClients() {
    try {
        const response = await fetch('/api/reports/sales-by-client?limit=10');
        const result = await response.json();
        
        if (result.success) {
            return result.data;
        } else {
            throw new Error(result.error || 'Error al obtener top clientes');
        }
    } catch (error) {
        console.error('Error fetching top clients:', error);
        throw error;
    }
}

// Función para actualizar KPI Cards
function updateKPICards(metrics) {
    document.getElementById('kpi-total-rentals').textContent = metrics.total_rentals.toLocaleString();
    document.getElementById('kpi-total-revenue').textContent = formatCurrency(metrics.total_revenue);
    document.getElementById('kpi-average-rental').textContent = formatCurrency(metrics.average_rental_amount);
    document.getElementById('kpi-active-clients').textContent = metrics.total_active_clients.toLocaleString();
}

// Función para actualizar gráfico de ventas mensuales
function updateMonthlySalesChart(metrics) {
    const monthlyData = metrics.rentals_by_month;
    const months = Object.keys(monthlyData).sort();
    const revenues = months.map(m => monthlyData[m].revenue);
    const counts = months.map(m => monthlyData[m].count);
    
    const labels = months.map(m => {
        const [year, month] = m.split('-');
        const monthNames = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        return monthNames[parseInt(month) - 1] + ' ' + year;
    });
    
    if (monthlySalesChart) {
        monthlySalesChart.destroy();
    }
    
    const ctx = document.getElementById('monthlySalesChart').getContext('2d');
    monthlySalesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Ingresos (₡)',
                data: revenues,
                borderColor: '#3fa9f5',
                backgroundColor: 'rgba(63, 169, 245, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Cantidad de Alquileres',
                data: counts,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                fill: true,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return formatCurrency(value);
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
}

// Función para actualizar gráfico de estados
function updateStatusChart(metrics) {
    const statusData = metrics.rentals_by_status;
    const labels = Object.keys(statusData);
    const data = Object.values(statusData);
    const colors = {
        'pagado': '#28a745',
        'pendiente': '#ffc107',
        'reservado': '#17a2b8',
        'cancelado': '#dc3545'
    };
    
    const backgroundColors = labels.map(status => colors[status] || '#6c757d');
    
    if (statusChart) {
        statusChart.destroy();
    }
    
    const ctx = document.getElementById('statusChart').getContext('2d');
    statusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels.map(s => s.charAt(0).toUpperCase() + s.slice(1)),
            datasets: [{
                data: data,
                backgroundColor: backgroundColors,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
}

// Función para actualizar gráfico de empresas
function updateEmpresaChart(metrics) {
    const empresaData = metrics.rentals_by_empresa;
    const labels = Object.keys(empresaData);
    const revenues = Object.values(empresaData).map(e => e.revenue);
    const counts = Object.values(empresaData).map(e => e.count);
    
    if (empresaChart) {
        empresaChart.destroy();
    }
    
    const ctx = document.getElementById('empresaChart').getContext('2d');
    empresaChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Ingresos (₡)',
                data: revenues,
                backgroundColor: 'rgba(63, 169, 245, 0.8)',
                borderColor: '#3fa9f5',
                borderWidth: 2
            }, {
                label: 'Cantidad',
                data: counts,
                backgroundColor: 'rgba(40, 167, 69, 0.8)',
                borderColor: '#28a745',
                borderWidth: 2,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            if (context.dataset.label === 'Ingresos (₡)') {
                                return context.dataset.label + ': ' + formatCurrency(context.parsed.y);
                            }
                            return context.dataset.label + ': ' + context.parsed.y;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return formatCurrency(value);
                        }
                    }
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false,
                    }
                }
            }
        }
    });
}

// Función para actualizar tabla de top clientes
function updateTopClientsTable(clients) {
    const tbody = document.getElementById('top-clients-tbody');
    
    if (clients.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 40px; color: #6c757d;">No hay datos disponibles</td></tr>';
        return;
    }
    
    tbody.innerHTML = clients.map((client, index) => {
        const rank = index + 1;
        let rankBadgeClass = '';
        if (rank === 1) rankBadgeClass = 'gold';
        else if (rank === 2) rankBadgeClass = 'silver';
        else if (rank === 3) rankBadgeClass = 'bronze';
        
        return `
            <tr>
                <td>
                    <span class="rank-badge ${rankBadgeClass}">${rank}</span>
                </td>
                <td><strong>${client.client_name || 'Sin nombre'}</strong></td>
                <td>${client.total_rentals}</td>
                <td><strong>${formatCurrency(client.total_amount)}</strong></td>
                <td>${formatCurrency(client.average_rental)}</td>
            </tr>
        `;
    }).join('');
}

// Función para actualizar la hora de última actualización
function updateLastUpdateTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('es-CR', { 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit' 
    });
    document.getElementById('last-update-time').textContent = 'Última actualización: ' + timeString;
}

// Función principal para cargar todos los datos
async function loadDashboardData() {
    try {
        // Mostrar indicador de actualización
        const refreshIndicator = document.getElementById('refresh-indicator');
        refreshIndicator.classList.add('show');
        
        // Cargar métricas
        const metrics = await fetchMetrics();
        updateKPICards(metrics);
        updateMonthlySalesChart(metrics);
        updateStatusChart(metrics);
        updateEmpresaChart(metrics);
        
        // Cargar top clientes
        const topClients = await fetchTopClients();
        updateTopClientsTable(topClients);
        
        // Actualizar tiempo
        updateLastUpdateTime();
        
        // Ocultar indicador después de un momento
        setTimeout(() => {
            refreshIndicator.classList.remove('show');
        }, 1000);
        
    } catch (error) {
        console.error('Error loading dashboard data:', error);
        alert('Error al cargar los datos del dashboard. Por favor, recarga la página.');
    }
}

// Inicializar dashboard cuando la página carga
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
    
    // Configurar actualización automática
    updateTimer = setInterval(loadDashboardData, UPDATE_INTERVAL);
});

// Limpiar timer cuando se sale de la página
window.addEventListener('beforeunload', function() {
    if (updateTimer) {
        clearInterval(updateTimer);
    }
});
</script>


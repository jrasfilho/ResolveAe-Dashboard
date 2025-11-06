/**
 * Dashboard GLPI - JavaScript Principal
 * Gerencia atualização de dados, gráficos e rotação de slides
 */

// Configurações
const CONFIG = {
    updateInterval: 30000,      // Atualização a cada 30 segundos
    slideInterval: 15000,       // Troca de slide a cada 15 segundos
    apiEndpoint: 'api.php',      // Endpoint da API
    enableAutoSlide: true,       // Habilitar rotação automática de slides
    debugMode: false            // Modo debug (console logs)
};

// Estado global
let currentSlide = 0;
let slideTimer = null;
let updateTimer = null;
let updateCountdown = CONFIG.updateInterval / 1000;
let charts = {};
let lastData = null;

/**
 * Inicialização
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Dashboard GLPI iniciando...');
    
    initializeDateTime();
    initializeCharts();
    loadData();
    startAutoUpdate();
    startSlideRotation();
    setupEventListeners();
});

/**
 * Configurar event listeners
 */
function setupEventListeners() {
    // Clique nos indicadores de slide
    document.querySelectorAll('.indicator').forEach((indicator, index) => {
        indicator.addEventListener('click', () => {
            goToSlide(index);
            resetSlideTimer();
        });
    });
    
    // Pausar rotação quando hover
    const container = document.querySelector('.container');
    container.addEventListener('mouseenter', () => {
        if (CONFIG.debugMode) console.log('Rotação pausada');
        clearInterval(slideTimer);
    });
    
    container.addEventListener('mouseleave', () => {
        if (CONFIG.debugMode) console.log('Rotação retomada');
        resetSlideTimer();
    });
}

/**
 * Inicializar data e hora
 */
function initializeDateTime() {
    function updateDateTime() {
        const now = new Date();
        
        // Data
        const dateOptions = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        };
        document.getElementById('current-date').textContent = 
            now.toLocaleDateString('pt-BR', dateOptions);
        
        // Hora
        document.getElementById('current-time').textContent = 
            now.toLocaleTimeString('pt-BR');
    }
    
    updateDateTime();
    setInterval(updateDateTime, 1000);
}

/**
 * Carregar dados da API
 */
async function loadData() {
    try {
        if (CONFIG.debugMode) console.log('📊 Carregando dados...');
        
        const response = await fetch(CONFIG.apiEndpoint);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            lastData = data;
            updateDashboard(data);
            updateLastUpdateTime();
            showNotification('Dados atualizados com sucesso', 'success');
        } else {
            throw new Error(data.error || 'Erro ao carregar dados');
        }
        
    } catch (error) {
        console.error('❌ Erro ao carregar dados:', error);
        showNotification('Erro ao carregar dados: ' + error.message, 'error');
        
        // Tentar novamente em 10 segundos
        setTimeout(loadData, 10000);
    }
}

/**
 * Atualizar dashboard com novos dados
 */
function updateDashboard(data) {
    // Slide 1 - Overview
    updateOverviewSlide(data);
    
    // Slide 2 - Performance da Equipe
    updateTeamSlide(data);
    
    // Slide 3 - Categorias
    updateCategoriesSlide(data);

    // Slide 4 - Entidades
    updateEntitySlide(data);
    
    // Atualizar gráficos
    updateCharts(data);
    
    // Adicionar animação de atualização
    animateDataUpdate();
}

/**
 * Atualizar slide de overview
 */
function updateOverviewSlide(data) {
    const status = data.tickets_status;

    // Atualizar números principais
    updateNumber('total-abertos', status.total_criados);
    updateNumber('novos', status.novos);
    updateNumber('atribuidos', status.atribuidos);
    updateNumber('pendentes', status.pendentes);

    // Atualizar tabela de chamados abertos
    const openTicketsBody = document.getElementById('open-tickets-body');
    const openTickets = data.open_tickets_details;

    if (openTickets && openTickets.length > 0) {
        let html = '';
        openTickets.forEach(ticket => {
            html += `
                <tr>
                    <td><strong>#${ticket.id}</strong></td>
                    <td>${ticket.categoria}</td>
                    <td>${ticket.name}</td>
                    <td>${ticket.tecnico}</td>
                    <td>${ticket.data_formatada}</td>
                </tr>
            `;
        });
        openTicketsBody.innerHTML = html;
    } else {
        openTicketsBody.innerHTML = '<tr><td colspan="5" class="no-data">Nenhum chamado aberto</td></tr>';
    }

    // Atualizar lista de chamados vencidos
    const overdueList = document.getElementById('overdue-list');
    const overdue = data.overdue_tickets;

    if (overdue.total_vencidos > 0) {
        let html = `<div class="overdue-count">Total: ${overdue.total_vencidos} chamados vencidos</div>`;

        if (overdue.lista_array && overdue.lista_array.length > 0) {
            overdue.lista_array.forEach(ticket => {
                html += `<div class="overdue-item">${ticket}</div>`;
            });

            if (overdue.total_vencidos > 5) {
                html += `<div class="overdue-more">... e mais ${overdue.total_vencidos - 5} chamados</div>`;
            }
        }

        overdueList.innerHTML = html;
    } else {
        overdueList.innerHTML = '<p class="no-data">✅ Nenhum chamado vencido</p>';
    }
}

/**
 * Atualizar slide da equipe
 */
function updateTeamSlide(data) {
    // Tabela de técnicos - Ranking Mensal
    const techTable = document.getElementById('technician-table');
    const technicians = data.technician_monthly_ranking;

    if (technicians && technicians.length > 0) {
        let html = '';
        technicians.forEach((tech, index) => {
            const position = index + 1;
            const medal = position === 1 ? '🥇' : position === 2 ? '🥈' : position === 3 ? '🥉' : position;

            html += `
                <tr>
                    <td><strong>${medal}</strong></td>
                    <td><strong>${tech.tecnico || 'Não atribuído'}</strong></td>
                    <td class="highlight-green">${tech.fechados || 0}</td>
                    <td>${tech.abertos || 0}</td>
                    <td><strong>${tech.total_chamados || 0}</strong></td>
                    <td>${tech.taxa_resolucao || 0}%</td>
                </tr>
            `;
        });
        techTable.innerHTML = html;
    } else {
        techTable.innerHTML = '<tr><td colspan="6" class="loading">Nenhum dado disponível</td></tr>';
    }
    
    // Tempo de resolução
    const resolution = data.resolution_time;
    if (resolution) {
        document.getElementById('avg-resolution').textContent = 
            resolution.media_formatada || '--';
        document.getElementById('total-resolved').textContent = 
            resolution.total_resolvidos || '0';
    }
    
    // Satisfação
    const satisfaction = data.satisfaction;
    if (satisfaction) {
        document.getElementById('satisfaction-percent').textContent = 
            satisfaction.percentual ? satisfaction.percentual + '%' : '--%';
        document.getElementById('satisfaction-total').textContent = 
            satisfaction.total_avaliacoes || '0';
        
        // Atualizar estrelas
        updateStars(satisfaction.estrelas || 0);
    }
}

/**
 * Atualizar slide de categorias
 */
function updateCategoriesSlide(data) {
    // Lista de categorias
    const categoryList = document.getElementById('category-list');
    const categories = data.tickets_category;
    
    if (categories && categories.length > 0) {
        let html = '';
        categories.forEach(cat => {
            html += `
                <div class="category-item">
                    <span class="category-name">${cat.categoria}</span>
                    <span class="category-count">${cat.total}</span>
                </div>
            `;
        });
        categoryList.innerHTML = html;
    } else {
        categoryList.innerHTML = '<p class="no-data">Nenhuma categoria encontrada</p>';
    }
    
    // Comparação diária
    const daily = data.daily_comparison;
    if (daily) {
        document.getElementById('tickets-today').textContent = daily.hoje || '0';
        document.getElementById('tickets-week').textContent = daily.ultima_semana || '0';
        document.getElementById('tickets-month').textContent = daily.ultimo_mes || '0';
        
        // Tendência
        const trendElement = document.getElementById('trend-today');
        if (daily.hoje > daily.ontem) {
            trendElement.textContent = `↑ ${daily.hoje - daily.ontem} vs ontem`;
            trendElement.className = 'comparison-trend trend-up';
        } else if (daily.hoje < daily.ontem) {
            trendElement.textContent = `↓ ${daily.ontem - daily.hoje} vs ontem`;
            trendElement.className = 'comparison-trend trend-down';
        } else {
            trendElement.textContent = '= Igual a ontem';
            trendElement.className = 'comparison-trend';
        }
    }
}

/**
 * Atualizar slide de entidades
 */
function updateEntitySlide(data) {
    // Por enquanto, esta função não precisa fazer nada, pois os gráficos
    // são atualizados de forma independente na função updateCharts.
    // A existência desta função é importante para a consistência da lógica de atualização.
}

/**
 * Inicializar gráficos Chart.js
 */
function initializeCharts() {
    // Configuração padrão para todos os gráficos
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.borderColor = '#334155';
    Chart.defaults.font.family = "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif";

    // Gráfico de Categorias (Pizza)
    const categoryCtx = document.getElementById('categoryCanvas');
    if (categoryCtx) {
        charts.category = new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: [
                        '#2563eb',
                        '#3b82f6',
                        '#60a5fa',
                        '#93c5fd',
                        '#dbeafe',
                        '#1e40af',
                        '#1d4ed8',
                        '#2563eb',
                        '#3b82f6',
                        '#60a5fa'
                    ],
                    borderWidth: 2,
                    borderColor: '#1e293b'
                }]
            },
            options: {
                responsive: false,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#f1f5f9',
                        bodyColor: '#94a3b8',
                        borderColor: '#334155',
                        borderWidth: 1,
                        padding: 12
                    }
                }
            }
        });
    }

    // Gráfico de Entidades (Barras)
    const entityCtx = document.getElementById('entityCanvas');
    if (entityCtx) {
        charts.entity = new Chart(entityCtx, {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Chamados por Entidade',
                    data: [],
                    backgroundColor: '#3b82f6',
                    borderColor: '#2563eb',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: false,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    // Gráfico Mensal (Linhas)
    const monthlyCtx = document.getElementById('monthlyCanvas');
    if (monthlyCtx) {
        charts.monthly = new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Total de Chamados',
                    data: [],
                    fill: false,
                    borderColor: '#16a34a',
                    tension: 0.1
                }]
            },
            options: {
                responsive: false,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
}

/**
 * Atualizar dados dos gráficos
 */
function updateCharts(data) {
    // Atualizar gráfico de categorias
    if (charts.category && data.tickets_category) {
        charts.category.destroy();
        const categoryCtx = document.getElementById('categoryCanvas');
        if (categoryCtx) {
            charts.category = new Chart(categoryCtx, {
                            type: 'doughnut',
                            data: {
                                labels: data.tickets_category.slice(0, 8).map(c => c.categoria),
                                datasets: [{
                                    data: data.tickets_category.slice(0, 8).map(c => c.total),
                                    backgroundColor: [
                                        '#2563eb',
                                        '#3b82f6',
                                        '#60a5fa',
                                        '#93c5fd',
                                        '#dbeafe',
                                        '#1e40af',
                                        '#1d4ed8',
                                        '#2563eb'
                                    ],
                                    borderWidth: 2,
                                    borderColor: '#1e293b'
                                }]
                            },
                            options: {
                                responsive: false,
                                maintainAspectRatio: false,                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            titleColor: '#f1f5f9',
                            bodyColor: '#94a3b8',
                            borderColor: '#334155',
                            borderWidth: 1,
                            padding: 12
                        }
                    }
                }
            });
        }
    }

    // Atualizar gráfico de entidades
    if (data.tickets_by_entity) {
        if (charts.entity) {
            charts.entity.destroy();
        }
        const entityCtx = document.getElementById('entityCanvas');
        if (entityCtx) {
            charts.entity = new Chart(entityCtx, {
                type: 'bar',
                data: {
                    labels: data.tickets_by_entity.map(e => e.entidade),
                    datasets: [{
                        label: 'Chamados',
                        data: data.tickets_by_entity.map(e => e.total),
                        backgroundColor: '#3b82f6',
                        borderColor: '#2563eb',
                        borderWidth: 2,
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            titleColor: '#f1f5f9',
                            bodyColor: '#94a3b8',
                            borderColor: '#334155',
                            borderWidth: 1,
                            padding: 12,
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.x + ' chamados';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: {
                                color: '#334155'
                            },
                            ticks: {
                                color: '#94a3b8'
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#94a3b8',
                                font: {
                                    size: 12
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    // Atualizar gráfico mensal
    if (data.tickets_by_month) {
        if (charts.monthly) {
            charts.monthly.destroy();
        }
        const monthlyCtx = document.getElementById('monthlyCanvas');
        if (monthlyCtx) {
            // Formatar labels de mês
            const monthLabels = data.tickets_by_month.map(m => {
                const [year, month] = m.mes.split('-');
                const months = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
                return months[parseInt(month) - 1] + '/' + year.substring(2);
            });

            charts.monthly = new Chart(monthlyCtx, {
                type: 'line',
                data: {
                    labels: monthLabels,
                    datasets: [{
                        label: 'Chamados',
                        data: data.tickets_by_month.map(m => m.total),
                        fill: true,
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        borderColor: '#22c55e',
                        borderWidth: 3,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: '#22c55e',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            titleColor: '#f1f5f9',
                            bodyColor: '#94a3b8',
                            borderColor: '#334155',
                            borderWidth: 1,
                            padding: 12,
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + ' chamados';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                color: '#334155'
                            },
                            ticks: {
                                color: '#94a3b8',
                                maxRotation: 45,
                                minRotation: 45
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#334155'
                            },
                            ticks: {
                                color: '#94a3b8'
                            }
                        }
                    }
                }
            });
        }
    }
}

/**
 * Atualizar número com animação
 */
function updateNumber(elementId, newValue) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const currentValue = parseInt(element.textContent) || 0;
    const increment = (newValue - currentValue) / 20;
    let step = 0;
    
    const timer = setInterval(() => {
        step++;
        if (step >= 20) {
            element.textContent = newValue;
            clearInterval(timer);
        } else {
            element.textContent = Math.round(currentValue + (increment * step));
        }
    }, 30);
}

/**
 * Atualizar estrelas de satisfação
 */
function updateStars(rating) {
    const starsContainer = document.getElementById('satisfaction-stars');
    if (!starsContainer) return;
    
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 >= 0.5;
    
    let html = '';
    
    // Estrelas cheias
    for (let i = 0; i < fullStars; i++) {
        html += '<i class="fas fa-star"></i>';
    }
    
    // Meia estrela
    if (hasHalfStar && fullStars < 5) {
        html += '<i class="fas fa-star-half-alt"></i>';
        for (let i = fullStars + 1; i < 5; i++) {
            html += '<i class="far fa-star"></i>';
        }
    } else {
        // Estrelas vazias
        for (let i = fullStars; i < 5; i++) {
            html += '<i class="far fa-star"></i>';
        }
    }
    
    starsContainer.innerHTML = html;
}

/**
 * Rotação de slides
 */
function startSlideRotation() {
    if (!CONFIG.enableAutoSlide) return;
    
    slideTimer = setInterval(() => {
        nextSlide();
    }, CONFIG.slideInterval);
}

function nextSlide() {
    const slides = document.querySelectorAll('.slide');
    const totalSlides = slides.length;
    
    currentSlide = (currentSlide + 1) % totalSlides;
    goToSlide(currentSlide);
}

function goToSlide(index) {
    const slides = document.querySelectorAll('.slide');
    const indicators = document.querySelectorAll('.indicator');
    
    slides.forEach(slide => slide.classList.remove('active'));
    indicators.forEach(indicator => indicator.classList.remove('active'));
    
    slides[index].classList.add('active');
    indicators[index].classList.add('active');
    
    currentSlide = index;
}

function resetSlideTimer() {
    clearInterval(slideTimer);
    if (CONFIG.enableAutoSlide) {
        startSlideRotation();
    }
}

/**
 * Auto-atualização de dados
 */
function startAutoUpdate() {
    // Atualizar contador
    setInterval(() => {
        updateCountdown--;
        if (updateCountdown <= 0) {
            updateCountdown = CONFIG.updateInterval / 1000;
        }
        document.getElementById('next-update').textContent = updateCountdown;
    }, 1000);
    
    // Atualizar dados
    updateTimer = setInterval(() => {
        loadData();
    }, CONFIG.updateInterval);
}

/**
 * Atualizar horário da última atualização
 */
function updateLastUpdateTime() {
    const now = new Date();
    document.getElementById('last-update').textContent = now.toLocaleTimeString('pt-BR');
}

/**
 * Animação de atualização de dados
 */
function animateDataUpdate() {
    const cards = document.querySelectorAll('.stat-card');
    cards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('data-updated');
            setTimeout(() => {
                card.classList.remove('data-updated');
            }, 500);
        }, index * 100);
    });
}

/**
 * Mostrar notificação
 */
function showNotification(message, type = 'info') {
    // Remover notificação existente
    const existingAlert = document.querySelector('.alert');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    // Criar nova notificação
    const alert = document.createElement('div');
    alert.className = `alert ${type}`;
    alert.textContent = message;
    
    document.body.appendChild(alert);
    
    // Remover após 3 segundos
    setTimeout(() => {
        alert.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            alert.remove();
        }, 300);
    }, 3000);
}

/**
 * Modo debug - tecla F12
 */
document.addEventListener('keydown', (e) => {
    if (e.key === 'F12') {
        CONFIG.debugMode = !CONFIG.debugMode;
        console.log('🐛 Modo debug:', CONFIG.debugMode ? 'ATIVADO' : 'DESATIVADO');
    }
});

/**
 * Tratamento de erros globais
 */
window.addEventListener('error', (e) => {
    console.error('❌ Erro global:', e.error);
    showNotification('Erro inesperado no sistema', 'error');
});

/**
 * Verificar se a API está online ao iniciar
 */
async function checkAPIStatus() {
    try {
        const response = await fetch(CONFIG.apiEndpoint);
        return response.ok;
    } catch {
        return false;
    }
}

// Verificar status da API na inicialização
checkAPIStatus().then(isOnline => {
    if (!isOnline) {
        showNotification('API offline - tentando reconectar...', 'error');
    }
});

console.log('✅ Dashboard GLPI carregado com sucesso!');

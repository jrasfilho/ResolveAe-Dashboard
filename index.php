<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard GLPI - Suporte TI</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <img src="pics/resolveae.png" alt="Resolve Aê" style="height: 50px;">
            </div>
            <div class="header-info">
                <span id="current-date"></span>
                <span id="current-time"></span>
                <span class="update-indicator">
                    <i class="fas fa-sync-alt rotating"></i> Atualização automática
                </span>
                <button class="config-button" id="open-config" title="Configurações de Visualização">
                    <i class="fas fa-cog"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Container Principal -->
    <div class="container">
        <!-- Slide 1: Overview Geral -->
        <div class="slide active" id="slide-overview">
            <h2 class="slide-title">Visão Geral dos Chamados</h2>
            
            <!-- Cards Principais -->
            <div class="stats-grid">
                <div class="stat-card highlight">
                    <div class="stat-icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" id="total-abertos">--</div>
                        <div class="stat-label"><strong>Chamados Totais</strong></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon new">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" id="novos">--</div>
                        <div class="stat-label">Novos</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" id="pendentes">--</div>
                        <div class="stat-label">Pendentes</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon assigned">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" id="atribuidos">--</div>
                        <div class="stat-label">Atribuídos</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon closed">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" id="fechados">--</div>
                        <div class="stat-label">Chamados Fechados</div>
                    </div>
                </div>
            </div>

            <!-- Tabela de Chamados Abertos -->
            <div class="chart-container">
                <h3>Chamados em Aberto</h3>
                <div class="open-tickets-table-container">
                    <table class="open-tickets-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Categoria</th>
                                <th>Título</th>
                                <th>Técnico</th>
                                <th>Horário de Abertura</th>
                            </tr>
                        </thead>
                        <tbody id="open-tickets-body">
                            <tr>
                                <td colspan="5" class="loading">Carregando...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Lista de Chamados Vencidos -->
            <div class="overdue-section">
                <h3><i class="fas fa-exclamation-triangle"></i> Chamados Vencidos (SLA)</h3>
                <div id="overdue-list" class="overdue-list">
                    <p class="no-data">Carregando...</p>
                </div>
            </div>
        </div>

        <!-- Slide 2: Performance da Equipe -->
        <div class="slide" id="slide-team">
            <h2 class="slide-title">Performance da Equipe - Ranking Mensal</h2>

            <!-- Tabela de Técnicos - Ranking Mensal -->
            <div class="team-performance">
                <table class="performance-table">
                    
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Técnico</th>
                            <th>Fechados</th>
                            <th>Em Aberto</th>
                            <th>Total</th>
                            <th>Taxa Resolução</th>
                        </tr>
                    </thead>
                    <tbody id="technician-table">
                        <tr>
                            <td colspan="6" class="loading">Carregando dados...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Tempo Médio de Resolução -->
            <div class="resolution-stats">
                <div class="resolution-card">
                    <i class="fas fa-hourglass-half"></i>
                    <div class="resolution-content">
                        <span class="resolution-label">Tempo Médio de Resolução</span>
                        <span class="resolution-time" id="avg-resolution">--</span>
                    </div>
                </div>
                <div class="resolution-card">
                    <i class="fas fa-check-circle"></i>
                    <div class="resolution-content">
                        <span class="resolution-label">Resolvidos (30 dias)</span>
                        <span class="resolution-count" id="total-resolved">--</span>
                    </div>
                </div>
            </div>

            <!-- Satisfação dos Usuários -->
            <div class="satisfaction-section">
                <h3>Satisfação dos Usuários</h3>
                <div class="satisfaction-content">
                    <div class="satisfaction-score">
                        <div class="stars" id="satisfaction-stars">
                            <i class="far fa-star"></i>
                            <i class="far fa-star"></i>
                            <i class="far fa-star"></i>
                            <i class="far fa-star"></i>
                            <i class="far fa-star"></i>
                        </div>
                        <div class="satisfaction-text">
                            <span id="satisfaction-percent">--%</span> de satisfação
                        </div>
                        <div class="satisfaction-count">
                            <span id="satisfaction-total">--</span> avaliações nos últimos 30 dias
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabela de Resolvidos por Técnico (30 dias) -->
            <div class="team-performance">
                <h3>Resolvidos por Técnico (<span id="period-30-days">Últimos 30 Dias</span>)</h3>
                <table class="performance-table">
                    <thead>
                        <tr>
                            <th>Técnico</th>
                            <th>Chamados Resolvidos</th>
                        </tr>
                    </thead>
                    <tbody id="resolved-technician-30-days-table">
                        <tr>
                            <td colspan="2" class="loading">Carregando dados...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Tabela de Resolvidos por Técnico (Mês Anterior) -->
            <div class="team-performance">
                <h3>Resolvidos por Técnico (<span id="period-previous-month">Mês Anterior</span>)</h3>
                <table class="performance-table">
                    <thead>
                        <tr>
                            <th>Técnico</th>
                            <th>Chamados Resolvidos</th>
                        </tr>
                    </thead>
                    <tbody id="resolved-technician-previous-month-table">
                        <tr>
                            <td colspan="2" class="loading">Carregando dados...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Slide 3: Análise por Categoria -->
        <div class="slide" id="slide-categories">
            <h2 class="slide-title">Análise por Categoria</h2>

            <!-- Top Categorias -->
            <div class="categories-container">
                <div class="category-chart">
                    <canvas id="categoryCanvas"></canvas>
                </div>
                <div class="category-list" id="category-list">
                    <!-- Será preenchido via JavaScript -->
                </div>
            </div>

            <!-- Comparação Diária -->
            <div class="daily-comparison">
                <h3>Evolução de Chamados</h3>
                <div class="comparison-grid">
                    <div class="comparison-card">
                        <span class="comparison-label">Hoje</span>
                        <span class="comparison-value" id="tickets-today">--</span>
                        <span class="comparison-trend" id="trend-today"></span>
                    </div>
                    <div class="comparison-card">
                        <span class="comparison-label">Esta Semana</span>
                        <span class="comparison-value" id="tickets-week">--</span>
                    </div>
                    <div class="comparison-card">
                        <span class="comparison-label">Este Mês</span>
                        <span class="comparison-value" id="tickets-month">--</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide 4: Análise por Entidade -->
        <div class="slide" id="slide-entity">
            <h2 class="slide-title">Análise por Entidade e Evolução Mensal</h2>
            
            <div class="charts-grid">
                <div class="entity-chart-container">
                    <h3>Chamados por Entidade</h3>
                    <canvas id="entityCanvas"></canvas>
                </div>
                <div class="monthly-chart-container">
                    <h3>Evolução de Chamados (12 Meses)</h3>
                    <canvas id="monthlyCanvas"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Indicadores de Slide -->
    <div class="slide-indicators">
        <span class="indicator active" data-slide="0"></span>
        <span class="indicator" data-slide="1"></span>
        <span class="indicator" data-slide="2"></span>
        <span class="indicator" data-slide="3"></span>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <span>Última atualização: <span id="last-update">--:--:--</span></span>
            <span class="separator">•</span>
            <span>Próxima atualização em: <span id="next-update">30</span>s</span>
        </div>
    </footer>

    <!-- Modal de Configurações -->
    <div class="config-modal" id="config-modal">
        <div class="config-modal-content">
            <div class="config-modal-header">
                <h2><i class="fas fa-cog"></i> Configurações de Visualização</h2>
                <button class="config-close" id="close-config">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="config-modal-body">
                <!-- Presets -->
                <div class="config-section">
                    <h3>Presets Rápidos</h3>
                    <div class="preset-buttons">
                        <button class="preset-btn" data-preset="tv">
                            <i class="fas fa-tv"></i>
                            <span>TV/Projetor</span>
                        </button>
                        <button class="preset-btn" data-preset="desktop">
                            <i class="fas fa-desktop"></i>
                            <span>Monitor Desktop</span>
                        </button>
                        <button class="preset-btn" data-preset="laptop">
                            <i class="fas fa-laptop"></i>
                            <span>Laptop</span>
                        </button>
                    </div>
                </div>

                <!-- Tamanho de Fonte Geral -->
                <div class="config-section">
                    <h3>Tamanho de Fonte Geral</h3>
                    <div class="config-control">
                        <input type="range" id="font-scale" min="80" max="150" value="100" step="5">
                        <span class="config-value" id="font-scale-value">100%</span>
                    </div>
                </div>

                <!-- Tamanho dos Números dos Cards -->
                <div class="config-section">
                    <h3>Tamanho dos Números (Cards)</h3>
                    <div class="config-control">
                        <input type="range" id="number-scale" min="80" max="200" value="100" step="10">
                        <span class="config-value" id="number-scale-value">100%</span>
                    </div>
                </div>

                <!-- Altura dos Gráficos -->
                <div class="config-section">
                    <h3>Altura dos Gráficos</h3>
                    <div class="config-control">
                        <input type="range" id="chart-height" min="400" max="900" value="700" step="50">
                        <span class="config-value" id="chart-height-value">700px</span>
                    </div>
                </div>

                <!-- Tamanho da Tabela -->
                <div class="config-section">
                    <h3>Tamanho de Fonte das Tabelas</h3>
                    <div class="config-control">
                        <input type="range" id="table-scale" min="80" max="140" value="100" step="10">
                        <span class="config-value" id="table-scale-value">100%</span>
                    </div>
                </div>

                <!-- Espaçamento -->
                <div class="config-section">
                    <h3>Espaçamento entre Elementos</h3>
                    <div class="config-control">
                        <select id="spacing-mode">
                            <option value="compact">Compacto</option>
                            <option value="normal" selected>Normal</option>
                            <option value="spacious">Espaçoso</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="config-modal-footer">
                <button class="config-btn config-btn-secondary" id="reset-config">
                    <i class="fas fa-undo"></i> Restaurar Padrões
                </button>
                <button class="config-btn config-btn-primary" id="save-config">
                    <i class="fas fa-save"></i> Salvar Configurações
                </button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="assets/js/dashboard.js"></script>
</body>
</html>

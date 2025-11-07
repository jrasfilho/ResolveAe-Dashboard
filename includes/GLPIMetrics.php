<?php
/**
 * Classe para coletar métricas do GLPI
 */

require_once __DIR__ . '/Database.php';

class GLPIMetrics {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Obtém o total de chamados por status
     */
    public function getTicketsByStatus() {
        $sql = "
            SELECT
                COUNT(*) as total_criados,
                SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as novos,
                SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as atribuidos,
                SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) as planejados,
                SUM(CASE WHEN status = 4 THEN 1 ELSE 0 END) as pendentes,
                SUM(CASE WHEN status = 5 THEN 1 ELSE 0 END) as resolvidos,
                SUM(CASE WHEN status = 6 THEN 1 ELSE 0 END) as fechados,
                SUM(CASE WHEN status IN (1,2,3) THEN 1 ELSE 0 END) as total_abertos
            FROM glpi_tickets
            WHERE is_deleted = 0
        ";

        $result = $this->db->query($sql);
        return $result ? $result->fetch() : $this->getEmptyStats();
    }
    
    /**
     * Obtém chamados por prioridade
     */
    public function getTicketsByPriority() {
        $sql = "
            SELECT 
                priority,
                COUNT(*) as total,
                CASE 
                    WHEN priority = 1 THEN 'Muito Baixa'
                    WHEN priority = 2 THEN 'Baixa'
                    WHEN priority = 3 THEN 'Média'
                    WHEN priority = 4 THEN 'Alta'
                    WHEN priority = 5 THEN 'Muito Alta'
                    WHEN priority = 6 THEN 'Crítica'
                    ELSE 'Não definida'
                END as priority_name
            FROM glpi_tickets
            WHERE status IN (1,2,3) AND is_deleted = 0
            GROUP BY priority
            ORDER BY priority DESC
        ";
        
        $result = $this->db->query($sql);
        return $result ? $result->fetchAll() : [];
    }
    
    /**
     * Obtém chamados por categoria
     */
    public function getTicketsByCategory() {
        $sql = "
            SELECT 
                COALESCE(ic.name, 'Sem Categoria') as categoria,
                COUNT(t.id) as total
            FROM glpi_tickets t
            LEFT JOIN glpi_itilcategories ic ON t.itilcategories_id = ic.id
            WHERE t.is_deleted = 0
                AND MONTH(t.date_creation) = MONTH(CURRENT_DATE())
                AND YEAR(t.date_creation) = YEAR(CURRENT_DATE())
            GROUP BY t.itilcategories_id, ic.name
            ORDER BY total DESC
            LIMIT 10
        ";
        
        $result = $this->db->query($sql);
        return $result ? $result->fetchAll() : [];
    }

    /**
     * Obtém chamados por entidade (todos os criados)
     */
    public function getTicketsByEntity() {
        $sql = "
            SELECT
                COALESCE(e.name, 'Sem Entidade') as entidade,
                COUNT(t.id) as total
            FROM glpi_tickets t
            LEFT JOIN glpi_entities e ON t.entities_id = e.id
            WHERE t.is_deleted = 0
            GROUP BY t.entities_id, e.name
            ORDER BY total DESC
            LIMIT 10
        ";

        $result = $this->db->query($sql);
        return $result ? $result->fetchAll() : [];
    }

    /**
     * Obtém chamados por mês
     */
    public function getTicketsByMonth() {
        $sql = "
            SELECT 
                DATE_FORMAT(date_creation, '%Y-%m') as mes,
                COUNT(id) as total
            FROM glpi_tickets
            WHERE is_deleted = 0 AND date_creation >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY mes
            ORDER BY mes ASC
        ";
        
        $result = $this->db->query($sql);
        return $result ? $result->fetchAll() : [];
    }
    
    /**
     * Obtém chamados por técnico responsável
     */
    public function getTicketsByTechnician() {
        $sql = "
            SELECT 
                CONCAT(u.firstname, ' ', u.realname) as tecnico,
                COUNT(DISTINCT t.id) as total_chamados,
                SUM(CASE WHEN t.status IN (1,2,3) THEN 1 ELSE 0 END) as abertos,
                SUM(CASE WHEN t.status = 5 THEN 1 ELSE 0 END) as resolvidos
            FROM glpi_tickets t
            INNER JOIN glpi_tickets_users tu ON t.id = tu.tickets_id
            INNER JOIN glpi_users u ON tu.users_id = u.id
            WHERE tu.type = 2 -- Técnico atribuído
                AND t.is_deleted = 0
                AND t.date_creation >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY u.id, u.firstname, u.realname
            HAVING total_chamados > 0
            ORDER BY abertos DESC
            LIMIT 10
        ";
        
        $result = $this->db->query($sql);
        return $result ? $result->fetchAll() : [];
    }
    
    /**
     * Obtém tempo médio de resolução
     */
    public function getAverageResolutionTime() {
        $sql = "
            SELECT 
                AVG(TIMESTAMPDIFF(HOUR, date_creation, solvedate)) as media_horas,
                MIN(TIMESTAMPDIFF(HOUR, date_creation, solvedate)) as min_horas,
                MAX(TIMESTAMPDIFF(HOUR, date_creation, solvedate)) as max_horas,
                COUNT(*) as total_resolvidos
            FROM glpi_tickets
            WHERE status IN (5,6) 
                AND is_deleted = 0
                AND solvedate IS NOT NULL
                AND solvedate >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ";
        
        $result = $this->db->query($sql);
        $data = $result ? $result->fetch() : null;
        
        if ($data && $data['media_horas'] !== null) {
            $data['media_formatada'] = $this->formatHours($data['media_horas']);
            return $data;
        }
        
        return ['media_horas' => 0, 'media_formatada' => '0h', 'total_resolvidos' => 0];
    }
    
    /**
     * Obtém chamados criados hoje vs ontem
     */
    public function getDailyComparison() {
        $sql = "
            SELECT 
                SUM(CASE WHEN DATE(date_creation) = CURDATE() THEN 1 ELSE 0 END) as hoje,
                SUM(CASE WHEN DATE(date_creation) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) THEN 1 ELSE 0 END) as ontem,
                SUM(CASE WHEN DATE(date_creation) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as ultima_semana,
                SUM(CASE WHEN DATE(date_creation) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as ultimo_mes
            FROM glpi_tickets
            WHERE is_deleted = 0
        ";
        
        $result = $this->db->query($sql);
        return $result ? $result->fetch() : ['hoje' => 0, 'ontem' => 0, 'ultima_semana' => 0, 'ultimo_mes' => 0];
    }
    
    /**
     * Obtém chamados vencidos (SLA)
     */
    public function getOverdueTickets() {
        $sql = "
            SELECT 
                COUNT(*) as total_vencidos,
                GROUP_CONCAT(
                    CONCAT('#', id, ' - ', SUBSTRING(name, 1, 50))
                    ORDER BY date_creation ASC
                    SEPARATOR '|||'
                ) as lista_vencidos
            FROM glpi_tickets
            WHERE status IN (1,2,3)
                AND is_deleted = 0
                AND time_to_resolve IS NOT NULL
                AND time_to_resolve < NOW()
        ";
        
        $result = $this->db->query($sql);
        $data = $result ? $result->fetch() : ['total_vencidos' => 0, 'lista_vencidos' => ''];
        
        if ($data['lista_vencidos']) {
            $data['lista_array'] = array_slice(explode('|||', $data['lista_vencidos']), 0, 5);
        } else {
            $data['lista_array'] = [];
        }
        
        return $data;
    }
    
    /**
     * Obtém ranking mensal de técnicos (chamados fechados e totais)
     */
    public function getTechnicianMonthlyRanking() {
        $sql = "
            SELECT
                CONCAT(u.firstname, ' ', u.realname) as tecnico,
                COUNT(DISTINCT t.id) as total_chamados,
                SUM(CASE WHEN t.status IN (5,6) THEN 1 ELSE 0 END) as fechados,
                SUM(CASE WHEN t.status IN (1,2,3,4) THEN 1 ELSE 0 END) as abertos,
                ROUND((SUM(CASE WHEN t.status IN (5,6) THEN 1 ELSE 0 END) / COUNT(DISTINCT t.id)) * 100, 1) as taxa_resolucao
            FROM glpi_tickets t
            INNER JOIN glpi_tickets_users tu ON t.id = tu.tickets_id
            INNER JOIN glpi_users u ON tu.users_id = u.id
            WHERE tu.type = 2 -- Técnico atribuído
                AND t.is_deleted = 0
                AND MONTH(t.date_creation) = MONTH(CURRENT_DATE())
                AND YEAR(t.date_creation) = YEAR(CURRENT_DATE())
            GROUP BY u.id, u.firstname, u.realname
            HAVING total_chamados > 0
            ORDER BY fechados DESC, total_chamados DESC
            LIMIT 15
        ";

        $result = $this->db->query($sql);
        return $result ? $result->fetchAll() : [];
    }

    /**
     * Obtém lista de chamados em aberto com detalhes
     */
    public function getOpenTicketsDetails() {
        $sql = "
            SELECT
                t.id,
                t.name as name,
                t.date_creation,
                DATE_FORMAT(t.date_creation, '%d/%m/%Y %H:%i') as data_formatada,
                COALESCE(ic.name, 'Sem Categoria') as categoria,
                COALESCE(CONCAT(u.firstname, ' ', u.realname), 'Não Atribuído') as tecnico,
                CASE
                    WHEN t.status = 1 THEN 'Novo'
                    WHEN t.status = 2 THEN 'Atribuído'
                    WHEN t.status = 3 THEN 'Planejado'
                    WHEN t.status = 4 THEN 'Pendente'
                    ELSE 'Outro'
                END as status_nome
            FROM glpi_tickets t
            LEFT JOIN glpi_itilcategories ic ON t.itilcategories_id = ic.id
            LEFT JOIN glpi_tickets_users tu ON t.id = tu.tickets_id AND tu.type = 2
            LEFT JOIN glpi_users u ON tu.users_id = u.id
            WHERE t.status IN (1,2,3,4)
                AND t.is_deleted = 0
            ORDER BY t.date_creation DESC
            LIMIT 20
        ";

        $result = $this->db->query($sql);
        return $result ? $result->fetchAll() : [];
    }

    /**
     * Obtém estatísticas de satisfação
     */
    public function getSatisfactionStats() {
        $sql = "
            SELECT 
                AVG(satisfaction) as media_satisfacao,
                COUNT(*) as total_avaliacoes
            FROM glpi_ticketsatisfactions
            WHERE satisfaction IS NOT NULL
                AND date_answered >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ";
        
        $result = $this->db->query($sql);
        $data = $result ? $result->fetch() : ['media_satisfacao' => null, 'total_avaliacoes' => 0];
        
        if ($data['media_satisfacao'] !== null) {
            $data['percentual'] = round(($data['media_satisfacao'] / 5) * 100, 1);
            $data['estrelas'] = round($data['media_satisfacao'], 1);
        } else {
            $data['percentual'] = 0;
            $data['estrelas'] = 0;
        }
        
        return $data;
    }
    
    /**
     * Formata horas em formato legível
     */
    private function formatHours($hours) {
        if ($hours < 1) {
            return round($hours * 60) . 'min';
        } elseif ($hours < 24) {
            return round($hours, 1) . 'h';
        } else {
            $days = floor($hours / 24);
            $remaining_hours = round($hours % 24);
            return $days . 'd ' . $remaining_hours . 'h';
        }
    }
    
    /**
     * Retorna estatísticas vazias para fallback
     */
    private function getEmptyStats() {
        return [
            'total_criados' => 0,
            'novos' => 0,
            'atribuidos' => 0,
            'planejados' => 0,
            'pendentes' => 0,
            'resolvidos' => 0,
            'fechados' => 0,
            'total_abertos' => 0
        ];
    }
    
    /**
     * Obtém todas as métricas de uma vez
     */
    public function getAllMetrics() {
        return [
            'timestamp' => date('Y-m-d H:i:s'),
            'tickets_status' => $this->getTicketsByStatus(),
            'tickets_priority' => $this->getTicketsByPriority(),
            'tickets_category' => $this->getTicketsByCategory(),
            'tickets_by_entity' => $this->getTicketsByEntity(),
            'tickets_by_month' => $this->getTicketsByMonth(),
            'tickets_technician' => $this->getTicketsByTechnician(),
            'technician_monthly_ranking' => $this->getTechnicianMonthlyRanking(),
            'resolution_time' => $this->getAverageResolutionTime(),
            'daily_comparison' => $this->getDailyComparison(),
            'overdue_tickets' => $this->getOverdueTickets(),
            'satisfaction' => $this->getSatisfactionStats(),
            'open_tickets_details' => $this->getOpenTicketsDetails(),
            'resolved_by_technician_30_days' => $this->getResolvedByTechnicianLast30Days(),
            'resolved_by_technician_previous_month' => $this->getResolvedByTechnicianPreviousMonth(),
            'period_last_30_days' => $this->getPeriodLast30Days(),
            'period_previous_month' => $this->getPreviousMonthName()
        ];
    }

    /**
     * Obtém o período dos últimos 30 dias formatado
     */
    private function getPeriodLast30Days() {
        $hoje = new DateTime();
        $inicio = new DateTime();
        $inicio->modify('-30 days');

        return $inicio->format('d/m/Y') . ' a ' . $hoje->format('d/m/Y');
    }

    /**
     * Obtém o nome do mês anterior formatado
     */
    private function getPreviousMonthName() {
        $mesAnterior = new DateTime();
        $mesAnterior->modify('-1 month');

        $meses = [
            '01' => 'Janeiro',
            '02' => 'Fevereiro',
            '03' => 'Março',
            '04' => 'Abril',
            '05' => 'Maio',
            '06' => 'Junho',
            '07' => 'Julho',
            '08' => 'Agosto',
            '09' => 'Setembro',
            '10' => 'Outubro',
            '11' => 'Novembro',
            '12' => 'Dezembro'
        ];

        $mesNumero = $mesAnterior->format('m');
        $ano = $mesAnterior->format('Y');

        return $meses[$mesNumero] . '/' . $ano;
    }

    /**
     * Obtém o ranking de técnicos por chamados resolvidos nos últimos 30 dias
     */
    public function getResolvedByTechnicianLast30Days() {
        $sql = "
            SELECT
                CONCAT(u.firstname, ' ', u.realname) as tecnico,
                COUNT(DISTINCT t.id) as resolvidos
            FROM glpi_tickets t
            INNER JOIN glpi_tickets_users tu ON t.id = tu.tickets_id
            INNER JOIN glpi_users u ON tu.users_id = u.id
            WHERE tu.type = 2 -- Técnico atribuído
                AND t.is_deleted = 0
                AND t.status = 6 -- Fechado
                AND t.solvedate >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY u.id, u.firstname, u.realname
            HAVING resolvidos > 0
            ORDER BY resolvidos DESC
            LIMIT 15
        ";

        $result = $this->db->query($sql);
        return $result ? $result->fetchAll() : [];
    }

    /**
     * Obtém o ranking de técnicos por chamados resolvidos no mês anterior
     */
    public function getResolvedByTechnicianPreviousMonth() {
        $sql = "
            SELECT
                CONCAT(u.firstname, ' ', u.realname) as tecnico,
                COUNT(DISTINCT t.id) as resolvidos
            FROM glpi_tickets t
            INNER JOIN glpi_tickets_users tu ON t.id = tu.tickets_id
            INNER JOIN glpi_users u ON tu.users_id = u.id
            WHERE tu.type = 2 -- Técnico atribuído
                AND t.is_deleted = 0
                AND t.status = 6 -- Fechado
                AND MONTH(t.solvedate) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH)
                AND YEAR(t.solvedate) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)
            GROUP BY u.id, u.firstname, u.realname
            HAVING resolvidos > 0
            ORDER BY resolvidos DESC
            LIMIT 15
        ";

        $result = $this->db->query($sql);
        return $result ? $result->fetchAll() : [];
    }
}

# 📊 ResolveAe Dashboard GLPI - Monitoramento e Gestão Inteligente

> **Transforme a gestão de TI com visualização de dados em tempo real.**

O **ResolveAe Dashboard** é uma solução profissional e open-source para monitoramento de chamados do GLPI. Desenvolvido para exibição em TVs (NOC/Helpdesk) e uso gerencial, ele oferece uma visão clara, moderna e acionável dos indicadores de suporte.

---

## ✨ Funcionalidades Principais

### 🖥️ Monitoramento em Tempo Real
- **Atualização Automática:** Dados sempre frescos a cada 30 segundos.
- **Modo TV (Kiosk):** Rotação automática de slides configurável, ideal para painéis em departamentos de TI.
- **Design Moderno:** Interface limpa, responsiva e com **Dark Mode** nativo.

### 📈 Análise Avançada de Dados
- **Heatmap de Demanda:** Visualize os horários e dias de pico para otimizar a escala da equipe.
- **Painel Executivo:** KPIs estratégicos para gestores (SLA, Satisfação, Produtividade).
- **Metas e Objetivos:** Defina e acompanhe metas mensais para a equipe.

### 📤 Relatórios e Exportação
- **Exportação Multi-formato:** Gere relatórios em **PDF**, **Excel** e **CSV**.
- **Relatórios Executivos:** Resumos compactos prontos para reuniões de diretoria.
- **Filtros Personalizados:** Exporte dados por período e entidade.

---

## 🚀 Telas e Métricas

### 1. 📋 Visão Operacional (Slide Principal)
Foco no "agora" para a equipe de suporte:
![Visão Geral](images/01.%20Visão%20Geral%20dos%20Chamados.png)
- **Status em Tempo Real:** Chamados Novos, Em Atendimento, Pendentes.
- **Prioridade:** Gráfico de distribuição de urgência.
- **SLA:** Alertas visuais para chamados vencidos ou próximos do vencimento.

### 2. 🏆 Performance da Equipe
Gamificação e acompanhamento de produtividade:
![Performance da Equipe](images/02.%20Performance%20da%20Equipe.png)
- **Ranking de Técnicos:** Quem está resolvendo mais chamados.
- **Tempo Médio de Resolução:** Monitoramento de agilidade.
- **Satisfação do Usuário:** Índice CSAT baseado nas pesquisas do GLPI.

### 3. 📊 Análise Tática
Identifique os problemas recorrentes (ex: Impressoras, Rede, Software):
![Análise por Categoria](images/03.%20Análise%20por%20Categoria.png)
- **Top Categorias:** Gráfico de rosca interativo.
- **Tendências:** Comparativo Hoje vs. Ontem e evolução semanal.

### 4. 🏢 Análise por Entidade e Evolução
![Análise por Entidade](images/04.%20Análise%20por%20Entidade%20e%20Evolução%20Mensal.png)
- **Chamados por Entidade:** Distribuição de carga por cliente/departamento.
- **Evolução Mensal:** Histórico de volume de chamados.

### 5. 🖨️ Gestão de Insumos e Consumíveis
![Gestão de Insumos](images/05.%20Gestão%20de%20Insumos%20e%20Consumíveis.png)
- **Monitoramento de Toners:** Níveis de suprimentos de impressoras.
- **Alertas de Reposição:** Saiba quando comprar novos insumos.

### 6. 📤 Exportação e Relatórios
![Exportação](images/06.%20Exportar%20Relatórios.png)
- **Formatos:** PDF, Excel e CSV.
- **Filtros:** Selecione por data, entidade e tipo de métrica.

---

## 🛠️ Instalação Rápida

### Pré-requisitos
- PHP 7.4 ou superior
- Banco de Dados GLPI (MySQL/MariaDB)
- Servidor Web (Apache/Nginx)

### Passo a Passo

1. **Clone o repositório:**
   ```bash
   git clone https://github.com/jrasfilho/ResolveAe-Dashboard.git
   ```

2. **Configure o Banco de Dados:**
   - Copie o arquivo de exemplo:
     ```bash
     cp config/database.example.php config/database.php
     ```
   - Edite `config/database.php` com suas credenciais do GLPI (recomenda-se criar um usuário somente leitura no banco).

3. **Acesse:**
   - Abra no navegador: `http://seu-servidor/dashboard`
   - Use o script de teste para validar: `http://seu-servidor/dashboard/test-connection.php`

---

## ⚙️ Configuração

### Personalização
Edite o arquivo `assets/js/dashboard.js` para ajustar:
- Tempo de rotação dos slides.
- Intervalo de atualização dos dados.
- Cores e temas.

### Configuração de Metas
Acesse a interface administrativa (se configurada) ou edite `goals-config.php` para definir os objetivos mensais da equipe.

---

## 🤝 Contribuição

Contribuições são bem-vindas! Sinta-se à vontade para abrir **Issues** para relatar bugs ou **Pull Requests** para novas funcionalidades.

1. Faça um Fork do projeto
2. Crie sua Feature Branch (`git checkout -b feature/MinhaFeature`)
3. Commit suas mudanças (`git commit -m 'Adiciona nova feature'`)
4. Push para a Branch (`git push origin feature/MinhaFeature`)
5. Abra um Pull Request

---

## 📄 Licença

Este projeto está licenciado sob a licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.

---

<p align="center">
  <sub>Desenvolvido para impulsionar a gestão de TI.</sub>
</p>
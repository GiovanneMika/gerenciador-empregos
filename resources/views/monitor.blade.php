<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Monitor - Gerenciador de Empregos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #1a1a2e;
            color: #eee;
            min-height: 100vh;
        }

        .header {
            background: #16213e;
            padding: 20px;
            border-bottom: 2px solid #0f3460;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: #e94560;
            font-size: 1.5rem;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .status-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }

        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #4ade80;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.85rem;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #0f3460;
            color: #fff;
        }

        .btn-primary:hover {
            background: #1a4a7a;
        }

        .btn-danger {
            background: #e94560;
            color: #fff;
        }

        .btn-danger:hover {
            background: #ff6b6b;
        }

        .container {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 20px;
            padding: 20px;
            height: calc(100vh - 80px);
        }

        .panel {
            background: #16213e;
            border-radius: 10px;
            overflow: hidden;
        }

        .panel-header {
            background: #0f3460;
            padding: 15px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .panel-content {
            padding: 15px;
            overflow-y: auto;
            max-height: calc(100vh - 180px);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: #0f3460;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: #4ade80;
        }

        .stat-value.error {
            color: #e94560;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #888;
            margin-top: 5px;
        }

        /* Logs */
        .log-entry {
            background: #0f3460;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 10px;
            font-size: 0.85rem;
        }

        .log-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .log-method {
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 0.75rem;
        }

        .log-method.GET { background: #3b82f6; }
        .log-method.POST { background: #22c55e; }
        .log-method.PATCH { background: #f59e0b; }
        .log-method.DELETE { background: #ef4444; }

        .log-status {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
        }

        .log-status.success { background: #22c55e; }
        .log-status.error { background: #ef4444; }
        .log-status.redirect { background: #f59e0b; }

        .log-path {
            color: #93c5fd;
            font-family: monospace;
        }

        .log-meta {
            color: #888;
            font-size: 0.75rem;
            display: flex;
            gap: 15px;
            margin-top: 5px;
        }

        .log-body {
            margin-top: 8px;
            padding: 8px;
            background: #1a1a2e;
            border-radius: 4px;
            font-family: monospace;
            font-size: 0.75rem;
            overflow-x: auto;
        }

        .log-body pre {
            margin: 0;
            white-space: pre-wrap;
            word-break: break-all;
        }

        .log-section-title {
            color: #888;
            font-size: 0.7rem;
            margin-bottom: 4px;
        }

        /* Users */
        .user-entry {
            background: #0f3460;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 10px;
        }

        .user-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-type {
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: bold;
        }

        .user-type.User { background: #3b82f6; }
        .user-type.Company { background: #8b5cf6; }

        .user-info {
            font-size: 0.85rem;
        }

        .user-meta {
            color: #888;
            font-size: 0.75rem;
            margin-top: 5px;
        }

        /* Sidebar */
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .sidebar .panel-content {
            max-height: 300px;
        }

        /* Toggle */
        .toggle-body {
            cursor: pointer;
            color: #93c5fd;
            font-size: 0.75rem;
        }

        .toggle-body:hover {
            text-decoration: underline;
        }

        .collapsed {
            display: none;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            color: #666;
            padding: 40px;
        }

        /* Auto-refresh indicator */
        .refresh-indicator {
            font-size: 0.8rem;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🖥️ Server Monitor</h1>
        <div class="header-actions">
            <div class="status-indicator">
                <div class="status-dot"></div>
                <span>Live</span>
            </div>
            <button class="btn btn-primary" onclick="refreshData()">🔄 Atualizar</button>
            <button class="btn btn-danger" onclick="clearLogs()">🗑️ Limpar Logs</button>
        </div>
    </div>

    <div class="container">
        <div class="main-panel">
            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value" id="total-requests">0</div>
                    <div class="stat-label">Total Requisições</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="success-count">0</div>
                    <div class="stat-label">Sucesso</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value error" id="error-count">0</div>
                    <div class="stat-label">Erros</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="avg-time">0ms</div>
                    <div class="stat-label">Tempo Médio</div>
                </div>
            </div>

            <!-- Logs -->
            <div class="panel">
                <div class="panel-header">
                    <span>📋 Request Logs</span>
                    <span id="log-count" style="color: #888; font-weight: normal;">0 entradas</span>
                </div>
                <div class="panel-content" id="logs-container">
                    <div class="empty-state">Aguardando requisições...</div>
                </div>
            </div>
        </div>

        <div class="sidebar">
            <!-- Active Users -->
            <div class="panel">
                <div class="panel-header">
                    <span>👥 Usuários Ativos</span>
                    <span id="user-count" style="color: #888; font-weight: normal;">0</span>
                </div>
                <div class="panel-content" id="users-container">
                    <div class="empty-state">Nenhum usuário ativo</div>
                </div>
            </div>

            <!-- Methods breakdown -->
            <div class="panel">
                <div class="panel-header">
                    <span>📊 Por Método</span>
                </div>
                <div class="panel-content" id="methods-container">
                    <div class="empty-state">Sem dados</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let lastHash = '';
        let checkInterval;

        function formatJson(obj) {
            if (!obj || Object.keys(obj).length === 0) return null;
            return JSON.stringify(obj, null, 2);
        }

        function getStatusClass(status) {
            if (status >= 200 && status < 300) return 'success';
            if (status >= 300 && status < 400) return 'redirect';
            return 'error';
        }

        function toggleBody(id) {
            const el = document.getElementById(id);
            el.classList.toggle('collapsed');
        }

        function renderLogs(logs) {
            const container = document.getElementById('logs-container');
            document.getElementById('log-count').textContent = `${logs.length} entradas`;

            if (logs.length === 0) {
                container.innerHTML = '<div class="empty-state">Aguardando requisições...</div>';
                return;
            }

            container.innerHTML = logs.map((log, i) => {
                const reqBody = formatJson(log.request_body);
                const resBody = formatJson(log.response_body);
                const bodyId = `body-${i}`;

                return `
                    <div class="log-entry">
                        <div class="log-header">
                            <div>
                                <span class="log-method ${log.method}">${log.method}</span>
                                <span class="log-path">${log.path}</span>
                            </div>
                            <span class="log-status ${getStatusClass(log.status)}">${log.status}</span>
                        </div>
                        <div class="log-meta">
                            <span>⏱️ ${log.duration_ms}ms</span>
                            <span>👤 ${log.user_type !== 'none' ? log.user_id : 'Guest'}</span>
                            <span>🌐 ${log.ip}</span>
                            <span>🕐 ${log.timestamp}</span>
                            ${(reqBody || resBody) ? `<span class="toggle-body" onclick="toggleBody('${bodyId}')">📦 Ver dados</span>` : ''}
                        </div>
                        ${(reqBody || resBody) ? `
                            <div id="${bodyId}" class="collapsed">
                                ${reqBody ? `
                                    <div class="log-body">
                                        <div class="log-section-title">REQUEST:</div>
                                        <pre>${reqBody}</pre>
                                    </div>
                                ` : ''}
                                ${resBody ? `
                                    <div class="log-body">
                                        <div class="log-section-title">RESPONSE:</div>
                                        <pre>${resBody}</pre>
                                    </div>
                                ` : ''}
                            </div>
                        ` : ''}
                    </div>
                `;
            }).join('');
        }

        function renderUsers(users) {
            const container = document.getElementById('users-container');
            document.getElementById('user-count').textContent = users.length;

            if (users.length === 0) {
                container.innerHTML = '<div class="empty-state">Nenhum usuário ativo</div>';
                return;
            }

            container.innerHTML = users.map(user => `
                <div class="user-entry">
                    <div class="user-header">
                        <span class="user-info">${user.user_id}</span>
                        <span class="user-type ${user.user_type}">${user.user_type}</span>
                    </div>
                    <div class="user-meta">
                        <div>📍 ${user.last_action}</div>
                        <div>🌐 ${user.ip} • 🕐 ${user.last_activity}</div>
                    </div>
                </div>
            `).join('');
        }

        function renderStats(stats) {
            document.getElementById('total-requests').textContent = stats.total_requests;
            document.getElementById('success-count').textContent = stats.success_count;
            document.getElementById('error-count').textContent = stats.error_count;
            document.getElementById('avg-time').textContent = stats.avg_response_time + 'ms';

            const methodsContainer = document.getElementById('methods-container');
            const methods = stats.requests_by_method || {};

            if (Object.keys(methods).length === 0) {
                methodsContainer.innerHTML = '<div class="empty-state">Sem dados</div>';
                return;
            }

            methodsContainer.innerHTML = Object.entries(methods).map(([method, count]) => `
                <div style="display: flex; justify-content: space-between; padding: 8px; background: #0f3460; border-radius: 4px; margin-bottom: 5px;">
                    <span class="log-method ${method}">${method}</span>
                    <span>${count}</span>
                </div>
            `).join('');
        }

        async function refreshData() {
            try {
                const [logsRes, usersRes, statsRes] = await Promise.all([
                    fetch('/monitor/logs'),
                    fetch('/monitor/users'),
                    fetch('/monitor/stats')
                ]);

                const logs = await logsRes.json();
                const users = await usersRes.json();
                const stats = await statsRes.json();

                renderLogs(logs.logs || []);
                renderUsers(users.users || []);
                renderStats(stats);
            } catch (err) {
                console.error('Erro ao buscar dados:', err);
            }
        }

        async function checkForUpdates() {
            try {
                const res = await fetch('/monitor/check');
                const data = await res.json();
                
                if (data.hash !== lastHash) {
                    lastHash = data.hash;
                    refreshData();
                }
            } catch (err) {
                console.error('Erro ao verificar atualizações:', err);
            }
        }

        async function clearLogs() {
            if (!confirm('Tem certeza que deseja limpar todos os logs?')) return;

            try {
                const res = await fetch('/monitor/clear', { method: 'POST' });
                const data = await res.json();
                
                if (data.success) {
                    lastHash = ''; // Reset hash para forçar atualização
                    refreshData();
                } else {
                    alert('Erro ao limpar logs');
                }
            } catch (err) {
                console.error('Erro ao limpar logs:', err);
                alert('Erro ao limpar logs');
            }
        }

        function startAutoRefresh() {
            // Verifica mudanças a cada 2 segundos (leve - só checa tamanho do arquivo)
            checkInterval = setInterval(checkForUpdates, 2000);
        }

        // Init
        refreshData();
        startAutoRefresh();
    </script>
</body>
</html>

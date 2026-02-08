<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Manager - Content Catalogz</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        .panel {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .query-box {
            width: 100%;
            min-height: 150px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
        }
        .btn:hover {
            background: #5568d3;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .result-info {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
            border-left: 4px solid #667eea;
        }
        .error {
            background: #ffe7e7;
            border-left-color: #dc3545;
            color: #721c24;
        }
        .success {
            background: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        .quick-queries {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 10px;
            margin: 15px 0;
        }
        .quick-btn {
            background: #f8f9fa;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            text-align: left;
            font-size: 13px;
        }
        .quick-btn:hover {
            background: #e9ecef;
            border-color: #667eea;
        }
        code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 12px;
        }
        .table-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            margin: 15px 0;
        }
        .table-item {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 4px;
            border: 1px solid #ddd;
            cursor: pointer;
        }
        .table-item:hover {
            background: #e9ecef;
            border-color: #667eea;
        }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🗄️ Database Manager</h1>
            <p>Content Catalogz - MySQL Database: <strong>Content_Catalogz</strong></p>
        </div>

        <div class="panel">
            <h3>Database Tables</h3>
            <div id="tables-list" class="table-list"></div>
        </div>

        <div class="panel">
            <h3>Quick Queries</h3>
            <div class="quick-queries">
                <button class="quick-btn" onclick="runQuickQuery('SHOW TABLES')">📋 Show All Tables</button>
                <button class="quick-btn" onclick="runQuickQuery('SELECT * FROM quotes LIMIT 10')">📝 View Quotes</button>
                <button class="quick-btn" onclick="runQuickQuery('SELECT * FROM activities ORDER BY activity_date DESC LIMIT 20')">📅 Recent Activities</button>
                <button class="quick-btn" onclick="runQuickQuery('SELECT * FROM tasks ORDER BY created_at DESC LIMIT 20')">✅ Recent Tasks</button>
                <button class="quick-btn" onclick="runQuickQuery('SELECT * FROM client_notes ORDER BY created_at DESC LIMIT 20')">📝 Recent Notes</button>
                <button class="quick-btn" onclick="runQuickQuery('SELECT status, COUNT(*) as count FROM quotes GROUP BY status')">📊 Quotes by Status</button>
                <button class="quick-btn" onclick="runQuickQuery('SELECT activity_type, COUNT(*) as count FROM activities GROUP BY activity_type')">📊 Activities by Type</button>
                <button class="quick-btn" onclick="runQuickQuery('SELECT priority, status, COUNT(*) as count FROM tasks GROUP BY priority, status')">📊 Tasks by Priority</button>
            </div>
        </div>

        <div class="panel">
            <h3>SQL Query</h3>
            <textarea id="sql-query" class="query-box" placeholder="Enter your SQL query here...
Example: SELECT * FROM quotes WHERE status = 'in_progress'"></textarea>
            <button class="btn" onclick="executeQuery()">▶ Execute Query</button>
            <button class="btn btn-danger" onclick="clearQuery()">🗑️ Clear</button>
        </div>

        <div id="result-container"></div>
    </div>

    <script>
        // Load tables on page load
        window.addEventListener('DOMContentLoaded', function() {
            loadTables();
        });

        function loadTables() {
            fetch('db-manager-api.php?action=tables')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        displayTables(data.tables);
                    }
                })
                .catch(err => console.error('Error loading tables:', err));
        }

        function displayTables(tables) {
            const container = document.getElementById('tables-list');
            container.innerHTML = tables.map(table => `
                <div class="table-item" onclick="describeTable('${table}')">
                    📁 ${table}
                </div>
            `).join('');
        }

        function describeTable(tableName) {
            runQuickQuery(`DESCRIBE ${tableName}`);
        }

        function runQuickQuery(query) {
            document.getElementById('sql-query').value = query;
            executeQuery();
        }

        function clearQuery() {
            document.getElementById('sql-query').value = '';
            document.getElementById('result-container').innerHTML = '';
        }

        function executeQuery() {
            const query = document.getElementById('sql-query').value.trim();
            if (!query) {
                alert('Please enter a SQL query');
                return;
            }

            const resultContainer = document.getElementById('result-container');
            resultContainer.innerHTML = '<div class="result-info">⏳ Executing query...</div>';

            fetch('db-manager-api.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ query: query })
            })
            .then(res => res.json())
            .then(data => {
                displayResult(data);
            })
            .catch(err => {
                resultContainer.innerHTML = `<div class="result-info error">❌ Network Error: ${err.message}</div>`;
            });
        }

        function displayResult(data) {
            const container = document.getElementById('result-container');
            
            if (!data.success) {
                container.innerHTML = `
                    <div class="panel">
                        <div class="result-info error">
                            <strong>❌ Query Error:</strong><br>
                            ${data.error}
                        </div>
                    </div>
                `;
                return;
            }

            let html = '<div class="panel">';
            html += `<div class="result-info success">✅ Query executed successfully</div>`;
            
            if (data.affected_rows !== undefined) {
                html += `<div class="result-info">Affected rows: <strong>${data.affected_rows}</strong></div>`;
            }

            if (data.results && data.results.length > 0) {
                html += `<div class="result-info">Found <strong>${data.results.length}</strong> rows</div>`;
                html += '<div style="overflow-x: auto;"><table>';
                
                // Headers
                const keys = Object.keys(data.results[0]);
                html += '<thead><tr>';
                keys.forEach(key => {
                    html += `<th>${key}</th>`;
                });
                html += '</tr></thead>';
                
                // Rows
                html += '<tbody>';
                data.results.forEach(row => {
                    html += '<tr>';
                    keys.forEach(key => {
                        let value = row[key];
                        if (value === null) value = '<em>NULL</em>';
                        else if (typeof value === 'object') value = JSON.stringify(value);
                        html += `<td>${value}</td>`;
                    });
                    html += '</tr>';
                });
                html += '</tbody>';
                
                html += '</table></div>';
            } else {
                html += '<div class="result-info">No results returned</div>';
            }
            
            html += '</div>';
            container.innerHTML = html;
        }
    </script>
</body>
</html>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/db.php';
include 'config/auth.php';

requireLogin();
$user = getCurrentUser();

// Get all pages
$pages_sql = "SELECT * FROM pages ORDER BY updated_at DESC";
$pages_result = $conn->query($pages_sql);
$pages = [];
if ($pages_result) {
    while ($row = $pages_result->fetch_assoc()) {
        $pages[] = $row;
    }
}

// Get invoice count
$invoices_sql = "SELECT COUNT(*) as count FROM invoices";
$invoices_result = $conn->query($invoices_sql);
$invoice_count = 0;
if ($invoices_result) {
    $invoice_count = $invoices_result->fetch_assoc()['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Content Catalogz</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }

        .navbar {
            background: linear-gradient(135deg, #db1c56 0%, #a01440 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-left {
            flex: 1;
        }
        
        .navbar-center {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .navbar-center h2 {
            font-size: 20px;
            margin: 0;
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        
        .navbar-right {
            flex: 1;
            display: flex;
            justify-content: flex-end;
        }

        .navbar h1 {
            font-size: 24px;
            margin: 0;
        }
        
        .navbar h1 img {
            height: 59px;
            width: 250px;
            display: block;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid white;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .container {
            display: flex;
            min-height: calc(100vh - 60px);
        }

        .sidebar {
            width: 250px;
            background: white;
            padding: 20px 0;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
        }

        .sidebar a {
            display: block;
            padding: 15px 30px;
            color: #333;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #f0f0f0;
            border-left-color: #667eea;
            color: #667eea;
        }

        .sidebar .menu-parent {
            cursor: pointer;
            position: relative;
            user-select: none;
        }

        .sidebar .menu-parent::after {
            content: '▼';
            position: absolute;
            right: 15px;
            font-size: 10px;
            transition: transform 0.3s ease;
        }

        .sidebar .menu-parent.open::after {
            transform: rotate(180deg);
        }

        .sidebar .submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            background: rgba(0, 0, 0, 0.02);
        }

        .sidebar .submenu.open {
            max-height: 300px;
        }

        .sidebar .submenu a {
            padding: 12px 20px 12px 45px;
            font-size: 14px;
            border-left-width: 3px;
        }

        .main-content {
            flex: 1;
            padding: 30px;
        }

        .content-section {
            display: none;
        }

        .content-section.active {
            display: block;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-header h2 {
            color: #333;
            margin-bottom: 10px;
            font-size: 20px;
            font-weight: 600;
        }

        /* Dashboard greeting should be more prominent than the page title */
        #dashboardGreeting {
            font-size: 30px;
            font-weight: 400; /* not bold */
            color: #222;
        }
        #dashboardGreeting .role-badge { font-size: 13px; padding: 4px 10px; }

        .btn-group {
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin-right: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
            margin: 0 3px;
        }

        .table-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f9f9f9;
            font-weight: 600;
            color: #333;
        }

        tr:hover {
            background: #f9f9f9;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-published {
            background: #d4edda;
            color: #155724;
        }

        .status-draft {
            background: #fff3cd;
            color: #856404;
        }

        .status-archived {
            background: #e2e3e5;
            color: #383d41;
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            justify-content: center;
            align-items: center;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            transform-origin: center top;
            will-change: transform, opacity;
        }

        /* subtle entrance for modals so newly-opened dialog feels forefront */
        .modal.show .modal-content {
            animation: modalIn 160ms cubic-bezier(.2,.8,.2,1);
        }
        @keyframes modalIn {
            from { transform: translateY(-8px) scale(.99); opacity: 0; }
            to   { transform: none; opacity: 1; }
        }

        .modal-header {
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }

        .modal-header h3 {
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
            font-size: 14px;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 200px;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .close-btn {
            float: right;
            font-size: 28px;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
            background: none;
            border: none;
        }

        .close-btn:hover {
            color: #000;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #777;
        }

        .empty-state h3 {
            margin-bottom: 10px;
            color: #333;
        }
        
        /* CRM Tabs */
        .crm-tabs {
            display: flex;
            gap: 5px;
            border-bottom: 2px solid #ddd;
            margin-bottom: 20px;
        }
        
        .crm-tab {
            background: transparent;
            border: none;
            padding: 12px 20px;
            cursor: pointer;
            font-size: 14px;
            color: #666;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
            position: relative;
            top: 2px;
        }
        
        .crm-tab:hover {
            color: #667eea;
            background: rgba(102, 126, 234, 0.05);
        }
        
        .crm-tab.active {
            color: #667eea;
            border-bottom-color: #667eea;
            font-weight: 600;
        }
        
        .client-tab-content {
            display: none;
        }
        
        .client-tab-content.active {
            display: block;
        }
        
        /* Activity Timeline */
        .activity-item {
            background: white;
            border-left: 3px solid #667eea;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .activity-item.type-call {
            border-left-color: #28a745;
        }
        
        .activity-item.type-email {
            border-left-color: #17a2b8;
        }
        
        .activity-item.type-meeting {
            border-left-color: #ffc107;
        }
        
        .activity-item.type-quote_sent,
        .activity-item.type-invoice_sent,
        .activity-item.type-email_sent {
            border-left-color: #007bff;
        }
        
        .activity-item.type-payment_received {
            border-left-color: #28a745;
        }
        
        .activity-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .activity-type {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .activity-type.type-call {
            background: #d4edda;
            color: #155724;
        }
        
        .activity-type.type-email {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .activity-type.type-meeting {
            background: #fff3cd;
            color: #856404;
        }
        
        .activity-type.type-note {
            background: #e7e7e7;
            color: #333;
        }
        
        .activity-type.type-quote_sent,
        .activity-type.type-invoice_sent {
            background: #cfe2ff;
            color: #084298;
        }
        
        .activity-type.type-payment_received {
            background: #d4edda;
            color: #155724;
        }
        
        .activity-type.type-task {
            background: #fff3cd;
            color: #856404;
        }
        
        .activity-type.type-other {
            background: #f8d7da;
            color: #721c24;
        }
        
        .activity-subject {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .activity-description {
            color: #666;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .activity-meta {
            display: flex;
            gap: 15px;
            font-size: 12px;
            color: #999;
        }
        
        .activity-delete {
            color: #dc3545;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
        }
        
        .activity-delete:hover {
            text-decoration: underline;
        }
        
        /* Notes */
        .note-item {
            background: #fffef0;
            border: 1px solid #f0e68c;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 4px;
            position: relative;
        }
        
        .note-item.important {
            background: #fff5f5;
            border-color: #ff6b6b;
        }
        
        .note-important-badge {
            position: absolute;
            top: -8px;
            right: 15px;
            background: #ff6b6b;
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .note-text {
            color: #333;
            margin-bottom: 10px;
            white-space: pre-wrap;
        }
        
        .note-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: #999;
        }
        
        .note-delete {
            color: #dc3545;
            cursor: pointer;
            text-decoration: none;
        }
        
        .note-delete:hover {
            text-decoration: underline;
        }
        
        /* Client Tasks */
        .client-task-item {
            background: white;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .client-task-item.completed {
            opacity: 0.6;
            background: #f8f9fa;
        }
        
        .client-task-left {
            flex: 1;
        }
        
        .client-task-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .client-task-title.completed {
            text-decoration: line-through;
        }
        
        .client-task-meta {
            display: flex;
            gap: 10px;
            font-size: 12px;
            color: #999;
        }
        
        .client-task-actions {
            display: flex;
            gap: 10px;
        }
        
        /* Main Tasks Section */
        .task-item {
            background: white;
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
        }
        
        .task-item:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .task-item.completed {
            opacity: 0.6;
            background: #f8f9fa;
        }
        
        .task-left {
            flex: 1;
        }
        
        .task-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .task-title.completed {
            text-decoration: line-through;
            color: #999;
        }
        
        .task-description {
            color: #666;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .task-meta {
            display: flex;
            gap: 15px;
            font-size: 13px;
            color: #999;
        }
        
        .task-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    
    <div class="navbar">
        <div class="navbar-left">
            <h1><img src="../assets/images/LogoWhiteSmall.png" alt="Content Catalogz"></h1>
        </div>
        <div class="navbar-center">
            <h2>Administration Panel</h2>
        </div>
        <div class="navbar-right">
            <div class="user-info">
                <form method="POST" action="api/logout.php" style="margin: 0;">
                    <button type="submit" class="logout-btn">Logout</button>
                </form>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="sidebar">
            <a href="#" onclick="showSection('dashboard'); return false;" id="nav-dashboard" class="active">📋 Dashboard</a>
            
            <a href="#" onclick="showSection('clients'); return false;" id="nav-clients">📝 Quote Requests</a>
            
            <a href="#" class="menu-parent" onclick="toggleSubmenu(event, 'clients-submenu'); return false;">👥 Clients</a>
            <div class="submenu" id="clients-submenu">
                <a href="#" onclick="showSection('existing-clients'); return false;" id="nav-existing-clients">👤 Existing Clients</a>
                <a href="#" onclick="openAddClientModal(); return false;" id="nav-add-client">➕ Add New Client</a>
            </div>
            
            <a href="#" class="menu-parent" onclick="toggleSubmenu(event, 'email-submenu'); return false;">📧 Email</a>
            <div class="submenu" id="email-submenu">
                <a href="#" onclick="showSection('email-inbox'); return false;" id="nav-email-inbox">📥 Inbox</a>
                <a href="#" onclick="showSection('email-draft'); return false;" id="nav-email-draft">📝 Drafts</a>
                <a href="#" onclick="showSection('email-sent'); return false;" id="nav-email-sent">📤 Sent</a>
                <a href="#" onclick="showSection('email-trash'); return false;" id="nav-email-trash">🗑️ Trash</a>
                <a href="#" onclick="showSection('email-settings'); return false;" id="nav-email-settings">⚙️ Settings</a>
            </div>
            
            <a href="#" onclick="showSection('tasks'); return false;" id="nav-tasks">✅ Tasks & To-Do</a>
            <a href="#" onclick="showSection('invoices'); return false;" id="nav-invoices">📄 Invoices</a>
            
            <a href="#" class="menu-parent" onclick="toggleSubmenu(event, 'pages-submenu'); return false;">🌐 Website Pages</a>
            <div class="submenu" id="pages-submenu">
                <a href="#" onclick="showSection('html-files'); return false;" id="nav-html-files">📝 Edit Pages</a>
                <a href="#" onclick="openNewPageModal(); return false;" id="nav-new-page">➕ Create New Page</a>
            </div>
            
            <a href="#" class="menu-parent" onclick="toggleSubmenu(event, 'users-submenu'); return false;">👤 Users</a>
            <div class="submenu" id="users-submenu">
                <a href="#" onclick="showSection('users-list'); return false;" id="nav-users-list">📋 View All Users</a>
                <a href="#" onclick="openCreateUserModal(); return false;" id="nav-create-user">➕ Create User</a>
            </div>
            
            <a href="#" class="menu-parent" onclick="toggleSubmenu(event, 'settings-submenu'); return false;">⚙️ Settings</a>
            <div class="submenu" id="settings-submenu">
                <a href="export.php" id="nav-export">📦 Export Website</a>
                <a href="#" onclick="openMenuCustomizationModal(); return false;" id="nav-customize-menu">⚙️ Customize Menu</a>
            </div>

            <a href="/" target="_blank" id="nav-view-site">🌐 View Site</a>
            <a href="api/logout.php" id="nav-logout">🚪 Logout</a>
        </div>

        <div class="main-content">
            <!-- Dashboard Section -->
            <div id="section-dashboard" class="content-section active">
                <!-- Dashboard greeting (time-aware) -->
                <div id="dashboardGreeting" style="margin-bottom: 8px;">
                    <?php echo !empty($user['first_name']) ? escapeHtml($user['first_name']) : escapeHtml($user['username']); ?>
                    <span class="role-badge" style="background: <?php echo (!empty($user['role']) && $user['role'] === 'superadmin') ? '#dc3545' : '#007bff'; ?>; color: white; padding: 3px 8px; border-radius: 999px; font-size: 12px; margin-left: 8px; vertical-align: middle;">
                        <?php echo (!empty($user['role']) && $user['role'] === 'superadmin') ? 'Super Admin' : 'Admin'; ?>
                    </span>
                </div>

                <div class="page-header">
                    <h2>Dashboard</h2>
                    <p>Overview of your business at a glance</p>
                </div>

                <!-- Email Stats -->
                <h3 style="color: #333; margin-bottom: 15px;">📧 Email</h3>
                <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px;">
                    <div class="stat-card" onclick="showSection('email-inbox')" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 5px rgba(0,0,0,0.05)';">
                        <h4 style="color: #007bff; font-size: 14px; margin-bottom: 5px;">Unread Emails</h4>
                        <p class="stat-number" id="dash-emails-unread" style="font-size: 28px; font-weight: bold; color: #007bff;">0</p>
                    </div>
                    <div class="stat-card" onclick="showSection('email-inbox')" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 5px rgba(0,0,0,0.05)';">
                        <h4 style="color: #28a745; font-size: 14px; margin-bottom: 5px;">Total Emails</h4>
                        <p class="stat-number" id="dash-emails-total" style="font-size: 28px; font-weight: bold; color: #28a745;">0</p>
                    </div>
                    <div class="stat-card" onclick="showSection('email-draft')" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 5px rgba(0,0,0,0.05)';">
                        <h4 style="color: #ffc107; font-size: 14px; margin-bottom: 5px;">Drafts</h4>
                        <p class="stat-number" id="dash-emails-drafts" style="font-size: 28px; font-weight: bold; color: #ffc107;">0</p>
                    </div>
                </div>

                <!-- Client & Quotes Stats -->
                <h3 style="color: #333; margin-bottom: 15px;">📊 Clients & Quotes</h3>
                <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px;">
                    <div class="stat-card" onclick="showSection('clients')" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 5px rgba(0,0,0,0.05)';">
                        <h4 style="color: #28a745; font-size: 14px; margin-bottom: 5px;">Total Quotes</h4>
                        <p class="stat-number" id="quotes-count" style="font-size: 28px; font-weight: bold; color: #28a745;">0</p>
                    </div>
                    <div class="stat-card" onclick="showSection('clients')" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 5px rgba(0,0,0,0.05)';">
                        <h4 style="color: #007bff; font-size: 14px; margin-bottom: 5px;">New Quotes</h4>
                        <p class="stat-number" id="dash-quotes-new" style="font-size: 28px; font-weight: bold; color: #007bff;">0</p>
                    </div>
                    <div class="stat-card" onclick="showSection('clients')" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 5px rgba(0,0,0,0.05)';">
                        <h4 style="color: #17a2b8; font-size: 14px; margin-bottom: 5px;">In Progress</h4>
                        <p class="stat-number" id="dash-quotes-progress" style="font-size: 28px; font-weight: bold; color: #17a2b8;">0</p>
                    </div>
                </div>

                <!-- Tasks Stats -->
                <h3 style="color: #333; margin-bottom: 15px;">✅ Tasks & To-Do</h3>
                <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px;">
                    <div class="stat-card" onclick="showSection('tasks')" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 5px rgba(0,0,0,0.05)';">
                        <h4 style="color: #ffc107; font-size: 14px; margin-bottom: 5px;">Pending Tasks</h4>
                        <p class="stat-number" id="dash-tasks-pending" style="font-size: 28px; font-weight: bold; color: #ffc107;">0</p>
                    </div>
                    <div class="stat-card" onclick="showSection('tasks')" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 5px rgba(0,0,0,0.05)';">
                        <h4 style="color: #dc3545; font-size: 14px; margin-bottom: 5px;">Overdue Tasks</h4>
                        <p class="stat-number" id="dash-tasks-overdue" style="font-size: 28px; font-weight: bold; color: #dc3545;">0</p>
                    </div>
                    <div class="stat-card" onclick="showSection('tasks')" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 5px rgba(0,0,0,0.05)';">
                        <h4 style="color: #ff69b4; font-size: 14px; margin-bottom: 5px;">Urgent Tasks</h4>
                        <p class="stat-number" id="dash-tasks-urgent" style="font-size: 28px; font-weight: bold; color: #ff69b4;">0</p>
                    </div>
                </div>

                <!-- Invoice Stats -->
                <h3 style="color: #333; margin-bottom: 15px;">📄 Invoices</h3>
                <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px;">
                    <div class="stat-card" onclick="showFilteredInvoices('outstanding')" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 5px rgba(0,0,0,0.05)';">
                        <h4 style="color: #ffc107; font-size: 14px; margin-bottom: 5px;">Outstanding</h4>
                        <p class="stat-number" id="dash-invoices-outstanding" style="font-size: 28px; font-weight: bold; color: #ffc107;">0</p>
                        <small id="dash-invoices-outstanding-amount" style="color: #666;">£0.00</small>
                    </div>
                    <div class="stat-card" onclick="showFilteredInvoices('overdue')" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 5px rgba(0,0,0,0.05)';">
                        <h4 style="color: #dc3545; font-size: 14px; margin-bottom: 5px;">Overdue</h4>
                        <p class="stat-number" id="dash-invoices-overdue" style="font-size: 28px; font-weight: bold; color: #dc3545;">0</p>
                        <small id="dash-invoices-overdue-amount" style="color: #666;">£0.00</small>
                    </div>
                    <div class="stat-card" onclick="showSection('invoices')" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 5px rgba(0,0,0,0.05)';">
                        <h4 style="color: #17a2b8; font-size: 14px; margin-bottom: 5px;">Total Invoiced</h4>
                        <p class="stat-number" id="dash-invoices-total" style="font-size: 28px; font-weight: bold; color: #17a2b8;">£0.00</p>
                        <small style="color: #666;">All time</small>
                    </div>
                    <div class="stat-card" onclick="showSection('invoices')" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 5px rgba(0,0,0,0.05)';">
                        <h4 style="color: #28a745; font-size: 14px; margin-bottom: 5px;">Collected</h4>
                        <p class="stat-number" id="dash-invoices-collected" style="font-size: 28px; font-weight: bold; color: #28a745;">£0.00</p>
                        <small style="color: #666;">All time</small>
                    </div>
                </div>

                <!-- CRM Charts -->
                <h3 style="color: #333; margin-bottom: 15px; margin-top: 40px;">📊 CRM Analytics</h3>
                <div class="charts-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 10px; margin-bottom: 30px;">
                    <div class="chart-card" style="background: white; padding: 10px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                        <h4 style="color: #333; font-size: 14px; margin-bottom: 8px;">Quote Status Breakdown</h4>
                        <canvas id="statusChart" width="200" height="120"></canvas>
                    </div>
                    <div class="chart-card" style="background: white; padding: 10px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                        <h4 style="color: #333; font-size: 14px; margin-bottom: 8px;">Lead Sources</h4>
                        <canvas id="leadSourceChart" width="200" height="120"></canvas>
                    </div>
                </div>
                <div class="charts-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 10px; margin-bottom: 30px;">
                    <div class="chart-card" style="background: white; padding: 10px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                        <h4 style="color: #333; font-size: 14px; margin-bottom: 8px;">Monthly Revenue Trends</h4>
                        <canvas id="revenueChart" width="200" height="120"></canvas>
                    </div>
                    <div class="chart-card" style="background: white; padding: 10px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                        <h4 style="color: #333; font-size: 14px; margin-bottom: 8px;">Task Priority Distribution</h4>
                        <canvas id="taskPriorityChart" width="200" height="120"></canvas>
                    </div>
                </div>
            </div>

            <!-- Clients Section -->
            <div id="section-clients" class="content-section" style="display: none;">
                <div class="page-header">
                    <h2>Client Quote Requests</h2>
                    <p>Manage client inquiries and quote requests</p>
                </div>

                <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 30px;">
                    <div class="stat-card" onclick="filterQuotesByStatus('all')" style="background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 5px rgba(0,0,0,0.05)';">
                        <h4 style="color: #666; font-size: 14px; margin-bottom: 5px;">Total</h4>
                        <p id="stat-total" style="font-size: 24px; font-weight: bold; color: #333;">0</p>
                    </div>
                    <div class="stat-card" onclick="filterQuotesByStatus('new')" style="background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 5px rgba(0,0,0,0.05)';">
                        <h4 style="color: #007bff; font-size: 14px; margin-bottom: 5px;">New</h4>
                        <p id="stat-new" style="font-size: 24px; font-weight: bold; color: #007bff;">0</p>
                    </div>
                    <div class="stat-card" onclick="filterQuotesByStatus('contacted')" style="background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 5px rgba(0,0,0,0.05)';">
                        <h4 style="color: #ffc107; font-size: 14px; margin-bottom: 5px;">Contacted</h4>
                        <p id="stat-contacted" style="font-size: 24px; font-weight: bold; color: #ffc107;">0</p>
                    </div>
                    <div class="stat-card" onclick="filterQuotesByStatus('in_progress')" style="background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 5px rgba(0,0,0,0.05)';">
                        <h4 style="color: #17a2b8; font-size: 14px; margin-bottom: 5px;">In Progress</h4>
                        <p id="stat-inprogress" style="font-size: 24px; font-weight: bold; color: #17a2b8;">0</p>
                    </div>
                    <div class="stat-card" onclick="filterQuotesByStatus('completed')" style="background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 5px rgba(0,0,0,0.05)';">
                        <h4 style="color: #28a745; font-size: 14px; margin-bottom: 5px;">Completed</h4>
                        <p id="stat-completed" style="font-size: 24px; font-weight: bold; color: #28a745;">0</p>
                    </div>
                    <div class="stat-card" onclick="filterQuotesByStatus('declined')" style="background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 5px rgba(0,0,0,0.05)';">
                        <h4 style="color: #dc3545; font-size: 14px; margin-bottom: 5px;">Declined</h4>
                        <p id="stat-declined" style="font-size: 24px; font-weight: bold; color: #dc3545;">0</p>
                    </div>
                </div>

                <div style="margin-bottom: 20px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                    <div>
                        <label for="statusFilter" style="margin-right: 8px; font-weight: 500;">Filter by Status:</label>
                        <select id="statusFilter" onchange="loadQuotes()" style="padding: 8px 12px; border-radius: 4px; border: 1px solid #ddd;">
                            <option value="all">All Quotes</option>
                            <option value="new" selected>New</option>
                            <option value="contacted">Contacted</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="declined">Declined</option>
                        </select>
                    </div>
                    <div>
                        <input type="text" id="searchQuotes" placeholder="Search by name, email, company..." style="padding: 8px 12px; border-radius: 4px; border: 1px solid #ddd; width: 300px;" onkeyup="loadQuotes()">
                    </div>
                    <button class="btn btn-secondary" onclick="loadQuotes()">Refresh</button>
                </div>

                <div id="quotes-list"></div>
            </div>

            <!-- Existing Clients Section -->
            <div id="section-existing-clients" class="content-section" style="display: none;">
                <div class="page-header">
                    <h2>Existing Clients</h2>
                    <p>Manage your active client relationships and ongoing projects</p>
                </div>

                <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px;">
                    <div class="stat-card" style="background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                        <h4 style="color: #28a745; font-size: 14px; margin-bottom: 5px;">Active Clients</h4>
                        <p id="active-clients-count" style="font-size: 24px; font-weight: bold; color: #28a745;">0</p>
                    </div>
                    <div class="stat-card" style="background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                        <h4 style="color: #17a2b8; font-size: 14px; margin-bottom: 5px;">Completed Projects</h4>
                        <p id="total-projects-count" style="font-size: 24px; font-weight: bold; color: #17a2b8;">0</p>
                    </div>
                </div>

                <div style="margin-bottom: 20px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                    <input type="text" id="searchClients" placeholder="Search by name, email, company..." style="padding: 8px 12px; border-radius: 4px; border: 1px solid #ddd; width: 300px;" onkeyup="loadExistingClients()">
                    <button class="btn btn-secondary" onclick="loadExistingClients()">Refresh</button>
                    <button class="btn btn-primary" onclick="openAddClientModal()">+ Add New Client</button>
                </div>

                <div id="existing-clients-list">
                    <div class="empty-state">
                        <h3>No Active Clients Yet</h3>
                        <p>Clients with active quotes will appear here automatically.</p>
                    </div>
                </div>
            </div>

            <!-- HTML Files Section -->
            <div id="section-html-files" class="content-section" style="display: none;">
                <div class="page-header">
                    <h2>Edit HTML Pages</h2>
                    <p>Edit your website's HTML files directly</p>
                </div>

                <div class="btn-group" style="margin-bottom: 20px;">
                    <button class="btn btn-primary" onclick="openNewPageModal()">➕ Create New Page</button>
                </div>

                <div id="html-files-list"></div>
            </div>

            <!-- Invoices Section -->
            <div id="section-invoices" class="content-section" style="display: none;">
                <div class="page-header">
                    <h2>Invoice Search</h2>
                    <p>Search and view all generated invoices</p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
                    <div class="stat-card" onclick="showFilteredInvoices('outstanding')" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 5px rgba(0,0,0,0.05)';">
                        <h4 style="color: #ffc107; font-size: 14px; margin-bottom: 5px;">Outstanding Invoices</h4>
                        <p id="stat-invoices-outstanding-count" style="font-size: 24px; font-weight: bold; color: #ffc107;">0</p>
                        <p id="stat-invoices-outstanding-amount" style="font-size: 14px; color: #666; margin-top: 5px;">£0.00</p>
                    </div>
                    <div class="stat-card" onclick="showFilteredInvoices('overdue')" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 5px rgba(0,0,0,0.05)';">
                        <h4 style="color: #dc3545; font-size: 14px; margin-bottom: 5px;">Overdue Invoices</h4>
                        <p id="stat-invoices-overdue-count" style="font-size: 24px; font-weight: bold; color: #dc3545;">0</p>
                        <p id="stat-invoices-overdue-amount" style="font-size: 14px; color: #666; margin-top: 5px;">£0.00</p>
                    </div>
                    <div class="stat-card" onclick="showFilteredInvoices('all')" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 5px rgba(0,0,0,0.05)';">
                        <h4 style="color: #17a2b8; font-size: 14px; margin-bottom: 5px;">Total Invoiced</h4>
                        <p id="stat-invoices-total" style="font-size: 24px; font-weight: bold; color: #17a2b8;">£0.00</p>
                        <p style="font-size: 14px; color: #666; margin-top: 5px;">All time</p>
                    </div>
                    <div class="stat-card" onclick="showFilteredInvoices('paid')" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 2px 5px rgba(0,0,0,0.05)';">
                        <h4 style="color: #28a745; font-size: 14px; margin-bottom: 5px;">Total Collected</h4>
                        <p id="stat-invoices-collected" style="font-size: 24px; font-weight: bold; color: #28a745;">£0.00</p>
                        <p style="font-size: 14px; color: #666; margin-top: 5px;">All time</p>
                    </div>
                </div>

                <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 20px;">
                    <h3 style="margin-bottom: 15px; color: #333;">Search Invoices</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 15px; align-items: end;">
                        <div class="form-group" style="margin: 0;">
                            <label for="invoiceSearch">Invoice Number or Client Name</label>
                            <input type="text" id="invoiceSearch" class="form-control" placeholder="INV-1234567890 or client name...">
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label for="invoiceDateSearch">Invoice Date</label>
                            <input type="date" id="invoiceDateSearch" class="form-control">
                        </div>
                        <button class="btn btn-primary" onclick="searchInvoices()" style="height: 38px;">🔍 Search</button>
                    </div>
                    <button class="btn btn-secondary" onclick="clearInvoiceSearch()" style="margin-top: 10px;">Clear Search</button>
                </div>

                <div id="invoices-results">
                    <div class="empty-state">
                        <h3>Search for Invoices</h3>
                        <p>Use the search form above to find invoices by number or date.</p>
                    </div>
                </div>
            </div>

            <!-- Tasks Section -->
            <div id="section-tasks" class="content-section" style="display: none;">
                <div class="page-header">
                    <h2>Tasks & To-Do List</h2>
                    <p>Manage tasks, follow-ups, and reminders</p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px;">
                    <div class="stat-card" onclick="filterTasks('pending')" style="background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); cursor: pointer;">
                        <h4 style="color: #ffc107; font-size: 14px; margin-bottom: 5px;">Pending</h4>
                        <p id="stat-tasks-pending" style="font-size: 24px; font-weight: bold; color: #ffc107;">0</p>
                    </div>
                    <div class="stat-card" onclick="showOverdueTasks()" style="background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); cursor: pointer;">
                        <h4 style="color: #dc3545; font-size: 14px; margin-bottom: 5px;">Overdue</h4>
                        <p id="stat-tasks-overdue" style="font-size: 24px; font-weight: bold; color: #dc3545;">0</p>
                    </div>
                    <div class="stat-card" onclick="showUrgentTasks()" style="background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); cursor: pointer;">
                        <h4 style="color: #ff69b4; font-size: 14px; margin-bottom: 5px;">Urgent</h4>
                        <p id="stat-tasks-urgent" style="font-size: 24px; font-weight: bold; color: #ff69b4;">0</p>
                    </div>
                </div>

                <div style="margin-bottom: 20px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
                    <button class="btn btn-primary" onclick="openAddTaskModal()">+ Add New Task</button>
                </div>

                <div id="tasks-list"></div>
            </div>
            
            <!-- Users Section -->
            <div id="section-users-list" class="content-section" style="display: none;">
                <div class="page-header">
                    <h2>User Management</h2>
                    <p>Manage admin users and their access</p>
                </div>

                <div style="margin-bottom: 20px;">
                    <button class="btn btn-primary" onclick="openCreateUserModal()">➕ Create New User</button>
                </div>

                <div id="users-table-container">
                    <table style="width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-radius: 8px; overflow: hidden;">
                        <thead>
                            <tr style="background: #f8f9fa;">
                                <th style="padding: 15px; text-align: left; border-bottom: 2px solid #ddd;">Full Name</th>
                                <th style="padding: 15px; text-align: left; border-bottom: 2px solid #ddd;">Username</th>
                                <th style="padding: 15px; text-align: left; border-bottom: 2px solid #ddd;">Email</th>
                                <th style="padding: 15px; text-align: left; border-bottom: 2px solid #ddd;">Role</th>
                                <th style="padding: 15px; text-align: left; border-bottom: 2px solid #ddd;">Created</th>
                                <th style="padding: 15px; text-align: center; border-bottom: 2px solid #ddd;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="users-list-tbody">
                            <tr>
                                <td colspan="6" style="padding: 40px; text-align: center; color: #999;">
                                    Loading users...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Email Inbox Section -->
            <div id="section-email-inbox" class="content-section" style="display: none;">
                <div class="page-header">
                    <h2>📥 Email Inbox</h2>
                    <p>Manage incoming email messages</p>
                </div>

                <div style="margin-bottom: 20px;">
                    <button class="btn btn-primary" onclick="composeEmail()">✉️ Compose New Email</button>
                    <button class="btn btn-secondary" onclick="loadInboxEmails()">🔄 Refresh</button>
                </div>

                <div id="inbox-email-list">
                    <div class="empty-state">
                        <h3>No Messages</h3>
                        <p>Your inbox is empty.</p>
                    </div>
                </div>
            </div>
            
            <!-- Email Drafts Section -->
            <div id="section-email-draft" class="content-section" style="display: none;">
                <div class="page-header">
                    <h2>📝 Email Drafts</h2>
                    <p>View and edit draft messages</p>
                </div>

                <div style="margin-bottom: 20px;">
                    <button class="btn btn-primary" onclick="composeEmail()">✉️ Compose New Email</button>
                    <button class="btn btn-secondary" onclick="loadDraftEmails()">🔄 Refresh</button>
                </div>

                <div id="draft-email-list">
                    <div class="empty-state">
                        <h3>No Drafts</h3>
                        <p>You don't have any saved drafts.</p>
                    </div>
                </div>
            </div>
            
            <!-- Email Sent Section -->
            <div id="section-email-sent" class="content-section" style="display: none;">
                <div class="page-header">
                    <h2>📤 Sent Emails</h2>
                    <p>View sent messages</p>
                </div>

                <div style="margin-bottom: 20px;">
                    <button class="btn btn-primary" onclick="composeEmail()">✉️ Compose New Email</button>
                    <button class="btn btn-secondary" onclick="loadSentEmails()">🔄 Refresh</button>
                </div>

                <div id="sent-email-list">
                    <div class="empty-state">
                        <h3>No Sent Messages</h3>
                        <p>You haven't sent any emails yet.</p>
                    </div>
                </div>
            </div>
            
            <!-- Email Trash Section -->
            <div id="section-email-trash" class="content-section" style="display: none;">
                <div class="page-header">
                    <h2>🗑️ Deleted Emails</h2>
                    <p>View and restore deleted messages</p>
                </div>

                <div style="margin-bottom: 20px;">
                    <button class="btn btn-danger" onclick="emptyTrash()">🗑️ Empty Trash</button>
                    <button class="btn btn-secondary" onclick="loadTrashEmails()">🔄 Refresh</button>
                </div>

                <div id="trash-email-list">
                    <div class="empty-state">
                        <h3>Trash is Empty</h3>
                        <p>No deleted messages.</p>
                    </div>
                </div>
            </div>
            
            <!-- Email Settings Section -->
            <div id="section-email-settings" class="content-section" style="display: none;">
                <div class="page-header">
                    <h2>⚙️ Email Settings</h2>
                    <p>Configure email server settings and preferences</p>
                </div>

                <form id="emailSettingsForm" onsubmit="saveEmailSettings(event)">
                    <div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 20px;">
                        <h3 style="margin-bottom: 20px; color: #333; border-bottom: 2px solid #ff69b4; padding-bottom: 10px;">SMTP Server Settings</h3>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div class="form-group">
                                <label for="smtpHost">SMTP Host *</label>
                                <input type="text" id="smtpHost" class="form-control" placeholder="smtp.example.com" required>
                                <small style="color: #666;">Your SMTP server hostname</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="smtpPort">SMTP Port *</label>
                                <input type="number" id="smtpPort" class="form-control" placeholder="587" required>
                                <small style="color: #666;">Common ports: 587 (TLS), 465 (SSL), 25</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="smtpUsername">Username *</label>
                                <input type="text" id="smtpUsername" class="form-control" placeholder="user@example.com" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="smtpPassword">Password *</label>
                                <input type="password" id="smtpPassword" class="form-control" placeholder="••••••••" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="smtpEncryption">Encryption</label>
                                <select id="smtpEncryption" class="form-control">
                                    <option value="tls">TLS</option>
                                    <option value="ssl">SSL</option>
                                    <option value="none">None</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="smtpFromEmail">From Email *</label>
                                <input type="email" id="smtpFromEmail" class="form-control" placeholder="noreply@example.com" required>
                                <small style="color: #666;">Default sender email address</small>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="smtpFromName">From Name</label>
                            <input type="text" id="smtpFromName" class="form-control" placeholder="Content Catalogz">
                            <small style="color: #666;">Name that appears as sender</small>
                        </div>
                    </div>
                    
                    <div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 20px;">
                        <h3 style="margin-bottom: 20px; color: #333; border-bottom: 2px solid #ff69b4; padding-bottom: 10px;">Email Preferences</h3>
                        
                        <div class="form-group">
                            <label>
                                <input type="checkbox" id="enableEmailNotifications" style="margin-right: 8px;">
                                Enable email notifications for new quote requests
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input type="checkbox" id="enableAutoReply" style="margin-right: 8px;">
                                Send automatic reply to quote requests
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <label for="notificationEmail">Notification Email Address</label>
                            <input type="email" id="notificationEmail" class="form-control" placeholder="admin@example.com">
                            <small style="color: #666;">Where to send notifications</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="autoReplyTemplate">Auto-Reply Template</label>
                            <textarea id="autoReplyTemplate" class="form-control" rows="5" placeholder="Thank you for your quote request. We will get back to you within 24 hours."></textarea>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <button type="button" class="btn btn-secondary" onclick="testEmailSettings()">📧 Test Connection</button>
                        <button type="submit" class="btn btn-primary">💾 Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add/Edit Task Modal -->
    <div id="taskModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="taskModalTitle">Add New Task</h3>
                <button class="close-btn" onclick="closeTaskModal()">&times;</button>
            </div>
            <form id="taskForm" onsubmit="saveTask(event)">
                <input type="hidden" id="taskId">
                <div class="form-group">
                    <label for="taskTitle">Task Title *</label>
                    <input type="text" id="taskTitle" class="form-control" required placeholder="Follow up with client">
                </div>
                <div class="form-group">
                    <label for="taskDescription">Description</label>
                    <textarea id="taskDescription" class="form-control" rows="3" placeholder="Task details..."></textarea>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label for="taskClientId">Related Client (Optional)</label>
                        <select id="taskClientId" class="form-control">
                            <option value="">No client</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="taskPriority">Priority</label>
                        <select id="taskPriority" class="form-control">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label for="taskDueDate">Due Date</label>
                        <input type="date" id="taskDueDate" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="taskStatus">Status</label>
                        <select id="taskStatus" class="form-control">
                            <option value="pending" selected>Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeTaskModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Task</button>
                </div>
            </form>
        </div>
    </div>

    <!-- HTML File Editor Modal -->
    <div id="htmlEditorModal" class="modal">
        <div class="modal-content" style="max-width: 90%; max-height: 90vh;">
            <div class="modal-header">
                <h3 id="editorModalTitle">Edit HTML File</h3>
                <button class="close-btn" onclick="closeHtmlEditorModal()">&times;</button>
            </div>
            <div style="margin-bottom: 15px; padding: 15px; background: #f0f0f0; border-radius: 4px;">
                <strong id="editingFilename"></strong>
                <span style="margin-left: 20px; color: #666;">Make sure to save your changes!</span>
            </div>
            <form id="htmlEditorForm" onsubmit="saveHtmlFile(event)">
                <input type="hidden" id="htmlFilename" name="filename">
                <div class="form-group">
                    <textarea id="htmlContent" name="content" style="font-family: 'Courier New', monospace; height: 500px; white-space: pre; overflow: auto;"></textarea>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 15px;">
                    <button type="button" class="btn btn-secondary" onclick="closeHtmlEditorModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add/Edit Page Modal -->
    <div id="pageModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add New Page</h3>
                <button class="close-btn" onclick="closePageModal()">&times;</button>
            </div>
            <form id="pageForm" onsubmit="savePage(event)">
                <div class="form-group">
                    <label for="pageId">Page ID</label>
                    <input type="hidden" id="pageId" name="id" value="">
                </div>
                <div class="form-group">
                    <label for="pageTitle">Page Title *</label>
                    <input type="text" id="pageTitle" name="title" required>
                </div>
                <div class="form-group">
                    <label for="pageSlug">URL Slug *</label>
                    <input type="text" id="pageSlug" name="slug" required placeholder="example-page">
                </div>
                <div class="form-group">
                    <label for="pageType">Page Type</label>
                    <select id="pageType" name="page_type">
                        <option value="standard">Standard</option>
                        <option value="blog">Blog Post</option>
                        <option value="service">Service</option>
                        <option value="testimonial">Testimonial</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="pageStatus">Status</label>
                    <select id="pageStatus" name="status">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="pageContent">Content *</label>
                    <textarea id="pageContent" name="content" required placeholder="Enter page content here..."></textarea>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closePageModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Page</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Quote Details Modal -->
    <div id="quoteModal" class="modal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3>Quote Request Details</h3>
                <button class="close-btn" onclick="closeQuoteModal()">&times;</button>
            </div>
            <div style="background: #f8f9fa; padding: 20px; border-radius: 4px; margin-bottom: 20px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <strong>Name:</strong><br>
                        <span id="quoteName"></span>
                    </div>
                    <div>
                        <strong>Company:</strong><br>
                        <span id="quoteCompany"></span>
                    </div>
                    <div>
                        <strong>Email:</strong><br>
                        <span id="quoteEmail"></span>
                    </div>
                    <div>
                        <strong>Phone:</strong><br>
                        <span id="quotePhone"></span>
                    </div>
                    <div>
                        <strong>Service Requested:</strong><br>
                        <span id="quoteService"></span>
                    </div>
                    <div>
                        <strong>Received:</strong><br>
                        <span id="quoteReceived"></span>
                    </div>
                </div>
                <div style="margin-top: 15px;">
                    <strong>Message:</strong><br>
                    <p id="quoteMessage" style="margin-top: 8px; line-height: 1.6; color: #333;"></p>
                </div>
            </div>

            <!-- Services and Costs Section -->
            <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h4 style="margin: 0; color: #333;">Services & Costs (GBP)</h4>
                    <button type="button" class="btn btn-primary btn-sm" onclick="addQuoteServiceRow()">+ Add Service</button>
                </div>
                
                <div id="quoteServicesContainer">
                    <!-- Services will be added here dynamically -->
                </div>
                
                <div style="margin-top: 20px; padding-top: 15px; border-top: 2px solid #ddd;">
                    <div class="form-group">
                        <label for="quoteTotalCost">Total Cost (£)</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-weight: bold; font-size: 18px; color: #333;">£</span>
                            <input type="number" id="quoteTotalCost" name="total_cost" class="form-control" step="0.01" min="0" readonly style="background:#f8f9fa; font-weight: bold; font-size: 18px; padding-left: 28px;">
                        </div>
                    </div>
                </div>
            </div>

            <form id="quoteForm" onsubmit="updateQuote(event)">
                <input type="hidden" id="quoteId" name="id">
                <div class="form-group">
                    <label for="quoteStatus">Status</label>
                    <select id="quoteStatus" name="status" class="form-control">
                        <option value="new">New</option>
                        <option value="contacted">Contacted</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="declined">Declined</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="quoteNotes">Admin Notes</label>
                    <textarea id="quoteNotes" name="notes" class="form-control" rows="4" placeholder="Add internal notes about this quote request..."></textarea>
                </div>
                <div style="display: flex; gap: 10px; justify-content: space-between; margin-top: 15px;">
                    <button type="button" class="btn btn-danger" onclick="confirmDeleteQuote()" title="Delete this quote request">🗑️ Delete Quote</button>
                    <div style="display: flex; gap: 10px;">
                        <button type="button" class="btn btn-secondary" onclick="closeQuoteModal()">Close</button>
                        <button type="button" class="btn btn-secondary" onclick="emailQuote()">📧 Email Quote</button>
                        <button type="submit" class="btn btn-primary">Update Quote</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Quote Confirmation Modal -->
    <div id="deleteQuoteModal" class="modal">
        <div class="modal-content" style="max-width: 450px;">
            <div class="modal-header" style="background: #dc3545; color: white;">
                <h3>⚠️ Confirm Delete</h3>
                <button class="close-btn" onclick="closeDeleteQuoteModal()" style="color: white;">&times;</button>
            </div>
            <div style="padding: 20px;">
                <p style="margin-bottom: 15px;">Are you sure you want to delete this quote request from <strong id="deleteQuoteName"></strong>?</p>
                <p style="color: #dc3545; font-weight: bold;">This action cannot be undone!</p>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end; padding: 15px 20px; border-top: 1px solid #ddd;">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteQuoteModal()">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="deleteQuote()">Delete Quote</button>
            </div>
        </div>
    </div>

    <!-- Client Details Modal -->
    <div id="clientModal" class="modal">
        <div class="modal-content" style="max-width: 1100px;">
            <div class="modal-header">
                <h3>Client Details - <span id="clientModalName"></span></h3>
                <button class="close-btn" onclick="closeClientModal()">&times;</button>
            </div>
            
            <!-- CRM Tabs -->
            <div style="border-bottom: 2px solid #ddd; margin-bottom: 20px;">
                <div style="display: flex; gap: 5px;">
                    <button type="button" class="crm-tab active" onclick="switchClientTab('details')" id="tab-details">📋 Details & Billing</button>
                    <button type="button" class="crm-tab" onclick="switchClientTab('activities')" id="tab-activities">📅 Activity Timeline</button>
                    <button type="button" class="crm-tab" onclick="switchClientTab('notes')" id="tab-notes">📝 Notes</button>
                    <button type="button" class="crm-tab" onclick="switchClientTab('tasks')" id="tab-tasks">✅ Tasks</button>
                </div>
            </div>
            
            <!-- Tab: Details & Billing -->
            <div id="client-tab-details" class="client-tab-content">
            <form id="clientForm" onsubmit="updateClient(event)">
                <input type="hidden" id="clientId" name="id">
                
                <!-- Client Information -->
                <div style="background: #f8f9fa; padding: 20px; border-radius: 4px; margin-bottom: 20px;">
                    <h4 style="margin-bottom: 15px; color: #333;">Contact Information</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <strong>Name:</strong><br>
                            <span id="clientName"></span>
                        </div>
                        <div>
                            <strong>Company:</strong><br>
                            <span id="clientCompany"></span>
                        </div>
                        <div>
                            <strong>Email:</strong><br>
                            <span id="clientEmail"></span>
                        </div>
                        <div>
                            <strong>Phone:</strong><br>
                            <span id="clientPhone"></span>
                        </div>
                    </div>
                </div>

                <!-- Address -->
                <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 20px;">
                    <h4 style="margin-bottom: 15px; color: #333;">Address</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label for="clientAddressStreet">Street Address</label>
                            <input type="text" id="clientAddressStreet" name="address_street" class="form-control" placeholder="123 Main Street">
                        </div>
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label for="clientAddressLine2">Address Line 2 (Optional)</label>
                            <input type="text" id="clientAddressLine2" name="address_line2" class="form-control" placeholder="Apartment, suite, unit, etc.">
                        </div>
                        <div class="form-group">
                            <label for="clientAddressCity">Town/City</label>
                            <input type="text" id="clientAddressCity" name="address_city" class="form-control" placeholder="London">
                        </div>
                        <div class="form-group">
                            <label for="clientAddressCounty">County</label>
                            <input type="text" id="clientAddressCounty" name="address_county" class="form-control" placeholder="Greater London">
                        </div>
                        <div class="form-group">
                            <label for="clientAddressPostcode">Postcode</label>
                            <input type="text" id="clientAddressPostcode" name="address_postcode" class="form-control" placeholder="SW1A 1AA" style="text-transform: uppercase;">
                        </div>
                        <div class="form-group">
                            <label for="clientAddressCountry">Country</label>
                            <input type="text" id="clientAddressCountry" name="address_country" class="form-control" value="United Kingdom">
                        </div>
                    </div>
                </div>

                <!-- Services and Costs -->
                <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h4 style="margin: 0; color: #333;">Services & Costs (GBP)</h4>
                        <button type="button" class="btn btn-primary btn-sm" onclick="addServiceRow()">+ Add Service</button>
                    </div>
                    
                    <div id="servicesContainer">
                        <!-- Services will be added here dynamically -->
                    </div>
                    
                    <div style="margin-top: 20px; padding-top: 15px; border-top: 2px solid #ddd;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label for="totalCost">Total Cost (£)</label>
                                <div style="position: relative;">
                                    <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-weight: bold; font-size: 18px; color: #333;">£</span>
                                    <input type="number" id="totalCost" name="total_cost" class="form-control" step="0.01" min="0" readonly style="background:#f8f9fa; font-weight: bold; font-size: 18px; padding-left: 28px;">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="totalPaid">Total Paid (£)</label>
                                <div style="display: flex; gap: 10px;">
                                    <div style="position: relative; flex: 1;">
                                        <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-weight: bold; font-size: 16px; color: #333;">£</span>
                                        <input type="number" id="totalPaid" name="total_paid" class="form-control" step="0.01" min="0" readonly style="background:#f8f9fa; padding-left: 28px;">
                                    </div>
                                    <button type="button" class="btn btn-primary" onclick="openPaymentModal()" style="white-space: nowrap;">💰 Record Payment</button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label id="totalRemainingLabel" for="totalRemaining">Balance Due (£)</label>
                            <div style="position: relative;">
                                <span id="totalRemainingCurrency" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-weight: bold; font-size: 18px; color: #dc3545;">£</span>
                                <input type="number" id="totalRemaining" name="total_remaining" class="form-control" step="0.01" readonly style="background:#f8f9fa; font-weight: bold; font-size: 18px; color: #dc3545; padding-left: 28px;">
                            </div>
                            <small id="totalRemainingHelp" style="color: #666; display: none;"></small>
                        </div>
                    </div>
                </div>

                <!-- Payment History -->
                <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 20px;">
                    <h4 style="margin-bottom: 15px; color: #333;">💰 Payment History</h4>
                    <div id="client-payments-list">
                        <div class="empty-state">
                            <p>No payments recorded yet.</p>
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; justify-content: space-between;">
                    <button type="button" class="btn btn-danger" onclick="confirmDeleteClient()" title="Delete this client and all related data">🗑️ Delete Client</button>
                    <div style="display: flex; gap: 10px;">
                        <button type="button" class="btn btn-secondary" onclick="closeClientModal()">Close</button>
                        <button type="button" class="btn btn-primary" onclick="generateInvoiceForClient()" title="Generate and save invoice to database">📄 Generate Invoice</button>
                        <button type="button" class="btn btn-secondary" onclick="composeEmail()">✉️ Send Email</button>
                        <button type="button" class="btn btn-secondary" onclick="printInvoice()">🖨️ Print Invoice</button>
                        <button type="button" class="btn btn-secondary" onclick="emailInvoice()">📧 Email Invoice</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </div>
            </form>
            </div>
            
            <!-- Tab: Activity Timeline -->
            <div id="client-tab-activities" class="client-tab-content" style="display: none;">
                <div style="margin-bottom: 20px;">
                    <button class="btn btn-primary" onclick="openLogActivityModal()">+ Log Activity</button>
                    <button class="btn btn-secondary" onclick="composeEmail()">✉️ Email Client</button>
                </div>
                <div id="client-activities-list">
                    <div class="empty-state">
                        <h3>No Activities Yet</h3>
                        <p>Log your first interaction with this client.</p>
                    </div>
                </div>
            </div>
            
            <!-- Tab: Notes -->
            <div id="client-tab-notes" class="client-tab-content" style="display: none;">
                <div style="margin-bottom: 20px;">
                    <button class="btn btn-primary" onclick="openAddNoteModal()">+ Add Note</button>
                </div>
                <div id="client-notes-list">
                    <div class="empty-state">
                        <h3>No Notes Yet</h3>
                        <p>Add notes to keep track of important information about this client.</p>
                    </div>
                </div>
            </div>
            
            <!-- Tab: Tasks -->
            <div id="client-tab-tasks" class="client-tab-content" style="display: none;">
                <div style="margin-bottom: 20px;">
                    <button class="btn btn-primary" onclick="openAddClientTaskModal()">+ Add Task</button>
                </div>
                <div id="client-tasks-list">
                    <div class="empty-state">
                        <h3>No Tasks Yet</h3>
                        <p>Create tasks related to this client.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add New Client Modal -->
    <div id="addClientModal" class="modal">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h3>Add New Client</h3>
                <button class="close-btn" onclick="closeAddClientModal()">&times;</button>
            </div>
            <form id="addClientForm" onsubmit="saveNewClient(event)">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label for="newClientFirstName">First Name <span style="color: red;">*</span></label>
                        <input type="text" id="newClientFirstName" name="first_name" class="form-control" required placeholder="John">
                    </div>
                    <div class="form-group">
                        <label for="newClientLastName">Last Name <span style="color: red;">*</span></label>
                        <input type="text" id="newClientLastName" name="last_name" class="form-control" required placeholder="Smith">
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="newClientEmail">Email <span style="color: red;">*</span></label>
                        <input type="email" id="newClientEmail" name="email" class="form-control" required placeholder="john@example.com">
                    </div>
                    <div class="form-group">
                        <label for="newClientCompany">Company</label>
                        <input type="text" id="newClientCompany" name="company" class="form-control" placeholder="Company Name">
                    </div>
                    <div class="form-group">
                        <label for="newClientPhone">Phone</label>
                        <input type="text" id="newClientPhone" name="phone" class="form-control" placeholder="+44 123 456 7890">
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="newClientAddressStreet">Address Street</label>
                        <input type="text" id="newClientAddressStreet" name="address_street" class="form-control" placeholder="123 Main Street">
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="newClientAddressLine2">Address Line 2</label>
                        <input type="text" id="newClientAddressLine2" name="address_line2" class="form-control" placeholder="Apt, Suite, Building (optional)">
                    </div>
                    <div class="form-group">
                        <label for="newClientCity">City</label>
                        <input type="text" id="newClientCity" name="address_city" class="form-control" placeholder="Dublin">
                    </div>
                    <div class="form-group">
                        <label for="newClientCounty">County/State</label>
                        <input type="text" id="newClientCounty" name="address_county" class="form-control" placeholder="County">
                    </div>
                    <div class="form-group">
                        <label for="newClientPostcode">Postcode</label>
                        <input type="text" id="newClientPostcode" name="address_postcode" class="form-control" placeholder="D01 A123">
                    </div>
                    <div class="form-group">
                        <label for="newClientCountry">Country</label>
                        <select id="newClientCountry" name="address_country" class="form-control">
                            <option value="United Kingdom" selected>United Kingdom</option>
                            <option value="Ireland">Ireland</option>
                            <option value="United States">United States</option>
                            <option value="Canada">Canada</option>
                            <option value="Australia">Australia</option>
                            <option value="New Zealand">New Zealand</option>
                            <option value="France">France</option>
                            <option value="Germany">Germany</option>
                            <option value="Spain">Spain</option>
                            <option value="Italy">Italy</option>
                            <option value="Netherlands">Netherlands</option>
                            <option value="Belgium">Belgium</option>
                            <option value="Switzerland">Switzerland</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="newClientMessage">Message/Request</label>
                        <textarea id="newClientMessage" name="message" class="form-control" rows="2" placeholder="Client's initial inquiry or message"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="newClientService">Service Interested In</label>
                        <select id="newClientService" name="service_type" class="form-control">
                            <option value="">-- Select Service --</option>
                            <option value="starter-pack">Starter Pack</option>
                            <option value="growth-bundle">Growth Bundle</option>
                            <option value="premium-suite">Premium Suite</option>
                            <option value="custom">Custom Package</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="newClientStatus">Status</label>
                        <select id="newClientStatus" name="status" class="form-control">
                            <option value="new">New</option>
                            <option value="contacted">Contacted</option>
                            <option value="in_progress">In Progress</option>
                        </select>
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="newClientNotes">Notes</label>
                        <textarea id="newClientNotes" name="notes" class="form-control" rows="3" placeholder="Additional notes about this client..."></textarea>
                    </div>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="btn btn-secondary" onclick="closeAddClientModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Client</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteClientModal" class="modal">
        <div class="modal-content" style="max-width: 450px;">
            <div class="modal-header" style="background: #dc3545; color: white;">
                <h3>⚠️ Confirm Delete</h3>
                <button class="close-btn" onclick="closeDeleteClientModal()" style="color: white;">&times;</button>
            </div>
            <div style="padding: 20px;">
                <p style="margin-bottom: 15px;">Are you sure you want to delete <strong id="deleteClientName"></strong>?</p>
                <p style="color: #dc3545; font-size: 14px; margin-bottom: 20px;">This will permanently delete:</p>
                <ul style="margin-left: 20px; margin-bottom: 20px; color: #666;">
                    <li>All invoices</li>
                    <li>All activities</li>
                    <li>All tasks</li>
                    <li>All notes</li>
                    <li>All tags</li>
                </ul>
                <p style="color: #dc3545; font-weight: bold;">This action cannot be undone!</p>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end; padding: 15px 20px; border-top: 1px solid #ddd;">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteClientModal()">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="deleteClient()">Delete Client</button>
            </div>
        </div>
    </div>

    <!-- Log Activity Modal -->
    <div id="activityModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Log Activity</h3>
                <button class="close-btn" onclick="closeActivityModal()">&times;</button>
            </div>
            <form id="activityForm" onsubmit="saveActivity(event)">
                <input type="hidden" id="activityClientId">
                <div class="form-group">
                    <label for="activityType">Activity Type *</label>
                    <select id="activityType" class="form-control" required>
                        <option value="call">Phone Call</option>
                        <option value="email">Email</option>
                        <option value="meeting">Meeting</option>
                        <option value="note">Note</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="activitySubject">Subject *</label>
                    <input type="text" id="activitySubject" class="form-control" required placeholder="Brief description">
                </div>
                <div class="form-group">
                    <label for="activityDescription">Details</label>
                    <textarea id="activityDescription" class="form-control" rows="4" placeholder="Activity details..."></textarea>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label for="activityDate">Date & Time</label>
                        <input type="datetime-local" id="activityDate" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="activityDuration">Duration (minutes)</label>
                        <input type="number" id="activityDuration" class="form-control" min="0" placeholder="0">
                    </div>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeActivityModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Activity</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Add Note Modal -->
    <div id="noteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add Note</h3>
                <button class="close-btn" onclick="closeNoteModal()">&times;</button>
            </div>
            <form id="noteForm" onsubmit="saveNote(event)">
                <input type="hidden" id="noteClientId">
                <div class="form-group">
                    <label for="noteText">Note *</label>
                    <textarea id="noteText" class="form-control" rows="5" required placeholder="Enter your note..."></textarea>
                </div>
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" id="noteImportant" style="margin: 0;">
                        <span>⭐ Mark as Important</span>
                    </label>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeNoteModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Note</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Record Payment Modal -->
    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Record Payment</h3>
                <button class="close-btn" onclick="closePaymentModal()">&times;</button>
            </div>
            <form id="paymentForm" onsubmit="recordPayment(event)">
                <input type="hidden" id="paymentClientId">
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; font-size: 14px;">
                        <div>
                            <strong>Total Cost:</strong><br>
                            <span style="font-size: 18px; color: #333;">£<span id="paymentTotalCost">0.00</span></span>
                        </div>
                        <div>
                            <strong>Total Paid:</strong><br>
                            <span style="font-size: 18px; color: #28a745;">£<span id="paymentTotalPaid">0.00</span></span>
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <strong>Outstanding Balance:</strong><br>
                            <span style="font-size: 20px; font-weight: bold; color: #dc3545;">£<span id="paymentOutstanding">0.00</span></span>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="paymentAmount">Payment Amount (£) *</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-weight: bold; font-size: 16px; color: #333;">£</span>
                        <input type="number" id="paymentAmount" class="form-control" step="0.01" min="0.01" required placeholder="0.00" style="padding-left: 28px; font-size: 18px; font-weight: bold;">
                    </div>
                    <small style="color: #666;">Enter the amount received from the client</small>
                </div>
                
                <div class="form-group">
                    <label for="paymentDate">Payment Date *</label>
                    <input type="date" id="paymentDate" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="paymentMethod">Payment Method</label>
                    <select id="paymentMethod" class="form-control">
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cash">Cash</option>
                        <option value="check">Cheque</option>
                        <option value="card">Card Payment</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="paymentNotes">Notes (Optional)</label>
                    <textarea id="paymentNotes" class="form-control" rows="3" placeholder="Payment reference, notes, etc..."></textarea>
                </div>
                
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closePaymentModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">💰 Record Payment</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Create User Modal -->
    <div id="createUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Create New Admin User</h3>
                <button class="close-btn" onclick="closeCreateUserModal()">&times;</button>
            </div>
            <form id="createUserForm" onsubmit="createUser(event)">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label for="newUserFirstName">First Name *</label>
                        <input type="text" id="newUserFirstName" class="form-control" required placeholder="e.g., John">
                    </div>
                    
                    <div class="form-group">
                        <label for="newUserLastName">Last Name *</label>
                        <input type="text" id="newUserLastName" class="form-control" required placeholder="e.g., Smith">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="newUsername">Username *</label>
                    <input type="text" id="newUsername" class="form-control" required minlength="3" placeholder="e.g., john_admin" pattern="[a-zA-Z0-9_]+" title="Only letters, numbers, and underscores">
                    <small style="color: #666;">Minimum 3 characters, letters, numbers, and underscores only</small>
                </div>
                
                <div class="form-group">
                    <label for="newUserEmail">Email *</label>
                    <input type="email" id="newUserEmail" class="form-control" required placeholder="admin@example.com">
                </div>
                
                <div class="form-group">
                    <label for="newUserPassword">Password *</label>
                    <input type="password" id="newUserPassword" class="form-control" required minlength="8" placeholder="Minimum 8 characters">
                    <small style="color: #666;">Minimum 8 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="newUserPasswordConfirm">Confirm Password *</label>
                    <input type="password" id="newUserPasswordConfirm" class="form-control" required minlength="8" placeholder="Re-enter password">
                </div>
                
                <div class="form-group">
                    <label for="newUserRole">Role</label>
                    <select id="newUserRole" class="form-control">
                        <option value="admin">Administrator</option>
                        <option value="superadmin">Super Administrator</option>
                    </select>
                    <small style="color: #666;">Super Admins can manage other users</small>
                </div>
                
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeCreateUserModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">➕ Create User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit User</h3>
                <button class="close-btn" onclick="closeEditUserModal()">&times;</button>
            </div>
            <form id="editUserForm" onsubmit="updateUser(event)">
                <input type="hidden" id="editUserId">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label for="editUserFirstName">First Name *</label>
                        <input type="text" id="editUserFirstName" class="form-control" required placeholder="e.g., John">
                    </div>
                    
                    <div class="form-group">
                        <label for="editUserLastName">Last Name *</label>
                        <input type="text" id="editUserLastName" class="form-control" required placeholder="e.g., Smith">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="editUsername">Username</label>
                    <input type="text" id="editUsername" class="form-control" readonly style="background: #f0f0f0; cursor: not-allowed;">
                    <small style="color: #666;">Username cannot be changed</small>
                </div>
                
                <div class="form-group">
                    <label for="editUserEmail">Email *</label>
                    <input type="email" id="editUserEmail" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="editUserPassword">New Password (Optional)</label>
                    <input type="password" id="editUserPassword" class="form-control" minlength="8" placeholder="Leave blank to keep current password">
                    <small style="color: #666;">Only enter a new password if you want to change it</small>
                </div>
                
                <div class="form-group">
                    <label for="editUserPasswordConfirm">Confirm New Password</label>
                    <input type="password" id="editUserPasswordConfirm" class="form-control" minlength="8" placeholder="Confirm new password">
                </div>
                
                <div class="form-group">
                    <label for="editUserRole">Role</label>
                    <select id="editUserRole" class="form-control">
                        <option value="admin">Administrator</option>
                        <option value="superadmin">Super Administrator</option>
                    </select>
                </div>
                
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeEditUserModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">💾 Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Menu Customization Modal -->
    <div id="menuCustomizationModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h3>Customize Sidebar Menu</h3>
                <button class="close-btn" onclick="closeMenuCustomizationModal()">&times;</button>
            </div>
            <div style="padding: 20px;">
                <p style="color: #666; margin-bottom: 20px;">Drag items to reorder or use arrow buttons. Changes save automatically.</p>
                <div id="menu-items-list" style="background: #f8f9fa; border-radius: 8px; padding: 15px;">
                    <!-- Menu items will be loaded here -->
                </div>
            </div>
            <div style="padding: 20px; border-top: 1px solid #ddd; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-secondary" onclick="resetMenuOrder()">Reset to Default</button>
                <button type="button" class="btn btn-primary" onclick="closeMenuCustomizationModal()">Done</button>
            </div>
        </div>
    </div>

    <!-- Delete User Confirmation Modal -->
    <div id="deleteUserModal" class="modal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3>🗑️ Delete User</h3>
                <button class="close-btn" onclick="closeDeleteUserModal()">&times;</button>
            </div>
            <div style="padding: 20px;">
                <p style="font-size: 16px; margin-bottom: 20px;">
                    Are you sure you want to delete the user <strong id="deleteUserName"></strong>?
                </p>
                <p style="color: #dc3545; margin-bottom: 20px;">
                    ⚠️ <strong>Warning:</strong> This action cannot be undone. The user will no longer be able to access the admin panel.
                </p>
                <input type="hidden" id="deleteUserId">
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeDeleteUserModal()">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="confirmDeleteUser()">🗑️ Delete User</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Compose Email Modal -->
    <div id="composeEmailModal" class="modal">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h3>✉️ Compose Email</h3>
                <button class="close-btn" onclick="closeComposeEmailModal()">&times;</button>
            </div>
            <form id="composeEmailForm" onsubmit="sendClientEmail(event)">
                <input type="hidden" id="emailClientId">
                
                <div class="form-group">
                    <label for="emailTo">To *</label>
                    <input type="email" id="emailTo" class="form-control" required readonly style="background: #f8f9fa;">
                    <small style="color: #666;">Client email address</small>
                </div>
                
                <div class="form-group">
                    <label for="emailSubject">Subject *</label>
                    <input type="text" id="emailSubject" class="form-control" required placeholder="Email subject...">
                </div>
                
                <div class="form-group">
                    <label for="emailMessage">Message *</label>
                    <textarea id="emailMessage" class="form-control" rows="8" required placeholder="Type your message here..."></textarea>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="emailLogActivity" checked style="margin-right: 8px;">
                        Log this email in activity timeline
                    </label>
                </div>
                
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeComposeEmailModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">📧 Send Email</button>
                </div>
            </form>
        </div>
    </div>

    <!-- New Page Modal -->
    <div id="newPageModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Create New Page</h3>
                <button class="close-btn" onclick="closeNewPageModal()">&times;</button>
            </div>
            <form id="newPageForm" onsubmit="createNewPage(event)">
                <div class="form-group">
                    <label for="newPageName">Page Name *</label>
                    <input type="text" id="newPageName" class="form-control" required placeholder="e.g., services, contact, portfolio" pattern="[a-zA-Z0-9_-]+" title="Only letters, numbers, hyphens and underscores allowed">
                    <small style="color: #666;">Will create: <span id="newPageFilename">pagename.html</span></small>
                </div>
                <div class="form-group">
                    <label for="newPageTitle">Page Title *</label>
                    <input type="text" id="newPageTitle" class="form-control" required placeholder="e.g., Our Services">
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeNewPageModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Page</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Invoice Modal -->
    <div id="invoiceModal" class="modal">
      <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
          <h3>Invoice Details</h3>
          <button class="close-btn" onclick="closeInvoiceModal()">&times;</button>
        </div>
        <div id="invoiceModalBody">
          <!-- Invoice details will be loaded here -->
        </div>
      </div>
    </div>

    <script>
        // Dashboard initialization and error handling
        console.log('%c Dashboard Script Loading...', 'background: #667eea; color: white; padding: 2px 8px; border-radius: 3px;');
        
        // Global error handler
        window.addEventListener('error', function(e) {
            console.error('%c JavaScript Error:', 'color: red; font-weight: bold;', e.message, 'at', e.filename + ':' + e.lineno);
        });
        
        function toggleSubmenu(event, submenuId) {
            event.preventDefault();
            event.stopPropagation();
            
            const submenu = document.getElementById(submenuId);
            const parent = event.currentTarget;
            
            // Toggle submenu
            submenu.classList.toggle('open');
            parent.classList.toggle('open');
        }

        function openAddPageModal() {
            document.getElementById('pageForm').reset();
            document.getElementById('pageId').value = '';
            document.getElementById('modalTitle').textContent = 'Add New Page';
            document.getElementById('pageModal').classList.add('show');
        }

        function openEditPageModal(pageId) {
            // Fetch page data via AJAX
            fetch('api/get_page.php?id=' + pageId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const page = data.page;
                        document.getElementById('pageId').value = page.id;
                        document.getElementById('pageTitle').value = page.title;
                        document.getElementById('pageSlug').value = page.slug;
                        document.getElementById('pageType').value = page.page_type || 'standard';
                        document.getElementById('pageStatus').value = page.status;
                        document.getElementById('pageContent').value = page.content;
                        document.getElementById('modalTitle').textContent = 'Edit Page';
                        document.getElementById('pageModal').classList.add('show');
                    }
                });
        }

        function closePageModal() {
            document.getElementById('pageModal').classList.remove('show');
        }

        function savePage(event) {
            event.preventDefault();
            
            const formData = new FormData(document.getElementById('pageForm'));
            
            fetch('api/save_page.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Page saved successfully!');
                    closePageModal();
                    location.reload();
                } else {
                    alert('Error saving page: ' + data.message);
                }
            })
            .catch(error => {
                alert('Network error: ' + error.message);
            });
        }

        function deletePage(pageId) {
            if (confirm('Are you sure you want to delete this page? This action cannot be undone.')) {
                fetch('api/delete_page.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + pageId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting page: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Network error: ' + error.message);
                });
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const pageModal = document.getElementById('pageModal');
            const htmlModal = document.getElementById('htmlEditorModal');
            const quoteModal = document.getElementById('quoteModal');
            if (event.target === pageModal) {
                closePageModal();
            }
            if (event.target === htmlModal) {
                closeHtmlEditorModal();
            }
            if (event.target === quoteModal) {
                closeQuoteModal();
            }
        };

        // Section switching
        function showSection(sectionName) {
            console.log('showSection called with:', sectionName);
            
            try {
                // Hide all sections
                document.querySelectorAll('.content-section').forEach(section => {
                    section.style.display = 'none';
                });
                
                // Remove active class from all nav items
                document.querySelectorAll('.sidebar a').forEach(link => {
                    link.classList.remove('active');
                });
                
                // Show selected section
                const targetSection = document.getElementById('section-' + sectionName);
                if (!targetSection) {
                    console.error('Section not found:', 'section-' + sectionName);
                    return;
                }
                
                targetSection.style.display = 'block';
                console.log('✓ Section displayed:', sectionName);
                
                const navElement = document.getElementById('nav-' + sectionName);
                if (navElement) {
                    navElement.classList.add('active');
                    console.log('✓ Nav item activated:', sectionName);
                } else {
                    console.warn('Nav element not found for:', sectionName);
                }
                
                // Auto-expand submenu if section is in a submenu
                if (sectionName === 'clients' || sectionName === 'existing-clients') {
                    const submenu = document.getElementById('clients-submenu');
                    const parent = document.querySelector('.sidebar .menu-parent');
                    if (submenu && !submenu.classList.contains('open')) {
                        submenu.classList.add('open');
                        parent.classList.add('open');
                    }
                    if (sectionName === 'clients') {
                        loadQuotes();
                    } else if (sectionName === 'existing-clients') {
                        loadExistingClients();
                    }
                }
                
                // Load HTML files if switching to that section
                if (sectionName === 'html-files') {
                    loadHtmlFiles();
                }
                
                // Load tasks if switching to tasks section
                if (sectionName === 'tasks') {
                    loadTasks();
                }
                
                // Load invoice stats if switching to invoices section
                if (sectionName === 'invoices') {
                    loadInvoiceStats();
                }
                
                // Load users if switching to users section
                if (sectionName === 'users-list') {
                    loadUsers();
                }
                
                // Load email sections
                if (sectionName === 'email-inbox') {
                    loadInboxEmails();
                }
                if (sectionName === 'email-draft') {
                    loadDraftEmails();
                }
                if (sectionName === 'email-sent') {
                    loadSentEmails();
                }
                if (sectionName === 'email-trash') {
                    loadTrashEmails();
                }
                if (sectionName === 'email-settings') {
                    loadEmailSettings();
                }
            } catch (error) {
                console.error('Error in showSection:', error);
            }
        }

        // Load HTML files
        function loadHtmlFiles() {
            fetch('api/get_html_files.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        displayHtmlFiles(data.files);
                        const htmlCountEl = document.getElementById('html-count');
                        if (htmlCountEl) htmlCountEl.textContent = data.files.length;
                    } else {
                        console.error('Failed to load HTML files:', data.message);
                        const htmlCountEl = document.getElementById('html-count');
                        if (htmlCountEl) htmlCountEl.textContent = '0';
                    }
                })
                .catch(error => {
                    console.error('Error loading HTML files:', error);
                    const htmlCountEl = document.getElementById('html-count');
                    if (htmlCountEl) htmlCountEl.textContent = '0';
                });
        }

        // Display HTML files in a table
        function displayHtmlFiles(files) {
            const container = document.getElementById('html-files-list');
            
            if (files.length === 0) {
                container.innerHTML = '<div class="empty-state"><h3>No HTML files found</h3></div>';
                return;
            }
            
            let html = '<div class="table-container"><table><thead><tr>';
            html += '<th>Filename</th><th>Title</th><th>Size</th><th>Last Modified</th><th>Actions</th>';
            html += '</tr></thead><tbody>';
            
            files.forEach(file => {
                const size = (file.size / 1024).toFixed(2) + ' KB';
                const modified = new Date(file.modified * 1000).toLocaleString();
                html += `<tr>
                    <td><code>${file.filename}</code></td>
                    <td>${file.title}</td>
                    <td>${size}</td>
                    <td>${modified}</td>
                    <td>
                        <button class="btn btn-primary btn-sm" onclick="openHtmlEditor('${file.filename}')">Edit</button>
                        <a href="/${file.filename}" target="_blank" class="btn btn-secondary btn-sm">View</a>
                    </td>
                </tr>`;
            });
            
            html += '</tbody></table></div>';
            container.innerHTML = html;
        }

        // Open HTML file editor
        function openHtmlEditor(filename) {
            fetch('api/read_html_file.php?filename=' + encodeURIComponent(filename))
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('htmlFilename').value = data.filename;
                        document.getElementById('editingFilename').textContent = data.filename;
                        document.getElementById('htmlContent').value = data.content;
                        document.getElementById('htmlEditorModal').classList.add('show');
                    } else {
                        alert('Error loading file: ' + data.message);
                    }
                });
        }

        // Close HTML editor modal
        function closeHtmlEditorModal() {
            document.getElementById('htmlEditorModal').classList.remove('show');
        }

        // Save HTML file
        function saveHtmlFile(event) {
            event.preventDefault();
            
            const formData = new FormData(document.getElementById('htmlEditorForm'));
            
            fetch('api/save_html_file.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('File saved successfully! A backup was created.');
                    closeHtmlEditorModal();
                    loadHtmlFiles();
                } else {
                    alert('Error saving file: ' + data.message);
                }
            });
        }

        // New Page functions
        function openNewPageModal() {
            document.getElementById('newPageForm').reset();
            document.getElementById('newPageFilename').textContent = 'pagename.html';
            document.getElementById('newPageModal').style.display = 'flex';
        }

        function closeNewPageModal() {
            document.getElementById('newPageModal').style.display = 'none';
        }

        // Update filename preview as user types
        document.addEventListener('DOMContentLoaded', function() {
            const nameInput = document.getElementById('newPageName');
            if (nameInput) {
                nameInput.addEventListener('input', function() {
                    const filename = this.value.toLowerCase().replace(/[^a-z0-9_-]/g, '') + '.html';
                    document.getElementById('newPageFilename').textContent = filename || 'pagename.html';
                });
            }
        });

        function createNewPage(event) {
            event.preventDefault();
            
            const pageName = document.getElementById('newPageName').value.toLowerCase().replace(/[^a-z0-9_-]/g, '');
            const pageTitle = document.getElementById('newPageTitle').value;
            
            if (!pageName) {
                alert('Please enter a valid page name');
                return;
            }
            
            const filename = pageName + '.html';
            
            // Create basic HTML template
            const htmlTemplate = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${pageTitle} - Content Catalogz</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.html">Home</a>
            <a href="about.html">About</a>
            <a href="${filename}" class="active">${pageTitle}</a>
            <a href="quote.html">Get a Quote</a>
        </nav>
    </header>

    <main>
        <section class="hero">
            <h1>${pageTitle}</h1>
            <p>Add your content here.</p>
        </section>

        <section class="content">
            <p>Edit this page to add your content.</p>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 Content Catalogz. All rights reserved.</p>
    </footer>
</body>
</html>`;

            const formData = new FormData();
            formData.append('filename', filename);
            formData.append('content', htmlTemplate);
            
            fetch('api/save_html_file.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Page created successfully!');
                    closeNewPageModal();
                    loadHtmlFiles();
                    showSection('html-files');
                } else {
                    alert('Error creating page: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error creating page');
            });
        }

        // Filter quotes by clicking on stat cards
        function filterQuotesByStatus(status) {
            document.getElementById('statusFilter').value = status;
            loadQuotes();
        }

        // Load quotes from database
        function loadQuotes() {
            const status = document.getElementById('statusFilter').value;
            const search = document.getElementById('searchQuotes').value;
            
            let url = 'api/get_quotes.php?';
            if (status !== 'all') {
                url += 'status=' + status + '&';
            }
            if (search) {
                url += 'search=' + encodeURIComponent(search);
            }
            
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        displayQuotes(data.quotes);
                        updateQuoteStats(data.stats);
                    } else {
                        console.error('Failed to load quotes:', data.message);
                        document.getElementById('quotes-list').innerHTML = '<div class="empty-state"><h3>Error Loading Quotes</h3><p>' + (data.message || 'Unknown error') + '</p></div>';
                    }
                })
                .catch(error => {
                    console.error('Error loading quotes:', error);
                    document.getElementById('quotes-list').innerHTML = '<div class="empty-state"><h3>Error Loading Quotes</h3><p>Network error: ' + error.message + '</p></div>';
                });
        }

        // Update statistics display
        function updateQuoteStats(stats) {
            document.getElementById('stat-total').textContent = stats.total || 0;
            document.getElementById('stat-new').textContent = stats.new || 0;
            document.getElementById('stat-contacted').textContent = stats.contacted || 0;
            document.getElementById('stat-inprogress').textContent = stats.in_progress || 0;
            document.getElementById('stat-completed').textContent = stats.completed || 0;
            document.getElementById('stat-declined').textContent = stats.declined || 0;
            document.getElementById('quotes-count').textContent = stats.total || 0;
        }

        // Display quotes in table
        function displayQuotes(quotes) {
            const container = document.getElementById('quotes-list');
            
            if (quotes.length === 0) {
                container.innerHTML = '<div class="empty-state"><h3>No quotes found</h3><p>No quote requests match your filter criteria.</p></div>';
                return;
            }
            
            const statusColors = {
                'new': '#007bff',
                'contacted': '#ffc107',
                'in_progress': '#17a2b8',
                'completed': '#28a745',
                'declined': '#dc3545'
            };
            
            let html = '<div class="table-container"><table><thead><tr>';
            html += '<th>Name</th><th>Company</th><th>Email</th><th>Phone</th><th>Service</th><th>Status</th><th>Received</th><th>Actions</th>';
            html += '</tr></thead><tbody>';
            
            quotes.forEach(quote => {
                const statusColor = statusColors[quote.status] || '#666';
                const statusLabel = quote.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
                const receivedDate = new Date(quote.created_at).toLocaleDateString();
                
                html += `<tr>
                    <td><strong>${escapeHtml(quote.name)}</strong></td>
                    <td>${quote.company ? escapeHtml(quote.company) : '<em>N/A</em>'}</td>
                    <td><a href="mailto:${escapeHtml(quote.email)}">${escapeHtml(quote.email)}</a></td>
                    <td>${quote.phone ? escapeHtml(quote.phone) : '<em>N/A</em>'}</td>
                    <td>${escapeHtml(quote.service)}</td>
                    <td><span style="display: inline-block; padding: 4px 12px; border-radius: 12px; background: ${statusColor}; color: white; font-size: 12px; font-weight: 600;">${statusLabel}</span></td>
                    <td>${receivedDate}</td>
                    <td>
                        <button class="btn btn-primary btn-sm" onclick="openQuoteModal(${quote.id})">View</button>
                    </td>
                </tr>`;
            });
            
            html += '</tbody></table></div>';
            container.innerHTML = html;
        }

        // HTML escape helper
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Open quote detail modal
        function openQuoteModal(quoteId) {
            fetch('api/get_quotes.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const quote = data.quotes.find(q => q.id === quoteId);
                        if (quote) {
                            document.getElementById('quoteId').value = quote.id;
                            document.getElementById('quoteName').textContent = quote.name;
                            document.getElementById('quoteCompany').textContent = quote.company || 'N/A';
                            document.getElementById('quoteEmail').textContent = quote.email;
                            document.getElementById('quotePhone').textContent = quote.phone || 'N/A';
                            document.getElementById('quoteService').textContent = quote.service;
                            document.getElementById('quoteMessage').textContent = quote.message;
                            document.getElementById('quoteReceived').textContent = new Date(quote.created_at).toLocaleString();
                            document.getElementById('quoteStatus').value = quote.status;
                            document.getElementById('quoteNotes').value = quote.notes || '';
                            
                            // Load services
                            const services = quote.services || [];
                            const servicesContainer = document.getElementById('quoteServicesContainer');
                            servicesContainer.innerHTML = '';
                            
                            if (services.length === 0) {
                                addQuoteServiceRow();
                            } else {
                                services.forEach(service => {
                                    addQuoteServiceRow(service.name, service.cost);
                                });
                            }
                            
                            calculateQuoteTotalCost();
                            
                            document.getElementById('quoteModal').classList.add('show');
                        } else {
                            alert('Quote not found');
                        }
                    } else {
                        alert('Error loading quote: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    alert('Network error: ' + error.message);
                });
        }

        // Close quote modal
        function closeQuoteModal() {
            document.getElementById('quoteModal').classList.remove('show');
        }

        // Add a service row to the quote modal
        function addQuoteServiceRow(serviceName = '', serviceCost = 0) {
            const container = document.getElementById('quoteServicesContainer');
            const rowId = 'quote-service-row-' + Date.now();
            
            const row = document.createElement('div');
            row.id = rowId;
            row.className = 'quote-service-row';
            row.style.cssText = 'display: grid; grid-template-columns: 2fr 1fr auto; gap: 10px; margin-bottom: 10px; align-items: end;';
            
            row.innerHTML = `
                <div class="form-group" style="margin: 0;">
                    <label>Service Description</label>
                    <input type="text" class="form-control quote-service-name" placeholder="e.g., Website Design" oninput="calculateQuoteTotalCost()">
                </div>
                <div class="form-group" style="margin: 0;">
                    <label>Cost (£)</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-weight: 500; color: #333;">£</span>
                        <input type="number" class="form-control quote-service-cost" step="0.01" min="0" placeholder="0.00" oninput="calculateQuoteTotalCost()" style="padding-left: 28px;">
                    </div>
                </div>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeQuoteServiceRow('${rowId}')" style="height: 38px;">Remove</button>
            `;
            
            container.appendChild(row);
            
            // Set values after DOM insertion to avoid escaping issues
            const nameInput = row.querySelector('.quote-service-name');
            const costInput = row.querySelector('.quote-service-cost');
            if (nameInput) nameInput.value = serviceName;
            if (costInput) costInput.value = serviceCost;
        }

        function removeQuoteServiceRow(rowId) {
            const row = document.getElementById(rowId);
            if (row) {
                row.remove();
                calculateQuoteTotalCost();
            }
        }

        function calculateQuoteTotalCost() {
            let total = 0;
            document.querySelectorAll('#quoteServicesContainer .quote-service-cost').forEach(input => {
                total += parseFloat(input.value) || 0;
            });
            document.getElementById('quoteTotalCost').value = total.toFixed(2);
        }

        function emailQuote() {
            const quoteId = document.getElementById('quoteId').value;
            const clientName = document.getElementById('quoteName').textContent;
            const clientEmail = document.getElementById('quoteEmail').textContent;
            
            // Collect services for the quote
            const services = [];
            document.querySelectorAll('#quoteServicesContainer .quote-service-row').forEach(row => {
                const name = row.querySelector('.quote-service-name').value.trim();
                const cost = parseFloat(row.querySelector('.quote-service-cost').value) || 0;
                if (name) {
                    services.push({ name, cost });
                }
            });
            
            const totalCost = document.getElementById('quoteTotalCost').value || 0;
            
            if (services.length === 0) {
                alert('Please add at least one service before emailing the quote.');
                return;
            }
            
            if (!confirm(`Send quote to ${clientName} at ${clientEmail}?`)) {
                return;
            }
            
            // First, save the quote
            const formData = new FormData(document.getElementById('quoteForm'));
            formData.append('services', JSON.stringify(services));
            formData.append('total_cost', totalCost);
            
            fetch('api/update_quote.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Failed to save quote');
                }
                // Quote saved, now send email
                return fetch('api/email_quote.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        quote_id: quoteId,
                        services: services,
                        total_cost: totalCost
                    })
                });
            })
            .then(response => response.json())
            .then(data => {
                loadQuotes(); // Refresh list
                if (data.success) {
                    alert('Quote saved and emailed successfully to ' + clientEmail);
                    
                    // Log email activity
                    const activityData = {
                        client_id: quoteId,
                        type: 'email',
                        subject: `Quote Emailed to ${clientName}`,
                        description: `Quote sent to ${clientEmail}. Total: £${totalCost}. Services: ${services.map(s => s.name).join(', ')}`,
                        activity_date: new Date().toISOString().slice(0, 19).replace('T', ' ')
                    };
                    
                    fetch('api/activities.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify(activityData)
                    })
                    .catch(err => console.error('Error logging activity:', err));
                    
                } else if (data.fallback) {
                    // Server mail not configured - open email client
                    const mailtoLink = 'mailto:' + encodeURIComponent(data.email) + 
                        '?subject=' + encodeURIComponent(data.subject) + 
                        '&body=' + encodeURIComponent(data.body);
                    window.open(mailtoLink, '_blank');
                    alert('Quote saved. Email client opened.');
                } else {
                    alert('Quote saved but email failed: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error: ' + error.message);
            });
        }

        function confirmDeleteQuote() {
            const quoteName = document.getElementById('quoteName').textContent;
            document.getElementById('deleteQuoteName').textContent = quoteName;
            document.getElementById('deleteQuoteModal').classList.add('show');
        }

        function closeDeleteQuoteModal() {
            document.getElementById('deleteQuoteModal').classList.remove('show');
        }

        function deleteQuote() {
            const quoteId = document.getElementById('quoteId').value;
            const quoteName = document.getElementById('quoteName').textContent;
            
            fetch('api/delete_quote.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ quote_id: quoteId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Quote from "' + quoteName + '" has been deleted.');
                    closeDeleteQuoteModal();
                    closeQuoteModal();
                    loadQuotes(); // Refresh the quotes list
                    loadDashboardStats(); // Refresh stats
                } else {
                    alert('Error deleting quote: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete quote');
            });
        }

        // Update quote status and notes
        function updateQuote(event) {
            event.preventDefault();
            
            // Collect services
            const services = [];
            document.querySelectorAll('#quoteServicesContainer .quote-service-row').forEach(row => {
                const name = row.querySelector('.quote-service-name').value.trim();
                const cost = parseFloat(row.querySelector('.quote-service-cost').value) || 0;
                if (name) {
                    services.push({ name, cost });
                }
            });
            
            const formData = new FormData(document.getElementById('quoteForm'));
            formData.append('services', JSON.stringify(services));
            formData.append('total_cost', document.getElementById('quoteTotalCost').value || 0);
            
            fetch('api/update_quote.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    closeQuoteModal();
                    loadQuotes();
                    loadExistingClients();
                    loadDashboardStats();
                    
                    if (data.client_created) {
                        // A new client was created
                        if (confirm('Client "' + data.client_name + '" created successfully!\n\nWould you like to view the client details?')) {
                            viewClientDetails(data.client_id);
                        }
                    } else {
                        alert('Quote updated successfully!');
                    }
                } else {
                    alert('Error updating quote: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                alert('Network error: ' + error.message);
            });
        }

        // Load existing clients (from new, in progress, and completed quotes)
        function loadExistingClients() {
            const search = document.getElementById('searchClients').value;
            
            // Fetch all quotes except 'contacted' and 'declined' to show as existing clients
            fetch('api/get_quotes.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Filter to show only new, in_progress, and completed
                        let filteredClients = data.quotes.filter(quote => 
                            quote.status === 'new' || 
                            quote.status === 'in_progress' || 
                            quote.status === 'completed'
                        );
                        
                        // Apply search filter if provided
                        if (search) {
                            const searchLower = search.toLowerCase();
                            filteredClients = filteredClients.filter(client =>
                                client.name.toLowerCase().includes(searchLower) ||
                                client.email.toLowerCase().includes(searchLower) ||
                                (client.company && client.company.toLowerCase().includes(searchLower))
                            );
                        }
                        
                        displayExistingClients(filteredClients);
                        document.getElementById('active-clients-count').textContent = filteredClients.length;
                        
                        // Count total projects (completed only)
                        const completedCount = data.quotes.filter(q => q.status === 'completed').length;
                        document.getElementById('total-projects-count').textContent = completedCount;
                    } else {
                        console.error('Failed to load clients:', data.message);
                        document.getElementById('existing-clients-list').innerHTML = '<div class="empty-state"><h3>Error Loading Clients</h3><p>' + (data.message || 'Unknown error') + '</p></div>';
                    }
                })
                .catch(error => {
                    console.error('Error loading clients:', error);
                    document.getElementById('existing-clients-list').innerHTML = '<div class="empty-state"><h3>Error Loading Clients</h3><p>Network error: ' + error.message + '</p></div>';
                });
        }

        // Display existing clients in table
        function displayExistingClients(clients) {
            const container = document.getElementById('existing-clients-list');
            
            if (clients.length === 0) {
                container.innerHTML = '<div class="empty-state"><h3>No Active Clients Yet</h3><p>Clients with active quotes will appear here automatically.</p></div>';
                return;
            }
            
            const statusColors = {
                'new': '#007bff',
                'in_progress': '#17a2b8',
                'completed': '#28a745'
            };
            
            let html = '<div class="table-container"><table><thead><tr>';
            html += '<th>Name</th><th>Company</th><th>Email</th><th>Phone</th><th>Service</th><th>Status</th><th>Date</th><th>Actions</th>';
            html += '</tr></thead><tbody>';
            
            clients.forEach(client => {
                const clientDate = new Date(client.updated_at || client.created_at).toLocaleDateString();
                const statusColor = statusColors[client.status] || '#666';
                const statusLabel = client.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
                
                html += `<tr>
                    <td><strong>${escapeHtml(client.name)}</strong></td>
                    <td>${client.company ? escapeHtml(client.company) : '<em>N/A</em>'}</td>
                    <td><a href="mailto:${escapeHtml(client.email)}">${escapeHtml(client.email)}</a></td>
                    <td>${client.phone ? escapeHtml(client.phone) : '<em>N/A</em>'}</td>
                    <td>${escapeHtml(client.service)}</td>
                    <td><span style="display: inline-block; padding: 4px 12px; border-radius: 12px; background: ${statusColor}; color: white; font-size: 12px; font-weight: 600;">${statusLabel}</span></td>
                    <td>${clientDate}</td>
                    <td>
                        <button class="btn btn-primary btn-sm" onclick="viewClientDetails(${client.id})">View</button>
                        <button class="btn btn-secondary btn-sm" onclick="viewClientDetails(${client.id})">Edit</button>
                        <a href="mailto:${escapeHtml(client.email)}" class="btn btn-secondary btn-sm">Email</a>
                    </td>
                </tr>`;
            });
            
            html += '</tbody></table></div>';
            container.innerHTML = html;
        }

        // View client details (reuse quote modal)
        function viewClientDetails(clientId) {
            // Fetch client data
            fetch('api/get_client.php?id=' + clientId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        openClientModal(data.client);
                    } else {
                        alert('Error loading client details: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load client details');
                });
        }

        function openClientModal(client) {
            // Set current client ID for CRM functions
            currentClientId = client.id;
            
            document.getElementById('clientId').value = client.id;
            
            // Set client name in modal header
            document.getElementById('clientModalName').textContent = client.name;
            
            document.getElementById('clientName').textContent = client.name;
            document.getElementById('clientCompany').textContent = client.company || 'N/A';
            document.getElementById('clientEmail').textContent = client.email;
            document.getElementById('clientPhone').textContent = client.phone || 'N/A';
            
            // Load address fields
            document.getElementById('clientAddressStreet').value = client.address_street || '';
            document.getElementById('clientAddressLine2').value = client.address_line2 || '';
            document.getElementById('clientAddressCity').value = client.address_city || '';
            document.getElementById('clientAddressCounty').value = client.address_county || '';
            document.getElementById('clientAddressPostcode').value = client.address_postcode || '';
            document.getElementById('clientAddressCountry').value = client.address_country || 'United Kingdom';
            
            document.getElementById('totalPaid').value = client.total_paid || 0.00;
            
            // Load services
            const services = client.services || [];
            const servicesContainer = document.getElementById('servicesContainer');
            servicesContainer.innerHTML = '';
            
            if (services.length === 0) {
                // Add one empty row by default
                addServiceRow();
            } else {
                services.forEach(service => {
                    addServiceRow(service.name, service.cost);
                });
            }
            
            calculateTotalCost();
            calculateRemaining();
            
            // Reset to Details tab
            document.querySelectorAll('.client-tab-content').forEach(tab => {
                tab.classList.remove('active');
                tab.style.display = 'none';
            });
            document.querySelectorAll('.crm-tab').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show Details tab by default
            document.getElementById('client-tab-details').style.display = 'block';
            document.getElementById('client-tab-details').classList.add('active');
            document.querySelector('.crm-tab[onclick*="details"]').classList.add('active');
            
            // Load payment history
            loadClientPayments(currentClientId);
            
            document.getElementById('clientModal').classList.add('show');
        }

        function closeClientModal() {
            document.getElementById('clientModal').classList.remove('show');
        }

        function confirmDeleteClient() {
            const clientName = document.getElementById('clientModalName').textContent;
            document.getElementById('deleteClientName').textContent = clientName;
            document.getElementById('deleteClientModal').classList.add('show');
        }

        function closeDeleteClientModal() {
            document.getElementById('deleteClientModal').classList.remove('show');
        }

        function deleteClient() {
            const clientId = document.getElementById('clientId').value;
            const clientName = document.getElementById('clientModalName').textContent;
            
            fetch('api/delete_client.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ client_id: clientId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Client "' + clientName + '" and all related data have been deleted.');
                    closeDeleteClientModal();
                    closeClientModal();
                    loadQuotes(); // Refresh the clients list
                    loadDashboardStats(); // Refresh stats
                } else {
                    alert('Error deleting client: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete client');
            });
        }

        function addServiceRow(serviceName = '', serviceCost = 0) {
            const container = document.getElementById('servicesContainer');
            const rowId = 'service-row-' + Date.now();
            
            const row = document.createElement('div');
            row.id = rowId;
            row.className = 'service-row';
            row.style.cssText = 'display: grid; grid-template-columns: 2fr 1fr auto; gap: 10px; margin-bottom: 10px; align-items: end;';
            
            row.innerHTML = `
                <div class="form-group" style="margin: 0;">
                    <label>Service Description</label>
                    <input type="text" class="form-control service-name" placeholder="e.g., Website Design" oninput="calculateTotalCost()">
                </div>
                <div class="form-group" style="margin: 0;">
                    <label>Cost (£)</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-weight: 500; color: #333;">£</span>
                        <input type="number" class="form-control service-cost" step="0.01" min="0" placeholder="0.00" oninput="calculateTotalCost()" style="padding-left: 28px;">
                    </div>
                </div>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeServiceRow('${rowId}')" style="height: 38px;">Remove</button>
            `;
            
            container.appendChild(row);
            
            // Set values after DOM insertion to avoid escaping issues
            const nameInput = row.querySelector('.service-name');
            const costInput = row.querySelector('.service-cost');
            if (nameInput) nameInput.value = serviceName;
            if (costInput) costInput.value = serviceCost;
        }

        function removeServiceRow(rowId) {
            const row = document.getElementById(rowId);
            if (row) {
                row.remove();
                calculateTotalCost();
            }
        }

        function calculateTotalCost() {
            const serviceCosts = document.querySelectorAll('.service-cost');
            let total = 0;
            
            serviceCosts.forEach(input => {
                const value = parseFloat(input.value) || 0;
                total += value;
            });
            
            document.getElementById('totalCost').value = total.toFixed(2);
            calculateRemaining();
        }

        function calculateRemaining() {
            const totalCost = parseFloat(document.getElementById('totalCost').value) || 0;
            const totalPaid = parseFloat(document.getElementById('totalPaid').value) || 0;
            const remaining = totalCost - totalPaid;
            
            // Always store the actual calculated value
            document.getElementById('totalRemaining').value = remaining.toFixed(2);
            
            // Update label, color code, and help text based on balance
            const remainingInput = document.getElementById('totalRemaining');
            const remainingLabel = document.getElementById('totalRemainingLabel');
            const remainingCurrency = document.getElementById('totalRemainingCurrency');
            const remainingHelp = document.getElementById('totalRemainingHelp');
            
            if (remaining > 0) {
                // Outstanding balance
                remainingInput.style.color = '#dc3545'; // Red
                remainingCurrency.style.color = '#dc3545';
                remainingLabel.textContent = 'Balance Due (£)';
                remainingHelp.style.display = 'none';
            } else if (remaining < 0) {
                // Account credit - negative value indicates credit
                remainingInput.style.color = '#28a745'; // Green
                remainingCurrency.style.color = '#28a745';
                remainingLabel.textContent = 'Account Credit (£)';
                remainingHelp.style.display = 'block';
                remainingHelp.style.color = '#28a745';
                remainingHelp.textContent = 'Client has paid £' + Math.abs(remaining).toFixed(2) + ' in advance';
            } else {
                // Paid in full
                remainingInput.style.color = '#28a745'; // Green
                remainingCurrency.style.color = '#28a745';
                remainingLabel.textContent = 'Balance (£)';
                remainingHelp.style.display = 'block';
                remainingHelp.style.color = '#28a745';
                remainingHelp.textContent = 'Paid in full ✓';
            }
        }

        function updateClient(event) {
            event.preventDefault();
            
            const clientId = document.getElementById('clientId').value;
            const addressStreet = document.getElementById('clientAddressStreet').value;
            const addressLine2 = document.getElementById('clientAddressLine2').value;
            const addressCity = document.getElementById('clientAddressCity').value;
            const addressCounty = document.getElementById('clientAddressCounty').value;
            const addressPostcode = document.getElementById('clientAddressPostcode').value;
            const addressCountry = document.getElementById('clientAddressCountry').value;
            const totalPaid = parseFloat(document.getElementById('totalPaid').value) || 0;
            const totalCost = parseFloat(document.getElementById('totalCost').value) || 0;
            
            // Collect services
            const services = [];
            const serviceRows = document.querySelectorAll('.service-row');
            serviceRows.forEach(row => {
                const name = row.querySelector('.service-name').value.trim();
                const cost = parseFloat(row.querySelector('.service-cost').value) || 0;
                
                if (name) {  // Only add if service has a name
                    services.push({ name, cost });
                }
            });
            
            const data = {
                id: clientId,
                address_street: addressStreet,
                address_line2: addressLine2,
                address_city: addressCity,
                address_county: addressCounty,
                address_postcode: addressPostcode,
                address_country: addressCountry,
                services: services,
                total_cost: totalCost,
                total_paid: totalPaid
            };
            
            fetch('api/update_client.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error('Server error: ' + response.status + ' - ' + text);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('Client information updated successfully!');
                    closeClientModal();
                    loadExistingClients(); // Refresh the clients list
                } else {
                    alert('Error: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to update client information: ' + error.message);
            });
        }

        function generateInvoiceForClient() {
            const clientId = document.getElementById('clientId').value;
            const clientName = document.getElementById('clientName').textContent;
            const totalCost = parseFloat(document.getElementById('totalCost').value) || 0;
            const totalPaid = parseFloat(document.getElementById('totalPaid').value) || 0;
            
            if (totalCost === 0) {
                alert('Cannot generate invoice: Total cost is £0.00. Please add services first.');
                return;
            }
            
            const invoiceDate = new Date().toISOString().split('T')[0];
            // Deterministic invoice number: INV-<YEAR>-<CLIENTID>-<DATE>
            const invoiceNumber = `INV-${new Date().getFullYear()}-${clientId}-${invoiceDate.replace(/-/g, '')}`;
            
            fetch('api/save_invoice.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    client_id: clientId,
                    invoice_number: invoiceNumber,
                    invoice_date: invoiceDate,
                    total_cost: totalCost,
                    total_paid: totalPaid
                })
            })
            .then(response => {
                // Log the response for debugging
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                // Try to get the response as text first to see what we're getting
                return response.text().then(text => {
                    console.log('Response text:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('JSON parse error:', e);
                        console.error('Response was not valid JSON:', text);
                        throw new Error('Server returned invalid JSON: ' + text.substring(0, 100));
                    }
                });
            })
            .then(data => {
                if (data.success) {
                    if (data.exists) {
                        showNotification('Invoice already exists for this client', 'info');
                    } else {
                        showNotification('Invoice ' + invoiceNumber + ' generated successfully for ' + clientName + '!', 'success');
                        // Reload invoice stats
                        loadInvoiceStats();
                        loadDashboardStats();
                    }
                } else {
                    alert('Failed to generate invoice: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to generate invoice: ' + error.message);
            });
        }

        async function printInvoice() {
            // Get current client data from the form
            const clientId = document.getElementById('clientId').value;
            const clientName = document.getElementById('clientName').textContent;
            const clientCompany = document.getElementById('clientCompany').textContent;
            const clientEmail = document.getElementById('clientEmail').textContent;
            const clientPhone = document.getElementById('clientPhone').textContent;
            
            // Get structured address
            const addressStreet = document.getElementById('clientAddressStreet').value || '';
            const addressLine2 = document.getElementById('clientAddressLine2').value || '';
            const addressCity = document.getElementById('clientAddressCity').value || '';
            const addressCounty = document.getElementById('clientAddressCounty').value || '';
            const addressPostcode = document.getElementById('clientAddressPostcode').value || '';
            const addressCountry = document.getElementById('clientAddressCountry').value || 'United Kingdom';
            
            // Fetch payment history
            let paymentsHTML = '';
            try {
                const response = await fetch('api/activities.php?client_id=' + clientId);
                const data = await response.json();
                const payments = data.activities ? data.activities.filter(a => a.type === 'payment_received') : [];
                
                if (payments.length > 0) {
                    payments.sort((a, b) => new Date(b.activity_date) - new Date(a.activity_date));
                    
                    paymentsHTML = `
                        <h3 style="margin-top: 30px; margin-bottom: 15px; color: #333;">Payment History</h3>
                        <table style="margin-bottom: 30px;">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th style="text-align: right;">Amount</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                    
                    payments.forEach(payment => {
                        const date = new Date(payment.activity_date);
                        const dateStr = date.toLocaleDateString('en-GB', { 
                            day: '2-digit', 
                            month: 'short', 
                            year: 'numeric' 
                        });
                        
                        const amountMatch = payment.subject.match(/£([\\d,]+\\.\\d{2})/);
                        const amount = amountMatch ? amountMatch[1] : '0.00';
                        
                        paymentsHTML += `
                            <tr>
                                <td style="padding: 12px; border-bottom: 1px solid #ddd;">${dateStr}</td>
                                <td style="padding: 12px; border-bottom: 1px solid #ddd; text-align: right; font-weight: 600; color: #28a745;">£${amount}</td>
                                <td style="padding: 12px; border-bottom: 1px solid #ddd;">${escapeHtml(payment.description || '')}</td>
                            </tr>
                        `;
                    });
                    
                    paymentsHTML += '</tbody></table>';
                }
            } catch (error) {
                console.error('Error fetching payment history:', error);
            }
            
            // Format address
            let formattedAddress = '';
            if (addressStreet) formattedAddress += addressStreet + '<br>';
            if (addressLine2) formattedAddress += addressLine2 + '<br>';
            if (addressCity || addressCounty || addressPostcode) {
                let cityLine = '';
                if (addressCity) cityLine += addressCity;
                if (addressCounty) cityLine += (cityLine ? ', ' : '') + addressCounty;
                if (addressPostcode) cityLine += (cityLine ? ', ' : '') + addressPostcode;
                formattedAddress += cityLine + '<br>';
            }
            if (addressCountry) formattedAddress += addressCountry;
            if (!formattedAddress) formattedAddress = 'N/A';
            
            const totalCost = parseFloat(document.getElementById('totalCost').value) || 0;
            const totalPaid = parseFloat(document.getElementById('totalPaid').value) || 0;
            const totalRemaining = parseFloat(document.getElementById('totalRemaining').value) || 0;
            
            // Collect services
            const services = [];
            const serviceRows = document.querySelectorAll('.service-row');
            serviceRows.forEach(row => {
                const name = row.querySelector('.service-name').value.trim();
                const cost = parseFloat(row.querySelector('.service-cost').value) || 0;
                if (name) {
                    services.push({ name, cost });
                }
            });
            
            // Generate invoice HTML
            const invoiceDate = new Date().toLocaleDateString('en-GB');
            const invoiceNumber = 'INV-' + Date.now();
            
            // Save invoice to database
            const invoiceDateISO = new Date().toISOString().split('T')[0];
            fetch('api/save_invoice.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    client_id: clientId,
                    invoice_number: invoiceNumber,
                    invoice_date: invoiceDateISO,
                    total_cost: totalCost,
                    total_paid: totalPaid
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success && !data.exists) {
                    console.error('Failed to save invoice:', data.message);
                }
            })
            .catch(error => {
                console.error('Error saving invoice:', error);
            });
            
            let servicesHTML = '';
            services.forEach(service => {
                servicesHTML += `
                    <tr>
                        <td style="padding: 12px; border-bottom: 1px solid #ddd;">${escapeHtml(service.name)}</td>
                        <td style="padding: 12px; border-bottom: 1px solid #ddd; text-align: right; font-weight: 600;">£${service.cost.toFixed(2)}</td>
                    </tr>
                `;
            });
            
            const invoiceHTML = `
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <title>Invoice - ${clientName}</title>
                    <style>
                        @media print {
                            body { margin: 0; }
                            .no-print { display: none; }
                        }
                        body {
                            font-family: Arial, sans-serif;
                            max-width: 800px;
                            margin: 20px auto;
                            padding: 20px;
                            color: #333;
                        }
                        .invoice-header {
                            display: flex;
                            justify-content: space-between;
                            margin-bottom: 30px;
                            padding-bottom: 20px;
                            border-bottom: 3px solid #ff69b4;
                        }
                        .company-info h1 {
                            margin: 0;
                            color: #ff69b4;
                            font-size: 28px;
                        }
                        .invoice-details {
                            text-align: right;
                        }
                        .invoice-details p {
                            margin: 5px 0;
                        }
                        .client-info {
                            background: #f8f9fa;
                            padding: 20px;
                            border-radius: 8px;
                            margin-bottom: 30px;
                        }
                        .client-info h3 {
                            margin-top: 0;
                            color: #333;
                        }
                        table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-bottom: 30px;
                        }
                        th {
                            background: #ff69b4;
                            color: white;
                            padding: 12px;
                            text-align: left;
                        }
                        .totals {
                            margin-left: auto;
                            width: 300px;
                        }
                        .totals tr td {
                            padding: 8px;
                            border-bottom: 1px solid #ddd;
                        }
                        .totals tr:last-child td {
                            border-top: 2px solid #333;
                            font-weight: bold;
                            font-size: 18px;
                            color: #dc3545;
                        }
                        .print-btn {
                            background: #ff69b4;
                            color: white;
                            border: none;
                            padding: 12px 24px;
                            border-radius: 4px;
                            cursor: pointer;
                            font-size: 16px;
                            margin-bottom: 20px;
                        }
                        .print-btn:hover {
                            background: #ff85c1;
                        }
                        .edit-btn {
                            background: #667eea;
                            color: white;
                            border: none;
                            padding: 12px 24px;
                            border-radius: 4px;
                            cursor: pointer;
                            font-size: 16px;
                            margin-bottom: 20px;
                            margin-right: 10px;
                        }
                        .edit-btn:hover {
                            background: #5568d3;
                        }
                        .save-btn {
                            background: #28a745;
                            color: white;
                            border: none;
                            padding: 12px 24px;
                            border-radius: 4px;
                            cursor: pointer;
                            font-size: 16px;
                            margin-bottom: 20px;
                            margin-right: 10px;
                            display: none;
                        }
                        .save-btn:hover {
                            background: #218838;
                        }
                        .editable {
                            border: 2px dashed transparent;
                            padding: 2px 4px;
                            min-width: 200px;
                            display: inline-block;
                        }
                        .editable.editing {
                            border-color: #ff69b4;
                            background: #fff;
                        }
                        input.invoice-edit {
                            width: 100%;
                            padding: 4px 8px;
                            border: 1px solid #ddd;
                            border-radius: 4px;
                        }
                    </style>
                </head>
                <body>
                    <button class="edit-btn no-print" onclick="toggleEditMode()">✏️ Edit Invoice</button>
                    <button class="save-btn no-print" id="saveInvoiceBtn" onclick="saveInvoiceEdits()">💾 Save Changes</button>
                    <button class="print-btn no-print" onclick="window.print()">🖨️ Print Invoice</button>
                    
                    <div class="invoice-header">
                        <div class="company-info">
                            <img src="/assets/images/LogoPink.png" alt="Content Catalogz" style="height: 75px; margin-bottom: 10px;">
                            <p>Professional Content Services</p>
                        </div>
                        <div class="invoice-details">
                            <h2 style="margin: 0; color: #ff69b4;">INVOICE</h2>
                            <p><strong>Invoice No:</strong> ${invoiceNumber}</p>
                            <p><strong>Date:</strong> ${invoiceDate}</p>
                        </div>
                    </div>
                    
                    <div class="client-info">
                        <h3>Bill To:</h3>
                        <p><strong>${clientName}</strong></p>
                        ${clientCompany !== 'N/A' ? '<p>' + clientCompany + '</p>' : ''}
                        <p>${formattedAddress}</p>
                        <p>Email: ${clientEmail}</p>
                        ${clientPhone !== 'N/A' ? '<p>Phone: ' + clientPhone + '</p>' : ''}
                    </div>
                    
                    <table>
                        <thead>
                            <tr>
                                <th>Service Description</th>
                                <th style="text-align: right;">Amount (GBP)</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${servicesHTML}
                        </tbody>
                    </table>
                    
                    <table class="totals">
                        <tr>
                            <td><strong>Total Cost:</strong></td>
                            <td style="text-align: right;">£${totalCost.toFixed(2)}</td>
                        </tr>
                        <tr>
                            <td><strong>Total Paid:</strong></td>
                            <td style="text-align: right;">£${totalPaid.toFixed(2)}</td>
                        </tr>
                        <tr>
                            <td><strong>Amount Due:</strong></td>
                            <td style="text-align: right;">£${totalRemaining.toFixed(2)}</td>
                        </tr>
                    </table>
                    
                    ${paymentsHTML}
                    
                    <div style="margin-top: 50px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #666;">
                        <p>Thank you for your business!</p>
                        <p style="font-size: 12px;">Payment is due within 30 days of invoice date.</p>
                    </div>
                    
                    \x3cscript>
                        let isEditMode = false;
                        
                        function toggleEditMode() {
                            isEditMode = !isEditMode;
                            const editBtn = document.querySelector('.edit-btn');
                            const saveBtn = document.querySelector('.save-btn');
                            
                            if (isEditMode) {
                                editBtn.style.display = 'none';
                                saveBtn.style.display = 'inline-block';
                                enableEditing();
                            } else {
                                editBtn.style.display = 'inline-block';
                                saveBtn.style.display = 'none';
                                disableEditing();
                            }
                        }
                        
                        function enableEditing() {
                            // Make all text content editable
                            document.querySelectorAll('.client-info p').forEach(el => {
                                el.contentEditable = true;
                                el.classList.add('editable', 'editing');
                            });
                        }
                        
                        function disableEditing() {
                            document.querySelectorAll('.client-info p').forEach(el => {
                                el.contentEditable = false;
                                el.classList.remove('editing');
                            });
                        }
                        
                        function saveInvoiceEdits() {
                            alert('Invoice changes saved! Note: This is a preview. To permanently update client details, edit them in the Client Details modal and regenerate the invoice.');
                            toggleEditMode();
                        }
                    \x3c/script>
                </body>
                </html>
            `;
            
            // Open invoice in new window
            const invoiceWindow = window.open('', '_blank');
            invoiceWindow.document.write(invoiceHTML);
            invoiceWindow.document.close();
        }

        // Email invoice as PDF
        function emailInvoice() {
            const clientName = document.getElementById('clientName').textContent;
            const clientEmail = document.getElementById('clientEmail').textContent;
            const invoiceNumber = 'INV-' + Date.now();
            const totalCost = document.getElementById('totalCost').value;
            const totalPaid = document.getElementById('totalPaid').value;
            const totalRemaining = document.getElementById('totalRemaining').value;
            
            if (!clientEmail || clientEmail.trim() === '') {
                alert('No email address found for this client.');
                return;
            }
            
            // Collect services
            const services = [];
            document.querySelectorAll('.service-row').forEach(row => {
                const nameInput = row.querySelector('.service-name');
                const costInput = row.querySelector('.service-cost');
                if (nameInput && costInput) {
                    const name = nameInput.value;
                    const cost = costInput.value;
                    if (name && cost) {
                        services.push({ name, cost: parseFloat(cost) });
                    }
                }
            });
            
            // Send to server to generate PDF and email
            fetch('api/email_invoice.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    client_id: currentClientId,
                    client_name: clientName,
                    client_email: clientEmail,
                    invoice_number: invoiceNumber,
                    services: services,
                    total_cost: totalCost,
                    total_paid: totalPaid,
                    total_remaining: totalRemaining
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Invoice sent successfully to ' + clientEmail);
                    
                    // Log email activity
                    const activityData = {
                        client_id: currentClientId,
                        type: 'email',
                        subject: `Invoice ${invoiceNumber} Emailed`,
                        description: `Invoice sent to ${clientEmail}. Total: £${totalCost}, Paid: £${totalPaid}, Remaining: £${totalRemaining}`,
                        activity_date: new Date().toISOString().slice(0, 19).replace('T', ' ')
                    };
                    
                    fetch('api/activities.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify(activityData)
                    })
                    .then(() => {
                        loadClientActivities(currentClientId);
                    })
                    .catch(err => console.error('Error logging activity:', err));
                    
                } else {
                    alert('Error sending invoice: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Error sending invoice. Please try again.');
            });
        }

        // Add New Client modal functions
        function openAddClientModal() {
            document.getElementById('addClientForm').reset();
            document.getElementById('addClientModal').classList.add('show');
        }
        
        function closeAddClientModal() {
            document.getElementById('addClientModal').classList.remove('show');
        }
        
        function saveNewClient(event) {
            event.preventDefault();
            
            const formData = {
                first_name: document.getElementById('newClientFirstName').value,
                last_name: document.getElementById('newClientLastName').value,
                email: document.getElementById('newClientEmail').value,
                company: document.getElementById('newClientCompany').value,
                phone: document.getElementById('newClientPhone').value,
                address_street: document.getElementById('newClientAddressStreet').value,
                address_line2: document.getElementById('newClientAddressLine2').value,
                address_city: document.getElementById('newClientCity').value,
                address_county: document.getElementById('newClientCounty').value,
                address_postcode: document.getElementById('newClientPostcode').value,
                address_country: document.getElementById('newClientCountry').value,
                message: document.getElementById('newClientMessage').value,
                service: document.getElementById('newClientService').value,
                status: document.getElementById('newClientStatus').value,
                notes: document.getElementById('newClientNotes').value
            };
            
            fetch('api/add_client.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(formData)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Client added successfully!');
                    closeAddClientModal();
                    loadExistingClients();
                } else {
                    alert('Error adding client: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Error adding client. Please try again.');
            });
        }

        // Invoice stats and search functions
        function loadInvoiceStats() {
            fetch('api/invoice_stats.php')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('stat-invoices-outstanding-count').textContent = data.outstanding_count || 0;
                        document.getElementById('stat-invoices-outstanding-amount').textContent = '£' + (parseFloat(data.outstanding_amount) || 0).toFixed(2);
                        document.getElementById('stat-invoices-overdue-count').textContent = data.overdue_count || 0;
                        document.getElementById('stat-invoices-overdue-amount').textContent = '£' + (parseFloat(data.overdue_amount) || 0).toFixed(2);
                        document.getElementById('stat-invoices-total').textContent = '£' + (parseFloat(data.total_invoiced) || 0).toFixed(2);
                        document.getElementById('stat-invoices-collected').textContent = '£' + (parseFloat(data.total_collected) || 0).toFixed(2);
                    }
                })
                .catch(err => console.error('Error loading invoice stats:', err));
        }

        function searchInvoices() {
            const searchQuery = document.getElementById('invoiceSearch').value.trim();
            const searchDate = document.getElementById('invoiceDateSearch').value;

            if (!searchQuery && !searchDate) {
                alert('Please enter an invoice number, client name, or select a date to search.');
                return;
            }

            const params = new URLSearchParams();
            if (searchQuery) params.append('q', searchQuery);
            if (searchDate) params.append('date', searchDate);

            fetch('api/search_invoices.php?' + params.toString())
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayInvoiceResults(data.invoices);
                    } else {
                        alert('Error searching invoices: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to search invoices');
                });
        }
        
        function showFilteredInvoices(filter) {
            // Show the invoices section first
            showSection('invoices');
            
            // Then fetch filtered invoices
            const filterLabels = {
                'outstanding': 'Outstanding Invoices',
                'overdue': 'Overdue Invoices',
                'paid': 'Paid Invoices',
                'all': 'All Invoices'
            };
            
            fetch('api/search_invoices.php?filter=' + filter)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayInvoiceResults(data.invoices, filterLabels[filter] || 'Filtered Invoices');
                    } else {
                        alert('Error loading invoices: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load invoices');
                });
        }

        function displayInvoiceResults(invoices, title) {
            const container = document.getElementById('invoices-results');
            const displayTitle = title || 'Search Results';

            if (invoices.length === 0) {
                container.innerHTML = '<div class="empty-state"><h3>No Invoices Found</h3><p>No invoices match your criteria.</p></div>';
                return;
            }

            let html = '<div style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 15px;"><h3 style="color: #333; margin-bottom: 10px;">' + displayTitle + ': ' + invoices.length + ' invoice(s) found</h3></div>';
            html += '<div class="table-container"><table><thead><tr>';
            html += '<th>Invoice Number</th><th>Client Name</th><th>Company</th><th>Invoice Date</th><th>Total Cost</th><th>Total Paid</th><th>Balance Due</th><th>Actions</th>';
            html += '</tr></thead><tbody>';

invoices.forEach(invoice => {
    const invoiceDate = new Date(invoice.invoice_date).toLocaleDateString('en-GB');
    const balanceColor = invoice.total_remaining > 0 ? '#dc3545' : '#28a745';
    
    html += '<tr>';
    html += `<td><a href="#" class="invoice-link" onclick="openInvoiceModal(${invoice.id});return false;"><strong>${escapeHtml(invoice.invoice_number)}</strong></a></td>`;
    html += '<td>' + escapeHtml(invoice.name) + '</td>';
    html += '<td>' + (invoice.company ? escapeHtml(invoice.company) : '<em>N/A</em>') + '</td>';
    html += '<td>' + invoiceDate + '</td>';
    html += '<td>£' + parseFloat(invoice.total_cost).toFixed(2) + '</td>';
    html += '<td>£' + parseFloat(invoice.total_paid).toFixed(2) + '</td>';
    html += '<td style="color: ' + balanceColor + '; font-weight: bold;">£' + parseFloat(invoice.total_remaining).toFixed(2) + '</td>';
    html += `<td><button class="btn btn-primary btn-sm" onclick="openInvoiceModal(${invoice.id})">Edit</button> <button class="btn btn-danger btn-sm" onclick="deleteInvoice(${invoice.id})">Delete</button></td>`;
    html += '</tr>';
});

            html += '</tbody></table></div>';
            container.innerHTML = html;
        }

        function clearInvoiceSearch() {
            document.getElementById('invoiceSearch').value = '';
            document.getElementById('invoiceDateSearch').value = '';
            document.getElementById('invoices-results').innerHTML = '<div class="empty-state"><h3>Search for Invoices</h3><p>Use the search form above to find invoices by number or date.</p></div>';
        }

        // Load HTML files count and quotes on page load
        window.addEventListener('DOMContentLoaded', function() {
            console.log('%c Dashboard Loaded', 'background: #28a745; color: white; font-weight: bold; padding: 4px 12px; border-radius: 4px;');
            
            // Check if required elements exist
            const requiredElements = [
                'section-dashboard',
                'html-count',
                'quotes-count',
                'nav-dashboard'
            ];
            
            let allElementsFound = true;
            requiredElements.forEach(id => {
                const el = document.getElementById(id);
                if (!el) {
                    console.error('❌ Missing required element:', id);
                    allElementsFound = false;
                } else {
                    console.log('✓ Found element:', id);
                }
            });
            
            if (!allElementsFound) {
                console.error('%c Some required elements are missing!', 'color: red; font-weight: bold;');
            }
            
            // Ensure dashboard section is visible
            const dashboardSection = document.getElementById('section-dashboard');
            if (dashboardSection) {
                if (!dashboardSection.classList.contains('active')) {
                    dashboardSection.classList.add('active');
                    dashboardSection.style.display = 'block';
                    console.log('✓ Dashboard section set to active');
                }
            } else {
                console.error('❌ Dashboard section not found!');
            }
            
            // Load initial data
            try {
                console.log('Loading HTML files...');
                loadHtmlFiles();
                
                console.log('Loading quotes...');
                loadQuotes();
                
                console.log('Loading dashboard stats...');
                loadDashboardStats();
                
                console.log('%c All data loading functions called successfully', 'background: #667eea; color: white; padding: 2px 8px; border-radius: 3px;');
            } catch (error) {
                console.error('%c Error during initialization:', 'color: red; font-weight: bold;', error);
            }
        });

        // ==================== Invoice Modal Handlers ====================
        function openInvoiceModal(invoiceId) {
          // TODO: Fetch invoice details via AJAX and populate modal
          document.getElementById('invoiceModalBody').innerHTML = '<p>Loading invoice #' + invoiceId + '...</p>';
          document.getElementById('invoiceModal').classList.add('show');
        }
        function closeInvoiceModal() {
          document.getElementById('invoiceModal').classList.remove('show');
        }
        function deleteInvoice(invoiceId) {
          if (confirm('Are you sure you want to delete this invoice?')) {
            // TODO: Implement AJAX delete
            alert('Delete invoice ' + invoiceId + ' (not yet implemented)');
          }
        }
        
        // Load all dashboard stats
        function loadDashboardStats() {
            // Load task stats
            fetch('api/tasks.php')
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.tasks) {
                        const pending = data.tasks.filter(t => t.status === 'pending').length;
                        const today = new Date().toISOString().split('T')[0];
                        const overdue = data.tasks.filter(t => t.due_date && t.due_date < today && t.status !== 'completed' && t.status !== 'cancelled').length;
                        const urgent = data.tasks.filter(t => t.priority === 'urgent' && t.status !== 'completed' && t.status !== 'cancelled').length;
                        
                        document.getElementById('dash-tasks-pending').textContent = pending;
                        document.getElementById('dash-tasks-overdue').textContent = overdue;
                        document.getElementById('dash-tasks-urgent').textContent = urgent;
                    }
                })
                .catch(err => console.error('Error loading task stats:', err));
            
            // Load invoice stats
            fetch('api/invoice_stats.php')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('dash-invoices-outstanding').textContent = data.outstanding_count || 0;
                        document.getElementById('dash-invoices-outstanding-amount').textContent = '£' + (parseFloat(data.outstanding_amount) || 0).toFixed(2);
                        document.getElementById('dash-invoices-overdue').textContent = data.overdue_count || 0;
                        document.getElementById('dash-invoices-overdue-amount').textContent = '£' + (parseFloat(data.overdue_amount) || 0).toFixed(2);
                        document.getElementById('dash-invoices-total').textContent = '£' + (parseFloat(data.total_invoiced) || 0).toFixed(2);
                        document.getElementById('dash-invoices-collected').textContent = '£' + (parseFloat(data.total_collected) || 0).toFixed(2);
                    }
                })
                .catch(err => console.error('Error loading invoice stats:', err));
            
            // Load quote stats for dashboard
            fetch('api/get_quotes.php')
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.quotes) {
                        const totalQuotes = data.quotes.length;
                        const newQuotes = data.quotes.filter(q => q.status === 'new').length;
                        const inProgress = data.quotes.filter(q => q.status === 'in_progress' || q.status === 'contacted').length;
                        
                        document.getElementById('quotes-count').textContent = totalQuotes;
                        document.getElementById('dash-quotes-new').textContent = newQuotes;
                        document.getElementById('dash-quotes-progress').textContent = inProgress;
                    }
                })
                .catch(err => console.error('Error loading quote stats:', err));
            
            // Load email stats (placeholder until email storage is implemented)
            // For now, showing 0 - can be connected to actual email data later
            document.getElementById('dash-emails-unread').textContent = 0;
            document.getElementById('dash-emails-total').textContent = 0;
            document.getElementById('dash-emails-drafts').textContent = 0;

            // Load CRM charts
            loadCRMCharts();
        }

        // Load CRM Charts
        function loadCRMCharts() {
            // Check if we're on the dashboard section
            const dashboardSection = document.getElementById('section-dashboard');
            if (!dashboardSection || !dashboardSection.classList.contains('active')) {
                return; // Don't load charts if not on dashboard
            }

            // Load status breakdown chart
            fetch('api/crm_dashboard.php')
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.stats) {
                        const stats = data.stats;

                        // Status breakdown pie chart
                        const statusCanvas = document.getElementById('statusChart');
                        if (statusCanvas) {
                            const statusCtx = statusCanvas.getContext('2d');
                            new Chart(statusCtx, {
                                type: 'doughnut',
                                data: {
                                    labels: ['New', 'Contacted', 'In Progress', 'Completed', 'Declined'],
                                    datasets: [{
                                        data: [
                                            stats.status_breakdown?.new || 0,
                                            stats.status_breakdown?.contacted || 0,
                                            stats.status_breakdown?.in_progress || 0,
                                            stats.status_breakdown?.completed || 0,
                                            stats.status_breakdown?.declined || 0
                                        ],
                                        backgroundColor: [
                                            '#007bff', // New - Blue
                                            '#ffc107', // Contacted - Yellow
                                            '#17a2b8', // In Progress - Teal
                                            '#28a745', // Completed - Green
                                            '#dc3545'  // Declined - Red
                                        ]
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    plugins: {
                                        legend: {
                                            position: 'bottom'
                                        }
                                    }
                                }
                            });
                        }

                        // Lead sources bar chart
                        const leadCanvas = document.getElementById('leadSourceChart');
                        if (leadCanvas) {
                            const leadCtx = leadCanvas.getContext('2d');
                            const leadLabels = stats.lead_sources?.map(item => item.lead_source || 'Unknown') || [];
                            const leadCounts = stats.lead_sources?.map(item => item.count) || [];
                            new Chart(leadCtx, {
                                type: 'bar',
                                data: {
                                    labels: leadLabels,
                                    datasets: [{
                                        label: 'Leads',
                                        data: leadCounts,
                                        backgroundColor: '#007bff',
                                        borderColor: '#0056b3',
                                        borderWidth: 1
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            ticks: {
                                                stepSize: 1
                                            }
                                        }
                                    }
                                }
                            });
                        }
                    }
                })
                .catch(err => console.error('Error loading CRM stats:', err));

            // Load revenue trends chart
            fetch('api/invoice_trends.php?metric=collected&range=monthly')
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.months) {
                        const revenueCanvas = document.getElementById('revenueChart');
                        if (revenueCanvas) {
                            const revenueCtx = revenueCanvas.getContext('2d');
                            const labels = Object.keys(data.months);
                            const values = Object.values(data.months);
                            new Chart(revenueCtx, {
                                type: 'line',
                                data: {
                                    labels: labels,
                                    datasets: [{
                                        label: 'Revenue (£)',
                                        data: values,
                                        borderColor: '#28a745',
                                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                                        tension: 0.1
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            ticks: {
                                                callback: function(value) {
                                                    return '£' + value.toFixed(0);
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                        }
                    }
                })
                .catch(err => console.error('Error loading revenue trends:', err));

            // Load task priority chart
            fetch('api/tasks.php')
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.tasks) {
                        const taskCanvas = document.getElementById('taskPriorityChart');
                        if (taskCanvas) {
                            const taskCtx = taskCanvas.getContext('2d');
                            const priorities = data.tasks.reduce((acc, task) => {
                                const priority = task.priority || 'normal';
                                acc[priority] = (acc[priority] || 0) + 1;
                                return acc;
                            }, {});

                            new Chart(taskCtx, {
                                type: 'pie',
                                data: {
                                    labels: ['Low', 'Normal', 'High', 'Urgent'],
                                    datasets: [{
                                        data: [
                                            priorities.low || 0,
                                            priorities.normal || 0,
                                            priorities.high || 0,
                                            priorities.urgent || 0
                                        ],
                                        backgroundColor: [
                                            '#28a745', // Low - Green
                                            '#17a2b8', // Normal - Teal
                                            '#ffc107', // High - Yellow
                                            '#dc3545'  // Urgent - Red
                                        ]
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    plugins: {
                                        legend: {
                                            position: 'bottom'
                                        }
                                    }
                                }
                            });
                        }
                    }
                })
                .catch(err => console.error('Error loading task stats:', err));
        }
        
        // ==================== CRM Functions ====================
        
        let currentClientId = null;
        
        // Tab Switching
        function switchClientTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.client-tab-content').forEach(tab => {
                tab.classList.remove('active');
                tab.style.display = 'none';
            });
            
            // Remove active class from all buttons
            document.querySelectorAll('.crm-tab').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            const selectedTab = document.getElementById('client-tab-' + tabName);
            if (selectedTab) {
                selectedTab.classList.add('active');
                selectedTab.style.display = 'block';
            }
            
            // Add active class to clicked button
            event.target.classList.add('active');
            
            // Load data for the selected tab
            if (currentClientId) {
                if (tabName === 'activities') {
                    loadClientActivities(currentClientId);
                } else if (tabName === 'notes') {
                    loadClientNotes(currentClientId);
                } else if (tabName === 'tasks') {
                    loadClientTasks(currentClientId);
                }
            }
        }
        
        // ==================== Activity Functions ====================
        
        function loadClientActivities(clientId) {
            fetch(`api/activities.php?client_id=${clientId}`)
                .then(res => res.json())
                .then(data => {
                    const container = document.getElementById('client-activities-list');
                    if (data.success && data.activities && data.activities.length > 0) {
                        container.innerHTML = data.activities.map(activity => {
                            // Get icon and label based on activity type
                            const typeIcons = {
                                'call': '📞',
                                'email': '📧',
                                'meeting': '👥',
                                'note': '📝',
                                'task': '✅',
                                'quote_sent': '📋',
                                'invoice_sent': '📄',
                                'payment_received': '💰',
                                'other': '📌'
                            };
                            
                            const typeLabels = {
                                'call': 'Phone Call',
                                'email': 'Email',
                                'meeting': 'Meeting',
                                'note': 'Note',
                                'task': 'Task',
                                'quote_sent': 'Quote Sent',
                                'invoice_sent': 'Invoice Sent',
                                'payment_received': 'Payment',
                                'other': 'Other'
                            };
                            
                            const icon = typeIcons[activity.type] || '📌';
                            const label = typeLabels[activity.type] || activity.type;
                            
                            return `
                                <div class="activity-item type-${activity.type}">
                                    <div class="activity-header">
                                        <span class="activity-type type-${activity.type}">${icon} ${label}</span>
                                        <a href="javascript:void(0)" class="activity-delete" onclick="deleteActivity(${activity.id})">Delete</a>
                                    </div>
                                    <div class="activity-subject">${escapeHtml(activity.subject || 'No Subject')}</div>
                                    ${activity.description ? `<div class="activity-description">${escapeHtml(activity.description)}</div>` : ''}
                                    <div class="activity-meta">
                                        <span>📅 ${new Date(activity.activity_date).toLocaleString()}</span>
                                        ${activity.duration_minutes ? `<span>⏱️ ${activity.duration_minutes} min</span>` : ''}
                                        <span>👤 ${escapeHtml(activity.created_by_name || activity.created_by_username || 'System')}</span>
                                    </div>
                                </div>
                            `;
                        }).join('');
                    } else {
                        container.innerHTML = '<div class="empty-state"><h3>No Activities Yet</h3><p>Log your first interaction with this client.</p></div>';
                    }
                })
                .catch(err => {
                    console.error('Error loading activities:', err);
                });
        }
        
        function openLogActivityModal() {
            document.getElementById('activityClientId').value = currentClientId;
            document.getElementById('activityForm').reset();
            document.getElementById('activityClientId').value = currentClientId;
            // Set default date to now
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            document.getElementById('activityDate').value = now.toISOString().slice(0, 16);
            document.getElementById('activityModal').style.display = 'flex';
        }
        
        function closeActivityModal() {
            document.getElementById('activityModal').style.display = 'none';
        }
        
        function saveActivity(event) {
            event.preventDefault();
            
            const formData = {
                client_id: document.getElementById('activityClientId').value,
                activity_type: document.getElementById('activityType').value,
                subject: document.getElementById('activitySubject').value,
                description: document.getElementById('activityDescription').value,
                activity_date: document.getElementById('activityDate').value,
                duration_minutes: document.getElementById('activityDuration').value || null
            };
            
            fetch('api/activities.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(formData)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    closeActivityModal();
                    loadClientActivities(currentClientId);
                    showNotification('Activity logged successfully', 'success');
                } else {
                    alert('Error: ' + (data.message || 'Failed to log activity'));
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Error logging activity');
            });
        }
        
        function deleteActivity(activityId) {
            if (!confirm('Delete this activity?')) return;
            
            fetch('api/activities.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({id: activityId})
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    loadClientActivities(currentClientId);
                    showNotification('Activity deleted', 'success');
                } else {
                    alert('Error: ' + (data.message || 'Failed to delete activity'));
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Error deleting activity');
            });
        }
        
        // ==================== Notes Functions ====================
        
        function loadClientNotes(clientId) {
            fetch(`api/notes.php?client_id=${clientId}`)
                .then(res => res.json())
                .then(data => {
                    const container = document.getElementById('client-notes-list');
                    if (data.success && data.notes && data.notes.length > 0) {
                        container.innerHTML = data.notes.map(note => `
                            <div class="note-item ${note.is_important ? 'important' : ''}">
                                ${note.is_important ? '<span class="note-important-badge">⭐ IMPORTANT</span>' : ''}
                                <div class="note-text">${escapeHtml(note.note_text || '')}</div>
                                <div class="note-meta">
                                    <span>📅 ${new Date(note.created_at).toLocaleString()}</span>
                                    <span>👤 ${escapeHtml(note.created_by_name || note.created_by_username || 'System')}</span>
                                    <a href="javascript:void(0)" class="note-delete" onclick="deleteNote(${note.id})">Delete</a>
                                </div>
                            </div>
                        `).join('');
                    } else {
                        container.innerHTML = '<div class="empty-state"><h3>No Notes Yet</h3><p>Add notes to keep track of important information about this client.</p></div>';
                    }
                })
                .catch(err => {
                    console.error('Error loading notes:', err);
                });
        }
        
        function openAddNoteModal() {
            document.getElementById('noteClientId').value = currentClientId;
            document.getElementById('noteForm').reset();
            document.getElementById('noteClientId').value = currentClientId;
            document.getElementById('noteModal').style.display = 'flex';
        }
        
        function closeNoteModal() {
            document.getElementById('noteModal').style.display = 'none';
        }
        
        function saveNote(event) {
            event.preventDefault();
            
            const formData = {
                client_id: document.getElementById('noteClientId').value,
                note: document.getElementById('noteText').value,
                is_important: document.getElementById('noteImportant').checked ? 1 : 0
            };
            
            fetch('api/notes.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(formData)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    closeNoteModal();
                    loadClientNotes(currentClientId);
                    showNotification('Note added successfully', 'success');
                } else {
                    alert('Error: ' + (data.message || 'Failed to add note'));
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Error adding note');
            });
        }
        
        function deleteNote(noteId) {
            if (!confirm('Delete this note?')) return;
            
            fetch('api/notes.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({id: noteId})
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    loadClientNotes(currentClientId);
                    showNotification('Note deleted', 'success');
                } else {
                    alert('Error: ' + (data.message || 'Failed to delete note'));
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Error deleting note');
            });
        }
        
        // ==================== Payment Functions ====================
        
        function openPaymentModal() {
            const clientId = document.getElementById('clientId').value;
            const totalCost = parseFloat(document.getElementById('totalCost').value) || 0;
            const totalPaid = parseFloat(document.getElementById('totalPaid').value) || 0;
            const outstanding = totalCost - totalPaid;
            
            document.getElementById('paymentClientId').value = clientId;
            document.getElementById('paymentTotalCost').textContent = totalCost.toFixed(2);
            document.getElementById('paymentTotalPaid').textContent = totalPaid.toFixed(2);
            document.getElementById('paymentOutstanding').textContent = outstanding.toFixed(2);
            
            // Set default date to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('paymentDate').value = today;
            
            // Set suggested payment amount to outstanding balance
            document.getElementById('paymentAmount').value = outstanding.toFixed(2);
            
            document.getElementById('paymentForm').reset();
            document.getElementById('paymentClientId').value = clientId;
            document.getElementById('paymentDate').value = today;
            document.getElementById('paymentAmount').value = outstanding.toFixed(2);
            
            document.getElementById('paymentModal').style.display = 'flex';
        }
        
        function closePaymentModal() {
            document.getElementById('paymentModal').style.display = 'none';
        }
        
        function recordPayment(event) {
            event.preventDefault();
            
            const clientId = document.getElementById('paymentClientId').value;
            const amount = parseFloat(document.getElementById('paymentAmount').value);
            const paymentDate = document.getElementById('paymentDate').value;
            const paymentMethod = document.getElementById('paymentMethod').value;
            const notes = document.getElementById('paymentNotes').value;
            
            const currentPaid = parseFloat(document.getElementById('totalPaid').value) || 0;
            const newTotalPaid = currentPaid + amount;
            
            // Update the client with new total_paid
            const formData = {
                id: clientId,
                total_paid: newTotalPaid
            };
            
            fetch('api/update_client.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(formData)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Log payment as an activity
                    const activityData = {
                        client_id: clientId,
                        type: 'payment_received',
                        subject: `Payment Received: £${amount.toFixed(2)}`,
                        description: `Payment of £${amount.toFixed(2)} received via ${paymentMethod}.${notes ? ' Notes: ' + notes : ''}`,
                        activity_date: paymentDate + ' 12:00:00'
                    };
                    
                    return fetch('api/activities.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify(activityData)
                    });
                } else {
                    throw new Error(data.message || 'Failed to update payment');
                }
            })
            .then(res => res.json())
            .then(data => {
                closePaymentModal();
                
                // Update the display
                document.getElementById('totalPaid').value = newTotalPaid.toFixed(2);
                calculateRemaining();
                
                // Reload activities to show the payment
                loadClientActivities(clientId);
                
                // Reload payment history
                loadClientPayments(clientId);
                
                showNotification(`Payment of £${amount.toFixed(2)} recorded successfully`, 'success');
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Error recording payment: ' + err.message);
            });
        }
        
        function loadClientPayments(clientId) {
            fetch('api/activities.php?client_id=' + clientId)
            .then(res => res.json())
            .then(data => {
                const container = document.getElementById('client-payments-list');
                
                // Filter only payment activities
                const payments = data.activities ? data.activities.filter(a => a.type === 'payment_received') : [];
                
                if (payments.length === 0) {
                    container.innerHTML = '<div class="empty-state"><p>No payments recorded yet.</p></div>';
                    return;
                }
                
                // Sort by date, newest first
                payments.sort((a, b) => new Date(b.activity_date) - new Date(a.activity_date));
                
                let html = '<div style="overflow-x: auto;">';
                html += '<table style="width: 100%; border-collapse: collapse;">';
                html += '<thead><tr style="background: #f8f9fa; border-bottom: 2px solid #ddd;">';
                html += '<th style="padding: 10px; text-align: left;">Date</th>';
                html += '<th style="padding: 10px; text-align: left;">Amount</th>';
                html += '<th style="padding: 10px; text-align: left;">Details</th>';
                html += '</tr></thead><tbody>';
                
                payments.forEach(payment => {
                    const date = new Date(payment.activity_date);
                    const dateStr = date.toLocaleDateString('en-GB', { 
                        day: '2-digit', 
                        month: 'short', 
                        year: 'numeric' 
                    });
                    
                    // Extract amount from subject (e.g., "Payment Received: £500.00")
                    const amountMatch = payment.subject.match(/£([\d,]+\.\d{2})/);
                    const amount = amountMatch ? amountMatch[1] : '0.00';
                    
                    html += '<tr style="border-bottom: 1px solid #eee;">';
                    html += `<td style="padding: 10px;">${dateStr}</td>`;
                    html += `<td style="padding: 10px; font-weight: bold; color: #28a745;">£${amount}</td>`;
                    html += `<td style="padding: 10px; color: #666;">${escapeHtml(payment.description || '')}</td>`;
                    html += '</tr>';
                });
                
                html += '</tbody></table></div>';
                container.innerHTML = html;
            })
            .catch(err => {
                console.error('Error loading payments:', err);
            });
        }
        
        // ==================== Client Tasks Functions ====================
        
        function loadClientTasks(clientId) {
            fetch(`api/tasks.php?client_id=${clientId}`)
                .then(res => res.json())
                .then(data => {
                    const container = document.getElementById('client-tasks-list');
                    if (data.success && data.tasks && data.tasks.length > 0) {
                        container.innerHTML = data.tasks.map(task => {
                            const priorityColors = {
                                urgent: '#dc3545',
                                high: '#fd7e14',
                                medium: '#ffc107',
                                low: '#28a745'
                            };
                            const statusBadges = {
                                pending: '⏳ Pending',
                                in_progress: '🔄 In Progress',
                                completed: '✅ Completed',
                                cancelled: '❌ Cancelled'
                            };
                            
                            return `
                                <div class="client-task-item ${task.status === 'completed' || task.status === 'cancelled' ? 'completed' : ''}">
                            <div class="client-task-left">
                                <div class="client-task-title ${task.status === 'completed' ? 'completed' : ''}">${task.title}</div>
                                <div class="client-task-meta">
                                    <span class="priority-badge" data-priority="${task.priority}">● ${task.priority.toUpperCase()}</span>
                                    <span>${statusBadges[task.status]}</span>
                                    ${task.due_date ? `<span>📅 Due: ${new Date(task.due_date).toLocaleDateString()}</span>` : ''}
                                </div>
                            </div>
                                <div class="client-task-actions">
                                    ${task.status !== 'completed' ? `<button class="btn btn-sm btn-primary" onclick="markTaskComplete(${task.id})">✓ Complete</button>` : ''}
                                    <button class="btn btn-sm btn-danger" onclick="deleteTask(${task.id})">Delete</button>
                                </div>
                            </div>
                        `}).join('');
                    } else {
                        container.innerHTML = '<div class="empty-state"><h3>No Tasks Yet</h3><p>Create tasks related to this client.</p></div>';
                    }
                })
                .catch(err => {
                    console.error('Error loading tasks:', err);
                });
        }
        
        function openAddClientTaskModal() {
            // Mark that the task modal was opened from a client and remember the client id
            window._taskOpenedForClient = true;
            window._preselectedTaskClientId = currentClientId;
            openTaskModal();
        }
        
        function markTaskComplete(taskId) {
            fetch(`api/tasks.php?id=${taskId}`, {
                method: 'PUT',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ status: 'completed' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    loadClientTasks(currentClientId);
                    loadTasks(); // Refresh main tasks list if visible
                    showNotification('Task marked as complete', 'success');
                } else {
                    alert('Error: ' + (data.message || 'Failed to update task'));
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Error updating task');
            });
        }
        
        function deleteTask(taskId) {
            if (!confirm('Delete this task?')) return;
            
            fetch(`api/tasks.php?id=${taskId}`, {
                method: 'DELETE'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    loadClientTasks(currentClientId);
                    loadTasks(); // Refresh main tasks list if visible
                    showNotification('Task deleted', 'success');
                } else {
                    alert('Error: ' + (data.message || 'Failed to delete task'));
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Error deleting task');
            });
        }
        
        // Helper notification function
        function showNotification(message, type) {
            // Simple alert for now - can be enhanced with a toast notification system
            console.log(`[${type.toUpperCase()}] ${message}`);
        }
        
        // ==================== Main Tasks Section Functions ====================
        
        let currentTaskFilter = 'all';
        let currentEditTaskId = null;
        
        function loadTasks() {
            // Get filter value from dropdown if it exists
            const filterSelect = document.getElementById('taskStatusFilter');
            if (filterSelect) {
                currentTaskFilter = filterSelect.value || 'all';
            }
            
            const statusFilter = currentTaskFilter === '' || currentTaskFilter === 'all' ? '' : `&status=${currentTaskFilter}`;
            
            // return promise so callers (stat-card clicks) can chain further UI filtering
            return fetch(`api/tasks.php?${statusFilter.substring(1)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.tasks) {
                        // Update statistics
                        const pending = data.tasks.filter(t => t.status === 'pending').length;
                        const today = new Date().toISOString().split('T')[0];
                        const overdue = data.tasks.filter(t => t.due_date && t.due_date < today && t.status !== 'completed' && t.status !== 'cancelled').length;
                        const urgent = data.tasks.filter(t => t.priority === 'urgent' && t.status !== 'completed' && t.status !== 'cancelled').length;
                        
                        document.getElementById('stat-tasks-pending').textContent = pending;
                        document.getElementById('stat-tasks-overdue').textContent = overdue;
                        document.getElementById('stat-tasks-urgent').textContent = urgent;
                        
                        // Render tasks list
                        renderTasksList(data.tasks);
                    }
                    return data;
                })
                .catch(err => {
                    console.error('Error loading tasks:', err);
                    throw err;
                });
        }
        
        function renderTasksList(tasks) {
            const container = document.getElementById('tasks-list');
            
            if (tasks.length === 0) {
                container.innerHTML = '<div class="empty-state"><h3>No Tasks Found</h3><p>Create your first task to get started.</p></div>';
                return;
            }
            
            const priorityColors = {
                urgent: '#dc3545',
                high: '#fd7e14',
                medium: '#ffc107',
                low: '#28a745'
            };
            
            const statusBadges = {
                pending: '⏳ Pending',
                in_progress: '🔄 In Progress',
                completed: '✅ Completed',
                cancelled: '❌ Cancelled'
            };
            
            container.innerHTML = tasks.map(task => {
                const priorityColor = priorityColors[task.priority] || '#666';
                return `
                <div class="task-item ${task.status === 'completed' || task.status === 'cancelled' ? 'completed' : ''}" data-status="${task.status}" data-priority="${task.priority}" data-due-date="${task.due_date || ''}">
                    <div class="task-left">
                        <div class="task-title ${task.status === 'completed' ? 'completed' : ''}">${task.title}</div>
                        ${task.description ? `<div class="task-description">${task.description}</div>` : ''}
                        <div class="task-meta">
                            <span class="priority-badge" style="font-weight: 600; margin-right: 10px; display: inline-block; color: ${priorityColor};">● ${task.priority.toUpperCase()}</span>
                            <span>${statusBadges[task.status]}</span>
                            ${task.due_date ? `<span>📅 Due: ${new Date(task.due_date).toLocaleDateString()}</span>` : ''}
                            ${task.client_name ? `<span>👤 ${task.client_name}</span>` : '<span>👤 General Task</span>'}
                            ${task.assigned_to_name || task.assigned_to_username ? `<span>👷 ${escapeHtml(task.assigned_to_name || task.assigned_to_username)}</span>` : ''}
                        </div>
                    </div>
                    <div class="task-actions">
                        <button class="btn btn-sm btn-secondary" onclick="editTask(${task.id})">Edit</button>
                        ${task.status !== 'completed' ? `<button class="btn btn-sm btn-primary" onclick="markTaskComplete(${task.id})">✓ Complete</button>` : ''}
                        <button class="btn btn-sm btn-danger" onclick="deleteTask(${task.id})">Delete</button>
                    </div>
                </div>
                `;
            }).join('');
        }
        
        function filterTasks(status) {
            currentTaskFilter = status || 'all';
            const select = document.getElementById('taskStatusFilter');
            if (select) select.value = currentTaskFilter;
            loadTasks();
        }

        function showOverdueTasks() {
            // ensure tasks list is loaded then hide non-overdue items
            const select = document.getElementById('taskStatusFilter');
            if (select) select.value = 'all';
            loadTasks().then(() => {
                const today = new Date();
                document.querySelectorAll('#tasks-list .task-item').forEach(el => {
                    const due = el.dataset.dueDate;
                    const status = el.dataset.status;
                    const isOverdue = due && new Date(due) < today && status !== 'completed' && status !== 'cancelled';
                    el.style.display = isOverdue ? '' : 'none';
                });
            }).catch(() => {});
        }

        function showUrgentTasks() {
            const select = document.getElementById('taskStatusFilter');
            if (select) select.value = 'all';
            loadTasks().then(() => {
                document.querySelectorAll('#tasks-list .task-item').forEach(el => {
                    const priority = el.dataset.priority;
                    const status = el.dataset.status;
                    const isUrgent = priority === 'urgent' && status !== 'completed' && status !== 'cancelled';
                    el.style.display = isUrgent ? '' : 'none';
                });
            }).catch(() => {});
        }
        
        function openAddTaskModal() {
            document.getElementById('taskForm').reset();
            document.getElementById('taskId').value = '';
            document.getElementById('taskModalTitle').textContent = 'Add New Task';
            
            // Load clients for dropdown
            fetch('api/get_clients.php')
                .then(res => res.json())
                .then(data => {
                    const clientSelect = document.getElementById('taskClientId');
                    clientSelect.innerHTML = '<option value="">-- General Task --</option>';
                    if (data.success && data.clients) {
                        data.clients.forEach(client => {
                            clientSelect.innerHTML += `<option value="${client.id}">${client.name}${client.company ? ' (' + client.company + ')' : ''}</option>`;
                        });
                    }

                    // If a client was preselected (opened from client details), set it here
                    if (window._preselectedTaskClientId) {
                        clientSelect.value = window._preselectedTaskClientId;
                        // clear the temporary value so subsequent opens are normal
                        delete window._preselectedTaskClientId;
                    }
                });
            
            bringModalToFront('taskModal');
            document.getElementById('taskModal').style.display = 'flex';
            // autofocus: if opened from client details, focus Due Date; otherwise focus Title
            setTimeout(() => {
                if (window._taskOpenedForClient) {
                    const due = document.getElementById('taskDueDate'); if (due) due.focus();
                    // clear flag after use
                    delete window._taskOpenedForClient;
                } else {
                    const f = document.getElementById('taskTitle'); if (f) f.focus();
                }
            }, 50);
        }
        
        function editTask(taskId) {
            document.getElementById('taskModalTitle').textContent = 'Edit Task';
            
            // Fetch task data
            fetch(`api/tasks.php?id=${taskId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.task) {
                        const task = data.task;
                        document.getElementById('taskId').value = task.id;
                        document.getElementById('taskTitle').value = task.title;
                        document.getElementById('taskDescription').value = task.description || '';
                        document.getElementById('taskPriority').value = task.priority;
                        document.getElementById('taskDueDate').value = task.due_date || '';
                        document.getElementById('taskStatus').value = task.status;
                        
                        // Load clients and select the right one
                        fetch('api/get_clients.php')
                            .then(res => res.json())
                            .then(clientData => {
                                const clientSelect = document.getElementById('taskClientId');
                                clientSelect.innerHTML = '<option value="">-- General Task --</option>';
                                if (clientData.success && clientData.clients) {
                                    clientData.clients.forEach(client => {
                                        const selected = client.id == task.client_id ? 'selected' : '';
                                        clientSelect.innerHTML += `<option value="${client.id}" ${selected}>${client.name}${client.company ? ' (' + client.company + ')' : ''}</option>`;
                                    });
                                }
                            });
                        
                        bringModalToFront('taskModal');
                        document.getElementById('taskModal').style.display = 'flex';
                        // autofocus first field when modal appears
                        setTimeout(() => { const f = document.getElementById('taskTitle'); if (f) f.focus(); }, 50);
                    }
                })
                .catch(err => {
                    console.error('Error loading task:', err);
                    alert('Error loading task');
                });
        }
        
        function closeTaskModal() {
            const m = document.getElementById('taskModal');
            if (m) {
                m.style.display = 'none';
                // remove any inline zIndex we may have set when bringing to front
                m.style.zIndex = '';
            }
        }

        // Bring a modal element to the top of modal stack so it appears forefront
        function bringModalToFront(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return;
            let maxZ = 1000; // base z-index used by .modal
            document.querySelectorAll('.modal').forEach(m => {
                const z = window.getComputedStyle(m).zIndex;
                const zi = parseInt(z, 10);
                if (!isNaN(zi) && zi > maxZ) maxZ = zi;
            });
            // give this modal a slightly higher z-index
            modal.style.zIndex = (maxZ + 10).toString();
        }
        
        function saveTask(event) {
            event.preventDefault();
            
            const taskId = document.getElementById('taskId').value;
            const formData = {
                title: document.getElementById('taskTitle').value,
                description: document.getElementById('taskDescription').value,
                client_id: document.getElementById('taskClientId').value || null,
                priority: document.getElementById('taskPriority').value,
                due_date: document.getElementById('taskDueDate').value || null,
                status: document.getElementById('taskStatus').value
            };
            
            const method = taskId ? 'PUT' : 'POST';
            const url = taskId ? `api/tasks.php?id=${taskId}` : 'api/tasks.php';
            
            fetch(url, {
                method: method,
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(formData)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    closeTaskModal();
                    loadTasks();
                    if (currentClientId) {
                        loadClientTasks(currentClientId);
                    }
                    showNotification(taskId ? 'Task updated successfully' : 'Task created successfully', 'success');
                } else {
                    alert('Error: ' + (data.message || 'Failed to save task'));
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Error saving task');
            });
        }
        
        function openTaskModal() {
            openAddTaskModal();
        }
        
        // ==================== User Management Functions ====================
        
        function loadUsers() {
            fetch('api/get_users.php')
                .then(res => res.json())
                .then(data => {
                    const tbody = document.getElementById('users-list-tbody');
                    
                    if (!data.success || !data.users || data.users.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="6" style="padding: 40px; text-align: center; color: #999;">
                                    No users found. Click "Create New User" to add your first user.
                                </td>
                            </tr>
                        `;
                        return;
                    }
                    
                    tbody.innerHTML = data.users.map(user => {
                        const createdDate = new Date(user.created_at).toLocaleDateString('en-GB');
                        const roleLabel = user.role === 'superadmin' ? 'Super Admin' : 'Admin';
                        const roleBadgeColor = user.role === 'superadmin' ? '#dc3545' : '#007bff';
                        
                        return `
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 15px; font-weight: 500;">${escapeHtml(user.full_name || 'N/A')}</td>
                                <td style="padding: 15px;">${escapeHtml(user.username)}</td>
                                <td style="padding: 15px;">${escapeHtml(user.email || 'N/A')}</td>
                                <td style="padding: 15px;">
                                    <span style="background: ${roleBadgeColor}; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                        ${roleLabel}
                                    </span>
                                </td>
                                <td style="padding: 15px; color: #666;">${createdDate}</td>
                                <td style="padding: 15px; text-align: center;">
                                    <button class="btn btn-sm btn-primary" onclick="openEditUserModal(${user.id})" style="margin-right: 5px;">
                                        ✏️ Edit
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="openDeleteUserModal(${user.id}, '${escapeHtml(user.username)}')">
                                        🗑️ Delete
                                    </button>
                                </td>
                            </tr>
                        `;
                    }).join('');
                })
                .catch(err => {
                    console.error('Error loading users:', err);
                    document.getElementById('users-list-tbody').innerHTML = `
                        <tr>
                            <td colspan="6" style="padding: 40px; text-align: center; color: #dc3545;">
                                Error loading users. Please refresh the page.
                            </td>
                        </tr>
                    `;
                });
        }
        
        function openCreateUserModal() {
            document.getElementById('createUserForm').reset();
            document.getElementById('createUserModal').classList.add('show');
        }
        
        function closeCreateUserModal() {
            document.getElementById('createUserModal').classList.remove('show');
        }
        
        function createUser(event) {
            event.preventDefault();
            
            const firstName = document.getElementById('newUserFirstName').value;
            const lastName = document.getElementById('newUserLastName').value;
            const username = document.getElementById('newUsername').value;
            const email = document.getElementById('newUserEmail').value;
            const password = document.getElementById('newUserPassword').value;
            const passwordConfirm = document.getElementById('newUserPasswordConfirm').value;
            const role = document.getElementById('newUserRole').value;
            
            if (password !== passwordConfirm) {
                alert('Passwords do not match!');
                return;
            }
            
            fetch('api/create_user.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    first_name: firstName,
                    last_name: lastName,
                    username: username,
                    email: email,
                    password: password,
                    role: role
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('User created successfully!');
                    closeCreateUserModal();
                    loadUsers();
                } else {
                    alert('Error: ' + (data.message || 'Failed to create user'));
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Error creating user');
            });
        }
        
        function openEditUserModal(userId) {
            fetch('api/get_users.php?id=' + userId)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.users && data.users.length > 0) {
                        const user = data.users[0];
                        document.getElementById('editUserId').value = user.id;
                        document.getElementById('editUserFirstName').value = user.first_name || '';
                        document.getElementById('editUserLastName').value = user.last_name || '';
                        document.getElementById('editUsername').value = user.username;
                        document.getElementById('editUserEmail').value = user.email || '';
                        document.getElementById('editUserRole').value = user.role || 'admin';
                        document.getElementById('editUserPassword').value = '';
                        document.getElementById('editUserPasswordConfirm').value = '';
                        document.getElementById('editUserModal').classList.add('show');
                    } else {
                        alert('Error loading user details');
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    alert('Error loading user details');
                });
        }
        
        function closeEditUserModal() {
            document.getElementById('editUserModal').classList.remove('show');
        }
        
        function updateUser(event) {
            event.preventDefault();
            
            const userId = document.getElementById('editUserId').value;
            const firstName = document.getElementById('editUserFirstName').value;
            const lastName = document.getElementById('editUserLastName').value;
            const email = document.getElementById('editUserEmail').value;
            const password = document.getElementById('editUserPassword').value;
            const passwordConfirm = document.getElementById('editUserPasswordConfirm').value;
            const role = document.getElementById('editUserRole').value;
            
            if (password && password !== passwordConfirm) {
                alert('Passwords do not match!');
                return;
            }
            
            const updateData = {
                id: userId,
                first_name: firstName,
                last_name: lastName,
                email: email,
                role: role
            };
            
            if (password) {
                updateData.password = password;
            }
            
            fetch('api/update_user.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(updateData)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('User updated successfully!');
                    closeEditUserModal();
                    loadUsers();
                } else {
                    alert('Error: ' + (data.message || 'Failed to update user'));
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Error updating user');
            });
        }
        
        function openDeleteUserModal(userId, username) {
            document.getElementById('deleteUserId').value = userId;
            document.getElementById('deleteUserName').textContent = username;
            document.getElementById('deleteUserModal').classList.add('show');
        }
        
        function closeDeleteUserModal() {
            document.getElementById('deleteUserModal').classList.remove('show');
        }
        
        function confirmDeleteUser() {
            const userId = document.getElementById('deleteUserId').value;
            
            fetch('api/delete_user.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ id: userId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('User deleted successfully!');
                    closeDeleteUserModal();
                    loadUsers();
                } else {
                    alert('Error: ' + (data.message || 'Failed to delete user'));
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Error deleting user');
            });
        }
        
        // ==================== Email Functions ====================
        
        function composeEmail(clientId = null, clientEmail = null, clientName = null) {
            if (clientId && clientEmail) {
                // Composing to a specific client
                document.getElementById('emailClientId').value = clientId;
                document.getElementById('emailTo').value = clientEmail;
                document.getElementById('emailSubject').value = '';
                document.getElementById('emailMessage').value = '';
                document.getElementById('emailLogActivity').checked = true;
            } else if (currentClientId) {
                // Use current client if in client modal
                const email = document.getElementById('clientEmail')?.textContent;
                const name = document.getElementById('clientName')?.textContent;
                if (email) {
                    document.getElementById('emailClientId').value = currentClientId;
                    document.getElementById('emailTo').value = email;
                    document.getElementById('emailSubject').value = '';
                    document.getElementById('emailMessage').value = '';
                    document.getElementById('emailLogActivity').checked = true;
                }
            } else {
                alert('Please select a client first to compose an email.');
                return;
            }
            
            document.getElementById('composeEmailModal').classList.add('show');
        }
        
        function closeComposeEmailModal() {
            document.getElementById('composeEmailModal').classList.remove('show');
        }
        
        function sendClientEmail(event) {
            event.preventDefault();
            
            const clientId = document.getElementById('emailClientId').value;
            const to = document.getElementById('emailTo').value;
            const subject = document.getElementById('emailSubject').value;
            const message = document.getElementById('emailMessage').value;
            const logActivity = document.getElementById('emailLogActivity').checked;
            
            // Send email
            fetch('api/send_email.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    to: to,
                    subject: subject,
                    message: message,
                    client_id: clientId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Email sent successfully!');
                    closeComposeEmailModal();
                    
                    // Log activity if checkbox is checked
                    if (logActivity && clientId) {
                        const activityData = {
                            client_id: clientId,
                            type: 'email',
                            subject: `Email: ${subject}`,
                            description: message.substring(0, 200) + (message.length > 200 ? '...' : ''),
                            activity_date: new Date().toISOString().slice(0, 19).replace('T', ' ')
                        };
                        
                        fetch('api/activities.php', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify(activityData)
                        })
                        .then(() => {
                            if (currentClientId == clientId) {
                                loadClientActivities(clientId);
                            }
                        })
                        .catch(err => console.error('Error logging activity:', err));
                    }
                } else {
                    alert('Error: ' + (data.message || 'Failed to send email. Please check your email settings.'));
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Error sending email. Please try again.');
            });
        }
        
        function loadInboxEmails() {
            const container = document.getElementById('inbox-email-list');
            container.innerHTML = `
                <div class="empty-state">
                    <h3>Email Integration Coming Soon</h3>
                    <p>The inbox feature will display emails sent through the quote request form and other contact forms.</p>
                    <p style="margin-top: 10px; color: #666;">Configure your email settings first to enable this feature.</p>
                </div>
            `;
        }
        
        function loadDraftEmails() {
            const container = document.getElementById('draft-email-list');
            container.innerHTML = `
                <div class="empty-state">
                    <h3>No Drafts</h3>
                    <p>Draft emails will appear here once the compose feature is enabled.</p>
                </div>
            `;
        }
        
        function loadSentEmails() {
            const container = document.getElementById('sent-email-list');
            container.innerHTML = `
                <div class="empty-state">
                    <h3>Email Integration Coming Soon</h3>
                    <p>Sent emails including quote confirmations and invoices will appear here.</p>
                    <p style="margin-top: 10px; color: #666;">Configure your email settings first to enable this feature.</p>
                </div>
            `;
        }
        
        function loadTrashEmails() {
            const container = document.getElementById('trash-email-list');
            container.innerHTML = `
                <div class="empty-state">
                    <h3>Trash is Empty</h3>
                    <p>Deleted emails will appear here.</p>
                </div>
            `;
        }
        
        function emptyTrash() {
            if (confirm('Are you sure you want to permanently delete all emails in trash?')) {
                alert('Trash emptied successfully!');
                loadTrashEmails();
            }
        }
        
        function saveEmailSettings(event) {
            event.preventDefault();
            
            const settings = {
                smtp_host: document.getElementById('smtpHost').value,
                smtp_port: document.getElementById('smtpPort').value,
                smtp_username: document.getElementById('smtpUsername').value,
                smtp_password: document.getElementById('smtpPassword').value,
                smtp_encryption: document.getElementById('smtpEncryption').value,
                smtp_from_email: document.getElementById('smtpFromEmail').value,
                smtp_from_name: document.getElementById('smtpFromName').value,
                enable_notifications: document.getElementById('enableEmailNotifications').checked,
                enable_auto_reply: document.getElementById('enableAutoReply').checked,
                notification_email: document.getElementById('notificationEmail').value,
                auto_reply_template: document.getElementById('autoReplyTemplate').value
            };
            
            fetch('api/save_email_settings.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(settings)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Email settings saved successfully!');
                } else {
                    alert('Error: ' + (data.message || 'Failed to save settings'));
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Error saving email settings. Please try again.');
            });
        }
        
        function testEmailSettings() {
            const settings = {
                smtp_host: document.getElementById('smtpHost').value,
                smtp_port: document.getElementById('smtpPort').value,
                smtp_username: document.getElementById('smtpUsername').value,
                smtp_password: document.getElementById('smtpPassword').value,
                smtp_encryption: document.getElementById('smtpEncryption').value,
                smtp_from_email: document.getElementById('smtpFromEmail').value
            };
            
            if (!settings.smtp_host || !settings.smtp_port || !settings.smtp_username || !settings.smtp_password) {
                alert('Please fill in all SMTP settings before testing.');
                return;
            }
            
            alert('Testing email connection...');
            
            fetch('api/test_email.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(settings)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Email connection successful! Test email sent to ' + settings.smtp_from_email);
                } else {
                    alert('❌ Email connection failed: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('❌ Error testing email connection. Please check your settings.');
            });
        }
        
        function loadEmailSettings() {
            fetch('api/get_email_settings.php')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.settings) {
                    const s = data.settings;
                    if (s.smtp_host) document.getElementById('smtpHost').value = s.smtp_host;
                    if (s.smtp_port) document.getElementById('smtpPort').value = s.smtp_port;
                    if (s.smtp_username) document.getElementById('smtpUsername').value = s.smtp_username;
                    if (s.smtp_password) document.getElementById('smtpPassword').value = s.smtp_password;
                    if (s.smtp_encryption) document.getElementById('smtpEncryption').value = s.smtp_encryption;
                    if (s.smtp_from_email) document.getElementById('smtpFromEmail').value = s.smtp_from_email;
                    if (s.smtp_from_name) document.getElementById('smtpFromName').value = s.smtp_from_name;
                    
                    document.getElementById('enableEmailNotifications').checked = s.enable_notifications == 1;
                    document.getElementById('enableAutoReply').checked = s.enable_auto_reply == 1;
                    
                    if (s.notification_email) document.getElementById('notificationEmail').value = s.notification_email;
                    if (s.auto_reply_template) document.getElementById('autoReplyTemplate').value = s.auto_reply_template;
                }
            })
            .catch(err => {
                console.error('Error loading email settings:', err);
            });
        }
        
        // ==================== Menu Customization ====================
        
        const defaultMenuOrder = [
            {id: 'nav-dashboard', label: '📋 Dashboard', section: 'dashboard', type: 'link'},
            {id: 'nav-clients', label: '📝 Quote Requests', section: 'clients', type: 'link'},
            {id: 'clients-submenu', label: '👥 Clients', type: 'parent', children: [
                {id: 'nav-existing-clients', label: '👤 Existing Clients', section: 'existing-clients'},
                {id: 'nav-add-client', label: '➕ Add New Client', action: 'openAddClientModal()'}
            ]},
            {id: 'email-submenu', label: '📧 Email', type: 'parent', children: [
                {id: 'nav-email-inbox', label: '📥 Inbox', section: 'email-inbox'},
                {id: 'nav-email-draft', label: '📝 Drafts', section: 'email-draft'},
                {id: 'nav-email-sent', label: '📤 Sent', section: 'email-sent'},
                {id: 'nav-email-trash', label: '🗑️ Trash', section: 'email-trash'},
                {id: 'nav-email-settings', label: '⚙️ Settings', section: 'email-settings'}
            ]},
            {id: 'nav-tasks', label: '✅ Tasks & To-Do', section: 'tasks', type: 'link'},
            {id: 'nav-invoices', label: '📄 Invoices', section: 'invoices', type: 'link'},
            {id: 'pages-submenu', label: '🌐 Website Pages', type: 'parent', children: [
                {id: 'nav-html-files', label: '📝 Edit Pages', section: 'html-files'},
                {id: 'nav-new-page', label: '➕ Create New Page', action: 'openNewPageModal()'}
            ]},
            {id: 'users-submenu', label: '👤 Users', type: 'parent', children: [
                {id: 'nav-users-list', label: '📋 View All Users', section: 'users-list'},
                {id: 'nav-create-user', label: '➕ Create User', action: 'openCreateUserModal()'}
            ]},
            {id: 'settings-submenu', label: '⚙️ Settings', type: 'parent', children: [
                {id: 'nav-export', label: '📦 Export Website', action: "location.href='export.php'"},
                {id: 'nav-customize-menu', label: '⚙️ Customize Menu', action: 'openMenuCustomizationModal()'}
            ]}
        ];
        
        function openMenuCustomizationModal() {
            loadMenuItems();
            document.getElementById('menuCustomizationModal').style.display = 'flex';
        }
        
        function closeMenuCustomizationModal() {
            document.getElementById('menuCustomizationModal').style.display = 'none';
        }
        
        function loadMenuItems() {
            fetch('api/get_menu_order.php')
                .then(res => res.json())
                .then(data => {
                    const menuOrder = data.success && data.order ? data.order : defaultMenuOrder;
                    renderMenuItems(menuOrder);
                })
                .catch(() => {
                    renderMenuItems(defaultMenuOrder);
                });
        }
        
        function renderMenuItems(items) {
            const container = document.getElementById('menu-items-list');
            container.innerHTML = items.map((item, index) => `
                <div class="menu-config-item" data-index="${index}" style="background: white; padding: 12px; margin-bottom: 8px; border-radius: 6px; display: flex; justify-content: space-between; align-items: center; border: 1px solid #ddd;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-weight: 500;">${item.label}</span>
                        ${item.children ? `<span style="font-size: 12px; color: #666;">(${item.children.length} items)</span>` : ''}
                    </div>
                    <div style="display: flex; gap: 5px;">
                        ${index > 0 ? `<button class="btn btn-sm btn-secondary" onclick="moveMenuItem(${index}, -1)" style="padding: 4px 8px;">↑</button>` : ''}
                        ${index < items.length - 1 ? `<button class="btn btn-sm btn-secondary" onclick="moveMenuItem(${index}, 1)" style="padding: 4px 8px;">↓</button>` : ''}
                    </div>
                </div>
            `).join('');
            
            // Store current order in memory
            window.currentMenuOrder = items;
        }
        
        function moveMenuItem(index, direction) {
            const newIndex = index + direction;
            if (newIndex < 0 || newIndex >= window.currentMenuOrder.length) return;
            
            const items = [...window.currentMenuOrder];
            [items[index], items[newIndex]] = [items[newIndex], items[index]];
            
            window.currentMenuOrder = items;
            renderMenuItems(items);
            saveMenuOrder(items);
        }
        
        function saveMenuOrder(order) {
            fetch('api/save_menu_order.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({order: order})
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    applySidebarOrder(order);
                }
            })
            .catch(err => console.error('Error saving menu order:', err));
        }
        
        function resetMenuOrder() {
            if (confirm('Reset menu to default order?')) {
                saveMenuOrder(defaultMenuOrder);
                renderMenuItems(defaultMenuOrder);
            }
        }
        
        function applySidebarOrder(order) {
            const sidebar = document.querySelector('.sidebar');
            if (!sidebar) return;

            // Capture available elements (preserve original DOM order)
            const elements = {};
            const originalOrder = [];
            sidebar.querySelectorAll('a, .submenu').forEach(el => {
                if (el.id) {
                    elements[el.id] = el.cloneNode(true);
                    originalOrder.push(el.id);
                }
            });

            // Preserve footer elements (they will be re-appended)
            const customizeMenu = elements['nav-customize-menu'];
            const viewSite = elements['nav-view-site'];
            const logout = elements['nav-logout'];

            // If saved order is missing or invalid, fall back to defaultMenuOrder
            const savedOrder = Array.isArray(order) ? order : [];

            // Build a merged order: start with saved items that still exist, then append any default items missing from saved order
            const merged = [];
            const addedIds = new Set();

            // Helper to add an item (marking its id and — for parents — their children)
            function pushItem(item) {
                if (!item || !item.id || addedIds.has(item.id)) return;
                merged.push(item);
                addedIds.add(item.id);

                // If this is a parent menu, also mark its children as "added" so they aren't re-appended later
                if (item.type === 'parent' && item.children && Array.isArray(item.children)) {
                    item.children.forEach(c => { if (c && c.id) addedIds.add(c.id); });
                }
            }

            // 1) keep saved items only if corresponding DOM element exists (prevents lost items when user-saved order is stale)
            savedOrder.forEach(it => {
                if (!it || !it.id) return;
                if (it.type === 'parent') {
                    if (elements[it.id] || (it.children && it.children.some(c => elements[c.id]))) pushItem(it);
                } else {
                    if (elements[it.id]) pushItem(it);
                }
            });

            // 2) append any defaults that aren't already in merged (restore missing items)
            (window.defaultMenuOrder || []).forEach(def => {
                if (!def || !def.id) return;
                if (!addedIds.has(def.id)) {
                    if (def.type === 'parent') {
                        if (elements[def.id] || (def.children && def.children.some(c => elements[c.id]))) pushItem(def);
                    } else {
                        if (elements[def.id]) pushItem(def);
                    }
                }
            });

            // Clear and rebuild sidebar from merged order
            sidebar.innerHTML = '';

            merged.forEach(item => {
                if (item.type === 'parent' && item.id) {
                    const parent = document.createElement('a');
                    parent.href = '#';
                    parent.className = 'menu-parent';
                    parent.textContent = item.label;
                    parent.onclick = (e) => { toggleSubmenu(e, item.id); return false; };
                    sidebar.appendChild(parent);

                    if (elements[item.id]) {
                        // append submenu clone and mark any anchor children as added (prevents later duplication)
                        const submenuClone = elements[item.id].cloneNode(true);
                        // mark child anchor ids so final sweep won't re-append them
                        submenuClone.querySelectorAll('a[id]').forEach(a => addedIds.add(a.id));
                        sidebar.appendChild(submenuClone);
                    } else {
                        const submenu = document.createElement('div');
                        submenu.className = 'submenu';
                        submenu.id = item.id;
                        if (item.children && item.children.length) {
                            item.children.forEach(c => {
                                if (elements[c.id]) submenu.appendChild(elements[c.id].cloneNode(true));
                            });
                        }
                        if (submenu.children.length) sidebar.appendChild(submenu);
                    }
                } else if (item.id && elements[item.id] && !item.id.includes('submenu')) {
                    sidebar.appendChild(elements[item.id].cloneNode(true));
                }
            });

            // 3) append any remaining *anchor* DOM elements that weren't included (skip standalone submenu divs)
            //    — do not re-append footer controls (customize/view-site/logout) which are added separately below
            const footerIds = new Set(['nav-customize-menu', 'nav-view-site', 'nav-logout']);
            originalOrder.forEach(id => {
                if (!addedIds.has(id) && elements[id] && elements[id].tagName === 'A' && !footerIds.has(id)) {
                    sidebar.appendChild(elements[id].cloneNode(true));
                }
            });

            // Re-append footer controls in a sensible order (customize, view site, logout)
            if (customizeMenu && !addedIds.has('nav-customize-menu')) {
                const cm = customizeMenu.cloneNode(true);
                cm.onclick = (e) => { openMenuCustomizationModal(); return false; };
                sidebar.appendChild(cm);
            }
            if (viewSite && !addedIds.has('nav-view-site')) sidebar.appendChild(viewSite.cloneNode(true));
            if (logout && !addedIds.has('nav-logout')) sidebar.appendChild(logout.cloneNode(true));
        }
        
        // Load and apply saved menu order on page load
        function initializeMenuOrder() {
            fetch('api/get_menu_order.php')
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.order) {
                        applySidebarOrder(data.order);
                    }
                })
                .catch(() => {
                    // Use default order if no saved order exists
                });
        }
        
        // Initialize menu order when page loads
        document.addEventListener('DOMContentLoaded', () => {
            initializeMenuOrder();

            // Time-of-day greeting (Good morning/afternoon/evening)
            try {
                const hour = new Date().getHours();
                const part = hour < 12 ? 'Good morning' : (hour < 18 ? 'Good afternoon' : 'Good evening');

                // Update dashboard greeting (keeps role badge if present)
                const dg = document.getElementById('dashboardGreeting');
                if (dg) {
                    const roleEl = dg.querySelector('.role-badge');
                    const roleHtml = roleEl ? (' ' + roleEl.outerHTML) : '';
                    const nameText = dg.childNodes[0] ? dg.childNodes[0].textContent.trim() : '';
                    dg.innerHTML = `${part}, ${nameText}${roleHtml}`;
                }
            } catch (e) { /* ignore */ }
        });
    </script>
</body>
</html>
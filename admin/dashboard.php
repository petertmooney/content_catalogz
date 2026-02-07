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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar h1 {
            font-size: 24px;
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
        }

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
        }

        .modal.show {
            display: flex;
            justify-content: center;
            align-items: center;
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
    </style>
</head>
<body>
    
    <div class="navbar">
        <h1>📊 Content Catalogz Admin</h1>
        <div class="user-info">
            <span>Welcome, <strong><?php echo escapeHtml($user['username']); ?></strong></span>
            <form method="POST" action="api/logout.php" style="margin: 0;">
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>
    </div>

    <div class="container">
        <div class="sidebar">
            <a href="#" onclick="showSection('dashboard'); return false;" id="nav-dashboard" class="active">📋 Dashboard</a>
            
            <a href="#" class="menu-parent" onclick="toggleSubmenu(event, 'clients-submenu'); return false;">👥 Clients</a>
            <div class="submenu" id="clients-submenu">
                <a href="#" onclick="showSection('clients'); return false;" id="nav-clients">📝 Quote Requests</a>
                <a href="#" onclick="showSection('existing-clients'); return false;" id="nav-existing-clients">👤 Existing Clients</a>
            </div>
            
            <a href="#" onclick="showSection('html-files'); return false;" id="nav-html-files">📝 Edit Pages</a>
            <a href="#" onclick="openAddPageModal(); return false;">➕ New Database Page</a>
            <a href="#" onclick="showSection('database-pages'); return false;" id="nav-database-pages">📄 Database Pages</a>
            <a href="#" onclick="showSection('invoices'); return false;" id="nav-invoices">📄 Invoices</a>
            <a href="/" target="_blank">🌐 View Site</a>
            <a href="api/logout.php">🚪 Logout</a>
        </div>

        <div class="main-content">
            <!-- Dashboard Section -->
            <div id="section-dashboard" class="content-section active">
                <div class="page-header">
                    <h2>Dashboard</h2>
                    <p>Manage your website content and pages</p>
                </div>

                <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
                    <div class="stat-card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                        <h3 style="color: #667eea; margin-bottom: 10px;">HTML Pages</h3>
                        <p class="stat-number" id="html-count" style="font-size: 32px; font-weight: bold; color: #333;">0</p>
                        <small style="color: #666;">Editable HTML files</small>
                    </div>
                    <div class="stat-card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                        <h3 style="color: #764ba2; margin-bottom: 10px;">Database Pages</h3>
                        <p class="stat-number" style="font-size: 32px; font-weight: bold; color: #333;"><?php echo count($pages); ?></p>
                        <small style="color: #666;">Pages in database</small>
                    </div>
                    <div class="stat-card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                        <h3 style="color: #28a745; margin-bottom: 10px;">Client Quotes</h3>
                        <p class="stat-number" id="quotes-count" style="font-size: 32px; font-weight: bold; color: #333;">0</p>
                        <small style="color: #666;">Total quote requests</small>
                    </div>
                    <div class="stat-card" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                        <h3 style="color: #ff69b4; margin-bottom: 10px;">Invoices</h3>
                        <p class="stat-number" style="font-size: 32px; font-weight: bold; color: #333;"><?php echo $invoice_count; ?></p>
                        <small style="color: #666;">Generated invoices</small>
                    </div>
                </div>

                <div class="btn-group">
                    <button class="btn btn-primary" onclick="showSection('clients')">Quote Requests</button>
                    <button class="btn btn-primary" onclick="showSection('existing-clients')">Existing Clients</button>
                    <button class="btn btn-primary" onclick="showSection('invoices')">Search Invoices</button>
                    <button class="btn btn-primary" onclick="showSection('html-files')">Edit HTML Pages</button>
                    <button class="btn btn-primary" onclick="openAddPageModal()">+ Add Database Page</button>
                </div>
            </div>

            <!-- Clients Section -->
            <div id="section-clients" class="content-section" style="display: none;">
                <div class="page-header">
                    <h2>Client Quote Requests</h2>
                    <p>Manage client inquiries and quote requests</p>
                </div>

                <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 30px;">
                    <div class="stat-card" style="background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                        <h4 style="color: #666; font-size: 14px; margin-bottom: 5px;">Total</h4>
                        <p id="stat-total" style="font-size: 24px; font-weight: bold; color: #333;">0</p>
                    </div>
                    <div class="stat-card" style="background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                        <h4 style="color: #007bff; font-size: 14px; margin-bottom: 5px;">New</h4>
                        <p id="stat-new" style="font-size: 24px; font-weight: bold; color: #007bff;">0</p>
                    </div>
                    <div class="stat-card" style="background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                        <h4 style="color: #ffc107; font-size: 14px; margin-bottom: 5px;">Contacted</h4>
                        <p id="stat-contacted" style="font-size: 24px; font-weight: bold; color: #ffc107;">0</p>
                    </div>
                    <div class="stat-card" style="background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                        <h4 style="color: #17a2b8; font-size: 14px; margin-bottom: 5px;">In Progress</h4>
                        <p id="stat-inprogress" style="font-size: 24px; font-weight: bold; color: #17a2b8;">0</p>
                    </div>
                    <div class="stat-card" style="background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                        <h4 style="color: #28a745; font-size: 14px; margin-bottom: 5px;">Completed</h4>
                        <p id="stat-completed" style="font-size: 24px; font-weight: bold; color: #28a745;">0</p>
                    </div>
                    <div class="stat-card" style="background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
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

                <div id="html-files-list"></div>
            </div>

            <!-- Database Pages Section -->
            <div id="section-database-pages" class="content-section" style="display: none;">
                <div class="page-header">
                    <h2>Database Pages</h2>
                    <p>Manage pages stored in the database</p>
                </div>

                <div class="btn-group">
                    <button class="btn btn-primary" onclick="openAddPageModal()">+ Add New Page</button>
                </div>

            <div id="pages-section" class="table-container" style="margin-top: 30px;">
                <?php if (count($pages) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Slug</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pages as $page): ?>
                                <tr>
                                    <td><strong><?php echo escapeHtml($page['title']); ?></strong></td>
                                    <td><code><?php echo escapeHtml($page['slug']); ?></code></td>
                                    <td><?php echo escapeHtml($page['page_type'] ?? 'standard'); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo escapeHtml($page['status']); ?>">
                                            <?php echo ucfirst(escapeHtml($page['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($page['updated_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-secondary btn-sm" onclick="openEditPageModal(<?php echo $page['id']; ?>)">Edit</button>
                                        <button class="btn btn-danger btn-sm" onclick="deletePage(<?php echo $page['id']; ?>)">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <h3>No Pages Yet</h3>
                        <p>Create your first page to get started.</p>
                        <button class="btn btn-primary" onclick="openAddPageModal()" style="margin-top: 20px;">Create Page</button>
                    </div>
                <?php endif; ?>
            </div>
            </div>

            <!-- Invoices Section -->
            <div id="section-invoices" class="content-section" style="display: none;">
                <div class="page-header">
                    <h2>Invoice Search</h2>
                    <p>Search and view all generated invoices</p>
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
        <div class="modal-content">
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
                        <strong>Service:</strong><br>
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
                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 15px;">
                    <button type="button" class="btn btn-secondary" onclick="closeQuoteModal()">Close</button>
                    <button type="submit" class="btn btn-primary">Update Quote</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Client Details Modal -->
    <div id="clientModal" class="modal">
        <div class="modal-content" style="max-width: 900px;">
            <div class="modal-header">
                <h3>Client Details</h3>
                <button class="close-btn" onclick="closeClientModal()">&times;</button>
            </div>
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
                                <div style="position: relative;">
                                    <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-weight: bold; font-size: 16px; color: #333;">£</span>
                                    <input type="number" id="totalPaid" name="total_paid" class="form-control" step="0.01" min="0" oninput="calculateRemaining()" style="padding-left: 28px;">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="totalRemaining">Total Remaining (£)</label>
                            <div style="position: relative;">
                                <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-weight: bold; font-size: 18px; color: #dc3545;">£</span>
                                <input type="number" id="totalRemaining" name="total_remaining" class="form-control" step="0.01" readonly style="background:#f8f9fa; font-weight: bold; font-size: 18px; color: #dc3545; padding-left: 28px;">
                            </div>
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeClientModal()">Close</button>
                    <button type="button" class="btn btn-secondary" onclick="printInvoice()">🖨️ Print Invoice</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
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
            // Hide all sections
            document.querySelectorAll('.content-section').forEach(section => {
                section.style.display = 'none';
            });
            
            // Remove active class from all nav items
            document.querySelectorAll('.sidebar a').forEach(link => {
                link.classList.remove('active');
            });
            
            // Show selected section
            document.getElementById('section-' + sectionName).style.display = 'block';
            const navElement = document.getElementById('nav-' + sectionName);
            if (navElement) {
                navElement.classList.add('active');
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
        }

        // Load HTML files
        function loadHtmlFiles() {
            fetch('api/get_html_files.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayHtmlFiles(data.files);
                        document.getElementById('html-count').textContent = data.files.length;
                    }
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

        // Update quote status and notes
        function updateQuote(event) {
            event.preventDefault();
            
            const formData = new FormData(document.getElementById('quoteForm'));
            
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
                    alert('Quote updated successfully!');
                    closeQuoteModal();
                    loadQuotes();
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
            document.getElementById('clientId').value = client.id;
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
            
            document.getElementById('clientModal').classList.add('show');
        }

        function closeClientModal() {
            document.getElementById('clientModal').classList.remove('show');
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
                    <input type="text" class="form-control service-name" placeholder="e.g., Website Design" value="${escapeHtml(serviceName)}" oninput="calculateTotalCost()">
                </div>
                <div class="form-group" style="margin: 0;">
                    <label>Cost (£)</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-weight: 500; color: #333;">£</span>
                        <input type="number" class="form-control service-cost" step="0.01" min="0" placeholder="0.00" value="${serviceCost}" oninput="calculateTotalCost()" style="padding-left: 28px;">
                    </div>
                </div>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeServiceRow('${rowId}')" style="height: 38px;">Remove</button>
            `;
            
            container.appendChild(row);
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
            
            document.getElementById('totalRemaining').value = remaining.toFixed(2);
            
            // Color code the remaining amount
            const remainingInput = document.getElementById('totalRemaining');
            if (remaining > 0) {
                remainingInput.style.color = '#dc3545'; // Red
            } else if (remaining < 0) {
                remainingInput.style.color = '#ffc107'; // Yellow/warning
            } else {
                remainingInput.style.color = '#28a745'; // Green
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
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Client information updated successfully!');
                    closeClientModal();
                    loadExistingClients(); // Refresh the clients list
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to update client information');
            });
        }

        function printInvoice() {
            // Get current client data from the form
            const clientName = document.getElementById('clientName').textContent;
            const clientCompany = document.getElementById('clientCompany').textContent;
            const clientEmail = document.getElementById('clientEmail').textContent;
            const clientPhone = document.getElementById('clientPhone').textContent;
            const clientAddress = document.getElementById('clientAddress').value || 'N/A';
            
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
            const clientId = document.getElementById('clientId').value;
            
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
                    </style>
                </head>
                <body>
                    <button class="print-btn no-print" onclick="window.print()">🖨️ Print Invoice</button>
                    
                    <div class="invoice-header">
                        <div class="company-info">
                            <h1>Content Catalogz</h1>
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
                        <p>${clientAddress.replace(/\n/g, '<br>')}</p>
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
                    
                    <div style="margin-top: 50px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #666;">
                        <p>Thank you for your business!</p>
                        <p style="font-size: 12px;">Payment is due within 30 days of invoice date.</p>
                    </div>
                </body>
                </html>
            `;
            
            // Open invoice in new window
            const invoiceWindow = window.open('', '_blank');
            invoiceWindow.document.write(invoiceHTML);
            invoiceWindow.document.close();
        }

        // Placeholder for add client modal
        function openAddClientModal() {
            alert('Add New Client feature coming soon! For now, clients are automatically added from completed quotes.');
        }

        // Invoice search functions
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

        function displayInvoiceResults(invoices) {
            const container = document.getElementById('invoices-results');

            if (invoices.length === 0) {
                container.innerHTML = '<div class="empty-state"><h3>No Invoices Found</h3><p>No invoices match your search criteria.</p></div>';
                return;
            }

            let html = '<div style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 15px;"><h3 style="color: #333; margin-bottom: 10px;">Search Results: ' + invoices.length + ' invoice(s) found</h3></div>';
            html += '<div class="table-container"><table><thead><tr>';
            html += '<th>Invoice Number</th><th>Client Name</th><th>Company</th><th>Invoice Date</th><th>Total Cost</th><th>Total Paid</th><th>Balance Due</th><th>Actions</th>';
            html += '</tr></thead><tbody>';

            invoices.forEach(invoice => {
                const invoiceDate = new Date(invoice.invoice_date).toLocaleDateString('en-GB');
                const balanceColor = invoice.total_remaining > 0 ? '#dc3545' : '#28a745';
                
                html += `<tr>
                    <td><strong>${escapeHtml(invoice.invoice_number)}</strong></td>
                    <td>${escapeHtml(invoice.name)}</td>
                    <td>${invoice.company ? escapeHtml(invoice.company) : '<em>N/A</em>'}</td>
                    <td>${invoiceDate}</td>
                    <td>£${parseFloat(invoice.total_cost).toFixed(2)}</td>
                    <td>£${parseFloat(invoice.total_paid).toFixed(2)}</td>
                    <td style="color: ${balanceColor}; font-weight: bold;">£${parseFloat(invoice.total_remaining).toFixed(2)}</td>
                    <td>
                        <button class="btn btn-primary btn-sm" onclick="viewClientDetails(${invoice.client_id})">View Client</button>
                        <a href="mailto:${escapeHtml(invoice.email)}" class="btn btn-secondary btn-sm">Email</a>
                    </td>
                </tr>`;
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
            loadHtmlFiles();
            loadQuotes();
        });
    </script>
</body>
</html>

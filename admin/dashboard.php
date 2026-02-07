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

        .main-content {
            flex: 1;
            padding: 30px;
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
            <a href="dashboard.php" class="active">📋 Dashboard</a>
            <a href="#" onclick="openAddPageModal(); return false;">➕ New Page</a>
            <a href="#" onclick="document.getElementById('pages-section').scrollIntoView(); return false;">📄 All Pages</a>
            <a href="api/logout.php">🚪 Logout</a>
        </div>

        <div class="main-content">
            <div class="page-header">
                <h2>Dashboard</h2>
                <p>Manage your website content and pages</p>
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
    </div>

    <!-- Add/Edit Page Modal -->
    <div id="pageModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Add New Page</h3>
                <button class="close-btn" onclick="closePageModal()">&times;</button>
            </div>
            <form id="pageForm" method="POST" action="api/save_page.php">
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

    <script>
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
                });
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('pageModal');
            if (event.target === modal) {
                closePageModal();
            }
        };
    </script>
</body>
</html>

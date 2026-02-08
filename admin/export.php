<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
require_once 'config/db.php';
require_once 'config/auth.php';

requireLogin();

// Handle export request BEFORE any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'export') {
    header('Content-Type: application/json');
    
    try {
        // Check if ZipArchive is available
        if (!class_exists('ZipArchive')) {
            throw new Exception('ZipArchive extension is not available on this server');
        }
        
        // Export configuration
        $exportDir = __DIR__ . '/../exports';
        $timestamp = date('Y-m-d_H-i-s');
        $exportName = "content_catalogz_export_{$timestamp}";
        $exportPath = "{$exportDir}/{$exportName}";
        
        // Create export directory
        if (!file_exists($exportDir)) {
            if (!mkdir($exportDir, 0755, true)) {
                throw new Exception('Failed to create export directory');
            }
        }
        if (!file_exists($exportPath)) {
            if (!mkdir($exportPath, 0755, true)) {
                throw new Exception('Failed to create temporary export directory');
            }
        }
        
        $exportLog = [];
        
        // Function to export database
        $exportDatabase = function($conn, $exportPath) {
            $sqlFile = "{$exportPath}/database.sql";
            $tables = [];
            
            // Get all tables
            $result = $conn->query("SHOW TABLES");
            if (!$result) {
                throw new Exception('Failed to fetch database tables: ' . $conn->error);
            }
            
            while ($row = $result->fetch_array()) {
                $tables[] = $row[0];
            }
            
            $output = "-- Content Catalogz Database Export\n";
            $output .= "-- Export Date: " . date('Y-m-d H:i:s') . "\n\n";
            $output .= "SET FOREIGN_KEY_CHECKS=0;\n";
            $output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
            $output .= "SET time_zone = \"+00:00\";\n\n";
            
            foreach ($tables as $table) {
                // Get table structure
                $result = $conn->query("SHOW CREATE TABLE `{$table}`");
                if (!$result) {
                    throw new Exception("Failed to get structure for table {$table}: " . $conn->error);
                }
                $row = $result->fetch_row();
                
                $output .= "\n-- Table structure for table `{$table}`\n";
                $output .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $output .= $row[1] . ";\n\n";
                
                // Get table data
                $result = $conn->query("SELECT * FROM `{$table}`");
                if ($result && $result->num_rows > 0) {
                    $output .= "-- Dumping data for table `{$table}`\n";
                    
                    while ($row = $result->fetch_assoc()) {
                        $values = [];
                        foreach ($row as $value) {
                            if ($value === null) {
                                $values[] = 'NULL';
                            } else {
                                $values[] = "'" . $conn->real_escape_string($value) . "'";
                            }
                        }
                        $output .= "INSERT INTO `{$table}` VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $output .= "\n";
                }
            }
            
            $output .= "SET FOREIGN_KEY_CHECKS=1;\n";
            
            if (file_put_contents($sqlFile, $output) === false) {
                throw new Exception('Failed to write database export file');
            }
            return filesize($sqlFile);
        };
        
        // Function to copy directory recursively
        $copyDirectory = function($src, $dst, $exclude = []) use (&$copyDirectory) {
            if (!is_dir($src)) {
                throw new Exception("Source directory does not exist: {$src}");
            }
            
            $dir = opendir($src);
            if (!$dir) {
                throw new Exception("Failed to open directory: {$src}");
            }
            
            if (!@mkdir($dst, 0755, true) && !is_dir($dst)) {
                throw new Exception("Failed to create directory: {$dst}");
            }
            
            $fileCount = 0;
            $totalSize = 0;
            
            while (false !== ($file = readdir($dir))) {
                if (($file != '.') && ($file != '..')) {
                    $fullPath = $src . '/' . $file;
                    
                    // Skip excluded directories
                    $skip = false;
                    foreach ($exclude as $pattern) {
                        if (strpos($fullPath, $pattern) !== false || $file === $pattern) {
                            $skip = true;
                            break;
                        }
                    }
                    
                    if ($skip) continue;
                    
                    if (is_dir($fullPath)) {
                        $result = $copyDirectory($fullPath, $dst . '/' . $file, $exclude);
                        $fileCount += $result['count'];
                        $totalSize += $result['size'];
                    } else {
                        if (!copy($fullPath, $dst . '/' . $file)) {
                            throw new Exception("Failed to copy file: {$fullPath}");
                        }
                        $fileCount++;
                        $totalSize += filesize($fullPath);
                    }
                }
            }
            closedir($dir);
            
            return ['count' => $fileCount, 'size' => $totalSize];
        };
        
        // Helper function to format bytes
        $formatBytes = function($bytes, $precision = 2) {
            $units = ['B', 'KB', 'MB', 'GB'];
            $bytes = max($bytes, 0);
            $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
            $pow = min($pow, count($units) - 1);
            $bytes /= pow(1024, $pow);
            return round($bytes, $precision) . ' ' . $units[$pow];
        };
        
        // Helper function to delete directory
        $deleteDirectory = function($dir) use (&$deleteDirectory) {
            if (!file_exists($dir)) return true;
            if (!is_dir($dir)) return unlink($dir);
            
            foreach (scandir($dir) as $item) {
                if ($item == '.' || $item == '..') continue;
                if (!$deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) return false;
            }
            
            return rmdir($dir);
        };
        
        // 1. Export Database
        $exportLog[] = "Exporting database...";
        $dbSize = $exportDatabase($conn, $exportPath);
        $exportLog[] = "✓ Database exported (" . $formatBytes($dbSize) . ")";
        
        // 2. Copy website files
        $exportLog[] = "Copying website files...";
        $exclude = [
            'exports',
            '.git',
            '.github',
            'node_modules',
            'vendor',
            '.env',
            '.devcontainer'
        ];
        
        $rootDir = dirname(__DIR__);
        $result = $copyDirectory($rootDir, "{$exportPath}/website", $exclude);
        $exportLog[] = "✓ Website files copied ({$result['count']} files, " . $formatBytes($result['size']) . ")";
        
        // 3. Create configuration file
        $exportLog[] = "Creating configuration file...";
        $config = [
            'export_date' => date('Y-m-d H:i:s'),
            'export_version' => '1.0',
            'database_name' => 'Content_Catalogz',
            'file_count' => $result['count'],
            'total_size' => $result['size'] + $dbSize
        ];
        
        file_put_contents("{$exportPath}/EXPORT_INFO.json", json_encode($config, JSON_PRETTY_PRINT));
        $exportLog[] = "✓ Configuration file created";
        
        // 4. Create README
        $exportLog[] = "Creating installation guide...";
        $readme = <<<README
# Content Catalogz Export Package
Export Date: {$config['export_date']}

## Contents
- database.sql: Complete database dump
- website/: All website files
- EXPORT_INFO.json: Export metadata
- README.txt: This file

## Installation Instructions

### 1. Upload Files
Upload the contents of the 'website' folder to your web server's public directory

### 2. Create Database
Create a new MySQL database and note the credentials

### 3. Import Database
Import database.sql using phpMyAdmin or command line:
mysql -u username -p database_name < database.sql

### 4. Update Configuration
Edit admin/config/db.php with your database credentials

### 5. Set Permissions
Set proper file permissions (755 for directories, 644 for files)

### 6. Test Your Site
Visit your domain to verify everything works

README;
        file_put_contents("{$exportPath}/README.txt", $readme);
        $exportLog[] = "✓ Installation guide created";
        
        // 5. Create zip archive
        $exportLog[] = "Creating zip archive...";
        $zipFile = "{$exportDir}/{$exportName}.zip";
        
        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            throw new Exception("Failed to create zip archive");
        }
        
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($exportPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($exportPath) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
        
        $zip->close();
        $zipSize = filesize($zipFile);
        $exportLog[] = "✓ Zip archive created (" . $formatBytes($zipSize) . ")";
        
        // Clean up temporary export folder
        $deleteDirectory($exportPath);
        $exportLog[] = "✓ Temporary files cleaned up";
        
        echo json_encode([
            'success' => true,
            'log' => $exportLog,
            'filename' => basename($zipFile),
            'size' => $zipSize,
            'download_url' => '../exports/' . basename($zipFile)
        ]);
        exit;
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'log' => isset($exportLog) ? $exportLog : []
        ]);
        exit;
    }
}

// Helper function to format bytes for display
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Export directory for page display
$exportDir = __DIR__ . '/../exports';

// List existing exports
$existingExports = [];
if (file_exists($exportDir)) {
    $files = glob($exportDir . '/*.zip');
    foreach ($files as $file) {
        $existingExports[] = [
            'name' => basename($file),
            'size' => filesize($file),
            'date' => date('Y-m-d H:i:s', filemtime($file))
        ];
    }
    usort($existingExports, function($a, $b) {
        return strcmp($b['date'], $a['date']);
    });
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Website - Content Catalogz</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 30px; }
        h1 { color: #333; margin-bottom: 10px; }
        .subtitle { color: #666; margin-bottom: 30px; }
        .export-section { background: #f8f9fa; padding: 25px; border-radius: 8px; margin-bottom: 30px; }
        .export-info { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .info-item { background: white; padding: 15px; border-radius: 6px; border-left: 4px solid #43081A; }
        .info-label { font-size: 12px; color: #666; text-transform: uppercase; margin-bottom: 5px; }
        .info-value { font-size: 18px; font-weight: 600; color: #333; }
        .btn { display: inline-block; padding: 12px 24px; background: #43081A; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; text-decoration: none; transition: all 0.3s; }
        .btn:hover { background: #5a0a22; }
        .btn:disabled { background: #ccc; cursor: not-allowed; }
        .btn-secondary { background: #6c757d; }
        .btn-secondary:hover { background: #5a6268; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .btn-sm { padding: 6px 12px; font-size: 13px; }
        .progress-container { display: none; margin-top: 20px; }
        .progress-log { background: #2d3748; color: #48bb78; padding: 20px; border-radius: 6px; font-family: 'Courier New', monospace; font-size: 13px; max-height: 300px; overflow-y: auto; margin-bottom: 15px; }
        .progress-log div { margin-bottom: 5px; }
        .success-message { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 6px; margin-bottom: 15px; }
        .error-message { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 6px; margin-bottom: 15px; }
        .exports-list { margin-top: 30px; }
        .export-item { background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        .export-details { flex: 1; }
        .export-name { font-weight: 600; color: #333; margin-bottom: 5px; }
        .export-meta { font-size: 13px; color: #666; }
        .export-actions { display: flex; gap: 10px; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #43081A; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
        .instructions { background: #fff3cd; border: 1px solid #ffeaa7; padding: 20px; border-radius: 6px; margin-top: 30px; }
        .instructions h3 { color: #856404; margin-bottom: 15px; }
        .instructions ol { margin-left: 20px; color: #856404; }
        .instructions li { margin-bottom: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
        
        <h1>📦 Export Website</h1>
        <p class="subtitle">Create a complete backup package for server deployment</p>
        
        <div class="export-section">
            <h3 style="margin-bottom: 15px; color: #333;">Export Package Includes:</h3>
            <div class="export-info">
                <div class="info-item">
                    <div class="info-label">Database</div>
                    <div class="info-value">Complete SQL dump</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Website Files</div>
                    <div class="info-value">All HTML, PHP, CSS, JS</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Media</div>
                    <div class="info-value">Images & Uploads</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Documentation</div>
                    <div class="info-value">Setup Instructions</div>
                </div>
            </div>
            
            <button id="exportBtn" class="btn" onclick="startExport()">
                📦 Create Export Package
            </button>
            
            <div id="progressContainer" class="progress-container">
                <div class="progress-log" id="progressLog"></div>
                <div id="exportResult"></div>
            </div>
        </div>
        
        <?php if (count($existingExports) > 0): ?>
        <div class="exports-list">
            <h3 style="margin-bottom: 15px; color: #333;">📋 Previous Exports</h3>
            <?php foreach ($existingExports as $export): ?>
            <div class="export-item">
                <div class="export-details">
                    <div class="export-name"><?php echo htmlspecialchars($export['name']); ?></div>
                    <div class="export-meta">
                        <?php echo formatBytes($export['size']); ?> • 
                        <?php echo htmlspecialchars($export['date']); ?>
                    </div>
                </div>
                <div class="export-actions">
                    <a href="../exports/<?php echo urlencode($export['name']); ?>" class="btn btn-sm btn-secondary" download>
                        ⬇️ Download
                    </a>
                    <button class="btn btn-sm btn-danger" onclick="deleteExport('<?php echo htmlspecialchars($export['name']); ?>')">
                        🗑️ Delete
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <div class="instructions">
            <h3>📘 After Export:</h3>
            <ol>
                <li>Download the generated .zip file</li>
                <li>Upload to your production server via FTP/SFTP</li>
                <li>Extract the zip file in your web root directory</li>
                <li>Follow the README.txt instructions included in the package</li>
                <li>Update database credentials in admin/config/db.php</li>
                <li>Import database.sql into your production database</li>
                <li>Set proper file permissions (755 for directories, 644 for files)</li>
                <li>Test your website thoroughly</li>
            </ol>
        </div>
    </div>
    
    <script>
        function startExport() {
            const btn = document.getElementById('exportBtn');
            const progressContainer = document.getElementById('progressContainer');
            const progressLog = document.getElementById('progressLog');
            const exportResult = document.getElementById('exportResult');
            
            btn.disabled = true;
            btn.textContent = 'Exporting... Please wait';
            progressContainer.style.display = 'block';
            progressLog.innerHTML = '<div>🚀 Starting export process...</div>';
            exportResult.innerHTML = '';
            
            fetch('export.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=export'
            })
            .then(res => res.json())
            .then(data => {
                if (data.log) {
                    progressLog.innerHTML = data.log.map(line => `<div>${line}</div>`).join('');
                }
                
                if (data.success) {
                    const size = formatBytes(data.size);
                    exportResult.innerHTML = `
                        <div class="success-message">
                            <strong>✅ Export Complete!</strong><br>
                            Package: ${data.filename}<br>
                            Size: ${size}<br>
                            <a href="${data.download_url}" class="btn" style="margin-top: 10px;" download>
                                ⬇️ Download Export Package
                            </a>
                        </div>
                    `;
                    setTimeout(() => location.reload(), 3000);
                } else {
                    exportResult.innerHTML = `
                        <div class="error-message">
                            <strong>❌ Export Failed</strong><br>
                            ${data.error || 'Unknown error occurred'}
                        </div>
                    `;
                }
                
                btn.disabled = false;
                btn.textContent = '📦 Create Export Package';
            })
            .catch(error => {
                console.error('Error:', error);
                exportResult.innerHTML = `
                    <div class="error-message">
                        <strong>❌ Export Failed</strong><br>
                        ${error.message}
                    </div>
                `;
                btn.disabled = false;
                btn.textContent = '📦 Create Export Package';
            });
        }
        
        function deleteExport(filename) {
            if (!confirm(`Delete export: ${filename}?`)) return;
            
            fetch('api/delete_export.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({filename: filename})
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to delete export'));
                }
            })
            .catch(error => {
                alert('Error deleting export: ' + error.message);
            });
        }
        
        function formatBytes(bytes) {
            const units = ['B', 'KB', 'MB', 'GB'];
            let size = Math.max(bytes, 0);
            let pow = Math.floor((size ? Math.log(size) : 0) / Math.log(1024));
            pow = Math.min(pow, units.length - 1);
            size /= Math.pow(1024, pow);
            return Math.round(size * 100) / 100 + ' ' + units[pow];
        }
    </script>
</body>
</html>

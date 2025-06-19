<?php
require_once '../../includes/config.php';

// Check admin authentication
requireAdmin();

// Validate file ID
if (!isset($_GET['file']) || !is_numeric($_GET['file'])) {
    http_response_code(400);
    die('ID de fichier invalide');
}

$fileId = (int)$_GET['file'];

// Get database connection
$pdo = getDBConnection();
if (!$pdo) {
    http_response_code(500);
    die('Database connection failed');
}

try {
    // Get file information
    $stmt = $pdo->prepare("
        SELECT rf.*, ar.user_id
        FROM request_files rf
        JOIN automation_requests ar ON rf.request_id = ar.id
        WHERE rf.id = ?
    ");
    
    $stmt->execute([$fileId]);
    $file = $stmt->fetch();

    if (!$file) {
        http_response_code(404);
        die('Fichier non trouvé');
    }

    $filePath = $file['file_path'];

    // Verify file exists
    if (!file_exists($filePath)) {
        http_response_code(404);
        die('Fichier non trouvé sur le serveur');
    }

    // Set headers for download
    header('Content-Type: ' . ($file['file_type'] ?: 'application/octet-stream'));
    header('Content-Disposition: attachment; filename="' . basename($file['file_name']) . '"');
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Output file content
    readfile($filePath);
    exit;

} catch (PDOException $e) {
    error_log("Error downloading file: " . $e->getMessage());
    http_response_code(500);
    die('Une erreur est survenue lors du téléchargement du fichier');
} 
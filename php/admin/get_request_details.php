<?php
require_once '../../includes/config.php';

header('Content-Type: application/json');

// Check admin authentication
requireAdmin();

// Validate request ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de demande invalide']);
    exit;
}

$requestId = (int)$_GET['id'];

// Get database connection
$pdo = getDBConnection();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

try {
    // Get request details with user information
    $query = "
        SELECT 
            ar.*,
            u.email as user_email,
            CONCAT(u.first_name, ' ', u.last_name) as user_name,
            u.account_type
        FROM automation_requests ar
        JOIN users u ON ar.user_id = u.id
        WHERE ar.id = ?
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$requestId]);
    $request = $stmt->fetch();

    if (!$request) {
        http_response_code(404);
        echo json_encode(['error' => 'Demande non trouvée']);
        exit;
    }

    // Get attached files
    $query = "
        SELECT id, file_name, file_type, file_size, uploaded_at
        FROM request_files
        WHERE request_id = ?
        ORDER BY uploaded_at DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$requestId]);
    $request['files'] = $stmt->fetchAll();

    // Get status history
    $query = "
        SELECT 
            rsh.*,
            CONCAT(u.first_name, ' ', u.last_name) as admin_name
        FROM request_status_history rsh
        JOIN users u ON rsh.admin_id = u.id
        WHERE rsh.request_id = ?
        ORDER BY rsh.created_at DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$requestId]);
    $request['status_history'] = $stmt->fetchAll();

    echo json_encode(['request' => $request]);

} catch (PDOException $e) {
    error_log("Error fetching request details: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Une erreur est survenue lors de la récupération des détails de la demande']);
    exit;
} 
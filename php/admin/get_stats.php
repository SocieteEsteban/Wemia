<?php
require_once '../../includes/config.php';

header('Content-Type: application/json');

// Check admin authentication
requireAdmin();

// Get database connection
$pdo = getDBConnection();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

try {
    // Get counts for each status
    $query = "
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
        FROM automation_requests
    ";
    
    $stmt = $pdo->query($query);
    $stats = $stmt->fetch();

    echo json_encode([
        'stats' => [
            'total' => (int)$stats['total'],
            'pending' => (int)$stats['pending'],
            'inProgress' => (int)$stats['in_progress'],
            'completed' => (int)$stats['completed']
        ]
    ]);

} catch (PDOException $e) {
    error_log("Error fetching stats: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Une erreur est survenue lors de la récupération des statistiques']);
    exit;
} 
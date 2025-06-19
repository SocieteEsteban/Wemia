<?php
require_once '../../includes/config.php';

header('Content-Type: application/json');

// Check admin authentication
requireAdmin();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Prepare pagination
$page = isset($input['page']) ? (int)$input['page'] : 1;
$perPage = isset($input['perPage']) ? (int)$input['perPage'] : 10;
$offset = ($page - 1) * $perPage;

// Get database connection
$pdo = getDBConnection();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

try {
    // Build query conditions
    $conditions = [];
    $params = [];

    if (!empty($input['status'])) {
        $conditions[] = 'ar.status = ?';
        $params[] = $input['status'];
    }

    if (!empty($input['priority'])) {
        $conditions[] = 'ar.priority = ?';
        $params[] = $input['priority'];
    }

    if (!empty($input['search'])) {
        $searchTerm = '%' . $input['search'] . '%';
        $conditions[] = '(ar.title LIKE ? OR ar.description LIKE ?)';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }

    $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

    // Get total count
    $countQuery = "
        SELECT COUNT(*) 
        FROM automation_requests ar
        $whereClause
    ";
    
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($params);
    $total = $stmt->fetchColumn();

    // Get requests
    $query = "
        SELECT 
            ar.*,
            u.email as user_email,
            CONCAT(u.first_name, ' ', u.last_name) as user_name,
            u.account_type
        FROM automation_requests ar
        JOIN users u ON ar.user_id = u.id
        $whereClause
        ORDER BY ar.created_at DESC
        LIMIT ? OFFSET ?
    ";

    $stmt = $pdo->prepare($query);
    $params[] = $perPage;
    $params[] = $offset;
    $stmt->execute($params);
    
    $requests = $stmt->fetchAll();

    echo json_encode([
        'requests' => $requests,
        'total' => $total
    ]);

} catch (PDOException $e) {
    error_log("Error fetching requests: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Une erreur est survenue lors de la récupération des demandes']);
    exit;
} 
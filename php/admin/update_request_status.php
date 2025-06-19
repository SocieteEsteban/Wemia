<?php
require_once '../../includes/config.php';

header('Content-Type: application/json');

// Check admin authentication
requireAdmin();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['request_id']) || !is_numeric($input['request_id']) ||
    !isset($input['new_status']) || !in_array($input['new_status'], ['pending', 'approved', 'rejected', 'in_progress', 'completed'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Données invalides']);
    exit;
}

// Get database connection
$pdo = getDBConnection();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Get current status
    $stmt = $pdo->prepare("SELECT status FROM automation_requests WHERE id = ?");
    $stmt->execute([$input['request_id']]);
    $currentStatus = $stmt->fetchColumn();

    if ($currentStatus === false) {
        throw new Exception('Demande non trouvée');
    }

    // Update request status
    $stmt = $pdo->prepare("UPDATE automation_requests SET status = ? WHERE id = ?");
    $stmt->execute([$input['new_status'], $input['request_id']]);

    // Add status history entry
    $stmt = $pdo->prepare("
        INSERT INTO request_status_history (
            request_id,
            admin_id,
            old_status,
            new_status,
            comment,
            created_at
        ) VALUES (?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $input['request_id'],
        $_SESSION['user_id'],
        $currentStatus,
        $input['new_status'],
        $input['comment'] ?? null
    ]);

    // Commit transaction
    $pdo->commit();

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Error updating request status: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Une erreur est survenue lors de la mise à jour du statut']);
    exit;
} 
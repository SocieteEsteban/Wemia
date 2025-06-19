<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

// Check authentication
if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

// Validate required fields
$required_fields = ['title', 'description'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        http_response_code(400);
        echo json_encode(['error' => 'Le titre et la description sont obligatoires']);
        exit;
    }
}

// Sanitize inputs
$title = sanitizeInput($_POST['title']);
$description = sanitizeInput($_POST['description']);
$budget = isset($_POST['budget']) ? floatval($_POST['budget']) : null;
$deadline = isset($_POST['deadline']) ? date('Y-m-d', strtotime($_POST['deadline'])) : null;

// Validate budget
if ($budget !== null && $budget < 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Le budget ne peut pas être négatif']);
    exit;
}

// Validate deadline
if ($deadline !== null && strtotime($deadline) < strtotime('today')) {
    http_response_code(400);
    echo json_encode(['error' => 'La date limite ne peut pas être dans le passé']);
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
    // Begin transaction
    $pdo->beginTransaction();

    // Insert request
    $stmt = $pdo->prepare('
        INSERT INTO automation_requests (user_id, title, description, budget, deadline, status)
        VALUES (?, ?, ?, ?, ?, \'pending\')
    ');
    $stmt->execute([$_SESSION['user_id'], $title, $description, $budget, $deadline]);
    $requestId = $pdo->lastInsertId();

    // Handle file uploads
    if (!empty($_FILES['files'])) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
            $fileName = $_FILES['files']['name'][$key];
            $fileType = $_FILES['files']['type'][$key];
            $fileSize = $_FILES['files']['size'][$key];
            $error = $_FILES['files']['error'][$key];

            if ($error === UPLOAD_ERR_OK) {
                // Generate unique filename
                $uniqueName = uniqid() . '_' . $fileName;
                $filePath = $uploadDir . $uniqueName;

                // Move uploaded file
                if (move_uploaded_file($tmp_name, $filePath)) {
                    // Save file info in database
                    $stmt = $pdo->prepare('
                        INSERT INTO request_files (request_id, file_name, file_path, file_type, file_size)
                        VALUES (?, ?, ?, ?, ?)
                    ');
                    $stmt->execute([$requestId, $fileName, $uniqueName, $fileType, $fileSize]);
                }
            }
        }
    }

    // Commit transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'request' => [
            'id' => $requestId,
            'title' => $title,
            'description' => $description,
            'budget' => $budget,
            'deadline' => $deadline
        ]
    ]);

} catch (PDOException $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Request submission error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Une erreur est survenue lors de l\'enregistrement de la demande']);
    exit;
} 
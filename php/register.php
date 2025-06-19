<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

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
$required_fields = ['firstName', 'lastName', 'email', 'password', 'passwordConfirm', 'accountType'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        http_response_code(400);
        echo json_encode(['error' => 'Tous les champs sont obligatoires']);
        exit;
    }
}

// Sanitize inputs
$firstName = sanitizeInput($_POST['firstName']);
$lastName = sanitizeInput($_POST['lastName']);
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$password = $_POST['password'];
$passwordConfirm = $_POST['passwordConfirm'];
$accountType = sanitizeInput($_POST['accountType']);
$companyName = isset($_POST['companyName']) ? sanitizeInput($_POST['companyName']) : null;

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Email invalide']);
    exit;
}

// Validate password
if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(['error' => 'Le mot de passe doit contenir au moins 8 caractères']);
    exit;
}

if ($password !== $passwordConfirm) {
    http_response_code(400);
    echo json_encode(['error' => 'Les mots de passe ne correspondent pas']);
    exit;
}

// Validate account type
$valid_account_types = ['individual', 'freelance', 'company'];
if (!in_array($accountType, $valid_account_types)) {
    http_response_code(400);
    echo json_encode(['error' => 'Type de compte invalide']);
    exit;
}

// Validate company name for company accounts
if ($accountType === 'company' && empty($companyName)) {
    http_response_code(400);
    echo json_encode(['error' => 'Le nom de l\'entreprise est obligatoire pour un compte entreprise']);
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
    // Check if email already exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['error' => 'Cet email est déjà utilisé']);
        exit;
    }

    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => HASH_COST]);

    // Begin transaction
    $pdo->beginTransaction();

    // Insert user
    $stmt = $pdo->prepare('
        INSERT INTO users (email, password_hash, first_name, last_name, company_name, account_type)
        VALUES (?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([$email, $password_hash, $firstName, $lastName, $companyName, $accountType]);
    $userId = $pdo->lastInsertId();

    // Commit transaction
    $pdo->commit();

    // Set session variables
    $_SESSION['user_id'] = $userId;
    $_SESSION['email'] = $email;
    $_SESSION['is_admin'] = false;
    
    // Generate new CSRF token
    $_SESSION['csrf_token'] = generateToken();

    echo json_encode([
        'success' => true,
        'user' => [
            'id' => $userId,
            'email' => $email,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'accountType' => $accountType,
            'companyName' => $companyName
        ]
    ]);

} catch (PDOException $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Registration error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Une erreur est survenue lors de l\'inscription']);
    exit;
} 
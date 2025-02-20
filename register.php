<?php
require_once 'config.php';
header('Content-Type: application/json');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $data = json_decode(file_get_contents('php://input'), true);

    // Validate required fields
    $required = ['firstName', 'lastName', 'birthdate', 'country', 'address', 'email', 'password'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception(ucfirst($field) . ' is required');
        }
    }

    // Process email
    $email = strtolower(trim($data['email']));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Please enter a valid email address');
    }
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    // Check existing email
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        throw new Exception('Email already registered');
    }

    // Validate birthdate
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['birthdate'])) {
        throw new Exception('Invalid birthdate format');
    }

    // Hash password
    $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);

    // Insert user
    $stmt = $pdo->prepare("INSERT INTO users (
        first_name,
        last_name,
        birthdate,
        country,
        address,
        email,
        password_hash,
        phone
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([
        htmlspecialchars(trim($data['firstName'])),
        htmlspecialchars(trim($data['lastName'])),
        $data['birthdate'],
        htmlspecialchars(trim($data['country'])),
        htmlspecialchars(trim($data['address'])),
        $email,
        $passwordHash,
        !empty($data['phone']) ? htmlspecialchars(trim($data['phone'])) : null
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Registration successful'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
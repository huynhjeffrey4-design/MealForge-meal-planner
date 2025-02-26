<?php



if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redirect to registration form
    header('Location: registration.php');
    exit;
}

require_once 'config.php';
require __DIR__ . '/vendor/autoload.php';
include __DIR__ . '/setup.php';
session_start();

$errors = [];

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Validate required fields
    $required = ['firstName', 'lastName', 'email', 'password'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $errors[$field] = ucfirst($field) . ' is required';
        }
    }
    
    // Process and validate email
    if (!empty($_POST['email'])) {
        $email = strtolower(trim($_POST['email']));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address';
        } else {
            // Check if email already exists
            try {
                $pdo = new PDO(
                    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                    DB_USER,
                    DB_PASS,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                
                $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->rowCount() > 0) {
                    $errors['email'] = 'Email already registered';
                }
            } catch (PDOException $e) {
                $error_message = "Database error: " . $e->getMessage();
            }
        }
    }
    
    // Validate password
    if (!empty($_POST['password']) && strlen($_POST['password']) < 8) {
        $errors['password'] = 'Password must be at least 8 characters';
    }
    
    // If no errors, proceed with registration
    if (empty($errors)) {
        try {
            // Hash password
            $passwordHash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            
            // Insert user
            $stmt = $pdo->prepare("INSERT INTO users (
                first_name,
                last_name,
                email,
                password_hash
            ) VALUES (?, ?, ?, ?)");
            
            $stmt->execute([
                htmlspecialchars(trim($_POST['firstName'])),
                htmlspecialchars(trim($_POST['lastName'])),
                $email,
                $passwordHash
            ]);
            
            // Redirect to login page with success message
            $_SESSION['registration_success'] = true;
            header('Location: login.php');
            exit;
            
        } catch (PDOException $e) {
            $error_message = "Registration failed: " . $e->getMessage();
        }
    }
}

// If there were errors, include the registration form again
include 'registration.php';
?>
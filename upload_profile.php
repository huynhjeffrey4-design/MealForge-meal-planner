<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];

// Handle the upload
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    // Store file in base64 format in the database instead of as a file
    $imageData = file_get_contents($_FILES['profile_picture']['tmp_name']);
    $base64Image = 'data:' . $_FILES['profile_picture']['type'] . ';base64,' . base64_encode($imageData);
    
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Update the database with the base64 image data
        $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE user_id = ?");
        $success = $stmt->execute([$base64Image, $userId]);
        
        if ($success) {
            $_SESSION['message'] = "Profile picture updated successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error updating profile picture in database.";
            $_SESSION['message_type'] = "error";
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = "Database error: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
    }
} else {
    $errorCode = $_FILES['profile_picture']['error'] ?? 'No file uploaded';
    $_SESSION['message'] = "Upload error: " . $errorCode;
    $_SESSION['message_type'] = "error";
}

// Redirect back to profile page
header('Location: profile.php');
exit;
?>
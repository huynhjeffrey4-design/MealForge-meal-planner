<?php
session_start();
require_once __DIR__ . '/controllers/control.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$userId = $_SESSION['user']['id'];
$userController = getUserController();

// Handle the upload
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    // Store file in base64 format in the database
    $imageData = file_get_contents($_FILES['profile_picture']['tmp_name']);
    $base64Image = 'data:' . $_FILES['profile_picture']['type'] . ';base64,' . base64_encode($imageData);
    
    // Update user data with new profile picture
    $userData = [
        'profile_picture' => $base64Image
    ];
    
    $success = $userController->updateUser($userId, $userData);
    
    if ($success) {
        $_SESSION['message'] = "Profile picture updated successfully!";
        $_SESSION['message_type'] = "success";
        
        // Update the session with the new profile picture
        $updatedUser = $userController->getUserById($userId);
        if ($updatedUser) {
            $_SESSION['user'] = $updatedUser;
        }
    } else {
        $_SESSION['message'] = "Error updating profile picture.";
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

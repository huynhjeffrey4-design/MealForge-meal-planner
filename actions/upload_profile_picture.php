<?php
session_start();
require_once __DIR__ . '/../controllers/user.php';

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$userId = $_SESSION['user']['id'];
$userController = getUserController();

if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $imageData = file_get_contents($_FILES['profile_picture']['tmp_name']);
    $base64Image = 'data:' . $_FILES['profile_picture']['type'] . ';base64,' . base64_encode($imageData);
    
    $userData = [
        'profile_picture' => $base64Image
    ];
    
    $success = $userController->updateUser($userId, $userData);
    
    if ($success) {
        $_SESSION['message'] = "Profile picture updated successfully!";
        $_SESSION['message_type'] = "success";
        
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

header('Location: profile.php');
exit;

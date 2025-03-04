<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Load database configuration
require_once 'config.php';

// Verify user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = "You must be logged in to update your profile.";
    $_SESSION['message_type'] = "error";
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Connect to database
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Prepare SQL statement with only the fields that are present in the form
        $updateFields = [];
        $params = [];
        
        // Map and filter fields
        $allowedFields = [
            'first_name' => 'first_name',
            'last_name' => 'last_name', 
            'dob' => 'date_of_birth',
            'gender' => 'gender',
            'email' => 'email',
            'phone' => 'phone_number'
        ];
        
        // Validate email if it's being updated
        if (isset($_POST['email']) && !empty($_POST['email'])) {
            $email = trim($_POST['email']);
            
            // Check if email is valid
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['message'] = "Please enter a valid email address.";
                $_SESSION['message_type'] = "error";
                header('Location: profile.php');
                exit;
            }
            
            // Check if email is already in use by another user
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
            $stmt->execute([$email, $userId]);
            if ($stmt->rowCount() > 0) {
                $_SESSION['message'] = "Email is already in use by another account.";
                $_SESSION['message_type'] = "error";
                header('Location: profile.php');
                exit;
            }
        }
        
        // Build update query for regular fields
        foreach ($allowedFields as $formField => $dbField) {
            if (isset($_POST[$formField])) {
                $updateFields[] = "$dbField = ?";
                $params[] = trim($_POST[$formField]);
            }
        }
        
        // Handle dietary restrictions (checkboxes)
        if (isset($_POST['dietary_restrictions']) && is_array($_POST['dietary_restrictions'])) {
            // Join selected values with commas
            $restrictions = implode(',', $_POST['dietary_restrictions']);
            $updateFields[] = "dietary_restrictions = ?";
            $params[] = $restrictions;
        } elseif (isset($_POST['edit_all'])) {
            // If form was submitted but no checkboxes were selected, set to empty
            $updateFields[] = "dietary_restrictions = ?";
            $params[] = "";
        }
        
        // Handle dietary preferences (checkboxes)
        if (isset($_POST['dietary_preferences']) && is_array($_POST['dietary_preferences'])) {
            // Join selected values with commas
            $preferences = implode(',', $_POST['dietary_preferences']);
            $updateFields[] = "dietary_preferences = ?";
            $params[] = $preferences;
        } elseif (isset($_POST['edit_all'])) {
            // If form was submitted but no checkboxes were selected, set to empty
            $updateFields[] = "dietary_preferences = ?";
            $params[] = "";
        }
        
        // Only proceed if there are fields to update
        if (count($updateFields) > 0) {
            // Add user_id to parameters
            $params[] = $userId;
            
            // Create and execute update query
            $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE user_id = ?";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute($params);
            
            if ($result) {
                $_SESSION['message'] = "Profile updated successfully!";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "No changes were made to your profile.";
                $_SESSION['message_type'] = "info";
            }
        } else {
            $_SESSION['message'] = "No fields were submitted for update.";
            $_SESSION['message_type'] = "info";
        }
    } catch (PDOException $e) {
        // Log the detailed error
        error_log("Database error in update_field.php: " . $e->getMessage());
        
        // Show a generic error message to the user
        $_SESSION['message'] = "A database error occurred. Please try again later.";
        $_SESSION['message_type'] = "error";
    } catch (Exception $e) {
        // Log the detailed error
        error_log("General error in update_field.php: " . $e->getMessage());
        
        // Show a generic error message to the user
        $_SESSION['message'] = "An error occurred. Please try again later.";
        $_SESSION['message_type'] = "error";
    }
}

// Redirect back to profile page
header('Location: profile.php');
exit;
?>
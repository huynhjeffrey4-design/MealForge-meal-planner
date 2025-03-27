<?php
require_once __DIR__ . '/controllers/forgot_password.php';
require_once __DIR__ . '/controllers/user.php';

session_start();

$error = '';
$success = '';
$validToken = false;
$email = '';
$token = '';

// Check if email and token are provided in the URL
if (isset($_GET['email']) && isset($_GET['token'])) {
    $email = $_GET['email'];
    $token = $_GET['token'];
    
    // Verify the token
    $forgotPasswordController = new ForgotPasswordController();
    $validToken = $forgotPasswordController->verifyToken($email, $token);
    
    if (!$validToken) {
        $error = 'Invalid or expired password reset link. Please request a new one.';
    }
} else {
    $error = 'Missing email or token. Please request a password reset link.';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    if (empty($_POST['password']) || empty($_POST['confirm_password'])) {
        $error = 'Please enter both password fields';
    } elseif ($_POST['password'] !== $_POST['confirm_password']) {
        $error = 'Passwords do not match';
    } elseif (strlen($_POST['password']) < 8) {
        $error = 'Password must be at least 8 characters long';
    } else {
        $userController = getUserController();
        
        try {
            // Update the user's password
            $result = $userController->resetPassword($email, $_POST['password']);
            
            if ($result['success']) {
                // Invalidate the token so it can't be used again
                $forgotPasswordController->invalidateToken($email, $token);
                $success = 'Your password has been reset successfully. You can now login with your new password.';
                $validToken = false;
            } else {
                $error = 'Failed to reset password. ' . ($result['validation']->getError('general') ?? 'Please try again.');
            }
        } catch (Exception $e) {
            $error = 'An error occurred: ' . $e->getMessage();
        }
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - MealForge</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .bg-primary {
            background-color: #00A651;
        }

        .text-primary {
            color: #00A651;
        }

        .hover\:bg-primary-dark:hover {
            background-color: #009048;
        }

        .focus\:ring-primary:focus {
            --tw-ring-color: rgba(0, 166, 81, 0.5);
        }

        .focus\:border-primary:focus {
            border-color: #00A651;
        }
    </style>
</head>

<body class="bg-white min-h-screen">
    <div class="min-h-screen flex flex-col md:flex-row">
        <!-- Brand Section -->
        <div class="bg-primary w-full md:w-1/2 p-6 md:p-10 lg:p-20">
            <div class="max-w-2xl mx-auto">
                <h1 class="text-2xl font-bold mb-6 md:mb-10 text-white">MealForge</h1>
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold mb-4 text-white">Reset your password</h2>
                <p class="text-white/90 text-lg md:text-xl">Enter your new password below to regain access to your account.</p>
            </div>
        </div>

        <!-- Form Section -->
        <div class="w-full md:w-1/2 p-6 md:p-10 lg:p-20 m-auto">
            <div class="max-w-2xl mx-auto">
                <?php if ($error): ?>
                    <div class="bg-red-50 border border-red-400 text-red-700 p-4 rounded-md mb-6">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="bg-green-50 border border-green-400 text-green-700 p-4 rounded-md mb-6">
                        <?php echo htmlspecialchars($success); ?>
                        <div class="mt-4">
                            <a href="login.php" class="bg-primary hover:bg-primary-dark text-white py-2 px-4 rounded-lg transition-colors font-medium">
                                Go to Login
                            </a>
                        </div>
                    </div>
                <?php elseif ($validToken): ?>
                    <form method="POST" action="?email=<?php echo urlencode($email); ?>&token=<?php echo urlencode($token); ?>" class="space-y-6">
                        <div class="space-y-2">
                            <label for="password" class="block font-medium text-gray-700">New Password</label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                required
                                minlength="8"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                            <p class="text-sm text-gray-500">Must be at least 8 characters long</p>
                        </div>

                        <div class="space-y-2">
                            <label for="confirm_password" class="block font-medium text-gray-700">Confirm New Password</label>
                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                required
                                minlength="8"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                        </div>

                        <button
                            type="submit"
                            class="w-full bg-primary hover:bg-primary-dark text-white py-4 rounded-lg transition-colors font-medium text-lg">
                            Reset Password
                        </button>
                    </form>
                <?php else: ?>
                    <div class="text-center">
                        <a href="forgot_password.php" class="text-primary hover:text-primary/80 font-medium">
                            Request a new password reset link
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>

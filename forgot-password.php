<?php
require_once __DIR__ . '/controllers/forgot_password.php';

session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['email'])) {
        $error = 'Please enter your email address';
    } else {
        $forgotPasswordController = new ForgotPasswordController();

        try {
            $result = $forgotPasswordController->forgotPassword($_POST['email']);

            // Always show success message regardless of whether the email exists
            // This prevents user enumeration attacks
            $success = 'If an account exists with this email, a password reset link has been sent.';
        } catch (Exception $e) {
            // Log the error but don't expose details to the user
            error_log('Password reset error: ' . $e->getMessage());
            $error = 'An error occurred. Please try again.';
        }
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - MealForge</title>
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
    
    .sr-only {
      position: absolute;
      width: 1px;
      height: 1px;
      padding: 0;
      margin: -1px;
      overflow: hidden;
      clip: rect(0, 0, 0, 0);
      white-space: nowrap;
      border: 0;
    }
    </style>
    
</head>

<body class="bg-white min-h-screen">
    <div class="min-h-screen flex flex-col md:flex-row">
        <!-- Brand Section -->
        <div class="bg-primary w-full md:w-1/2 p-6 md:p-10 lg:p-20">
            <div class="max-w-2xl mx-auto">
                <h1 class="text-2xl font-bold mb-6 md:mb-10 text-white">MealForge</h1>
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold mb-4 text-white">Forgot your password?</h2>
                <p class="text-white/90 text-lg md:text-xl">Enter your email address and we'll send you a link to reset your password.</p>
            </div>
        </div>

        <!-- Form Section -->
        <div class="w-full md:w-1/2 p-6 md:p-10 lg:p-20 m-auto">
            <div class="max-w-2xl mx-auto">
                <?php if ($error): ?>
                    <div class="bg-red-50 border border-red-400 text-red-700 p-4 rounded-md mb-6" role="alert" aria-live="assertive">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="bg-green-50 border border-green-400 text-green-700 p-4 rounded-md mb-6" role="alert" aria-live="polite">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-6">
                    <div class="space-y-2">
                        <label for="email" class="block font-medium text-gray-700">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-primary hover:bg-primary-dark text-white py-4 rounded-lg transition-colors font-medium text-lg">
                        Send Reset Link<span class="sr-only"> to the email you entered</span>
                    </button>

                    <div class="text-center text-gray-600">
                        Remember your password?
                        <a href="login.php" class="text-primary hover:text-primary/80 font-medium">
                            Back to login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>

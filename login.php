<?php
require __DIR__ . '/vendor/autoload.php';
require_once 'controllers/control.php';
use function App\Controllers\getUserController;

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
	$controller = getUserController();
	$success = $controller->login($email, $password, $remember);
	
	if ($success === true) {
		header('Location: dashboard.php');
		exit;
	} else {
		$error = 'Invalid email or password';
	}
}

function generateRememberToken() {
    // Generate a secure random token
    $token = bin2hex(random_bytes(32));
    // In a real implementation, you would store this token in the database
    // associated with the user ID
    return $token;
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login to your MealForge account</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#00A651',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-white min-h-screen">
    <div class="min-h-screen flex flex-col md:flex-row">
        <!-- Brand Section -->
        <div class="bg-primary w-full md:w-1/2 p-6 md:p-10 lg:p-20">
            <div class="max-w-2xl mx-auto">
                <h1 class="text-2xl font-bold mb-6 md:mb-10 text-white">MealForge</h1>
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold mb-4 text-white">Login to your MealForge account</h2>
                <p class="text-white/90 text-lg md:text-xl">Your meal planner, powered by your needs.</p>
            </div>
        </div>
        <!-- Form Section -->
        <div class="w-full md:w-1/2 p-6 md:p-10 lg:p-20 m-auto">
            <div class="max-w-2xl mx-auto">
                <form method="POST" action="" class="space-y-6">
                    <?php if ($error): ?>
                        <div class="text-red-600 mb-4">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    <div class="space-y-2">
                        <label for="email" class="block font-medium text-gray-700">
                            Email
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                    </div>
                    <div class="space-y-2">
                        <label for="password" class="block font-medium text-gray-700">
                            Password
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                    </div>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center space-x-2">
                            <input 
                                type="checkbox" 
                                id="remember" 
                                name="remember"
                                class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary"
                            >
                            <label for="remember" class="text-sm text-gray-600">
                                Remember me
                            </label>
                        </div>
                        <a href="forgot-password.php" class="text-primary hover:text-primary/80">
                            Forgot your password?
                        </a>
                    </div>
                    <button 
                        type="submit" 
                        class="w-full bg-primary text-white py-4 rounded-lg hover:bg-primary/90 transition-colors font-medium text-lg"
                    >
                        Continue
                    </button>
                    <div class="text-center text-gray-600">
                        Don't have a MealForge account? 
                        <a href="register.php" class="text-primary hover:text-primary/80 font-medium">
                            Create one now
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

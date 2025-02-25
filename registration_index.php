<?php
// Initialize error variables
$errors = [];
$error_message = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MealForge - Registration</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-green': '#00B341',
                        'error-red': '#EF4444',  // Added explicit error color
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-white min-h-screen p-5">
    <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-10">
        <!-- Image Grid Section -->
        <div class="hidden md:grid grid-cols-2 gap-2">
            <img src="/CSE442/2025-Spring/cse-442v/placeholder-1.jpg" alt="Food 1" class="w-full h-48 rounded-lg object-cover bg-gray-200">
            <img src="/CSE442/2025-Spring/cse-442v/placeholder-2.jpg" alt="Food 2" class="w-full h-48 rounded-lg object-cover bg-gray-200">
            <img src="/CSE442/2025-Spring/cse-442v/placeholder-3.jpg" alt="Food 3" class="w-full h-48 rounded-lg object-cover bg-gray-200">
            <img src="/CSE442/2025-Spring/cse-442v/placeholder-4.jpg" alt="Food 4" class="w-full h-48 rounded-lg object-cover bg-gray-200">
        </div>

        <!-- Form Section -->
        <div class="p-4">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">
                Create a <span class="text-primary-green">MealForge</span> Profile
            </h1>
            <p class="text-gray-600 mb-8">Get started on your meal prep journey today - for free!</p>

            <?php if (isset($error_message)): ?>
                <div id="generalError" class="bg-red-50 border border-red-400 text-red-700 p-4 rounded-md mb-4">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form id="registrationForm" method="POST" action="process_register.php" class="space-y-5">
                <div>
                    <label for="firstName" class="block text-gray-700 font-medium mb-2">First name</label>
                    <input type="text" id="firstName" name="firstName" required 
                        value="<?php echo isset($_POST['firstName']) ? htmlspecialchars($_POST['firstName']) : ''; ?>"
                        class="w-full px-3 py-3 border <?php echo isset($errors['firstName']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-primary-green focus:ring-opacity-20 focus:border-primary-green">
                    <?php if (isset($errors['firstName'])): ?>
                        <div class="text-red-600 text-sm mt-1 font-medium">
                            <span class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <?php echo $errors['firstName']; ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="lastName" class="block text-gray-700 font-medium mb-2">Last name</label>
                    <input type="text" id="lastName" name="lastName" required 
                        value="<?php echo isset($_POST['lastName']) ? htmlspecialchars($_POST['lastName']) : ''; ?>"
                        class="w-full px-3 py-3 border <?php echo isset($errors['lastName']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-primary-green focus:ring-opacity-20 focus:border-primary-green">
                    <?php if (isset($errors['lastName'])): ?>
                        <div class="text-red-600 text-sm mt-1 font-medium">
                            <span class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <?php echo $errors['lastName']; ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="email" class="block text-gray-700 font-medium mb-2">Email (username)</label>
                    <input type="email" id="email" name="email" required 
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                        class="w-full px-3 py-3 border <?php echo isset($errors['email']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-primary-green focus:ring-opacity-20 focus:border-primary-green">
                    <?php if (isset($errors['email'])): ?>
                        <div class="text-red-600 text-sm mt-1 font-medium">
                            <span class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <?php echo $errors['email']; ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
                    <input type="password" id="password" name="password" required
                        class="w-full px-3 py-3 border <?php echo isset($errors['password']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-primary-green focus:ring-opacity-20 focus:border-primary-green">
                    <?php if (isset($errors['password'])): ?>
                        <div class="text-red-600 text-sm mt-1 font-medium">
                            <span class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <?php echo $errors['password']; ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    <div class="text-gray-500 text-xs mt-1">Password must be at least 8 characters</div>
                </div>

                <button type="submit" class="w-full py-3 bg-primary-green hover:bg-green-700 text-white font-semibold rounded-md transition duration-200">
                    Register now
                </button>
            </form>

            <div class="text-center mt-6">
                Already have an account? 
                <a href="login.php" class="text-primary-green hover:underline">
                    Login
                </a>
            </div>
        </div>
    </div>
</body>
</html>
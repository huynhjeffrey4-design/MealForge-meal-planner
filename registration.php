<?php
// Start session at the very beginning before any output
session_start();

// Start output buffering to prevent "headers already sent" errors
ob_start();

// Initialize variables
$errors = [];
$error_message = null;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Load required files
    require_once 'config.php';
    require_once __DIR__ . '/vendor/autoload.php';
    include_once __DIR__ . '/setup.php';
    
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
                    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                    DB_USER,
                    DB_PASS,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
                
                $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->rowCount() > 0) {
                    $errors['email'] = 'Email already registered';
                }
            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
                $error_message = "Database error: " . $e->getMessage();
            }
        }
    }
    
    // Validate password
    if (!empty($_POST['password']) && strlen($_POST['password']) < 8) {
        $errors['password'] = 'Password must be at least 8 characters';
    }
    
    // If no errors, proceed with registration
    if (empty($errors) && !isset($error_message)) {
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
            
            $result = $stmt->execute([
                htmlspecialchars(trim($_POST['firstName'])),
                htmlspecialchars(trim($_POST['lastName'])),
                $email,
                $passwordHash
            ]);
            
            if ($result) {
                // Redirect to login page with success message
                $_SESSION['registration_success'] = true;
                
                // Flush buffer before redirect
                ob_end_flush();
                
                // Redirect and exit
                header('Location: login.php');
                exit;
            } else {
                $error_message = "Registration failed: Unable to create account";
            }
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            $error_message = "Registration failed: " . $e->getMessage();
        }
    }
}
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
                        'primary': '#00A651',
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
                Create a <span class="text-primary">MealForge</span> Profile
            </h1>
            <p class="text-gray-600 mb-8">Get started on your meal prep journey today - for free!</p>

            <?php if (isset($error_message)): ?>
                <div id="generalError" class="bg-red-50 border border-red-400 text-red-700 p-3 rounded-md mb-4">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form id="registrationForm" method="POST" class="space-y-5">
                <div>
                    <label for="firstName" class="block text-gray-700 font-medium mb-2">First name</label>
                    <input type="text" id="firstName" name="firstName" required 
                        value="<?php echo isset($_POST['firstName']) ? htmlspecialchars($_POST['firstName']) : ''; ?>"
                        class="w-full px-3 py-3 border <?php echo isset($errors['firstName']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:ring-opacity-20 focus:border-primary">
                    <?php if (isset($errors['firstName'])): ?>
                        <div class="text-red-600 text-sm mt-1 font-medium">
                            <?php echo $errors['firstName']; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="lastName" class="block text-gray-700 font-medium mb-2">Last name</label>
                    <input type="text" id="lastName" name="lastName" required 
                        value="<?php echo isset($_POST['lastName']) ? htmlspecialchars($_POST['lastName']) : ''; ?>"
                        class="w-full px-3 py-3 border <?php echo isset($errors['lastName']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:ring-opacity-20 focus:border-primary">
                    <?php if (isset($errors['lastName'])): ?>
                        <div class="text-red-600 text-sm mt-1 font-medium">
                            <?php echo $errors['lastName']; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="email" class="block text-gray-700 font-medium mb-2">Email (username)</label>
                    <input type="email" id="email" name="email" required 
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                        class="w-full px-3 py-3 border <?php echo isset($errors['email']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:ring-opacity-20 focus:border-primary">
                    <?php if (isset($errors['email'])): ?>
                        <div class="text-red-600 text-sm mt-1 font-medium">
                            <?php echo $errors['email']; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div>
                    <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
                    <input type="password" id="password" name="password" required
                        class="w-full px-3 py-3 border <?php echo isset($errors['password']) ? 'border-red-500' : 'border-gray-200'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:ring-opacity-20 focus:border-primary">
                    <?php if (isset($errors['password'])): ?>
                        <div class="text-red-600 text-sm mt-1 font-medium">
                            <?php echo $errors['password']; ?>
                        </div>
                    <?php endif; ?>
                    <div class="text-gray-500 text-xs mt-1">Password must be at least 8 characters</div>
                </div>

                <button type="submit" class="w-full py-3 bg-primary hover:bg-primary/90 text-white font-semibold rounded-md transition duration-200">
                    Register now
                </button>
            </form>

            <div class="text-center mt-6">
                Already have an account? 
                <a href="login.php" class="text-primary hover:underline">
                    Login
                </a>
            </div>
        </div>
    </div>
</body>
</html>
<?php
// End output buffering if we reached this point (no redirect happened)
if (ob_get_level() > 0) {
    ob_end_flush();
}
?>

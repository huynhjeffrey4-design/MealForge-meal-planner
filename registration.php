<?php
require_once __DIR__ . '/controllers/user.php';

session_start();

$formData = [
    'firstName' => '',
    'lastName' => '',
    'email' => ''
];

$validation = new ValidationResult();
$registrationSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'firstName' => trim($_POST['firstName'] ?? ''),
        'lastName' => trim($_POST['lastName'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? ''
    ];
    
    $userController = getUserController();
    
    $result = $userController->createUser(
        $formData['email'],
        $formData['password'],
        $formData['firstName'],
        $formData['lastName']
    );
    
    if ($result['success']) {
        $_SESSION['registration_success'] = true;
        header("Location: login.php");
        exit;
    } else {
        $validation = $result['validation'];
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MealForge - Registration</title>
    
    <!-- Direct Tailwind CSS CDN link -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    
    <style>
        .text-primary { color: #00A651; }
        .bg-primary { background-color: #00A651; }
        .hover\:bg-primary:hover { background-color: #009048; }
        .border-primary { border-color: #00A651; }
        .ring-primary { --tw-ring-color: rgba(0, 166, 81, 0.5); }
    </style>
</head>
<body class="bg-gray-50 min-h-screen p-4">
    <div class="max-w-6xl mx-auto bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-2">
            <!-- Image Grid Section -->
            <div class="hidden md:grid grid-cols-2 gap-3 p-6 bg-gray-50">
                <div class="bg-gray-200 rounded-lg h-48 flex items-center justify-center text-gray-500">
                    <span>Food Image 1</span>
                </div>
                <div class="bg-gray-200 rounded-lg h-48 flex items-center justify-center text-gray-500">
                    <span>Food Image 2</span>
                </div>
                <div class="bg-gray-200 rounded-lg h-48 flex items-center justify-center text-gray-500">
                    <span>Food Image 3</span>
                </div>
                <div class="bg-gray-200 rounded-lg h-48 flex items-center justify-center text-gray-500">
                    <span>Food Image 4</span>
                </div>
            </div>

            <!-- Form Section -->
            <div class="p-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-4">
                    Create a <span class="text-primary">MealForge</span> Profile
                </h1>
                <p class="text-gray-600 mb-8">Get started on your meal prep journey today - for free!</p>

                <?php if ($validation->getError('general')): ?>
                    <div id="generalError" class="bg-red-50 border border-red-400 text-red-700 p-4 rounded-md mb-6">
                        <?php echo htmlspecialchars($validation->getError('general')); ?>
                    </div>
                <?php endif; ?>

                <form id="registrationForm" method="POST" action="" class="space-y-6">
                    <!-- First Name Field -->
                    <div>
                        <label for="firstName" class="block text-gray-700 font-medium mb-2">First name</label>
                        <input 
                            type="text" 
                            id="firstName" 
                            name="firstName" 
                            required 
                            value="<?php echo htmlspecialchars($formData['firstName']); ?>"
                            class="w-full px-4 py-3 border <?php echo $validation->getError('firstName') ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg focus:outline-none focus:ring-2 ring-primary focus:border-primary"
                        >
                        <?php if ($validation->getError('firstName')): ?>
                            <div class="text-red-600 text-sm mt-1">
                                <?php echo htmlspecialchars($validation->getError('firstName')); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Last Name Field -->
                    <div>
                        <label for="lastName" class="block text-gray-700 font-medium mb-2">Last name</label>
                        <input 
                            type="text" 
                            id="lastName" 
                            name="lastName" 
                            required 
                            value="<?php echo htmlspecialchars($formData['lastName']); ?>"
                            class="w-full px-4 py-3 border <?php echo $validation->getError('lastName') ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg focus:outline-none focus:ring-2 ring-primary focus:border-primary"
                        >
                        <?php if ($validation->getError('lastName')): ?>
                            <div class="text-red-600 text-sm mt-1">
                                <?php echo htmlspecialchars($validation->getError('lastName')); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-gray-700 font-medium mb-2">Email (username)</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required 
                            value="<?php echo htmlspecialchars($formData['email']); ?>"
                            class="w-full px-4 py-3 border <?php echo $validation->getError('email') ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg focus:outline-none focus:ring-2 ring-primary focus:border-primary"
                        >
                        <?php if ($validation->getError('email')): ?>
                            <div class="text-red-600 text-sm mt-1">
                                <?php echo htmlspecialchars($validation->getError('email')); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-gray-700 font-medium mb-2">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            class="w-full px-4 py-3 border <?php echo $validation->getError('password') ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg focus:outline-none focus:ring-2 ring-primary focus:border-primary"
                        >
                        <?php if ($validation->getError('password')): ?>
                            <div class="text-red-600 text-sm mt-1">
                                <?php echo htmlspecialchars($validation->getError('password')); ?>
                            </div>
                        <?php endif; ?>
                        <div class="text-gray-500 text-xs mt-1">Password must be at least 8 characters</div>
                    </div>

                    <button type="submit" class="w-full py-3 bg-primary hover:bg-primary text-white font-semibold rounded-lg transition duration-200 mt-4">
                        Register now
                    </button>
                </form>

                <div class="text-center mt-6">
                    Already have an account? 
                    <a href="login.php" class="text-primary hover:underline font-medium">
                        Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

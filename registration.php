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


<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create a MealForge Account</title>
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
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold mb-4 text-white">Create your MealForge account</h2>
                <p class="text-white/90 text-lg md:text-xl">Get started on your meal prep journey today - for free!</p>
            </div>
        </div>

        <!-- Form Section -->
        <div class="w-full md:w-1/2 p-6 md:p-10 lg:p-20 m-auto">
            <div class="max-w-2xl mx-auto">
                <?php if ($validation->getError('general')): ?>
                    <div id="generalError" class="bg-red-50 border border-red-400 text-red-700 p-4 rounded-md mb-6" role="alert" aria-live="assertive">
                        <?php echo htmlspecialchars($validation->getError('general')); ?>
                    </div>
                <?php endif; ?>

                <form id="registrationForm" method="POST" action="" class="space-y-6">
                    <!-- First Name Field -->
                    <div class="space-y-2">
                        <label for="firstName" class="block font-medium text-gray-700">First name</label>
                        <input 
                            type="text" 
                            id="firstName" 
                            name="firstName" 
                            required 
                            value="<?php echo htmlspecialchars($formData['firstName']); ?>"
                            class="w-full px-4 py-3 border <?php echo $validation->getError('firstName') ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                        >
                        <?php if ($validation->getError('firstName')): ?>
                            <div class="text-red-600 text-sm mt-1">
                                <?php echo htmlspecialchars($validation->getError('firstName')); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Last Name Field -->
                    <div class="space-y-2">
                        <label for="lastName" class="block font-medium text-gray-700">Last name</label>
                        <input 
                            type="text" 
                            id="lastName" 
                            name="lastName" 
                            required 
                            value="<?php echo htmlspecialchars($formData['lastName']); ?>"
                            class="w-full px-4 py-3 border <?php echo $validation->getError('lastName') ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                        >
                        <?php if ($validation->getError('lastName')): ?>
                            <div class="text-red-600 text-sm mt-1">
                                <?php echo htmlspecialchars($validation->getError('lastName')); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Email Field -->
                    <div class="space-y-2">
                        <label for="email" class="block font-medium text-gray-700">Email (username)</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required 
                            value="<?php echo htmlspecialchars($formData['email']); ?>"
                            class="w-full px-4 py-3 border <?php echo $validation->getError('email') ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                        >
                        <?php if ($validation->getError('email')): ?>
                            <div class="text-red-600 text-sm mt-1">
                                <?php echo htmlspecialchars($validation->getError('email')); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Password Field -->
                    <div class="space-y-2">
                        <label for="password" class="block font-medium text-gray-700">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            class="w-full px-4 py-3 border <?php echo $validation->getError('password') ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                        >
                        <?php if ($validation->getError('password')): ?>
                            <div class="text-red-600 text-sm mt-1">
                                <?php echo htmlspecialchars($validation->getError('password')); ?>
                            </div>
                        <?php endif; ?>
                        <div class="text-gray-500 text-xs mt-1">Password must be at least 8 characters</div>
                    </div>

                    <button type="submit" class="w-full bg-primary hover:bg-primary-dark text-white py-4 rounded-lg transition-colors font-medium text-lg">
                        Create Account<span class="sr-only"> to register a MealForge user account</span>
                    </button>

                    <div class="text-center text-gray-600">
                        Already have a MealForge account?
                        <a href="login.php" class="text-primary hover:text-primary/80 font-medium">
                            Login now
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

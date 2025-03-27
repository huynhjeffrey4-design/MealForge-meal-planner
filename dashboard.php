<?php
// Start session and require config
session_start();
require_once __DIR__ . '/controllers/user.php';

if (!isset($_SESSION['user']['id'])) {
    header('Location: login.php');
    exit;
}

// Initialize meal plan in session if not exists
if (!isset($_SESSION['meal_plan'])) {
    $_SESSION['meal_plan'] = [
        'Monday' => [],
        'Tuesday' => [],
        'Wednesday' => [],
        'Thursday' => [],
        'Friday' => [],
        'Saturday' => [],
        'Sunday' => []
    ];
}

// Sample meals data (in a real app, this would come from a database)
$meals = [
    // Your meal data array here...
];

// Handle AJAX requests for adding/removing meals
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'add_meal' && isset($_POST['day']) && isset($_POST['meal_id'])) {
        $day = $_POST['day'];
        $mealId = (int)$_POST['meal_id'];
        
        // Find the meal by ID
        $mealToAdd = null;
        foreach ($meals as $meal) {
            if ($meal['id'] === $mealId) {
                $mealToAdd = $meal;
                break;
            }
        }
        
        if ($mealToAdd) {
            $_SESSION['meal_plan'][$day][] = $mealToAdd;
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Meal not found']);
        }
        exit;
    }
    
    if ($_POST['action'] === 'remove_meal' && isset($_POST['day']) && isset($_POST['index'])) {
        $day = $_POST['day'];
        $index = (int)$_POST['index'];
        
        if (isset($_SESSION['meal_plan'][$day][$index])) {
            array_splice($_SESSION['meal_plan'][$day], $index, 1);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Meal not found']);
        }
        exit;
    }
}

// Get user info for the sidebar
$userId = $_SESSION['user']['id'];
$userController = getUserController();
$user = $userController->getUserById($userId);
$firstName = $user['first_name'] ?? 'User';
$profilePicture = $user['profile_picture'] ?? '';
$defaultProfilePic = 'assets/default-profile.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MealForge - Meal Plan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#00A651',
                            dark: '#008c44'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .meal-card {
            transition: transform 0.2s;
        }
        .meal-card:hover {
            transform: translateY(-5px);
        }
        .meal-options {
            opacity: 0;
            transition: opacity 0.2s;
        }
        .meal-card:hover .meal-options {
            opacity: 1;
        }
        /* Fix iframe styling */
        #search-iframe {
            width: 100%;
            height: 100%;
            border: 0;
        }
    </style>
</head>
<body class="flex flex-col md:flex-row min-h-screen">
    <!-- Sidebar -->
    <div class="w-full md:w-64 bg-primary text-white p-5 flex flex-col">
        <div class="text-2xl font-bold mb-8">MealForge</div>
        <div class="w-44 h-44 rounded-full bg-gray-100 mx-auto mb-5 overflow-hidden relative">
            <img src="<?php 
                if (!empty($profilePicture)) {
                    echo $profilePicture;
                } else {
                    echo $defaultProfilePic;
                }
            ?>" alt="Profile Picture" class="w-full h-full object-cover">
        </div>
        <div class="text-center mb-10 text-lg">
            Welcome back, <span class="font-bold"><?php echo htmlspecialchars($firstName); ?>!</span>
        </div>
        <ul class="space-y-4">
            <li><a href="dashboard.php" class="text-white no-underline text-lg font-bold flex items-center py-1"><i class="fa fa-home fa-fw mr-2"></i> Dashboard</a></li>
            <li><a href="meal-plan.php" class="text-white no-underline text-lg font-bold flex items-center py-1 bg-primary-dark rounded px-2"><i class="fa fa-calendar fa-fw mr-2"></i> Meal Plan</a></li>
            <li><a href="dashboard/bookmarks.php" class="text-white no-underline text-lg font-bold flex items-center py-1"><i class="fa fa-bookmark fa-fw mr-2"></i> Bookmarks</a></li>
            <li><a href="#" class="text-white no-underline text-lg font-bold flex items-center py-1"><i class="fa fa-users fa-fw mr-2"></i> Social</a></li>
            <li><a href="#" class="text-white no-underline text-lg font-bold flex items-center py-1"><i class="fa fa-shopping-cart fa-fw mr-2"></i> Shop</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-5 overflow-y-auto bg-gray-50">
        <div class="flex justify-between items-center mb-8 pb-2 border-b border-gray-200">
            <a href="profile.php" class="text-gray-600 no-underline flex items-center">
                <i class="fa fa-arrow-left mr-2"></i> Back to Profile
            </a>
            <a href="logout.php" class="text-primary font-bold no-underline">Log out</a>
        </div>
        
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800">Weekly Meal Plan</h1>
        </div>
        
        <!-- Meal Plan Grid - Top Row -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
            <?php foreach(['Monday', 'Tuesday', 'Wednesday'] as $day): ?>
            <div class="bg-white rounded-lg shadow-sm p-4 flex flex-col">
                <h2 class="text-lg font-bold text-center mb-4"><?php echo $day; ?></h2>
                
                <div class="flex-1 flex flex-col" id="meals-<?php echo strtolower($day); ?>">
                    <?php if(isset($_SESSION['meal_plan'][$day]) && !empty($_SESSION['meal_plan'][$day])): ?>
                        <?php foreach($_SESSION['meal_plan'][$day] as $index => $meal): ?>
                            <div class="mb-3 relative meal-card">
                                <a href="#" class="meal-details" data-meal='<?php echo json_encode($meal); ?>'>
                                    <img src="<?php echo $meal['image']; ?>" alt="<?php echo htmlspecialchars($meal['name']); ?>" class="w-full h-40 object-cover rounded-lg">
                                    <div class="bg-black bg-opacity-60 text-white text-sm p-2 absolute bottom-0 left-0 right-0 rounded-b-lg">
                                        <?php echo htmlspecialchars($meal['name']); ?>
                                    </div>
                                </a>
                                <div class="absolute top-2 right-2 meal-options">
                                    <div class="relative inline-block">
                                        <button class="bg-white bg-opacity-80 rounded-full w-8 h-8 flex items-center justify-center text-gray-700 hover:bg-opacity-100 meal-menu-button">
                                            <i class="fa fa-ellipsis-h"></i>
                                        </button>
                                        <div class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 meal-menu">
                                            <div class="py-1">
                                                <button class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 remove-meal" data-day="<?php echo $day; ?>" data-index="<?php echo $index; ?>">
                                                    <i class="fa fa-trash mr-2 text-red-500"></i> Remove
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <button class="add-meal mt-2 w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center mx-auto hover:bg-primary-dark" data-day="<?php echo $day; ?>">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Meal Plan Grid - Middle Row -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
            <?php foreach(['Thursday', 'Friday', 'Saturday'] as $day): ?>
            <div class="bg-white rounded-lg shadow-sm p-4 flex flex-col">
                <h2 class="text-lg font-bold text-center mb-4"><?php echo $day; ?></h2>
                
                <div class="flex-1 flex flex-col" id="meals-<?php echo strtolower($day); ?>">
                    <?php if(isset($_SESSION['meal_plan'][$day]) && !empty($_SESSION['meal_plan'][$day])): ?>
                        <?php foreach($_SESSION['meal_plan'][$day] as $index => $meal): ?>
                            <div class="mb-3 relative meal-card">
                                <a href="#" class="meal-details" data-meal='<?php echo json_encode($meal); ?>'>
                                    <img src="<?php echo $meal['image']; ?>" alt="<?php echo htmlspecialchars($meal['name']); ?>" class="w-full h-40 object-cover rounded-lg">
                                    <div class="bg-black bg-opacity-60 text-white text-sm p-2 absolute bottom-0 left-0 right-0 rounded-b-lg">
                                        <?php echo htmlspecialchars($meal['name']); ?>
                                    </div>
                                </a>
                                <div class="absolute top-2 right-2 meal-options">
                                    <div class="relative inline-block">
                                        <button class="bg-white bg-opacity-80 rounded-full w-8 h-8 flex items-center justify-center text-gray-700 hover:bg-opacity-100 meal-menu-button">
                                            <i class="fa fa-ellipsis-h"></i>
                                        </button>
                                        <div class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 meal-menu">
                                            <div class="py-1">
                                                <button class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 remove-meal" data-day="<?php echo $day; ?>" data-index="<?php echo $index; ?>">
                                                    <i class="fa fa-trash mr-2 text-red-500"></i> Remove
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <button class="add-meal mt-2 w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center mx-auto hover:bg-primary-dark" data-day="<?php echo $day; ?>">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Meal Plan Grid - Bottom Row (Sunday) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="md:col-start-2 bg-white rounded-lg shadow-sm p-4 flex flex-col">
                <h2 class="text-lg font-bold text-center mb-4">Sunday</h2>
                
                <div class="flex-1 flex flex-col" id="meals-sunday">
                    <?php if(isset($_SESSION['meal_plan']['Sunday']) && !empty($_SESSION['meal_plan']['Sunday'])): ?>
                        <?php foreach($_SESSION['meal_plan']['Sunday'] as $index => $meal): ?>
                            <div class="mb-3 relative meal-card">
                                <a href="#" class="meal-details" data-meal='<?php echo json_encode($meal); ?>'>
                                    <img src="<?php echo $meal['image']; ?>" alt="<?php echo htmlspecialchars($meal['name']); ?>" class="w-full h-40 object-cover rounded-lg">
                                    <div class="bg-black bg-opacity-60 text-white text-sm p-2 absolute bottom-0 left-0 right-0 rounded-b-lg">
                                        <?php echo htmlspecialchars($meal['name']); ?>
                                    </div>
                                </a>
                                <div class="absolute top-2 right-2 meal-options">
                                    <div class="relative inline-block">
                                        <button class="bg-white bg-opacity-80 rounded-full w-8 h-8 flex items-center justify-center text-gray-700 hover:bg-opacity-100 meal-menu-button">
                                            <i class="fa fa-ellipsis-h"></i>
                                        </button>
                                        <div class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 meal-menu">
                                            <div class="py-1">
                                                <button class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 remove-meal" data-day="Sunday" data-index="<?php echo $index; ?>">
                                                    <i class="fa fa-trash mr-2 text-red-500"></i> Remove
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <button class="add-meal mt-2 w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center mx-auto hover:bg-primary-dark" data-day="Sunday">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Meal Selection Modal -->
    <div id="meal-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg w-full max-w-6xl h-[90vh] flex flex-col">
        <div class="p-4 border-b flex justify-between items-center">
            <h2 class="text-xl font-bold">Add Meal</h2>
            <button id="close-modal" class="text-gray-500 hover:text-gray-700">
                <i class="fa fa-times"></i>
            </button>
        </div>
        <iframe id="search-iframe" class="w-full h-full border-0"></iframe>
    </div>
</div>
</div>
            
    <!-- Recipe Details Modal -->
    <div id="recipe-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg w-full max-w-3xl max-h-[90vh] overflow-y-auto">
            <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                <div class="flex items-center">
                    <button id="back-to-meal-plan" class="text-gray-500 hover:text-gray-700 mr-2">
                        <i class="fa fa-arrow-left"></i>
                    </button>
                    <h2 class="text-xl font-bold" id="recipe-title">Recipe Details</h2>
                </div>
                <button id="close-recipe" class="text-gray-500 hover:text-gray-700">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            
            <div class="p-4">
                <img id="recipe-image" src="" alt="Recipe" class="w-full h-64 object-cover rounded-lg mb-4">
                
                <p id="recipe-description" class="text-gray-700 mb-6"></p>
                
                <div class="mb-6">
                    <h3 class="text-lg font-bold mb-2">Ingredients</h3>
                    <ul id="recipe-ingredients" class="list-disc pl-5 space-y-1"></ul>
                </div>
                
                <div class="mb-6">
                    <h3 class="text-lg font-bold mb-2">Instructions</h3>
                    <p id="recipe-instructions" class="text-gray-700"></p>
                </div>
                
                <div>
                    <h3 class="text-lg font-bold mb-2">Nutrition Information</h3>
                    <ul id="recipe-nutrition" class="list-disc pl-5 space-y-1"></ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Confirmation Modal -->
    <div id="confirm-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg w-full max-w-md p-6">
            <h3 class="text-lg font-bold mb-4" id="confirm-title">Remove this meal?</h3>
            <p class="text-gray-700 mb-6" id="confirm-message">Are you sure you want to remove this meal?</p>
            
            <div class="flex justify-end gap-3">
                <button id="cancel-confirm" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">
                    Cancel
                </button>
                <button id="confirm-action" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                    Yes, Remove
                </button>
            </div>
        </div>
    </div>

    <script>
        // Store all meals data in JavaScript
        const mealsData = <?php echo json_encode($meals); ?>;
        
        // Variables to track modal state
        let currentDay = '';
        let selectedMealId = null;
        let removeData = { day: '', index: -1 };
        
        // DOM elements
        const mealModal = document.getElementById('meal-modal');
        const recipeModal = document.getElementById('recipe-modal');
        const confirmModal = document.getElementById('confirm-modal');
        const modalTitle = document.getElementById('modal-title');
        const searchIframe = document.getElementById('search-iframe');
        
        // Add meal button click handlers
document.querySelectorAll('.add-meal').forEach(button => {
    button.addEventListener('click', function() {
        const day = this.dataset.day;
        const iframe = document.getElementById('search-iframe');
        
        // Clear iframe first
        iframe.src = 'about:blank';
        
        // Load new content after slight delay
        setTimeout(() => {
        iframe.src = `search.php?modal=true&day=${encodeURIComponent(day)}`;            document.getElementById('meal-modal').classList.remove('hidden');
        }, 50);
    });
});

// Close modal handler
document.getElementById('close-modal').addEventListener('click', () => {
    document.getElementById('meal-modal').classList.add('hidden');
    location.reload(); // Optional: Only include if you want to refresh on close
});

        // Listen for messages from the iframe
        window.addEventListener('message', function(e) {
            if (e.data.action === 'addMeal') {
                const formData = new FormData();
                formData.append('action', 'add_meal');
                formData.append('day', e.data.day);
                formData.append('meal_id', e.data.mealId);

                fetch('dashboard.php', { // Use dashboard.php instead of meal-plan.php
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mealModal.classList.add('hidden');
                        mealModal.classList.remove('flex');
                        window.location.reload();
                    }
                });
            }
        });
        
        // Close modal handlers
        document.getElementById('close-modal').addEventListener('click', () => {
            mealModal.classList.add('hidden');
            mealModal.classList.remove('flex');
            searchIframe.src = 'about:blank'; // Clear iframe content
        });
        
        document.getElementById('close-recipe').addEventListener('click', () => {
            recipeModal.classList.add('hidden');
        });
        
        document.getElementById('back-to-meal-plan').addEventListener('click', () => {
            recipeModal.classList.add('hidden');
        });
        
        document.getElementById('cancel-confirm').addEventListener('click', () => {
            confirmModal.classList.add('hidden');
        });
        
        // Meal details handlers
        document.querySelectorAll('.meal-details').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                const mealData = JSON.parse(this.dataset.meal);
                
                // Populate recipe modal with meal data
                document.getElementById('recipe-title').textContent = mealData.name;
                document.getElementById('recipe-image').src = mealData.image;
                document.getElementById('recipe-image').alt = mealData.name;
                document.getElementById('recipe-description').textContent = mealData.description;
                
                // Populate ingredients
                const ingredientsList = document.getElementById('recipe-ingredients');
                ingredientsList.innerHTML = '';
                mealData.ingredients.forEach(ingredient => {
                    const li = document.createElement('li');
                    li.textContent = ingredient;
                    ingredientsList.appendChild(li);
                });
                
                // Populate instructions
                document.getElementById('recipe-instructions').textContent = mealData.instructions;
                
                // Populate nutrition
                const nutritionList = document.getElementById('recipe-nutrition');
                nutritionList.innerHTML = '';
                mealData.nutrition.forEach(item => {
                    const li = document.createElement('li');
                    li.textContent = item;
                    nutritionList.appendChild(li);
                });
                
                // Show recipe modal
                recipeModal.classList.remove('hidden');
            });
        });
        
        // Meal menu toggle
        document.querySelectorAll('.meal-menu-button').forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                
                // Close all other menus
                document.querySelectorAll('.meal-menu').forEach(menu => {
                    if (menu !== this.nextElementSibling) {
                        menu.classList.add('hidden');
                    }
                });
                
                // Toggle this menu
                this.nextElementSibling.classList.toggle('hidden');
            });
        });
        
        // Close meal menus when clicking elsewhere
        document.addEventListener('click', function() {
            document.querySelectorAll('.meal-menu').forEach(menu => {
                menu.classList.add('hidden');
            });
        });
        
        // Prevent clicks inside menu from closing it
        document.querySelectorAll('.meal-menu').forEach(menu => {
            menu.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
        
        // Remove meal handlers
        document.querySelectorAll('.remove-meal').forEach(button => {
            button.addEventListener('click', function() {
                const day = this.dataset.day;
                const index = parseInt(this.dataset.index);
                
                // Store removal data
                removeData.day = day;
                removeData.index = index;
                
                // Show confirmation modal
                document.getElementById('confirm-title').textContent = `Remove this meal from ${day}?`;
                document.getElementById('confirm-message').textContent = 'Are you sure you want to remove this meal from your plan?';
                confirmModal.classList.remove('hidden');
            });
        });
        
        // Confirm removal handler
        document.getElementById('confirm-action').addEventListener('click', function() {
            if (removeData.day && removeData.index >= 0) {
                // Send AJAX request to remove meal
                const formData = new FormData();
                formData.append('action', 'remove_meal');
                formData.append('day', removeData.day);
                formData.append('index', removeData.index);
                
                fetch('dashboard.php', { // Use dashboard.php instead of meal-plan.php
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Close modal
                        confirmModal.classList.add('hidden');
                        
                        // Reload page to show updated meal plan
                        window.location.reload();
                    } else {
                        alert('Failed to remove meal: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while removing the meal');
                });
            }
        });
    </script>
</body>
</html>

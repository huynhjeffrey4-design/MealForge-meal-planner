<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/controllers/user.php';

require_once __DIR__ . '/controllers/recipe.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'random_recipes') {
    $controller = new RecipeController(null);
    $recipes = $controller->getFiveRandomRecipes();
    header('Content-Type: application/json');
    echo json_encode($recipes);
    exit;
}

$recipeController = new RecipeController(null);
$randomRecipes = $recipeController->getFiveRandomRecipes();

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


// Get user ID from validated session
$userId = (int)$_SESSION['user']['id'];
$userController = getUserController();
$user = $userController->getUserById($userId);

// Handle invalid user
if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$meals = R::findAll('mealplan', 'user_id = ?', [$userId]);
$groupedMeals = [
    'Monday' => [], 'Tuesday' => [], 'Wednesday' => [],
    'Thursday' => [], 'Friday' => [], 'Saturday' => [], 'Sunday' => []
];

foreach ($meals as $meal) {
    $day = $meal->day ?? '';
    if (isset($groupedMeals[$day])) {
        $groupedMeals[$day][] = $meal->export();
    }
}


// Add recipe directly to meal plan
if (isset($_POST['add_day']) && isset($_POST['add_meal_data'])) {
    $day = $_POST['add_day'];
    $mealData = json_decode($_POST['add_meal_data'], true);

    if ($mealData) {
        $_SESSION['meal_plan'][$day][] = $mealData;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid meal data']);
    }
    exit;
}
if (isset($_POST['add_day']) && isset($_POST['add_recipe_id'])) {
    $day = $_POST['add_day'];
    $recipeId = (int)$_POST['add_recipe_id'];

    if ($recipeId > 0) {
        $_SESSION['meal_plan'][$day][] = $recipeId;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid recipe ID']);
    }
    exit;
}
// Simplified approach for handling meal plan updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    // Add meal to plan
    if (isset($_POST['add_day']) && isset($_POST['add_meal_id'])) {
        $day = $_POST['add_day'];
        $mealId = (int)$_POST['add_meal_id'];

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

    // Remove meal from plan
    if (isset($_POST['remove_day']) && isset($_POST['remove_index'])) {
        $day = $_POST['remove_day'];
        $index = (int)$_POST['remove_index'];

        if (isset($_SESSION['meal_plan'][$day][$index])) {
            array_splice($_SESSION['meal_plan'][$day], $index, 1);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Meal not found']);
        }
        exit;
    }

    if (isset($_POST['remove_meal_id'])) {
        $mealId = (int)$_POST['remove_meal_id'];
        $meal = R::load('mealplan', $mealId);

        if ($meal && $meal->user_id == $userId) {
            R::trash($meal);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Meal not found or access denied']);
        }
        exit;
    }

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MealForge - Meal Plan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link type="text/css" href="static/css/dashboard.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
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
        #search-iframe {
            width: 100%;
            height: 100%;
            border: 0;
        }
        .main-container {
            margin-top: 0 !important; /* Remove existing margin-top */
        }
        .header-content {
            height: 4rem;
        }
    </style>
</head>
<body>
    <!-- Header inclusion -->
    <?php include 'header.php'; ?>


    <div class="min-h-screen main-container">

        <!-- Main Content Area -->
		<div class="p-5 overflow-y-auto bg-gray-50 w-full">
			<div class="text-center mb-8">
				<h1 class="text-4xl font-bold text-gray-800">Weekly Meal Plan</h1>
				<div class="mt-2">
					<a href="dashboard/nutrition.php" class="inline-flex items-center text-primary hover:text-primary-dark">
						<i class="fas fa-chart-pie mr-1"></i> View Nutrition Summary
					</a>
				</div>
			</div>
            
            <!-- Meal Plan Grid - Top Row -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
                <?php foreach (['Monday', 'Tuesday', 'Wednesday'] as $day): ?>
                <div class="bg-white rounded-lg shadow-sm p-4 flex flex-col">
                    <h2 class="text-lg font-bold text-center mb-4"><?php echo $day; ?></h2>
                    
                    <div class="flex-1 flex flex-col" id="meals-<?php echo strtolower($day); ?>">
                        <?php if (!empty($groupedMeals[$day])): ?>
                            <?php foreach ($groupedMeals[$day] as $index => $meal): ?>
                                <div class="mb-3 relative meal-card">
                                <a href="#" class="meal-details" data-meal='<?= htmlspecialchars(json_encode($meal), ENT_QUOTES, 'UTF-8') ?>'>
                                <img
                                   src="<?= !empty($meal['image']) ? htmlspecialchars($meal['image']) : 'uploads/generic_food.png' ?>"
                                   alt="<?= htmlspecialchars($meal['name'] ?? $meal['recipe'] ?? 'No name') ?>"
                                   class="w-full h-40 object-cover rounded-lg"
                                   />

                                 
                                        <div class="bg-black bg-opacity-60 text-white text-sm p-2 absolute bottom-0 left-0 right-0 rounded-b-lg">
                                            <?php echo htmlspecialchars($meal['recipe']); ?>
                                        </div>
                                    </a>
                                    <div class="absolute top-2 right-2 meal-options">
                                        <div class="relative inline-block">
                                            <button class="bg-white bg-opacity-80 rounded-full w-8 h-8 flex items-center justify-center text-gray-700 hover:bg-opacity-100 meal-menu-button">
                                                <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
                                            </button>
                                            <div class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 meal-menu">
                                                <div class="py-1">
                                                    <button class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 remove-meal"
                                                            data-meal-id="<?= $meal['id'] ?>">
                                                        <i class="fa fa-trash mr-2 text-red-500" aria-hidden="true"></i> Remove
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <button class="add-meal mt-2 w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center mx-auto hover:bg-primary-dark" data-day="<?php echo $day; ?>">
                            <i class="fa fa-plus" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Meal Plan Grid - Middle Row -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-5">
                <?php foreach (['Thursday', 'Friday', 'Saturday'] as $day): ?>
                <div class="bg-white rounded-lg shadow-sm p-4 flex flex-col">
                    <h2 class="text-lg font-bold text-center mb-4"><?php echo $day; ?></h2>
                    
                    <div class="flex-1 flex flex-col" id="meals-<?php echo strtolower($day); ?>">
                        <?php if (isset($_SESSION['meal_plan'][$day]) && !empty($_SESSION['meal_plan'][$day])): ?>
                            <?php foreach ($_SESSION['meal_plan'][$day] as $index => $meal): ?>
                                <div class="mb-3 relative meal-card">
                                <a href="#" class="meal-details" data-meal='<?php echo htmlspecialchars(json_encode($meal), ENT_QUOTES, 'UTF-8'); ?>'>                                        <img src="<?php echo !empty($meal['image']) ? $meal['image'] : 'uploads/generic_food.png'; ?>" alt="<?php echo htmlspecialchars($meal['name']); ?>" class="w-full h-40 object-cover rounded-lg">
                                        <div class="bg-black bg-opacity-60 text-white text-sm p-2 absolute bottom-0 left-0 right-0 rounded-b-lg">
                                            <?php echo htmlspecialchars($meal['name']); ?>
                                        </div>
                                    </a>
                                    <div class="absolute top-2 right-2 meal-options">
                                        <div class="relative inline-block">
                                            <button class="bg-white bg-opacity-80 rounded-full w-8 h-8 flex items-center justify-center text-gray-700 hover:bg-opacity-100 meal-menu-button">
                                                <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
                                            </button>
                                            <div class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 meal-menu">
                                                <div class="py-1">
                                                    <button class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 remove-meal" data-day="<?php echo $day; ?>" data-index="<?php echo $index; ?>">
                                                        <i class="fa fa-trash mr-2 text-red-500" aria-hidden="true"></i> Remove
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <button class="add-meal mt-2 w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center mx-auto hover:bg-primary-dark" data-day="<?php echo $day; ?>">
                            <i class="fa fa-plus" aria-hidden="true"></i>
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
                        <?php if (isset($_SESSION['meal_plan']['Sunday']) && !empty($_SESSION['meal_plan']['Sunday'])): ?>
                            <?php foreach ($_SESSION['meal_plan']['Sunday'] as $index => $meal): ?>
                                <div class="mb-3 relative meal-card">
                                <a href="#" class="meal-details" data-meal='<?= htmlspecialchars(json_encode($meal), ENT_QUOTES, 'UTF-8') ?>'>

                                        <img src="<?php echo !empty($meal['image']) ? $meal['image'] : 'uploads/generic_food.png'; ?>" alt="<?php echo htmlspecialchars($meal['name']); ?>" class="w-full h-40 object-cover rounded-lg">
                                        <div class="bg-black bg-opacity-60 text-white text-sm p-2 absolute bottom-0 left-0 right-0 rounded-b-lg">
                                            <?php echo htmlspecialchars($meal['name']); ?>
                                        </div>
                                    </a>
                                    <div class="absolute top-2 right-2 meal-options">
                                        <div class="relative inline-block">
                                            <button class="bg-white bg-opacity-80 rounded-full w-8 h-8 flex items-center justify-center text-gray-700 hover:bg-opacity-100 meal-menu-button">
                                                <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
                                            </button>
                                            <div class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 meal-menu">
                                                <div class="py-1">
                                                    <button class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 remove-meal" data-day="Sunday" data-index="<?php echo $index; ?>">
                                                        <i class="fa fa-trash mr-2 text-red-500" aria-hidden="true"></i> Remove
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <button class="add-meal mt-2 w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center mx-auto hover:bg-primary-dark" data-day="Sunday">
                        <i class="fa fa-plus" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="relative w-full mt-8 flex justify-center">
            <div class="w-full max-w-[420px] overflow-x-auto scroll-container px-4">
                <div class="flex gap-4 snap-x snap-mandatory" id="slider-content">
                    <!-- Dynamic Insert -->
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function standardizeImagePath($imagePath) {
    if (empty($imagePath) || $imagePath === 'assets/default-recipe.jpg') {
        return 'uploads/generic_food.png';
    }
    return $imagePath;
}
        const scrollContainer = document.querySelector('.scroll-container');
        let isDown = false;
        let startX;
        let scrollLeft;

        scrollContainer.addEventListener('mousedown', (e) => {
            isDown = true;
            scrollContainer.classList.add('cursor-grabbing');
            startX = e.pageX - scrollContainer.offsetLeft;
            scrollLeft = scrollContainer.scrollLeft;
        });

        scrollContainer.addEventListener('mouseleave', () => {
            isDown = false;
            scrollContainer.classList.remove('cursor-grabbing');
        });

        scrollContainer.addEventListener('mouseup', () => {
            isDown = false;
            scrollContainer.classList.remove('cursor-grabbing');
        });

        scrollContainer.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - scrollContainer.offsetLeft;
            const walk = (x - startX) * 1.5;
            scrollContainer.scrollLeft = scrollLeft - walk;
        });

        // Render recipe cards
        function renderRecipes(data) {
            const container = document.getElementById('slider-content');
            container.innerHTML = '';
            data.forEach(recipe => {
                const card = document.createElement('div');
                card.className = "w-full flex-shrink-0 cursor-pointer";

                card.innerHTML = `
                    <div class="w-full h-full bg-white shadow rounded-lg flex items-center p-2 hover:shadow-lg transition">
                        <img src="${recipe.imageURL || 'uploads/generic_food.png'}" alt="${recipe.meal_name}" draggable="false" class="h-auto w-[calc(50%)] rounded object-cover select-none">
                        <div class="ml-4 flex flex-col justify-center">
                            <h4 class="font-bold text-base mb-1">${recipe.meal_name}</h4>
                            <p class="text-gray-500 text-sm">${recipe.meal_type}</p>
                        </div>
                    </div>
                `;

                card.addEventListener('click', () => {
                    window.location.href = `./recipe.php?id=${recipe.id}&from=dashboard.php`;
                });

                // Touch for mobile
                let startX, startY, startTime;
                card.addEventListener('touchstart', (e) => {
                    startX = e.touches[0].pageX;
                    startY = e.touches[0].pageY;
                    startTime = new Date().getTime();
                });
                card.addEventListener('touchend', (e) => {
                    const touch = e.changedTouches[0];
                    const diffX = Math.abs(touch.pageX - startX);
                    const diffY = Math.abs(touch.pageY - startY);
                    const timeDiff = new Date().getTime() - startTime;
                    if (diffX < 5 && diffY < 5 && timeDiff < 500) {
                        window.location.href = `./recipe.php?id=${recipe.id}&from=dashboard.php`;
                    }
                });

                container.appendChild(card);
            });
        }

        // Load new recipes via AJAX
        function loadRecipes() {
            fetch('dashboard.php?action=random_recipes')
                .then(response => response.json())
                .then(data => {
                    renderRecipes(data);
                })
                .catch(error => {
                    console.error('Failed to load recipes:', error);
                });
        }

        // On page load: use server-side rendered $randomRecipes
        document.addEventListener('DOMContentLoaded', () => {
            const initialRecipes = <?php echo json_encode($randomRecipes); ?>;
            renderRecipes(initialRecipes);
        });
    </script>
    
    
    <!-- Meal Selection Modal -->
    <div id="meal-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4" role="dialog" aria-label="Add Meal Modal">
    <div class="bg-white rounded-lg w-full max-w-6xl h-[90vh] flex flex-col">
        <div class="p-4 border-b flex justify-between items-center">
            <h2 class="text-xl font-bold">Add Meal</h2>
            <button id="close-modal" class="text-gray-500 hover:text-gray-700">
                <i class="fa fa-times" aria-hidden="true"></i><span class="sr-only">Close</span>
            </button>
        </div>
        <iframe id="search-iframe" class="w-full h-full border-0"></iframe>
    </div>
</div>
</div>
            
    <!-- Recipe Details Modal -->
    <div id="recipe-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50 p-2 sm:p-4" role="dialog" aria-label="Recipe Details Modal">
      
       <div class="bg-white rounded-lg w-full max-w-2xl mx-auto p-6 max-h-[90vh] overflow-y-auto">




            <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                <div class="flex items-center">
                    <button id="back-to-meal-plan" class="text-gray-500 hover:text-gray-700 mr-2">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i><span class="sr-only">Back to recipe list</span>
                    </button>
                    <h2 class="text-xl font-bold" id="recipe-title">Recipe Details</h2>
                </div>
                <a href="social.php"
                   class="text-gray-500 hover:text-blue-600 mr-2"
                   title="Share to Social Page">
                    <i data-lucide="share-2" class="w-5 h-5"></i>
                    <span class="sr-only">Share to Social Page</span>
                </a>
                <button id="close-recipe" class="text-gray-500 hover:text-gray-700">
                    <i class="fa fa-times" aria-hidden="true"></i><span class="sr-only">Close</span>
                </button>
            </div>
            
            <div class="p-0">
                <img id="recipe-image" src="" alt="Preview of selected recipe" class="w-full h-64 object-cover rounded-lg mb-4">

                <!-- Meal Name Section -->
                <div class="mb-4">
                    <h3 class="text-lg font-bold">Meal Name</h3>
                    <p id="meal-name" class="text-gray-700"></p>
                </div>


                <!-- Description Section -->
                <div id="description-section" class="mb-6">
                    <h3 class="text-lg font-bold mb-2 mt-6">Description</h3>
                    <p id="recipe-description" class="text-gray-700"></p>
                </div>


                <h3 class="text-lg font-bold mb-2">Meal Details</h3>
                   <ul class="list-disc pl-5 space-y-1 text-sm text-gray-700">



                      <li id="prep-time"></li>
                      <li id="cook-time"></li>
                      <li id="difficulty"></li>
                      <li id="serves"></li>
                      <li id="meal-type"></li>
                  </ul>
                </div>
                
                <div class="mb-6">
                    <h3 class="text-lg font-bold mt-6 mb-2">Ingredients</h3>
                    <ul id="recipe-ingredients" class="list-disc pl-5 space-y-1"></ul>
                </div>
                
                <div class="mb-6">
                    <h3 class="text-lg font-bold mb-2">Instructions</h3>
                    <p id="recipe-instructions" class="text-gray-700"></p>
                </div>
                <div class="mb-6">
               
                  <div id="recipe-tags" class="flex flex-wrap gap-2 mb-4"></div>
                 </div>

           <div id="nutrition-section" class="mb-6">
               <h3 class="text-lg font-bold mb-2">Nutrition Information</h3>
               <ul id="recipe-nutrition" class="list-disc pl-5 space-y-1"></ul>
           </div>
       </div>
        </div>

    <!-- Confirmation Modal -->
    <div id="confirm-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50 p-2 sm:p-4" role="dialog" aria-label="Confirmation Modal">
        <div class="bg-white rounded-lg w-[95%] max-w-md p-4 sm:p-6">
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
        const searchIframe = document.getElementById('search-iframe');
        
        // Add meal button click handlers - open choose modal 
        document.querySelectorAll('.add-meal').forEach(button => {
    button.addEventListener('click', function () {
        const day = this.dataset.day;
        currentDay = day;
        const searchIframe = document.getElementById('search-iframe');
   searchIframe.src = `search.php?modal=true&day=${encodeURIComponent(currentDay)}&from=dashboard`;

    document.getElementById('meal-modal').classList.remove('hidden');
    });
});

        // Close modal handler
        document.getElementById('close-modal').addEventListener('click', () => {
            mealModal.classList.add('hidden');
            location.reload();
        });

        // Listen for messages from the iframe
  
      window.addEventListener('message', function(e) {
            if (e.data.action === 'addMeal') {
                const formData = new FormData();
                formData.append('add_day', e.data.day);
                formData.append('add_meal_data', JSON.stringify(e.data.recipe));

                fetch('dashboard.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        //mealModal.classList.add('hidden');
                        window.location.reload();
                    }
                });
            } 
            else if (e.data.action === 'addRecipe') {//server
                // Handle recipe data from search.php
                const recipe = e.data.recipe;
                const day = e.data.day;
               

                
                // Create a meal object from the recipe data
                  const meal = {
                      id: recipe.id,
                      name: recipe.recipe,
                      image: standardizeImagePath(recipe.imageURL || recipe.image),
                      description: recipe.description || '',
                      ingredients: recipe.ingredients ? recipe.ingredients.split(', ') : [],
                      instructions: recipe.instructions || '',
                      nutrition: [],  // Empty nutrition for now
                      prepTime: recipe.prep_time || '',
                      cookTime: recipe.cook_time || '',
                      difficulty: recipe.difficulty || '',
                      serves: recipe.serves || '',
                      mealType: recipe.meal_type || '',
                      tags: Array.isArray(recipe.tags) ? recipe.tags : []
                   
                  };
                 

                
                // Add the meal to the session via AJAX
                const formData = new FormData();
                formData.append('add_day', day);
                formData.append('add_meal_data', JSON.stringify(meal));
                
                fetch('dashboard.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                       
                        window.location.reload();
                    } else {
                        alert('Failed to add meal: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while adding the meal');
                });
            }
        });
        
        // Close modal handlers
        document.getElementById('close-modal').addEventListener('click', () => {
            mealModal.classList.add('hidden');
            searchIframe.src = 'about:blank';
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
                document.getElementById('meal-name').textContent = mealData.recipe || "Unnamed"
                document.getElementById('recipe-image').src = mealData.image;
                document.getElementById('recipe-image').alt = mealData.name;
                document.getElementById('recipe-description').textContent = mealData.description;
                document.getElementById("prep-time").textContent     = "Prep Time: " + (mealData.prepTime || "N/A") + " min";
                document.getElementById("cook-time").textContent     = "Cook Time: " + (mealData.cookTime || "N/A") + " min";
                document.getElementById("difficulty").textContent    = "Difficulty: " + (mealData.difficulty || "N/A");
                document.getElementById("serves").textContent      = "Serves: " + (mealData.serves || "N/A");
                document.getElementById("meal-type").textContent = "Meal Type: " + (mealData.mealType || "N/A");



                // ingredients instructions
                const safeIngredients = mealData.ingredients
            ? (Array.isArray(mealData.ingredients)
             ? mealData.ingredients
             : (typeof mealData.ingredients === 'string'
              ? mealData.ingredients.split(',').map(s => s.trim())
             : []))
            : [];

              const safeInstructions = mealData.instructions
             ? (Array.isArray(mealData.instructions)
              ? mealData.instructions
             : (typeof mealData.instructions === 'string'
             ? mealData.instructions.split('\n').map(s => s.trim())
            : []))
            : [];


// Populate ingredients
const ingredientsList = document.getElementById('recipe-ingredients');
ingredientsList.innerHTML = '';
safeIngredients.forEach(ingredient => {
    const li = document.createElement('li');
    li.textContent = ingredient;
    ingredientsList.appendChild(li);
});

// Populate instructions
                const instructionsContainer = document.getElementById('recipe-instructions');
                instructionsContainer.textContent = safeInstructions.join('\n\n');

              

                // Populate tags
                const tagsContainer = document.getElementById('recipe-tags');
                tagsContainer.innerHTML = '';

                if (mealData.tags && mealData.tags.length > 0) {
                    tagsContainer.style.display = 'flex';
                    const label = document.createElement('span');
                    label.textContent = 'Tags: ';
                    label.className = 'font-semibold mr-2';
                    tagsContainer.appendChild(label);

                    mealData.tags.forEach(tag => {
                        const span = document.createElement('span');
                        span.className = 'inline-block bg-green-100 text-green-800 text-sm rounded-full px-3 py-1 mr-1';
                        span.textContent = tag;
                        tagsContainer.appendChild(span);
                    });
                } else {
                    tagsContainer.style.display = 'none';
                }



                // Populate nutrition
                const nutritionList = document.getElementById('recipe-nutrition');
                const nutritionSection = document.getElementById('recipe-nutrition');
                const nutritionWrapper = nutritionSection?.closest('div'); // container with heading + list
                nutritionSection.innerHTML = '';

                const safeNutrition = Array.isArray(mealData.nutrition)
                    ? mealData.nutrition
                    : (typeof mealData.nutrition === 'string'
                        ? mealData.nutrition.split(',').map(n => n.trim())
                        : []);

                const validNutrition = safeNutrition.filter(item => {
                    const value = parseInt(item);
                    return !isNaN(value) && value > 0;
                });

                if (validNutrition.length > 0) {
                    validNutrition.forEach(item => {
                        const li = document.createElement('li');
                        li.textContent = item;
                        nutritionSection.appendChild(li);
                    });
                    if (nutritionWrapper) nutritionWrapper.classList.remove('hidden');
                } else {
                    if (nutritionWrapper) nutritionWrapper.classList.add('hidden');
                }




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
        let mealIdToRemove = null;

        document.querySelectorAll('.remove-meal').forEach(button => {
            button.addEventListener('click', function () {
                mealIdToRemove = this.dataset.mealId;

                // Show confirmation modal
                document.getElementById('confirm-title').textContent = `Remove this meal?`;
                document.getElementById('confirm-message').textContent = 'Are you sure you want to remove this meal from your plan?';
                document.getElementById('confirm-modal').classList.remove('hidden');
            });
        });


        // Confirm removal handler
        document.getElementById('confirm-action').addEventListener('click', function() {
            if (mealIdToRemove) {
                const formData = new FormData();
                formData.append('remove_meal_id', mealIdToRemove);

                fetch('dashboard.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('confirm-modal').classList.add('hidden');
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






        document.addEventListener("DOMContentLoaded", function() {
        const chooseSearchBtn = document.getElementById("chooseSearchBtn");
        const chooseCreateBtn = document.getElementById("chooseCreateBtn");
        const mealModal = document.getElementById("meal-modal");
        const searchIframe = document.getElementById("search-iframe");




     chooseCreateBtn.addEventListener("click", function () {



    const iframe = document.getElementById("createRecipeIframe");
    iframe.src = `add-recipe.php?from=dashboard&day=${encodeURIComponent(currentDay)}`;
     iframe.onload = () => {
        const userId = <?= json_encode($_SESSION['user']['id']) ?>;
        setTimeout(() => {
        iframe.contentWindow.postMessage({
            type: "USER_ID",
            userId: userId
        }, "*");
    }, 100);



    };



    document.getElementById("createRecipeModal").classList.remove("hidden");
});
    document.getElementById("close-createRecipeModal").addEventListener("click", function() {
        document.getElementById("createRecipeModal").classList.add("hidden");
      
        document.getElementById("createRecipeIframe").src = "";
    });
});
    </script> 
  
    

<!-- Create Recipe Modal -->
<div id="createRecipeModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-lg w-full max-w-md h-[80vh] flex flex-col">
    <div class="p-4 border-b flex justify-between items-center">
      <h2 class="text-xl font-bold">Create Your Own Recipe</h2>
        <button id="close-createRecipeModal" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-center flex items-center justify-center">
            <i class="fa fa-times" aria-hidden="true"></i><span class="sr-only">Close</span>
        </button>
    </div>
    <iframe id="createRecipeIframe" class="w-full flex-1 border-0" src=""></iframe>
  </div>
</div>
<script>

    let handledAddMeal = false;

    window.addEventListener('message', function (event) {
        const data = event.data;
        if (data.action === 'addMealSuccess') {
            window.location.reload(); // <- just reload cleanly
        }
    });

</script>
</body>
</html>
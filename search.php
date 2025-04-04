<?php
session_start();
// Include the controller
require_once __DIR__ .  '/controllers/recipe.php';

// Get current page and perPage from the query parameters (default to 1 and 10 if not set)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = isset($_GET['perPage']) ? (int)$_GET['perPage'] : 15;

// Initialize the controller
$recipeController = new \RecipeController(null);

// Get filter values from URL parameters  
$search = $_GET['search'] ?? null;
$dietary = $_GET['dietary'] ?? null;
$maxPrepTime = isset($_GET['max_prep_time']) ? (int)$_GET['max_prep_time'] : 60;

$mealType = $_GET['meal_type'] ?? null;
$priceRange = $_GET['price_range'] ?? null;
$isModal = isset($_GET['modal']) && $_GET['modal'] === 'true';
$minBudget = isset($_GET['min_budget']) ? (int)$_GET['min_budget'] : 0;
$maxBudget = isset($_GET['max_budget']) ? (int)$_GET['max_budget'] : 50;


$budgetErrors = validateBudget($minBudget, $maxBudget);

function validateBudget($minBudget, $maxBudget) {
    $errors = [];

    if ($minBudget < 0) {
        $errors[] = "Minimum budget cannot be negative.";
    }

    if ($maxBudget < 0) {
        $errors[] = "Maximum budget cannot be negative.";
    }

    if ($minBudget > $maxBudget) {
        $errors[] = "Minimum budget cannot be greater than maximum budget.";
    }

    if (!empty($errors)) {
        return $errors;
        
    }
    return [];
}


//  If there is no priceRange but min/max are provided, generate it automatically
if (isset($_GET['min_budget']) && isset($_GET['max_budget']) && !$priceRange) {
    $priceRange = "{$_GET['min_budget']}-{$_GET['max_budget']}";
}



// Use the controller to get filtered recipes
if (empty($budgetErrors)) {
    $result = $recipeController->searchAction(
        $search,
        $dietary,
        $maxPrepTime,
        $mealType,
        $priceRange,
        $page,
        $perPage
    );
    // Get the actual recipe list from the returned result
    $recipes = $result['recipes'];
    $totalPages = $result['totalPages'];
    $currentPage = $result['currentPage'];
} else {
    $recipes = []; // Don't show recipes if budget is invalid
    $totalPages = 1;
    $currentPage = 1;
}

// Get all dietary options for filter radio buttons
$dietaryOptions = ['Vegetarian', 'Vegan', 'Gluten-Free', 'Dairy-Free', 'Keto', 'Paleo'];
$mealTypeOptions = ['Breakfast', 'Lunch', 'Dinner', 'Snack', 'Dessert'];

// Calculate recipes count
$recipesCount = count($recipes);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe Search</title>
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
        body {
            padding-top: 4rem; /* Space for fixed header */
        }
    </style>

</head>
<body class="bg-green-50">
    <?php if (!$isModal): ?>
        <?php include 'header.php'; ?>
    <?php endif; ?>


    
    <div class="container mx-auto p-4">
        <?php if (!$isModal): ?>
        <a href="profile.php" class="flex items-center text-gray-600 mb-6">
            <i data-lucide="arrow-left" class="h-5 w-5 mr-1"></i>
            Back to Profile
        </a>
        <?php endif; ?>

        <!-- Search Bar -->
        <div class="relative mb-6">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <i data-lucide="search" class="w-4 h-4 text-gray-400"></i>
            </div>
            <form action="" method="GET" id="search-form">
                <?php if ($isModal): ?>
                <input type="hidden" name="modal" value="true">
                <?php endif; ?>
                  
                
                <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" 
                    class="bg-white text-gray-700 border-2 border rounded-lg pl-10 p-2.5 w-full" 
                    placeholder="Search recipes...">

                <?php if ($dietary): ?>
                <input type="hidden" name="dietary" value="<?= htmlspecialchars($dietary) ?>">
                <?php endif; ?>
                
                <?php if ($maxPrepTime): ?>
                <input type="hidden" name="max_prep_time" value="<?= htmlspecialchars($maxPrepTime) ?>">
                <?php endif; ?>
                
                <?php if ($mealType): ?>
                <input type="hidden" name="meal_type" value="<?= htmlspecialchars($mealType) ?>">
                <?php endif; ?>
            </form>
        </div>

        <div class="flex flex-col md:flex-row gap-6">
            <!-- Left Column: Filters -->
            <div class="w-full md:w-1/3 h-fit bg-white rounded-lg shadow p-6">
            <?php if (!empty($budgetErrors)): ?>
            <div id="budget-error-box" class="relative mb-4 p-4 bg-red-100 text-red-800 border border-red-300 rounded-lg">
            <button onclick="document.getElementById('budget-error-box').style.display='none';"
                    class="absolute top-2 right-2 text-red-700 hover:text-red-900 text-lg font-bold focus:outline-none"
                    aria-label="Close">
                &times;
            </button>
            <ul class="list-disc list-inside text-sm">
                <?php foreach ($budgetErrors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
         </div>
      <?php endif; ?>

                <!-- Filters Section -->
                <div class="mb-4 flex justify-between items-center">
                    <div class="flex items-center">
                        <i data-lucide="filter" class="h-5 w-5 mr-2 text-gray-600"></i>
                        <h2 class="text-lg font-semibold">Filters</h2>
                    </div>
                    <a href="?<?= $isModal ? 'modal=true' : '' ?>" class="text-sm text-red-500">Reset All</a>
                </div>

                <form action="" method="GET" class="space-y-6" id="filter-form">
                    <?php if ($isModal): ?>
                    <input type="hidden" name="modal" value="true">
                    <?php endif; ?>
                    
                    <?php if ($search): ?>
                    <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                    <?php endif; ?>
                    
                    

                    <!-- Dietary Preferences -->
                    <div class="border-b pb-4">
                        <div class="flex justify-between items-center mb-2 cursor-pointer" onclick="toggleSection('dietary')">
                            <h3 class="font-medium">Dietary Preference</h3>
                            <i data-lucide="chevron-down" id="dietary-icon" class="h-5 w-5 transform transition-transform"></i>
                        </div>
                        <div id="dietary-options" class="mt-2 space-y-2">
                            <div class="flex items-center">
                                <input type="radio" name="dietary" value="" id="dietary-any"
                                    class="h-4 w-4 text-red-500 rounded" <?= !$dietary ? 'checked' : '' ?>>
                                <label for="dietary-any" class="ml-2 text-sm text-gray-700">Any</label>
                            </div>
                            <?php foreach ($dietaryOptions as $option): ?>
                            <div class="flex items-center">
                                <input type="radio" name="dietary" value="<?= htmlspecialchars($option) ?>" 
                                    id="<?= htmlspecialchars(strtolower(str_replace(' ', '-', $option))) ?>" 
                                    class="h-4 w-4 text-red-500 rounded" <?= $dietary === $option ? 'checked' : '' ?>>
                                <label for="<?= htmlspecialchars(strtolower(str_replace(' ', '-', $option))) ?>" 
                                    class="ml-2 text-sm text-gray-700"><?= htmlspecialchars($option) ?></label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Maximum Preparation Time -->
                    <div class="border-b pb-4">
                        <div class="flex justify-between items-center mb-2 cursor-pointer" onclick="toggleSection('prep-time')">
                            <h3 class="font-medium">Maximum Preparation Time</h3>
                            <i data-lucide="chevron-down" id="prep-time-icon" class="h-5 w-5 transform transition-transform"></i>
                        </div>
                        <div id="prep-time-options" class="mt-2">
                            <input type="range" name="max_prep_time" min="5" max="120" step="5" 
                                value="<?= $maxPrepTime ?>" 
                                class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer" 
                                oninput="updatePrepTimeValue(this.value)">
                            <div class="flex justify-between text-sm text-gray-600 mt-1">
                                <span>5 minutes</span>
                                <span id="prep-time-value"><?= $maxPrepTime ?> minutes</span>
                                <span>120 minutes</span>
                            </div>
                        </div>
                    </div>

                    <!-- Meal Type -->
                    <div class="border-b pb-4">
                        <div class="flex justify-between items-center mb-2 cursor-pointer" onclick="toggleSection('meal-type')">
                            <h3 class="font-medium">Meal Type</h3>
                            <i data-lucide="chevron-down" id="meal-type-icon" class="h-5 w-5 transform transition-transform"></i>
                        </div>
                        <div id="meal-type-options" class="mt-2">
                            <select name="meal_type" class="w-full p-2 bg-gray-100 rounded-lg text-gray-700">
                                <option value="">Any</option>
                                <?php foreach ($mealTypeOptions as $option): ?>
                                <option value="<?= htmlspecialchars($option) ?>" <?= $mealType === $option ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($option) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <!-- Budget Filter -->
            <div class="border-b pb-4">
                <div class="flex justify-between items-center mb-2 cursor-pointer" onclick="toggleSection('budget')">
                   <h3 class="font-medium">Budget ($)</h3>
                    <i data-lucide="chevron-down" id="budget-icon" class="h-5 w-5 transform transition-transform"></i>
            </div>
           <div id="budget-options" class="mt-2 space-y-2">
              <input type="number" name="min_budget" placeholder="Min $" value="<?= htmlspecialchars($minBudget ?? 0) ?>"
                class="w-full p-2 border rounded-lg text-gray-700 bg-gray-100">
              <input type="number" name="max_budget" placeholder="Max $" value="<?= htmlspecialchars($maxBudget ?? 75) ?>"
                class="w-full p-2 border rounded-lg text-gray-700 bg-gray-100">
          </div>
           </div>

                    <button type="submit" class="w-full py-2 px-4 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition duration-150 ease-in-out">
                        Apply Filters
                    </button>
                </form>
            </div>

            <!-- Right Column: Recipe Results -->
            <div id="results" class="w-full md:w-2/3">
                <p class="text-sm text-gray-600 mb-4">Showing <?= $recipesCount ?> recipes that match your preferences</p>
                
                <div class="grid grid-cols-1 gap-6">
                    <?php if (empty($recipes)): ?>
                    <div class="bg-white rounded-lg shadow p-6 text-center">
                        <i data-lucide="search-x" class="h-12 w-12 mx-auto text-gray-400 mb-4"></i>
                        <h3 class="text-lg font-semibold mb-2">No recipes found</h3>
                        <p class="text-gray-600">Try adjusting your filters or search criteria.</p>
                   </div>
                    <?php else: ?>
                        <?php foreach ($recipes as $recipe): ?>   
                        <a href="<?= $isModal ? '#' : 'recipe.php?id=' . htmlspecialchars($recipe['id']) ?>" 
                           class="block bg-white rounded-lg shadow overflow-hidden hover:shadow-md transition-shadow duration-200"
                           <?php if ($isModal): ?>
                           onclick="addRecipeToMealPlan(<?= htmlspecialchars(json_encode($recipe)) ?>)"
                           <?php endif; ?>>
                                <?php if (!empty($recipe['image'])): ?>
                                <div class="w-full">
                                    <img src="<?= htmlspecialchars($recipe['image']) ?>" alt="<?= htmlspecialchars($recipe['recipe']) ?>" class="w-full h-48 object-cover">
                                </div>
                                <?php endif; ?>
                            <div class="flex flex-col md:flex-row">
                                <div class="p-4">
                                    <h3 class="font-bold text-lg mb-2"><?= htmlspecialchars($recipe['recipe']) ?></h3>
                                    
                                    <?php if (!empty($recipe['description'])): ?>
                                    <p class="text-gray-600 text-sm mb-3"><?= htmlspecialchars($recipe['description']) ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="flex flex-wrap gap-2 mb-4">
                                        <?php foreach ($recipe['tags'] as $tag): ?>
                                        <span class="inline-block px-2 py-1 text-xs rounded-xl bg-green-100 text-green-800"><?= htmlspecialchars($tag) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php if (isset($recipe['budget']) && is_numeric($recipe['budget'])): ?>
                                       <p class="text-sm text-gray-600 font-semibold mb-2">💵 Budget: $<?= number_format((float)$recipe['budget'], 2) ?></p>
                                    <?php else: ?>
                                       <p class="text-sm text-gray-600 font-semibold mb-2">💵 Budget: $</p>
                                    <?php endif; ?>
                                    
                                    <div class="flex items-center gap-4">
                                        <div class="flex items-center">
                                            <i data-lucide="clock" class="h-5 w-5 text-gray-500 mr-1"></i>
                                            <span class="text-sm text-gray-600"><?= $recipe['prep_time'] ?> mins</span>
                                        </div>
                                        <div class="flex items-center">
                                            <i data-lucide="gauge" class="h-5 w-5 text-gray-500 mr-1"></i>
                                            <span class="text-sm text-gray-600"><?= htmlspecialchars($recipe['difficulty']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                        <div class="mt-6 flex justify-between items-center">
                            <!-- Previous Page Link -->
                            <?php if ($page > 1): ?>
                                <a href="?page=<?= $page - 1 ?>&perPage=<?= $perPage ?>&search=<?= htmlspecialchars($search ?? '') ?>&dietary=<?= htmlspecialchars($dietary ?? '') ?>&max_prep_time=<?= htmlspecialchars($maxPrepTime ?? '') ?>&meal_type=<?= htmlspecialchars($mealType ?? '') ?>"
                                   class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                                    Previous
                                </a>
                            <?php else: ?>
                                <span class="px-4 py-2 bg-gray-200 text-gray-400 rounded-md">Previous</span>
                            <?php endif; ?>

                            <!-- Next Page Link -->
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?= $page + 1 ?>&perPage=<?= $perPage ?>&search=<?= htmlspecialchars($search ?? '') ?>&dietary=<?= htmlspecialchars($dietary ?? '') ?>&max_prep_time=<?= htmlspecialchars($maxPrepTime ?? '') ?>&meal_type=<?= htmlspecialchars($mealType ?? '') ?>"
                                   class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                                    Next
                                </a>
                            <?php else: ?>
                                <span class="px-4 py-2 bg-gray-200 text-gray-400 rounded-md">Next</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($isModal): ?>
    <script>
        // Function to add recipe to meal plan
        function addRecipeToMealPlan(recipe) {
            // Get the day from URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const day = urlParams.get('day');
            
            // Send message to parent window
            window.parent.postMessage({
                action: 'addRecipe',
                day: day,
                recipe: recipe
            }, '*');
        }
    </script>
    <?php endif; ?>

    <script>
        lucide.createIcons();
        
        function toggleSection(id) {
            const element = document.getElementById(`${id}-options`);
            const icon = document.getElementById(`${id}-icon`);
            element.classList.toggle('hidden');
            icon.setAttribute('data-lucide', element.classList.contains('hidden') ? 'chevron-down' : 'chevron-up');
            lucide.createIcons({ elements: [icon] });
        }

        function updatePrepTimeValue(value) {
            document.getElementById('prep-time-value').textContent = `${value} minutes`;

        }
       

        document.querySelectorAll('input[type=radio], input[type=range], select').forEach(element => {
            element.addEventListener('change', function() {
                this.closest('form').submit();
            });
        });

        document.querySelector('#search-form input[name="search"]').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') document.getElementById('search-form').submit();
        });
    </script>
</body>
</html>

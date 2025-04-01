<?php
session_start();
// Include the controller
require_once __DIR__ .  '/controllers/recipe.php';

// Get current page and perPage from the query parameters (default to 1 and 10 if not set)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = isset($_GET['perPage']) ? (int)$_GET['perPage'] : 15;

// Initialize the controller
$recipeController = new \RecipeController(null);

// Get filter values from URL parameters if present
$search = $_GET['search'] ?? null;
$dietary = $_GET['dietary'] ?? null;
$maxPrepTime = isset($_GET['max_prep_time']) ? (int)$_GET['max_prep_time'] : 60;
$mealType = $_GET['meal_type'] ?? null;
$priceRange = $_GET['price_range'] ?? null;
$isModal = isset($_GET['modal']) && $_GET['modal'] === 'true';

// Use the controller to get filtered recipes
$recipesData = $recipeController->searchAction(
    $search,
    $dietary,
    $maxPrepTime,
    $mealType,
    $priceRange,
    $page,
    $perPage
);

// Get all dietary options for filter radio buttons
$dietaryOptions = ['Vegetarian', 'Vegan', 'Gluten-Free', 'Dairy-Free', 'Keto', 'Paleo'];
$mealTypeOptions = ['Breakfast', 'Lunch', 'Dinner', 'Snack', 'Dessert'];

$recipes = $recipesData['recipes'];
$totalPages = $recipesData['totalPages'];
$currentPage = $recipesData['currentPage'];
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
                            <a href="recipe.php?id=<?= htmlspecialchars($recipe['id']) ?>" class="block bg-white rounded-lg shadow overflow-hidden hover:shadow-md transition-shadow duration-200">
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
<?php
session_start();
require_once 'controllers/recipe.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$recipeController = new RecipeController(null);

// Initialize variables
$selectedIngredients = [];
$recipes = [];
$matchPercentages = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ingredients']) && !empty($_POST['ingredients'])) {
    $selectedIngredients = $_POST['ingredients'];

    // Get matching recipes
    $result = $recipeController->findRecipesByIngredients($selectedIngredients);
    $recipes = $result['recipes'];
    $matchPercentages = $result['matchPercentages'];
}

// Common ingredients for the dropdown
$commonIngredients = [
    'Chicken Breast', 'Chicken Thighs', 'Ground Beef', 'Salmon', 'Tuna',
    'Onion', 'Garlic', 'Tomato', 'Potato', 'Carrot', 'Bell Pepper',
    'Rice', 'Pasta', 'Bread', 'Flour',
    'Milk', 'Eggs', 'Cheese', 'Butter', 'Olive Oil',
    'Salt', 'Pepper', 'Oregano', 'Basil', 'Thyme',
    'Lemon', 'Lime', 'Orange',
    'Broccoli', 'Spinach', 'Lettuce', 'Mushroom',
    'Black Beans', 'Chickpeas', 'Lentils',
    'Sugar', 'Honey', 'Maple Syrup',
    'Soy Sauce', 'Vinegar', 'Mustard'
];
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cook With What You Have - MealForge</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        .select2-container--default .select2-selection--multiple {
            border-radius: 0.375rem;
            border-color: #D1D5DB;
            min-height: 42px;
        }
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #10B981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #10B981;
            border: none;
            color: white;
            border-radius: 9999px;
            padding: 2px 8px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: white;
            margin-right: 5px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #f3f4f6;
        }
    </style>
</head>
<body class="bg-green-50">
    <?php include 'header.php'; ?>
    
    <div class="container mx-auto max-w-6xl p-4 mt-16">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-green-700 mb-2">Cook With What You Have</h1>
            <p class="text-lg text-gray-600">Enter the ingredients you have on hand, and we'll find recipes you can make!</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <form method="POST" action="ingredients.php" class="space-y-4">
                <div>
                    <label for="ingredients" class="block text-sm font-medium text-gray-700 mb-1">Search for Ingredients...</label>
                    <select id="ingredients" name="ingredients[]" class="ingredient-select w-full" multiple="multiple">
                        <?php foreach ($commonIngredients as $ingredient): ?>
                            <option value="<?= $ingredient ?>" <?= in_array($ingredient, $selectedIngredients ?? []) ? 'selected' : '' ?>><?= $ingredient ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="text-center">
                    <button type="submit" class="px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                        Find Recipes
                    </button>
                </div>
            </form>
        </div>
        
        <?php if (!empty($recipes)): ?>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Recipes You Can Make</h2>
        <p class="text-gray-600 mb-4">Found <?= count($recipes) ?> recipes using your ingredients</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($recipes as $recipe): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <?php if (!empty($recipe['imageURL'])): ?>
                        <img src="<?= htmlspecialchars($recipe['imageURL']) ?>" alt="<?= htmlspecialchars($recipe['recipe']) ?>" class="w-full h-48 object-cover">
                    <?php else: ?>
                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                            <i data-lucide="utensils" class="w-12 h-12 text-gray-400"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="p-4">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($recipe['recipe']) ?></h3>
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                                <?= $matchPercentages[$recipe['id']] ?>% match
                            </span>
                        </div>
                        
                        <p class="text-gray-600 mb-4 line-clamp-3"><?= htmlspecialchars($recipe['description']) ?></p>
                        
                        <div class="flex justify-between items-center mb-4">
                            <div class="flex items-center">
                                <i data-lucide="clock" class="w-4 h-4 text-gray-500 mr-1"></i>
                                <span class="text-sm text-gray-500"><?= $recipe['total_time'] ?> mins</span>
                            </div>
                            <div class="flex items-center">
                                <i data-lucide="gauge" class="w-4 h-4 text-gray-500 mr-1"></i>
                                <span class="text-sm text-gray-500"><?= htmlspecialchars($recipe['difficulty']) ?></span>
                            </div>
                        </div>
                        
                        <a href="recipe.php?id=<?= $recipe['id'] ?>" class="block w-full text-center px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors">
                            View Recipe
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
    </div>
    
    <script>
        // Initialize Lucide icons
        lucide.createIcons();
        
        // Initialize Select2
        $(document).ready(function() {
            $('.ingredient-select').select2({
                placeholder: 'Search for ingredients...',
                tags: true,
                tokenSeparators: [',', ' '],
                createTag: function(params) {
                    return {
                        id: params.term,
                        text: params.term,
                        newTag: true
                    };
                }
            });
        });
    </script>
</body>
</html>

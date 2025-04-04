<?php
require_once __DIR__ . '/controllers/recipe.php';
require_once __DIR__ . '/controllers/bookmark.php';

session_start();

$recipeController = new RecipeController(null);

$recipe_id = isset($_GET['id']) ? intval($_GET['id']) : null;

$recipe = null;
if ($recipe_id) {
    $recipe = $recipeController->getRecipeById($recipe_id);
}

$recipeNotFound = ($recipe == null);
if (!$recipeNotFound) {
  // TODO: move some of this parsing logic to controller or provider
    $ingredientsList = explode(", ", $recipe['ingredients']);

    $instructionsList = explode(". ", $recipe['instructions']);

    $tagsList = [];
    if (!empty($recipe['tags'])) {
            $tagsList = $recipe['tags'];
    }

    //NOTE: Dummy rating, replace with actual data one day
    function generateRating() {
        return [
            'rating' => rand(40, 50) / 10,
            'reviews' => rand(80, 150)
        ];
    }

    $ratingInfo = generateRating();
}


$isBookmarked = false;
if (isset($_SESSION['user']) && isset($_SESSION['user']['id']) && $recipe_id) {
  $user_id = $_SESSION['user']['id'];
  $bookmarkController = new BookmarkController($user_id);
  $isBookmarked = $bookmarkController->isBookmarked($recipe_id);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $recipeNotFound ? 'Recipe Not Found' : htmlspecialchars($recipe['recipe']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
	<script src="https://unpkg.com/lucide@latest"></script>
    <style>
        [data-lucide="bookmark"].filled {
            fill: currentColor;
        }

        @media print {
        /* Hide elements that don’t need to be printed */
        .bookmark-button,
        .sidebar,
        .back-button,
        .print-hide,
        form,
        button,
        nav {
            display: none !important;
        }

        .container {
            max-width: 100% !important;
            padding: 0;
            margin: 0 auto !important;
        }

        body {
            background: white !important;
            color: black !important;
            /* Force background colors and images */
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Only remove outer card borders and shadows */
        .rounded-lg,
        .shadow-md {
            box-shadow: none !important;
            border-radius: 0 !important;
        }

        li {
            break-inside: avoid;
            page-break-inside: avoid;
        }
    }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto max-w-3xl p-4">
        <!-- Back to Search Button -->
        <div class="mb-6 print-hide">
            <a href="javascript:history.back()" class="flex items-center text-gray-600 hover:text-gray-900">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2">
                    <path d="M19 12H5"></path>
                    <path d="M12 19l-7-7 7-7"></path>
                </svg>
                Return
            </a>
        </div>

        <?php if ($recipeNotFound): ?>
        <!-- Recipe Not Found Message -->
        <div class="bg-white rounded-lg shadow-md p-6 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-4 text-gray-400">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Recipe Not Found</h2>
            <p class="text-gray-600 mb-6">The recipe you're looking for doesn't exist or was not specified.</p>
            <a href="recipes.php" class="inline-flex items-center px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                Browse All Recipes
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-2">
                    <path d="M9 18l6-6-6-6"></path>
                </svg>
            </a>
        </div>
        <?php else: ?>
				<?php if (isset($_SESSION['message'])): ?>
					<div class="bg-green-50 border border-green-400 text-green-700 p-4 rounded-md mb-6">
<p>
						<?php echo $_SESSION['message']; ?>
</p>
					</div>
					<?php unset($_SESSION['message']); ?>
				<?php endif; ?>

				<?php if (isset($_SESSION['error'])): ?>
					<div id="login-error" class="bg-red-50 border border-red-400 text-red-700 p-4 rounded-md mb-6">
<p>
						<?php echo $_SESSION['error']; ?>
</p>
					</div>
					<?php unset($_SESSION['error']); ?>
				<?php endif; ?>

        <div class="bg-white rounded-lg shadow-md p-6">
            <!-- Recipe Header -->
            <div class="flex justify-between items-start mb-4">
                <h1 class="text-3xl font-bold text-gray-800"><?= htmlspecialchars($recipe['recipe']) ?></h1>
                <div class="flex space-x-4">
                    <!-- Print Icon -->
                    <button id="print-button" class="text-gray-500 hover:text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 6 2 18 2 18 9"></polyline>
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                            <rect x="6" y="14" width="12" height="8"></rect>
                        </svg>
                    </button>
                    <!-- Share Icon -->
                    <button class="text-gray-500 hover:text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="18" cy="5" r="3"></circle>
                            <circle cx="6" cy="12" r="3"></circle>
                            <circle cx="18" cy="19" r="3"></circle>
                            <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
                            <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
                        </svg>
                    </button>
<!-- Bookmark Form -->
<form action="actions/bookmark.php" method="POST" id="bookmark_form">
    <input type="hidden" name="recipe_id" value="<?= $recipe_id ?>">
    <button type="submit" class="<?= $isBookmarked ? 'text-green-600' : 'text-gray-500' ?> hover:text-gray-700" id="bookmark_button">
        <?php if ($isBookmarked): ?>
            <i data-lucide="bookmark-check"></i>
        <?php else: ?>
            <i data-lucide="bookmark-plus"></i>
        <?php endif; ?>
    </button>
</form>
                </div>
            </div>

            <!-- Recipe Description -->
            <p class="text-gray-600 mb-4"><?= htmlspecialchars($recipe['description']) ?></p>

            <!-- Rating (demo) -->
            <div class="flex items-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
                </svg>
                <span class="text-green-600 font-medium"><?= $ratingInfo['rating'] ?></span>
                <span class="text-gray-500 ml-1">(<?= $ratingInfo['reviews'] ?> reviews)</span>
            </div>

            <!-- Recipe Meta Info - 2 rows x 2 columns grid -->
            <div class="grid grid-cols-2 gap-6 mb-6">
                <!-- Time - Now shows prep + cook separately -->
                <div class="flex items-start">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 text-gray-500">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <div>
                        <p class="text-gray-500 text-sm">Time</p>
                        <p class="font-medium"><?= $recipe['total_time'] ?> mins</p>
                        <p class="text-xs text-gray-500">(Prep: <?= $recipe['prep_time'] ?> mins, Cook: <?= $recipe['cook_time'] ?> mins)</p>
                    </div>
                </div>
                
                <!-- Serves -->
                <div class="flex items-start">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 text-gray-500">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <div>
                        <p class="text-gray-500 text-sm">Serves</p>
                        <p class="font-medium"><?= $recipe['serves'] ?></p>
                    </div>
                </div>
                
                <!-- Meal Type -->
                <div class="flex items-start">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 text-gray-500">
                        <path d="M18 8h1a4 4 0 0 1 0 8h-1"></path>
                        <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"></path>
                        <line x1="6" y1="1" x2="6" y2="4"></line>
                        <line x1="10" y1="1" x2="10" y2="4"></line>
                        <line x1="14" y1="1" x2="14" y2="4"></line>
                    </svg>
                    <div>
                        <p class="text-gray-500 text-sm">Meal Type</p>
                        <p class="font-medium"><?= htmlspecialchars($recipe['meal_type']) ?></p>
                        <p class="text-xs text-gray-500"><?= htmlspecialchars($recipe['dish_type']) ?></p>
                    </div>
                </div>
                
                <!-- Difficulty -->
                <div class="flex items-start">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 text-gray-500">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                        <path d="M2 17l10 5 10-5"></path>
                        <path d="M2 12l10 5 10-5"></path>
                    </svg>
                    <div>
                        <p class="text-gray-500 text-sm">Difficulty</p>
                        <p class="font-medium"><?= htmlspecialchars($recipe['difficulty']) ?></p>
                    </div>
                </div>
            </div>

            <!-- Tags -->
            <?php if (!empty($tagsList)): ?>
            <div class="flex flex-wrap gap-2 mb-4">
                <?php foreach ($tagsList as $tag): ?>
                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm"><?= htmlspecialchars(trim($tag)) ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Ingredients -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold">Ingredients</h2>
<!--  TODO: feature: adjustable serving size
                    <div class="flex items-center">
                        <button class="p-1 text-gray-500 hover:text-gray-700">-</button>
                        <span class="mx-2"><?= $recipe['serves'] ?> servings</span>
                        <button class="p-1 text-gray-500 hover:text-gray-700">+</button>

                    </div>
-->
                </div>
                
<div class="border-t border-gray-200">
    <?php foreach ($ingredientsList as $ingredient): ?>
        <?php
        $ingredient = trim($ingredient);
        
        // Try to match patterns like "160g fine beans", "2 large eggs", "1 tbsp white miso paste"
        if (preg_match('/^((?:\d+(?:\/\d+)?|\d*\.\d+)?\s*(?:g|kg|oz|lb|cups?|tbsps?|tsps?|ounces?|pounds?|ml|l)?(?:\s+[^\s]+)?)\s+([^\s,]+(?:\s+[^\s,]+)*)(?:,\s*(.+))?$/i', $ingredient, $matches)) {
            // Group 1: Quantity + unit + adjective (e.g., "2 large", "75g wholewheat")
            // Group 2: Main ingredient (e.g., "eggs", "penne")
            // Group 3 (optional): Extra description (e.g., "trimmed and halved")
            
            $quantity = trim($matches[1]);
            $name = trim($matches[2]);
            
            // Handle special cases where descriptions should be separate items
            if (!empty($matches[3]) && $matches[3] == "trimmed and halved") {
                // Create a new row for "trimmed and halved"
                $extraDesc = trim($matches[3]);
            } else {
                $extraDesc = "";
            }
        } else {
            // Fallback
            $parts = explode(' ', $ingredient, 2);
            if (count($parts) > 1) {
                $quantity = $parts[0];
                $name = $parts[1];
            } else {
                $name = $ingredient;
                $quantity = "";
            }
            $extraDesc = "";
        }
        ?>
        
        <div class="py-3 flex justify-between items-start border-b border-gray-200">
            <span class="flex-grow"><?= htmlspecialchars($name) ?></span>
            <span class="text-gray-600 ml-2 text-right"><?= htmlspecialchars($quantity) ?></span>
        </div>
        
        <?php if (!empty($extraDesc)): ?>
        <div class="py-3 flex justify-between items-start border-b border-gray-200">
            <span class="flex-grow"><?= htmlspecialchars($extraDesc) ?></span>
            <span class="text-gray-600 ml-2 text-right"></span>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

            </div>

            <!-- Instructions -->
            <div>
                <h2 class="text-xl font-bold mb-4">Instructions</h2>
                <ol class="list-none space-y-4">
                    <?php foreach (array_values($instructionsList) as $i => $instruction): ?>
                        <?php 
// NOTE: in a perfect world steps and ingredients would be in their own tables
// and we wouldn't do this 
                        // Extract the step number and instruction text
                        preg_match('/^(\d+)\.?\s*(.+)$/', $instruction, $matches);
                        if (count($matches) >= 3) {
                            $stepNumber = $matches[1];
                            $instructionText = $matches[2];
                        } else {
                            $stepNumber = $i + 1;
                            $instructionText = $instruction;
                        }
                        ?>
                        <li class="flex">
                            <div class="flex-shrink-0 mr-4">
                                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-green-100 text-green-800">
                                    <?= $stepNumber ?>
                                </div>
                            </div>
                            <div class="text-gray-700"><?= htmlspecialchars($instructionText) ?></div>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>
        </div>
        <?php endif; ?>
    </div>

	<script>
		// Initialize Lucide icons
		lucide.createIcons();

        // Save as PDF
        document.getElementById('print-button').addEventListener('click', function () {
            window.print();
        });
	</script>
</body>
</html>
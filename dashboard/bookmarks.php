<?php
// Start session and require necessary files
session_start();
require_once __DIR__ . '/../controllers/recipe.php';
require_once __DIR__ . '/../controllers/bookmark.php';

// Check if user is logged in
if (!isset($_SESSION['user']['id'])) {
    header('Location: ../login.php');
    exit;
}

// Get user ID from session
$userId = $_SESSION['user']['id'];

// Initialize controllers
$bookmarkController = new BookmarkController($userId);
$recipeController = new RecipeController(null);

// Get user's bookmarks
$bookmarksResponse = $bookmarkController->getUserBookmarks();
$bookmarkedRecipes = [];

// If bookmarks were successfully retrieved
if ($bookmarksResponse['success'] && !empty($bookmarksResponse['bookmarks'])) {
    // Convert RedBean objects to arrays and get full recipe details
    foreach ($bookmarksResponse['bookmarks'] as $bookmarkedRecipe) {
        $recipeId = $bookmarkedRecipe->id;
        $recipe = $recipeController->getRecipeById($recipeId);
        if ($recipe) {
            $bookmarkedRecipes[] = $recipe;
        }
    }
}

// Get user info
require_once __DIR__ . '/../controllers/user.php';
$userController = getUserController();
$user = $userController->getUserById($userId);
$firstName = $user['first_name'] ?? 'User';

// Calculate recipes count
$recipesCount = count($bookmarkedRecipes);

// Define a constant so the header knows we're in a subdirectory
define('IN_SUBDIRECTORY', true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MealForge - My Bookmarks</title>
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
</head>
<body class="bg-gray-50">
    <?php
    include_once __DIR__ . '/../header.php';
?>
    
    <div class="container mx-auto py-8 px-4 md:px-12">
        <header class="mb-8 pb-2 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h1 class="text-3xl font-semibold">My Bookmarked Recipes</h1>
                <div class="text-gray-600">
                    <span>Welcome, <span class="font-semibold"><?= htmlspecialchars($firstName) ?></span>!</span>
                </div>
            </div>
            <p class="text-gray-600 mt-2">You have <?= $recipesCount ?> bookmarked recipes</p>
        </header>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p><?= htmlspecialchars($_SESSION['message']); ?></p>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p><?= htmlspecialchars($_SESSION['error']); ?></p>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <!-- Recipe Results -->
        <div id="results" class="w-full">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (empty($bookmarkedRecipes)): ?>
                <div class="bg-white rounded-lg shadow p-6 text-center md:col-span-2 lg:col-span-3">
                    <i data-lucide="bookmark-x" class="h-12 w-12 mx-auto text-gray-400 mb-4"></i>
                    <h3 class="text-lg font-semibold mb-2">No bookmarked recipes</h3>
                    <p class="text-gray-600 mb-4">You haven't bookmarked any recipes yet.</p>
                    <a href="../search.php" class="inline-block py-2 px-4 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-150 ease-in-out">
                        Browse Recipes
                    </a>
                </div>
                <?php else: ?>
                    <?php foreach ($bookmarkedRecipes as $recipe): ?>
                    <a href="../recipe.php?id=<?= htmlspecialchars($recipe['id']) ?>" class="block bg-white rounded-lg shadow overflow-hidden hover:shadow-md transition-shadow duration-200">
                            <?php if (!empty($recipe['image'])): ?>
                            <div class="w-full">
                                <img src="<?= htmlspecialchars($recipe['image']) ?>" alt="<?= htmlspecialchars($recipe['recipe']) ?>" class="w-full h-48 object-cover">
                            </div>
                            <?php endif; ?>
                        <div class="p-4">
                            <h3 class="font-bold text-lg mb-2"><?= htmlspecialchars($recipe['recipe']) ?></h3>
                            
                            <?php if (!empty($recipe['description'])): ?>
                            <p class="text-gray-600 text-sm mb-3"><?= htmlspecialchars($recipe['description']) ?></p>
                            <?php endif; ?>
                            
                            <div class="flex flex-wrap gap-2 mb-4">
                                <?php if (isset($recipe['tags']) && is_array($recipe['tags'])): ?>
                                    <?php foreach ($recipe['tags'] as $tag): ?>
                                    <span class="inline-block px-2 py-1 text-xs rounded-xl bg-green-100 text-green-800"><?= htmlspecialchars($tag) ?></span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex items-center gap-4">
                                <div class="flex items-center">
                                    <i data-lucide="clock" class="h-5 w-5 text-gray-500 mr-1"></i>
                                    <span class="text-sm text-gray-600"><?= htmlspecialchars($recipe['prep_time'] ?? '-') ?> mins</span>
                                </div>
                                <div class="flex items-center">
                                    <i data-lucide="gauge" class="h-5 w-5 text-gray-500 mr-1"></i>
                                    <span class="text-sm text-gray-600"><?= htmlspecialchars($recipe['difficulty'] ?? 'Easy') ?></span>
                                </div>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
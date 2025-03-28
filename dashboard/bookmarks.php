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

// Get user info for the sidebar (similar to dashboard.php)
require_once __DIR__ . '/../controllers/user.php';
$userController = getUserController();
$user = $userController->getUserById($userId);
$firstName = $user['first_name'] ?? 'User';
$profilePicture = $user['profile_picture'] ?? '';
$defaultProfilePic = '../assets/default-profile.png';

// Calculate recipes count
$recipesCount = count($bookmarkedRecipes);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MealForge - My Bookmarks</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
<body class="flex flex-col md:flex-row min-h-screen bg-green-50">
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
            <li><a href="../dashboard.php" class="text-white no-underline text-lg font-bold flex items-center py-1"><i class="fa fa-home fa-fw mr-2"></i> Dashboard</a></li>
            <li><a href="../meal-plan.php" class="text-white no-underline text-lg font-bold flex items-center py-1"><i class="fa fa-calendar fa-fw mr-2"></i> Meal Plan</a></li>
            <li><a href="bookmarks.php" class="text-white no-underline text-lg font-bold flex items-center py-1 bg-primary-dark rounded px-2"><i class="fa fa-bookmark fa-fw mr-2"></i> Bookmarks</a></li>
            <li><a href="#" class="text-white no-underline text-lg font-bold flex items-center py-1"><i class="fa fa-users fa-fw mr-2"></i> Social</a></li>
            <li><a href="#" class="text-white no-underline text-lg font-bold flex items-center py-1"><i class="fa fa-shopping-cart fa-fw mr-2"></i> Shop</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-5 overflow-y-auto">
        <div class="flex justify-between items-center mb-8 pb-2 border-b border-gray-200">
            <a href="../profile.php" class="text-gray-600 no-underline flex items-center">
                <i class="fa fa-arrow-left mr-2"></i> Back to Profile
            </a>
            <a href="../logout.php" class="text-primary font-bold no-underline">Log out</a>
        </div>
        
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800">My Bookmarked Recipes</h1>
            <p class="text-gray-600 mt-2">You have <?php echo $recipesCount; ?> bookmarked recipes</p>
        </div>
        
        <!-- Recipe Results -->
        <div id="results" class="w-full">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (empty($bookmarkedRecipes)): ?>
                <div class="bg-white rounded-lg shadow p-6 text-center md:col-span-2 lg:col-span-3">
                    <i data-lucide="bookmark-x" class="h-12 w-12 mx-auto text-gray-400 mb-4"></i>
                    <h3 class="text-lg font-semibold mb-2">No bookmarked recipes</h3>
                    <p class="text-gray-600 mb-4">You haven't bookmarked any recipes yet.</p>
                    <a href="../search.php" class="inline-block py-2 px-4 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition duration-150 ease-in-out">
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

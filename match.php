<?php
// Include the necessary files
require_once __DIR__ . '/controllers/recipe.php';

// Initialize the controller
$recipeController = new \RecipeController(null);

// Fetch a random recipe from the database
$randomRecipe = $recipeController->getRandomRecipeWithImage();

$cleanRecipeName = $randomRecipe["recipe"];
$cleanDescription = $randomRecipe["description"];
$cleanTags = $randomRecipe["tags"];
$imageURL = $randomRecipe['imageURL'] ?? '';
$serves = $randomRecipe["serves"];
$difficulty = $randomRecipe["difficulty"];
$totalTime = $randomRecipe["total_time"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe Suggestions</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-green-50">
<div class="container mx-auto p-4">
    <!-- Navigation Back to Profile -->
    <a href="profile.php" class="flex items-center text-gray-600 mb-6">
        <i data-lucide="arrow-left" class="h-5 w-5 mr-1"></i>
        Back to Profile
    </a>

    <!-- Display Random Recipe -->
    <div class="bg-white rounded-lg shadow p-10 flex flex-col md:flex-row md:h-auto">
        <!-- Recipe Text Information Section (1/3 width on larger screens) -->
        <div class="md:w-1/3 flex flex-col justify-start mr-12">
            <h2 class="text-3xl font-extrabold mb-6 text-center md:text-left">Recipe Suggestions</h2>

            <?php if ($randomRecipe): ?>
                <div class="mb-6">
                    <!-- Recipe Name -->
                    <h3 class="text-xl font-bold mb-4"><?= htmlspecialchars($cleanRecipeName) ?></h3>
                    <!-- Recipe Description -->
                    <p class="text-gray-600 text-sm mb-4"><?= htmlspecialchars($cleanDescription) ?></p>
                    <!-- Recipe Tags -->
                    <div class="flex flex-wrap gap-2 mb-6">
                        <?php foreach ($cleanTags as $tag): ?>
                            <span class="inline-block px-2 py-1 text-xs rounded-xl bg-green-100 text-green-800"><?= htmlspecialchars($tag) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="flex items-center gap-4 mb-6">
                        <!-- Total Time -->
                        <div class="flex items-center">
                            <i data-lucide="clock" class="h-5 w-5 text-gray-500 mr-1"></i>
                            <span class="text-sm text-gray-600"><?= htmlspecialchars($totalTime) ?> mins</span>
                        </div>

                        <!-- Difficulty -->
                        <div class="flex items-center">
                            <i data-lucide="star" class="h-5 w-5 text-gray-500 mr-1"></i>
                            <span class="text-sm text-gray-600"><?= htmlspecialchars($difficulty) ?></span>
                        </div>

                        <!-- Serves -->
                        <div class="flex items-center">
                            <i data-lucide="users" class="h-5 w-5 text-gray-500 mr-1"></i>
                            <span class="text-sm text-gray-600">Serves <?= htmlspecialchars($serves) ?></span>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <p>No recipe found.</p>
            <?php endif; ?>

            <div class="flex justify-center gap-8 mt-6">
                <button onclick="window.location.reload();" class="bg-red-600 hover:bg-red-700 text-white py-8 px-12 rounded-lg text-4xl font-bold w-auto">
                    X
                </button>
                <!-- Green check navigates to link -->
                <a href="recipe.php?id=<?= urlencode($randomRecipe['id']) ?>" class="bg-green-600 hover:bg-green-700 text-white py-8 px-12 rounded-lg text-4xl font-bold w-auto">
                    ✓
                </a>
            </div>
        </div>

        <!-- Recipe Image Section (2/3 width on larger screens) -->
        <div class="md:w-3/5 w-full h-100 rounded-lg mb-6 flex items-center justify-center <?php echo ($imageURL !== '') ? '' : 'border-4 border-dashed border-gray-400'; ?>">
            <?php if ($imageURL !== ''): ?>
                <img src="<?= htmlspecialchars($imageURL) ?>" alt="Recipe Image" class="w-4/5 h-auto object-cover rounded-lg">
            <?php else: ?>
                <span class="text-gray-500">Recipe Image Placeholder</span>
            <?php endif; ?>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
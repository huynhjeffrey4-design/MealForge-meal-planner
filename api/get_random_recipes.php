<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../setup.php';
require_once __DIR__ . '/../SetupRedbean.php';
require_once __DIR__ . '/../controllers/recipe.php';
require_once __DIR__ . '/../providers/RandRecipeDataProvider.php';

$provider = new RandRecipeDataProvider();
$controller = new RecipeController($provider); 

$allRecipes = $controller->getAllRecipes();
shuffle($allRecipes);
$randomRecipes = array_slice($allRecipes, 0, 5);

$recipes = array_map(function($recipe) {
    return [
        'id' => $recipe['id'],
        'meal_name' => $recipe['recipe'],
        'meal_type' => $recipe['meal_type'],
        'imageURL' => $recipe['imageURL'] ?? null
    ];
}, $randomRecipes);

header('Content-Type: application/json');
echo json_encode($recipes);

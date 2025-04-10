<?php
require_once __DIR__ . '/../controllers/recipe.php';
$recipeController = new RecipeController(null);
$result = $recipeController->appendAutoTagsToRecipe(1);

echo json_encode($result);
?>
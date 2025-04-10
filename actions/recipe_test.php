<?php

require_once __DIR__ . '/../controllers/recipe.php';
$recipeController = new RecipeController(null);
$result = $recipeController->appendAutoTagsToRecipe(3);
$result2 = $recipeController->appendAutoTagsToRecipe(1);

echo json_encode([
    'result_3' => $result,
    'result_1' => $result2
]);


/*
<?php
require_once __DIR__ . '/../controllers/recipe.php';

$recipeController = new RecipeController(null);

// 获取所有菜谱
$allRecipes = $recipeController->getAllRecipes();

$results = [];
foreach ($allRecipes as $recipe) {
    $id = $recipe['id'];
    $result = $recipeController->appendAutoTagsToRecipe($id);
    $results[] = $result;
}

// 输出 JSON 结果
header('Content-Type: application/json');
echo json_encode([
    'updated' => count($results),
    'details' => $results
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
*/

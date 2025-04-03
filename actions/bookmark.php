<?php
require_once __DIR__ . '/../controllers/bookmark.php';
require_once __DIR__ . '/../controllers/recipe.php';

session_start();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	if (!isset($_SESSION['user'])) {
		$_SESSION['error'] = 'You must be logged in to bookmark a recipe. <a href="login.php">Log in</a>';
	} else if (!isset($_POST['recipe_id'])) {
		$_SESSION['error'] = "Error: we couldn't find that recipe...";
	} else {
		$recipeId = isset($_POST['recipe_id']) ? intval($_POST['recipe_id']) : null;

		$bookmarkController = new BookmarkController($_SESSION['user']['id']);

		$result = $bookmarkController->toggleBookmark($recipeId);

		$recipeController = new RecipeController(null);
		$recipe = $recipeController->getRecipeById($recipeId);

		$_SESSION['message'] = 'Bookmark toggled for recipe ' . $recipe['recipe'];
	}

	header('Location: /recipe.php?id=' . $recipeId);
	exit;
}

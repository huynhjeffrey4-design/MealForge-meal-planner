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

		//Get the current date parameter (used to update the corresponding meal plan)
        $currentDay = $_POST['day'] ?? 'Monday';

		$bookmarkController = new BookmarkController($_SESSION['user']['id']);

		

		$result = $bookmarkController->toggleBookmark($recipeId);

		$recipeController = new RecipeController(null);
		$recipe = $recipeController->getRecipeById($recipeId);
		//Update the meal plan in the session based on the toggle result
		  if ($result['action'] === 'added') {
            // Remove the recipe from the meal plan for the current date when unliked.
            $_SESSION['meal_plan'][$currentDay][] = $recipe;
        } elseif ($result['action'] === 'removed') {
            // When unliked, remove the recipe from the meal plan of the current date.
            if (isset($_SESSION['meal_plan'][$currentDay])) {
                foreach ($_SESSION['meal_plan'][$currentDay] as $index => $r) {
                    if (isset($r['id']) && $r['id'] == $recipeId) {
                        array_splice($_SESSION['meal_plan'][$currentDay], $index, 1);
                        break;
                    }
                }
            }
        }


		$_SESSION['message'] = 'Bookmark toggled for recipe ' . $recipe['recipe'];
	}

	header('Location: /recipe.php?id=' . $recipeId);
	exit;
}

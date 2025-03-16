<?php
require_once __DIR__ . '/setup.php';
require_once __DIR__ . '/SetupRedbean.php';
require_once __DIR__ . '/controllers/bookmark.php';

session_start();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$bookmarkController = new BookmarkController($_SESSION['user']['id']);

    $recipeId = isset($_POST['recipe_id']) ? intval($_POST['recipe_id']) : null;
    $result = $bookmarkController->toggleBookmark($recipeId);
	$_SESSION['message'] = 'Bookmark toggled for recipe ' . $recipeId;
    
    header('Location: /recipe.php?id=' . $recipeId);
    exit;
}


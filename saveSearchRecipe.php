<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

session_start();
require_once 'SetupRedbean.php';
DatabaseConnection::getInstance()->setup();


$data = json_decode(file_get_contents('php://input'), true);
error_log(" Incoming Data: " . print_r($data, true));


if (!$data || !isset($data['recipe']) || !isset($data['day'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data format']);
    exit;
}


$recipe = $data['recipe'];
$day = $data['day'];
$userId = $_SESSION['user']['id'] ?? null;

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$recipe = $data['recipe'];

$recipe['name'] = $recipe['recipe'] ?? '';
$recipe['prep_time'] = $recipe['prep_time'] ?? $recipe['prepTime'] ?? 0;
$recipe['cook_time'] = $recipe['cook_time'] ?? $recipe['cookTime'] ?? 0;
$recipe['meal_type'] = $recipe['meal_type'] ?? $recipe['mealType'] ?? '';
$recipe['serves']  = $recipe['serves'] ?? $recipe['serves'] ?? '';



$recipe['image']     = $recipe['image'] ?? $recipe['imageURL'] ?? 'assets/default-recipe.jpg';


$bean = R::dispense('mealplan');
$bean->user_id     = $userId;
$bean->day         = $day;
$bean->recipe      = $recipe['name'];
$bean->description = $recipe['description'] ?? '';
$bean->prep_time   = $recipe['prep_time'];

$bean->cook_time   = $recipe['cook_time'];
$bean->difficulty  = $recipe['difficulty'] ?? '';
$bean->serves      = $recipe['serves'];
$bean->image       = $recipe['image'];
$bean->meal_type   = $recipe['meal_type'];
$bean->calories    = $recipe['calories'] ?? 0;
$bean->protein     = $recipe['protein'] ?? 0;

// Clean and join instructions into a single text block
$bean->instructions = is_array($recipe['instructions'])
    ? implode("\n", array_map('trim', $recipe['instructions']))
    : trim($recipe['instructions']);

// Clean and join ingredients into a single comma-separated string
$bean->ingredients = is_array($recipe['ingredients'])
    ? implode(', ', array_map('trim', $recipe['ingredients']))
    : trim($recipe['ingredients']);


$bean->tags = json_encode($recipe['tags'] ?? []);


$id = R::store($bean);
$recipe['id'] = $id;

$recipe['prepTime'] = $bean->prep_time;
$recipe['cookTime'] = $bean->cook_time;
$recipe['mealType'] = $bean->meal_type;
$recipe['serves'] = $bean->serves;
$recipe['recipe'] = $bean->recipe;


echo json_encode(['success' => true, 'recipe' => $recipe]);

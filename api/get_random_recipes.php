<?php
require_once __DIR__ . '/../setup.php';
require_once __DIR__ . '/../SetupRedbean.php';

$db = DatabaseConnection::getInstance();
$db->setup();

$recipes = \R::getAll("
    SELECT id, recipe AS meal_name, meal_type, imageURL 
    FROM recipes 
    WHERE imageURL IS NOT NULL AND imageURL != '' 
    ORDER BY RAND() 
    LIMIT 5
");

header('Content-Type: application/json');
echo json_encode($recipes);
?>
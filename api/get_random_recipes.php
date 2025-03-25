<?php
include 'config.php';

$sql = "SELECT id, recipe AS meal_name, meal_type, imageURL FROM rand_recipes ORDER BY RAND() LIMIT 5";
$result = $conn->query($sql);

$recipes = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recipes[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($recipes);
?>

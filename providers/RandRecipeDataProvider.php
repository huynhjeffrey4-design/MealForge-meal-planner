<?php

require_once __DIR__ . '/../SetupRedbean.php';
require_once __DIR__ . '/../controllers/recipe.php';

class RandRecipeDataProvider implements RecipeDataProvider {
    public function __construct(array $config = []) {
        $db = DatabaseConnection::getInstance();
        $db->setup($config);
    }

    public function getAllRecipes(): array {
        $recipes = \R::findAll('rand_recipes');
        return \R::exportAll($recipes);
    }

    public function getRecipeById($id): ?array {
        $recipe = \R::findOne('rand_recipes', 'id = ?', [$id]);
        if ($recipe) {
            return \R::exportAll([$recipe])[0];
        }
        return null;
    }
}

<?php

require_once __DIR__ . '/DatabaseConnection.php';

/**
 * Recipe Controller with parameter-based filtering
 */
class RecipeController2 {
    private $recipeProvider;

    public function __construct() {
        // TODO: Implement "real" provider.
        $this->recipeProvider = new TrueRecipeDataProvider();
    }

    /**
     * Search for recipes with specified filters
     *
     * @param string|null $search Search term for recipe title/description
     * @param string|null $dietary String of dietary preference
     * @param int|null $maxPrepTime Maximum preparation time in minutes
     * @param string|null $mealType Type of meal (e.g., Breakfast, Lunch)
     * @param string|null $priceRange Price range category (budget, moderate, premium)
     * @return array Filtered recipes
     */
    public function searchAction(
        ?string $search = null,
        ?string $dietary = null,
        ?int $maxPrepTime = null,
        ?string $mealType = null,
        ?string $priceRange = null
    ): array {
        // Get all recipes first
        $recipes = $this->recipeProvider->getAllRecipes();
        $filteredRecipes = $recipes;

        // Filter by dietary preferences
        if (!empty($dietary)) {
            $filteredRecipes = array_filter($filteredRecipes, function($recipe) use ($dietary) {
                // Normalize the dietary preference by removing hyphens for "gluten-free" or "dairy-free"
                $dietaryNormalized = strtolower(str_replace('-', ' ', $dietary));

                // Check if the subcategory matches the dietary preference
                $subcategoryMatch = $recipe['subcategory'] == $dietary;

                // Check if dietary value exists in recipe['recipe'], recipe['dish_type'], or recipe['description']
                $containsDietaryInRecipe = str_contains(strtolower($recipe['recipe']), $dietaryNormalized);
                $containsDietaryInDishType = str_contains(strtolower($recipe['dish_type']), $dietaryNormalized);
                $containsDietaryInDescription = str_contains(strtolower($recipe['description']), $dietaryNormalized);

                // Also include the variations of dietary (gluten-free -> gluten free, dairy-free -> dairy free)
                // Check for the non-hyphenated form by replacing hyphens with spaces
                $containsDietaryInRecipeWithoutHyphen = str_contains(strtolower(str_replace('-', ' ', $recipe['recipe'])), $dietaryNormalized);
                $containsDietaryInDishTypeWithoutHyphen = str_contains(strtolower(str_replace('-', ' ', $recipe['dish_type'])), $dietaryNormalized);
                $containsDietaryInDescriptionWithoutHyphen = str_contains(strtolower(str_replace('-', ' ', $recipe['description'])), $dietaryNormalized);

                // Return true if any of the conditions is met
                return $subcategoryMatch ||
                    $containsDietaryInRecipe ||
                    $containsDietaryInDishType ||
                    $containsDietaryInDescription ||
                    $containsDietaryInRecipeWithoutHyphen ||
                    $containsDietaryInDishTypeWithoutHyphen ||
                    $containsDietaryInDescriptionWithoutHyphen;
            });
        }



        // Filter by max preparation time
        if (!empty($maxPrepTime)) {
            $filteredRecipes = array_filter($filteredRecipes, function($recipe) use ($maxPrepTime) {
                return $recipe['total_time'] <= $maxPrepTime;
            });
        }

        // Filter by meal type
        if (!empty($mealType)) {
            $filteredRecipes = array_filter($filteredRecipes, function($recipe) use ($mealType) {
                return $recipe['meal_type'] == $mealType;
            });
        }

        // Search by title or keywords
        if (!empty($search)) {
            $search = strtolower($search);
            $filteredRecipes = array_filter($filteredRecipes, function($recipe) use ($search) {
                return strpos(strtolower($recipe['recipe']), $search) !== false ||
                    strpos(strtolower($recipe['description']), $search) !== false;
            });
        }

        // Limit number of filtered recipes
        $filteredRecipes = array_slice($filteredRecipes,0,30);

        return array_values($filteredRecipes); // Reset array indices
    }
}

/**
 * Interface for recipe data providers
 */
interface RecipeDataProvider
{
    /**
     * Get all available recipes
     *
     * NOTE: Assumed format:
    [[
    'id' => int,                    // Unique identifier for the recipe
    'title' => string,              // Title/name of the recipe
    'description' => string,        // Description of the recipe
    'tags' => array,                // Array of tags (dietary preferences, meal types, cuisines)
    'prep_time' => int,             // Preparation time in minutes
    'price' => int|float,           // Price in numeric format
    'difficulty' => string,         // Difficulty level (Easy, Medium, Hard)
    'image' => string|null          // Image filename or null if no image
    ]]
     *
     *
     * @return array of recipe data
     */
    public function getAllRecipes(): array;
}

class TrueRecipeDataProvider implements RecipeDataProvider {
    private $dbConnection;

    public function __construct() {
        // Establish the database connection using the DatabaseConnection class
        $this->dbConnection = (new DatabaseConnection())->getConnection();
    }

    // Implement the getAllRecipes() function to fetch all recipes from the database
    public function getAllRecipes(): array {
        try {
            // Prepare the SQL query to select all recipes from the 'recipes' table
            $query = "SELECT * FROM recipes WHERE 1=1";
            $stmt = $this->dbConnection->prepare($query);

            // Execute the query
            $stmt->execute();

            // Fetch all the results as an associative array
            $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $recipes;
        } catch (PDOException $e) {
            // If there's an error, return an empty array or handle the error as needed
            echo "Error: " . $e->getMessage();
            return [];
        }
    }
}

<?php

namespace App\Controllers;

/**
 * Recipe Controller with parameter-based filtering
 */
class RecipeController {
    private $recipeProvider;
    
    public function __construct() {
		// TODO: Implement "real" provider.
        $this->recipeProvider = new MockRecipeDataProvider();
    }
    
    /**
     * Get all recipes without filtering
     * 
     * @return array All available recipes
     */
    public function getAllRecipes(): array {
        return $this->recipeProvider->getAllRecipes();
    }
    
    /**
     * Search for recipes with specified filters
     * 
     * @param string|null $search Search term for recipe title/description
     * @param array|null $dietary Array of dietary preferences
     * @param int|null $maxPrepTime Maximum preparation time in minutes
     * @param string|null $mealType Type of meal (e.g., Breakfast, Lunch)
     * @param string|null $priceRange Price range category (budget, moderate, premium)
     * @return array Filtered recipes
     */
    public function searchAction(
        ?string $search = null,
        ?array $dietary = null,
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
                foreach ($dietary as $diet) {
                    if (in_array($diet, $recipe['tags'])) {
                        return true;
                    }
                }
                return false;
            });
        }
        
        // Filter by max preparation time
        if (!empty($maxPrepTime)) {
            $filteredRecipes = array_filter($filteredRecipes, function($recipe) use ($maxPrepTime) {
                return $recipe['prep_time'] <= $maxPrepTime;
            });
        }
        
        // Filter by meal type
        if (!empty($mealType)) {
            $filteredRecipes = array_filter($filteredRecipes, function($recipe) use ($mealType) {
                return in_array($mealType, $recipe['tags']);
            });
        }
        
        // Filter by price range
        if (!empty($priceRange)) {
            $filteredRecipes = array_filter($filteredRecipes, function($recipe) use ($priceRange) {
                switch($priceRange) {
                    case 'budget':
                        return $recipe['price'] < 5;
                    case 'moderate':
                        return $recipe['price'] >= 5 && $recipe['price'] <= 10;
                    case 'premium':
                        return $recipe['price'] > 10;
                    default:
                        return true;
                }
            });
        }
        
        // Search by title or keywords
        if (!empty($search)) {
            $search = strtolower($search);
            $filteredRecipes = array_filter($filteredRecipes, function($recipe) use ($search) {
                return strpos(strtolower($recipe['title']), $search) !== false || 
                       strpos(strtolower($recipe['description']), $search) !== false;
            });
        }
        
        return array_values($filteredRecipes); // Reset array indices
    }
    
    /**
     * Helper method to extract search parameters from $_GET
     * Use this when connecting with the search page form
     * 
     * @return array Array of recipes matching the GET request filters
     */
    public function handleSearchRequest(): array {
        $search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : null;
        
        $dietary = null;
        if (isset($_GET['dietary']) && is_array($_GET['dietary'])) {
            $dietary = array_map('htmlspecialchars', $_GET['dietary']);
        }
        
        $maxPrepTime = isset($_GET['max_prep_time']) ? (int)$_GET['max_prep_time'] : null;
        $mealType = isset($_GET['meal_type']) ? htmlspecialchars($_GET['meal_type']) : null;
        $priceRange = isset($_GET['price_range']) ? htmlspecialchars($_GET['price_range']) : null;
        
        return $this->searchAction($search, $dietary, $maxPrepTime, $mealType, $priceRange);
    }
    
    /**
     * Get a recipe by ID
     * 
     * @param int $recipeId ID of the recipe to retrieve
     * @return array|null Recipe data or null if not found
     */
    public function getRecipeById($recipeId): array|null {
        $recipes = $this->recipeProvider->getAllRecipes();
        
        foreach ($recipes as $recipe) {
            if ($recipe['id'] == $recipeId) {
                return $recipe;
            }
        }
        
        return null;
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

/**
 * Mock implementation of recipe data provider
 */
class MockRecipeDataProvider implements RecipeDataProvider {
    private $recipes = [];
    
    public function __construct() {
        $this->initializeRecipes();
    }
    
    public function getAllRecipes(): array {
        return $this->recipes;
    }
    
    /**
     * Initialize mock recipe data
     */
    private function initializeRecipes(): void {
        $this->recipes = [
            [
                'id' => 1,
                'title' => 'Mediterranean Quinoa Bowl',
                'description' => 'A protein-packed bowl with quinoa, chickpeas, cucumber, tomatoes, and feta cheese.',
                'tags' => ['Vegetarian', 'High Protein', 'Mediterranean', 'Lunch', 'Dinner'],
                'prep_time' => 35,
                'price' => 7,
                'difficulty' => 'Medium',
                'image' => null
            ],
            [
                'id' => 2,
                'title' => 'Avocado Toast with Poached Eggs',
                'description' => 'Whole grain toast topped with smashed avocado, poached eggs, and red pepper flakes.',
                'tags' => ['Vegetarian', 'High Protein', 'Breakfast', 'Brunch'],
                'prep_time' => 20,
                'price' => 6,
                'difficulty' => 'Easy',
                'image' => null
            ],
            [
                'id' => 3,
                'title' => 'Black Bean and Sweet Potato Tacos',
                'description' => 'Corn tortillas filled with spiced black beans, roasted sweet potatoes, and avocado cream.',
                'tags' => ['Vegan', 'Gluten-Free', 'Mexican', 'Dinner'],
                'prep_time' => 45,
                'price' => 8,
                'difficulty' => 'Medium',
                'image' => null
            ],
            [
                'id' => 4,
                'title' => 'Salmon with Lemon-Dill Sauce',
                'description' => 'Pan-seared salmon fillets with a creamy lemon-dill sauce and steamed asparagus.',
                'tags' => ['High Protein', 'Gluten-Free', 'Keto', 'Dinner'],
                'prep_time' => 30,
                'price' => 12,
                'difficulty' => 'Medium',
                'image' => null
            ],
            [
                'id' => 5,
                'title' => 'Chickpea Curry with Basmati Rice',
                'description' => 'Creamy chickpea curry with aromatic spices, served over fluffy basmati rice.',
                'tags' => ['Vegan', 'Indian', 'Dinner', 'Spicy'],
                'prep_time' => 40,
                'price' => 6,
                'difficulty' => 'Medium',
                'image' => null
            ],
            [
                'id' => 6,
                'title' => 'Greek Yogurt Parfait',
                'description' => 'Layers of Greek yogurt, mixed berries, honey, and homemade granola.',
                'tags' => ['Vegetarian', 'High Protein', 'Breakfast', 'Snack'],
                'prep_time' => 15,
                'price' => 4,
                'difficulty' => 'Easy',
                'image' => null
            ],
            [
                'id' => 7,
                'title' => 'Lentil Soup with Crusty Bread',
                'description' => 'Hearty lentil soup with carrots, celery, and aromatic herbs, served with crusty bread.',
                'tags' => ['Vegan', 'Mediterranean', 'Lunch', 'Dinner'],
                'prep_time' => 50,
                'price' => 5,
                'difficulty' => 'Easy',
                'image' => null
            ],
            [
                'id' => 8,
                'title' => 'Grilled Chicken Caesar Salad',
                'description' => 'Romaine lettuce with grilled chicken breast, parmesan cheese, and homemade Caesar dressing.',
                'tags' => ['High Protein', 'Lunch', 'Dinner'],
                'prep_time' => 25,
                'price' => 9,
                'difficulty' => 'Easy',
                'image' => null
            ],
            [
                'id' => 9,
                'title' => 'Spinach and Mushroom Frittata',
                'description' => 'Fluffy egg frittata with sautéed spinach, mushrooms, and goat cheese.',
                'tags' => ['Vegetarian', 'High Protein', 'Gluten-Free', 'Breakfast', 'Brunch', 'Keto'],
                'prep_time' => 30,
                'price' => 6,
                'difficulty' => 'Medium',
                'image' => null
            ],
            [
                'id' => 10,
                'title' => 'Thai Peanut Noodle Stir-Fry',
                'description' => 'Rice noodles with colorful vegetables in a spicy peanut sauce.',
                'tags' => ['Vegan', 'Thai', 'Dinner', 'Spicy'],
                'prep_time' => 35,
                'price' => 8,
                'difficulty' => 'Medium',
                'image' => null
            ],
            [
                'id' => 11,
                'title' => 'Beef and Broccoli Stir-Fry',
                'description' => 'Tender beef strips and crisp broccoli in a savory sauce served over rice.',
                'tags' => ['High Protein', 'Chinese', 'Dinner'],
                'prep_time' => 30,
                'price' => 10,
                'difficulty' => 'Medium',
                'image' => null
            ],
            [
                'id' => 12,
                'title' => 'Overnight Chia Pudding',
                'description' => 'Chia seeds soaked in almond milk, topped with fresh fruit and a drizzle of maple syrup.',
                'tags' => ['Vegan', 'Gluten-Free', 'Breakfast', 'Snack'],
                'prep_time' => 10,
                'price' => 3,
                'difficulty' => 'Easy',
                'image' => null
            ],
            [
                'id' => 13,
                'title' => 'Cauliflower Pizza with Fresh Vegetables',
                'description' => 'Low-carb pizza with a cauliflower crust, topped with fresh vegetables and mozzarella.',
                'tags' => ['Vegetarian', 'Gluten-Free', 'Keto', 'Dinner'],
                'prep_time' => 60,
                'price' => 9,
                'difficulty' => 'Hard',
                'image' => null
            ],
            [
                'id' => 14,
                'title' => 'Korean Bibimbap',
                'description' => 'Rice bowl topped with seasoned vegetables, beef, a fried egg, and gochujang sauce.',
                'tags' => ['Korean', 'High Protein', 'Dinner'],
                'prep_time' => 55,
                'price' => 11,
                'difficulty' => 'Hard',
                'image' => null
            ],
            [
                'id' => 15,
                'title' => 'Mango Berry Smoothie Bowl',
                'description' => 'Frozen mango and mixed berries blended and topped with granola and fresh fruit.',
                'tags' => ['Vegan', 'Gluten-Free', 'Breakfast', 'Snack'],
                'prep_time' => 15,
                'price' => 5,
                'difficulty' => 'Easy',
                'image' => null
            ]
        ];
    }
}

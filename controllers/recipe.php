<?php

//
require_once __DIR__ . '/../setup.php';
require_once __DIR__ . '/../SetupRedbean.php';

/**
 * Security utility function to prevent XSS attacks
 *
 * @param mixed $data Data to be sanitized
 * @return mixed Sanitized data
 */
function sanitizeOutput($data)
{
    if (is_string($data)) {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    } elseif (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sanitizeOutput($value);
        }
    }
    return $data;
}

/**
 * Recipe Controller with parameter-based filtering
 */
class RecipeController
{
    private $recipeProvider;

    public function __construct(?RecipeDataProvider $recipeProvider)
    {
        if ($recipeProvider !== null) {
            $this->recipeProvider = $recipeProvider;
        } else {
            $env = env('PROVIDER_RECIPE', '');
            $this->recipeProvider = $env == 'mock' ? new MockRecipeDataProvider() : new RedbeanRecipeDataProvider();
        }
    }

    /**
     * Get similar recipes based on embedding vector similarity
     *
     * @param int $recipeId ID of the recipe to find similar recipes for
     * @param int $limit Maximum number of similar recipes to return
     * @return array List of similar recipes sorted by similarity score
     */
    public function getSimilarRecipesByEmbedding($recipeId, $limit = 5): array
    {
        $recipeId = (int)$recipeId;
        $limit = (int)$limit;

        $similarRecipes = $this->recipeProvider->getSimilarRecipesByEmbedding($recipeId, $limit);

        foreach ($similarRecipes as $key => $recipe) {
            if (isset($recipe['tags'])) {
                if (is_string($recipe['tags'])) {
                    $tags = json_decode($recipe['tags'], true);

                    if (json_last_error() === JSON_ERROR_NONE && is_array($tags)) {
                        $indexedTags = array_values($tags);

                        $associativeTags = array();
                        foreach ($indexedTags as $tag) {
                            $associativeTags[$tag] = true;
                        }
                        $similarRecipes[$key]['tags'] = $associativeTags;
                    } else {
                        $similarRecipes[$key]['tags'] = array();
                    }
                } elseif (is_array($recipe['tags'])) {
                    if (isset($recipe['tags'][0])) {
                        $associativeTags = array();
                        foreach ($recipe['tags'] as $tag) {
                            $associativeTags[$tag] = true;
                        }
                        $similarRecipes[$key]['tags'] = $associativeTags;
                    }
                } else {
                    $similarRecipes[$key]['tags'] = array();
                }
            } else {
                $similarRecipes[$key]['tags'] = array();
            }

            if (isset($recipe['recipe'])) {
                $similarRecipes[$key]['recipe'] = sanitizeOutput($recipe['recipe']);
            }
            if (isset($recipe['description'])) {
                $similarRecipes[$key]['description'] = sanitizeOutput($recipe['description']);
            }
            if (isset($recipe['dish_type'])) {
                $similarRecipes[$key]['dish_type'] = sanitizeOutput($recipe['dish_type']);
            }
            if (isset($recipe['ingredients'])) {
                $similarRecipes[$key]['ingredients'] = sanitizeOutput($recipe['ingredients']);
            }
            if (isset($recipe['instructions'])) {
                $similarRecipes[$key]['instructions'] = sanitizeOutput($recipe['instructions']);
            }
            if (isset($recipe['difficulty'])) {
                $similarRecipes[$key]['difficulty'] = sanitizeOutput($recipe['difficulty']);
            }
            if (isset($recipe['subcategory'])) {
                $similarRecipes[$key]['subcategory'] = sanitizeOutput($recipe['subcategory']);
            }
            if (isset($recipe['meal_type'])) {
                $similarRecipes[$key]['meal_type'] = sanitizeOutput($recipe['meal_type']);
            }
        }

        return $similarRecipes;
    }

    public function getCommentsForRecipe($recipeId): array
    {
        return $this->recipeProvider->getCommentsForRecipe($recipeId);
    }

    // Add a comment to a post
    public function addComment($recipeId, $userId, $commentBody): void
    {
        $this->recipeProvider->addComment($recipeId, $userId, $commentBody);
    }

    // Toggle like status on a post
    public function toggleLike($recipeId, $userId): void
    {
        $this->recipeProvider->toggleLike($recipeId, $userId);
    }

    public function getLikeCount($recipeId): int
    {
        return $this->recipeProvider->getLikeCount($recipeId);
    }

    public function isLikedByUser($recipeId, $userId): bool
    {
        return $this->recipeProvider->isLikedByUser($recipeId, $userId);
    }

    public function getLikedRecipes($userId): array
    {
        return $this->recipeProvider->getLikedRecipes($userId);
    }

    public function editComment($commentId, $newCommentBody, $userId)
    {
        return $this->recipeProvider->editComment($commentId, $newCommentBody, $userId);
    }

    public function deleteComment($commentId, $userId)
    {
        return $this->recipeProvider->deleteComment($commentId, $userId);
    }

    /**
     * Get all recipes without filtering
     *
     * @return array All available recipes
     */
    public function getAllRecipes(): array
    {
        $recipes = $this->recipeProvider->getAllRecipes();

        // Process and sanitize recipes
        foreach ($recipes as $key => $recipe) {
            // Decode JSON tags
            if (isset($recipe['tags'])) {
                if (is_string($recipe['tags'])) {
                    // Try standard JSON decode first
                    $tags = json_decode($recipe['tags'], true);

                    if (json_last_error() === JSON_ERROR_NONE && is_array($tags)) {
                        // If successfully decoded to array, use it
                        $indexedTags = array_values($tags);

                        // Convert to associative array where keys are tag names (needed for the tests)
                        $associativeTags = array();
                        foreach ($indexedTags as $tag) {
                            $associativeTags[$tag] = true;
                        }
                        $recipes[$key]['tags'] = $associativeTags;
                    } else {
                        // If JSON decode fails, try to parse the string format: "['tag1', 'tag2', 'tag3']"
                        $tagString = trim($recipe['tags'], "[]");
                        $tagString = str_replace("'", "", $tagString);
                        $tagArray = explode(", ", $tagString);

                        // Convert to associative array
                        $associativeTags = array();
                        foreach ($tagArray as $tag) {
                            $associativeTags[$tag] = true;
                        }
                        $recipes[$key]['tags'] = $associativeTags;
                    }
                } elseif (is_array($recipe['tags'])) {
                    // If already an array, ensure it's in the correct format
                    if (isset($recipe['tags'][0])) {
                        // If indexed array, convert to associative
                        $associativeTags = array();
                        foreach ($recipe['tags'] as $tag) {
                            $associativeTags[$tag] = true;
                        }
                        $recipes[$key]['tags'] = $associativeTags;
                    }
                } else {
                    // Default to empty array if not string or array
                    $recipes[$key]['tags'] = array();
                }
            } else {
                // Default to empty array if not set
                $recipes[$key]['tags'] = array();
            }

            // Sanitize string fields to prevent XSS
            if (isset($recipe['recipe'])) {
                $recipes[$key]['recipe'] = sanitizeOutput($recipe['recipe']);
            }
            if (isset($recipe['description'])) {
                $recipes[$key]['description'] = sanitizeOutput($recipe['description']);
            }
            if (isset($recipe['dish_type'])) {
                $recipes[$key]['dish_type'] = sanitizeOutput($recipe['dish_type']);
            }
            if (isset($recipe['ingredients'])) {
                $recipes[$key]['ingredients'] = sanitizeOutput($recipe['ingredients']);
            }
            if (isset($recipe['instructions'])) {
                $recipes[$key]['instructions'] = sanitizeOutput($recipe['instructions']);
            }
            if (isset($recipe['difficulty'])) {
                $recipes[$key]['difficulty'] = sanitizeOutput($recipe['difficulty']);
            }
            if (isset($recipe['subcategory'])) {
                $recipes[$key]['subcategory'] = sanitizeOutput($recipe['subcategory']);
            }
            if (isset($recipe['meal_type'])) {
                $recipes[$key]['meal_type'] = sanitizeOutput($recipe['meal_type']);
            }
        }

        return $recipes;
    }

    /**
     * Get a random recipe containing an imageURL from the recipes table
     *
     * @return array A single random recipe
     */
    public function getRandomRecipeWithImage(): array
    {
        $randomRecipe = $this->recipeProvider->getRandomRecipeWithImage();

        if (empty($randomRecipe)) {
            return [];
        }

        // Decode JSON tags
        $randomRecipe['tags'] = json_decode($randomRecipe['tags']);

        // Sanitize string fields to prevent XSS
        if (isset($randomRecipe['recipe'])) {
            $randomRecipe['recipe'] = sanitizeOutput($randomRecipe['recipe']);
        }
        if (isset($randomRecipe['description'])) {
            $randomRecipe['description'] = sanitizeOutput($randomRecipe['description']);
        }
        if (isset($randomRecipe['dish_type'])) {
            $randomRecipe['dish_type'] = sanitizeOutput($randomRecipe['dish_type']);
        }
        if (isset($randomRecipe['ingredients'])) {
            $randomRecipe['ingredients'] = sanitizeOutput($randomRecipe['ingredients']);
        }
        if (isset($randomRecipe['instructions'])) {
            $randomRecipe['instructions'] = sanitizeOutput($randomRecipe['instructions']);
        }
        if (isset($randomRecipe['difficulty'])) {
            $randomRecipe['difficulty'] = sanitizeOutput($randomRecipe['difficulty']);
        }
        if (isset($randomRecipe['subcategory'])) {
            $randomRecipe['subcategory'] = sanitizeOutput($randomRecipe['subcategory']);
        }
        if (isset($randomRecipe['meal_type'])) {
            $randomRecipe['meal_type'] = sanitizeOutput($randomRecipe['meal_type']);
        }

        return $randomRecipe;
    }

    /**
     * Get 5 random recipes with imageURL from the recipes table
     *
     * @return array List of formatted recipes
     */
    public function getFiveRandomRecipes(): array
    {
        $db = DatabaseConnection::getInstance();
        $db->setup();

        $recipes = \R::getAll("
            SELECT id, recipe AS meal_name, meal_type, imageURL 
            FROM recipes 
            WHERE imageURL IS NOT NULL AND imageURL != '' 
            ORDER BY RAND() 
            LIMIT 5
        ");
        return $recipes;
    }

    /**
     * Search for recipes with specified filters
     *
     * @param string|null $search Search term for recipe title/description
     * @param array|null $dietary Array of dietary preferences
     * @param int|null $maxPrepTime Maximum preparation time in minutes
     * @param string|null $mealType Type of meal (e.g., Breakfast, Lunch)
     * @param string|null $priceRange Price range category (budget, moderate, premium)
     * @param int|null $page Current page number for pagination
     * @param int|null $perPage Number of items per page
     * @return array Filtered recipes
     */
    public function searchAction(
        ?string $search = null,
        ?string $dietary = null,
        ?int $maxPrepTime = null,
        ?string $mealType = null,
        ?string $priceRange = null,
        ?int $page = 1,
        ?int $perPage = 15
    ): array {
        // Sanitize input parameters to prevent XSS
        if ($search !== null) {
            $search = sanitizeOutput($search);
        }
        if ($dietary !== null) {
            $dietary = sanitizeOutput($dietary);
        }
        if ($mealType !== null) {
            $mealType = sanitizeOutput($mealType);
        }
        if ($priceRange !== null) {
            $priceRange = sanitizeOutput($priceRange);
        }

        // Force integer type for numeric parameters to prevent SQL injection
        if ($maxPrepTime !== null) {
            $maxPrepTime = (int)$maxPrepTime;
        }
        if ($page !== null) {
            $page = (int)$page;
        }
        if ($perPage !== null) {
            $perPage = (int)$perPage;
        }

        // Get all recipes first (already sanitized in getAllRecipes)
        $recipes = $this->getAllRecipes();
        $filteredRecipes = $recipes;

        // Filter by dietary preferences
        if (!empty($dietary)) {
            $filteredRecipes = array_filter($filteredRecipes, function ($recipe) use ($dietary) {
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

        // Filter by meal type
        if (!empty($mealType)) {
            $filteredRecipes = array_filter($filteredRecipes, function ($recipe) use ($mealType) {
                return $recipe['meal_type'] == $mealType;
            });
        }

        // Filter by max preparation time
        if (!empty($maxPrepTime)) {
            $filteredRecipes = array_filter($filteredRecipes, function ($recipe) use ($maxPrepTime) {
                return $recipe['total_time'] <= $maxPrepTime;
            });
        }

        // Search by recipe name or description
        if (!empty($search)) {
            $search = strtolower($search);
            $filteredRecipes = array_filter($filteredRecipes, function ($recipe) use ($search) {
                $recipeName = strtolower($recipe['recipe']);
                $description = strtolower($recipe['description']);
                $ingredients = strtolower($recipe['ingredients']);

                return strpos($recipeName, $search) !== false ||
                    strpos($description, $search) !== false ||
                    strpos($ingredients, $search) !== false;
            });
        }

        // Reset array indices
        return array_values($filteredRecipes);
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
    public function searchActionRedbean($search = null, $dietary = null, $maxPrepTime = 60, $mealType = null, $minBudget = 0, $maxBudget = 75, $page = 1, $perPage = 10)
    {
        // Start building the query
        $query = 'WHERE 1=1';
        $params = [];

        // Add search filter if provided
        if ($search) {
            $query .= ' AND (recipe LIKE ? OR description LIKE ? OR dish_type LIKE ?)';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        // Add dietary filter if provided
        if ($dietary) {
            $query .= ' AND (subcategory LIKE ? OR LOWER(recipe) LIKE ? OR LOWER(dish_type) LIKE ? OR LOWER(description) LIKE ?)';
            $params[] = '%' . $dietary . '%';  // Check subcategory
            $params[] = '%' . $dietary . '%';  // Check recipe
            $params[] = '%' . $dietary . '%';  // Check dish_type
            $params[] = '%' . $dietary . '%';  // Check description
        }

        // Add max preparation time filter if provided
        if ($maxPrepTime) {
            $query .= ' AND prep_time <= ?';
            $params[] = $maxPrepTime;
        }

        // Add meal type filter if provided
        if ($mealType) {
            $query .= ' AND meal_type LIKE ?';
            $params[] = '%' . $mealType . '%';
        }

        // Add min price range filter if provided
        if ($minBudget) {
            $query .= ' AND budget >= ?';
            $params[] = $minBudget;
        }

        // Add max price range filter if provided
        if ($maxBudget) {
            $query .= ' AND budget <= ?';
            $params[] = $maxBudget;
        }

        // Retrieve all filtered recipes (without pagination)
        $recipes = \R::getAll('SELECT `id`, `meal_type`, `subcategory`, `recipe`, `description`, `dish_type`, `prep_time`, `cook_time`, `difficulty`, `ingredients`, `instructions`, `serves`, `total_time`, `tags`, `budget`, `imageURL`, `calories`, `protein` FROM recipes ' . $query, $params);

        // Remove duplicates from the entire list
        $recipes = $this->removeDuplicateRecipes($recipes);

        // Calculate pagination details
        $totalRecipes = count($recipes);  // Total unique recipes
        $totalPages = ceil($totalRecipes / $perPage);

        // Apply pagination to the unique list (get a slice of the list)
        $offset = ($page - 1) * $perPage;
        $recipes = array_slice($recipes, $offset, $perPage);

        // Loop through each recipe and clean the tags (optional)
        foreach ($recipes as &$recipe) {
            if (isset($recipe['tags'])) {
                $recipe['tags'] = is_string($recipe['tags']) ? json_decode($recipe['tags'], true) : $recipe['tags'];
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $recipe['tags'] = [];
                }
            }
        }

        return [
            'recipes' => $recipes,
            'totalPages' => $totalPages,
            'currentPage' => $page,
        ];
    }


    private function removeDuplicateRecipes($recipes)
    {
        // Use an array to store unique recipes based on a field (e.g., 'recipe_id')
        $uniqueRecipes = [];
        $seenRecipes = [];

        foreach ($recipes as $recipe) {
            // Assuming 'recipe_id' is the field you want to use to remove duplicates
            if (!in_array($recipe['recipe'], $seenRecipes)) {
                $uniqueRecipes[] = $recipe;
                $seenRecipes[] = $recipe['recipe'];
            }
        }

        return $uniqueRecipes;
    }

    /**
     * Search for recipes with pagination support
     * This is a separate method to support the application's pagination needs
     * while keeping compatibility with tests
     */
    public function searchActionPaginated(
        ?string $search = null,
        ?string $dietary = null,
        ?int $maxPrepTime = null,
        ?string $mealType = null,
        ?string $priceRange = null,
        ?int $page = 1,
        ?int $perPage = 15
    ): array {
        $filteredRecipes = $this->searchAction($search, $dietary, $maxPrepTime, $mealType, $priceRange);

        // Pagination logic
        $totalRecipes = count($filteredRecipes);
        $totalPages = ceil($totalRecipes / $perPage);
        $offset = ($page - 1) * $perPage;
        $paginatedRecipes = array_slice($filteredRecipes, $offset, $perPage);

        return [
            'recipes' => $paginatedRecipes,
            'totalPages' => $totalPages,
            'currentPage' => $page
        ];
    }

    /**
     * Get a recipe by ID
     *
     * @param int $recipeId ID of the recipe to retrieve
     * @return array|null Recipe data or null if not found
     */
    public function getRecipeById($recipeId): array|null
    {
        // Cast to integer to prevent SQL injection
        $recipeId = (int)$recipeId;

        $recipe = $this->recipeProvider->getRecipeById($recipeId);

        if ($recipe === null) {
            return null;
        }

        // Decode JSON tags
        $recipe['tags'] = json_decode($recipe['tags']);

        // Sanitize string fields to prevent XSS
        if (isset($recipe['recipe'])) {
            $recipe['recipe'] = sanitizeOutput($recipe['recipe']);
        }
        if (isset($recipe['description'])) {
            $recipe['description'] = sanitizeOutput($recipe['description']);
        }
        if (isset($recipe['dish_type'])) {
            $recipe['dish_type'] = sanitizeOutput($recipe['dish_type']);
        }
        if (isset($recipe['ingredients'])) {
            $recipe['ingredients'] = sanitizeOutput($recipe['ingredients']);
        }
        if (isset($recipe['instructions'])) {
            $recipe['instructions'] = sanitizeOutput($recipe['instructions']);
        }
        if (isset($recipe['difficulty'])) {
            $recipe['difficulty'] = sanitizeOutput($recipe['difficulty']);
        }
        if (isset($recipe['subcategory'])) {
            $recipe['subcategory'] = sanitizeOutput($recipe['subcategory']);
        }
        if (isset($recipe['meal_type'])) {
            $recipe['meal_type'] = sanitizeOutput($recipe['meal_type']);
        }

        return $recipe;
    }

    public function appendAutoTagsToRecipe($recipeId)
    {
        // 用 Provider 获取原始数据（array）
        $recipe = $this->recipeProvider->getRecipeById($recipeId);
    
        if ($recipe === null) {
            return ['success' => false, 'message' => "Recipe ID $recipeId not found"];
        }
    
        // 原有标签处理（JSON 字符串或数组）
        $existingTags = [];
        if (!empty($recipe['tags'])) {
            if (is_string($recipe['tags'])) {
                $decoded = json_decode($recipe['tags'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $existingTags = $decoded;
                }
            } elseif (is_array($recipe['tags'])) {
                $existingTags = $recipe['tags'];
            }
        }
    
        // 营养标签生成
        $newTags = $this->generateNutrientTags($recipe);
        $mergedTags = array_values(array_unique(array_merge($existingTags, $newTags)));
    
        // RedBean 保存标签（获取 bean 对象）
        $bean = \R::load('recipes', $recipeId);
        $bean->tags = json_encode($mergedTags, JSON_UNESCAPED_UNICODE);
        \R::store($bean);
    
        return [
            'success' => true,
            'message' => "Updated recipe ID $recipeId",
            'tags' => $mergedTags
        ];
    }    
    
    // 工具函数：生成标签
    private function generateNutrientTags($r) {
        $tags = [];
    
        $sugar = $this->parseNutrient($r['nutrients_sugars'] ?? '');
        $fat = $this->parseNutrient($r['nutrients_fat'] ?? '');
        $saturates = $this->parseNutrient($r['nutrients_saturates'] ?? '');
        $salt = $this->parseNutrient($r['nutrients_salt'] ?? '');
        $protein = $this->parseNutrient($r['nutrients_protein'] ?? '');
        $fibre = $this->parseNutrient($r['nutrients_fibre'] ?? '');
        $kcal = $this->parseNutrient($r['nutrients_kcal'] ?? '');
    
        if ($sugar > 22) $tags[] = "High Sugar";
        elseif ($sugar > 0 && $sugar < 8) $tags[] = "Low Sugar";
    
        if ($fat > 17) $tags[] = "High Fat";
        if ($saturates > 5) $tags[] = "High Saturates";
        if ($salt > 1.5) $tags[] = "High Salt";
    
        if ($protein > 10) $tags[] = "High Protein";
        if ($fibre > 5) $tags[] = "High Fiber";
        if ($kcal > 700) $tags[] = "Treat Only";
    
        return $tags;
    }
    
    // 工具函数：字符串去单位转数字
    private function parseNutrient($value) {
        return is_numeric($value) ? floatval($value) : floatval(preg_replace('/[^0-9.]/', '', $value));
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
    public function getRecipeById($recipeId): array|null;

    /**
     * Get similar recipes based on embedding vector similarity using cosine similarity
     *
     * @param int $recipeId ID of the recipe to find similar recipes for
     * @param int $limit Maximum number of similar recipes to return
     * @return array List of similar recipes sorted by similarity score
     */
    public function getSimilarRecipesByEmbedding($recipeId, $limit = 5): array;
}

/**
 * Mock implementation of recipe data provider
 */
class MockRecipeDataProvider implements RecipeDataProvider
{
    private $recipes = [];

    public function __construct()
    {
        $this->initializeRecipes();
    }

    public function getAllRecipes(): array
    {
        return $this->recipes;
    }

    public function getRecipeById($recipeId): ?array
    {
        // Cast to integer to prevent SQL injection
        $recipeId = (int)$recipeId;

        $filterf = function ($recipe) use ($recipeId) {
            return $recipe['id'] == $recipeId;
        };
        $result = array_filter($this->recipes, $filterf);
        return $result ? reset($result) : null;
    }

    /**
     * Mock implementation of getSimilarRecipesByEmbedding
     * Since this is a mock provider, we'll just return random recipes
     */
    public function getSimilarRecipesByEmbedding($recipeId, $limit = 5): array
    {
        // Cast to integer to prevent SQL injection
        $recipeId = (int)$recipeId;
        $limit = (int)$limit;

        // Get the recipe we're finding similar recipes for
        $targetRecipe = $this->getRecipeById($recipeId);
        if (!$targetRecipe) {
            return [];
        }

        // Filter out the target recipe from the list
        $otherRecipes = array_filter($this->recipes, function ($recipe) use ($recipeId) {
            return $recipe['id'] != $recipeId;
        });

        // If we have fewer recipes than the limit, return all of them
        if (count($otherRecipes) <= $limit) {
            return array_values($otherRecipes);
        }

        // Otherwise, return a random selection
        shuffle($otherRecipes);
        return array_slice($otherRecipes, 0, $limit);
    }

    /**
     * Initialize mock recipe data
     */
    private function initializeRecipes(): void
    {
        $this->recipes = [
            [
                'id' => 1,
                'meal_type' => 'Lunch',
                'subcategory' => 'Bowl',
                'recipe' => 'Mediterranean Quinoa Bowl',
                'description' => 'A protein-packed bowl with quinoa, chickpeas, cucumber, tomatoes, and feta cheese.',
                'dish_type' => 'Main Course',
                'prep_time' => 15,
                'cook_time' => 20,
                'difficulty' => 'Medium',
                'ingredients' => "1 cup quinoa\n2 cups vegetable broth\n1 can (15 oz) chickpeas, drained and rinsed\n1 medium cucumber, diced\n2 medium tomatoes, diced\n1/2 red onion, finely chopped\n1/2 cup feta cheese, crumbled\n1/4 cup kalamata olives, pitted and sliced\n3 tbsp extra virgin olive oil\n2 tbsp lemon juice\n1 clove garlic, minced\n1 tsp dried oregano\nSalt and pepper to taste\n2 tbsp fresh parsley, chopped",
                'instructions' => "1. Rinse the quinoa under cold water using a fine mesh strainer.\n2. In a medium saucepan, bring the vegetable broth to a boil.\n3. Add the quinoa, reduce heat to low, cover, and simmer for 15-20 minutes until all liquid is absorbed.\n4. Remove from heat and let stand for 5 minutes, then fluff with a fork and allow to cool to room temperature.\n5. In a large bowl, combine cooled quinoa, chickpeas, cucumber, tomatoes, red onion, feta cheese, and olives.\n6. In a small bowl, whisk together olive oil, lemon juice, garlic, oregano, salt, and pepper.\n7. Pour the dressing over the quinoa mixture and toss gently to combine.\n8. Sprinkle with fresh parsley before serving.\n9. Can be served immediately or refrigerated for up to 3 days.",
                'serves' => 4,
                'calories' => '420',
                'protein' => '11',
                'total_time' => 35,
                'tags' => "['Vegan', 'Thai', 'Dinner', 'Spicy']"
            ],
            [
                'id' => 11,
                'meal_type' => 'Dinner',
                'subcategory' => 'Stir-Fry',
                'recipe' => 'Beef and Broccoli Stir-Fry',
                'description' => 'Tender beef strips and crisp broccoli in a savory sauce served over rice.',
                'dish_type' => 'Main Course',
                'prep_time' => 15,
                'cook_time' => 15,
                'difficulty' => 'Medium',
                'ingredients' => "1 lb flank steak, thinly sliced against the grain\n1 cup jasmine rice\n2 cups water\n2 tbsp vegetable oil, divided\n4 cups broccoli florets\n1 red bell pepper, sliced\n3 cloves garlic, minced\n1 tbsp fresh ginger, grated\n2 tbsp sesame oil\n\nFor the marinade:\n2 tbsp soy sauce\n1 tbsp cornstarch\n1 tsp baking soda (tenderizes the meat)\n\nFor the sauce:\n1/3 cup beef broth\n1/4 cup oyster sauce\n3 tbsp soy sauce\n1 tbsp brown sugar\n1 tbsp cornstarch\n1 tsp rice vinegar",
                'instructions' => "1. In a medium bowl, combine the marinade ingredients. Add the sliced beef and toss to coat. Let marinate for at least 15 minutes or up to 1 hour in the refrigerator.\n2. Rinse rice until water runs clear. In a medium saucepan, combine rice and water. Bring to a boil, then reduce heat to low, cover, and simmer for 15 minutes. Remove from heat and let stand, covered, for 5 minutes.\n3. In a small bowl, whisk together all sauce ingredients until smooth. Set aside.\n4. Heat 1 tablespoon vegetable oil in a large wok or skillet over high heat.\n5. Add broccoli and bell pepper, and stir-fry for 3-4 minutes until broccoli is bright green and crisp-tender. Remove vegetables and set aside.\n6. Add the remaining tablespoon of vegetable oil to the wok.\n7. Add the marinated beef in a single layer (you may need to do this in batches). Cook without stirring for 1 minute to sear, then stir-fry for another 1-2 minutes until beef is nearly cooked through.\n8. Add garlic and ginger, and stir-fry for 30 seconds until fragrant.\n9. Return the vegetables to the wok and pour in the sauce.\n10. Stir-fry for another 1-2 minutes until the sauce thickens and everything is well coated.\n11. Drizzle with sesame oil and toss to combine.\n12. Serve over cooked jasmine rice.",
                'serves' => 4,
                'calories' => '450',
                'protein' => '28',
                'total_time' => 30,
                'tags' => "['High Protein', 'Chinese', 'Dinner']"
            ],
            [
                'id' => 12,
                'meal_type' => 'Breakfast',
                'subcategory' => 'Vegetarian',
                'recipe' => 'Overnight Chia Pudding',
                'description' => 'Chia seeds soaked in almond milk, topped with fresh fruit and a drizzle of maple syrup.',
                'dish_type' => 'Breakfast',
                'prep_time' => 10,
                'cook_time' => 0,
                'difficulty' => 'Easy',
                'ingredients' => "1/4 cup chia seeds\n1 cup almond milk (or any milk of choice)\n1 tbsp maple syrup or honey\n1/2 tsp vanilla extract\nPinch of salt\nToppings: fresh berries, sliced banana, chopped nuts, coconut flakes, additional maple syrup",
                'instructions' => "1. In a mason jar or container with a lid, combine chia seeds, almond milk, maple syrup, vanilla extract, and salt.\n2. Stir well to combine, making sure there are no clumps of chia seeds.\n3. Secure the lid and refrigerate for at least 4 hours or overnight.\n4. About halfway through the soaking time, give the mixture another good stir to prevent clumping.\n5. When ready to serve, stir the pudding and check consistency. If it's too thick, add a splash more milk; if too thin, add more chia seeds and wait 10 minutes.\n6. Top with your favorite fresh fruits, nuts, coconut flakes, and a drizzle of additional maple syrup if desired.\n7. Enjoy cold straight from the refrigerator. The pudding will keep for up to 5 days refrigerated.",
                'serves' => 2,
                'calories' => '200',
                'protein' => '6',
                'total_time' => 10,
                'tags' => "['Vegan', 'Gluten-Free', 'Breakfast', 'Snack']"
            ],
            [
                'id' => 13,
                'meal_type' => 'Dinner',
                'subcategory' => 'Pizza',
                'recipe' => 'Cauliflower Pizza with Fresh Vegetables',
                'description' => 'Low-carb Vegetarian pizza with a cauliflower crust, topped with fresh vegetables and mozzarella.',
                'dish_type' => 'Main Course',
                'prep_time' => 30,
                'cook_time' => 30,
                'difficulty' => 'Hard',
                'ingredients' => "1 medium head cauliflower, cut into florets\n1/4 cup grated parmesan cheese\n1/4 cup mozzarella cheese, shredded\n1 large egg\n1 tsp dried oregano\n1/2 tsp garlic powder\nSalt and pepper to taste\n\nFor the toppings:\n1/3 cup tomato sauce or pizza sauce\n1 cup mozzarella cheese, shredded\n1/2 red bell pepper, thinly sliced\n1/2 yellow bell pepper, thinly sliced\n1/4 red onion, thinly sliced\n1/2 cup cherry tomatoes, halved\n1/4 cup black olives, sliced\nFresh basil leaves\nRed pepper flakes (optional)",
                'instructions' => "1. Preheat oven to 425°F (220°C) and line a baking sheet with parchment paper.\n2. Process cauliflower florets in a food processor until they resemble rice grains.\n3. Transfer the cauliflower rice to a microwave-safe bowl and microwave on high for 5 minutes.\n4. Allow the cauliflower to cool enough to handle, then transfer to a clean kitchen towel and squeeze out as much moisture as possible (this is crucial for a crispy crust).\n5. In a bowl, combine the cauliflower with parmesan, 1/4 cup mozzarella, egg, oregano, garlic powder, salt, and pepper. Mix well.\n6. Transfer the mixture to the prepared baking sheet and shape into a circle about 1/4 inch thick.\n7. Bake for 15-20 minutes until golden brown and firm.\n8. Remove from oven and spread tomato sauce evenly over the crust, leaving a small border around the edges.\n9. Sprinkle with mozzarella cheese and arrange vegetable toppings over the cheese.\n10. Return to the oven and bake for an additional 10-12 minutes until cheese is melted and bubbly.\n11. Garnish with fresh basil leaves and red pepper flakes if desired.\n12. Allow to cool for 5 minutes before slicing and serving.",
                'serves' => 2,
                'calories' => '300',
                'protein' => '20',
                'total_time' => 60,
                'tags' => "['Vegetarian','Gluten-Free' , 'Keto', 'Dinner']"
            ],
            [
                'id' => 14,
                'meal_type' => 'Dinner',
                'subcategory' => 'Bowl',
                'recipe' => 'Korean Bibimbap',
                'description' => 'Rice bowl topped with seasoned vegetables, beef, a fried egg, and gochujang sauce.',
                'dish_type' => 'Main Course',
                'prep_time' => 25,
                'cook_time' => 30,
                'difficulty' => 'Hard',
                'ingredients' => "1 1/2 cups short-grain rice\n2 cups water\n1/2 lb lean ground beef or thinly sliced beef\n2 tbsp soy sauce, divided\n2 tsp sesame oil, divided\n2 tsp brown sugar\n2 cloves garlic, minced\n1 cup spinach\n1 cup bean sprouts\n1 medium zucchini, julienned\n1 large carrot, julienned\n1/2 cup shiitake mushrooms, sliced\n4 eggs\nSalt to taste\n2 green onions, sliced\n1 tbsp sesame seeds\n\nFor the gochujang sauce:\n3 tbsp gochujang (Korean chili paste)\n1 tbsp sesame oil\n1 tbsp water\n1 tsp rice vinegar\n1 tsp brown sugar\n1 clove garlic, minced",
                'instructions' => "1. Rinse rice until water runs clear. Cook rice with 2 cups water in a rice cooker or stovetop according to package directions.\n2. In a small bowl, mix 1 tablespoon soy sauce, 1 teaspoon sesame oil, brown sugar, and half the minced garlic. Toss with the beef and marinate for 15 minutes.\n3. Prepare the gochujang sauce by mixing all sauce ingredients in a small bowl. Set aside.\n4. Blanch spinach for 30 seconds in boiling water, then drain and rinse with cold water. Squeeze out excess water and season with a pinch of salt and a few drops of sesame oil.\n5. Blanch bean sprouts for 1 minute, drain, and season with a pinch of salt and a few drops of sesame oil.\n6. In a large skillet, sauté zucchini over medium-high heat for 2-3 minutes until tender-crisp. Season with a pinch of salt and set aside.\n7. In the same skillet, sauté carrots for 2-3 minutes. Season with a pinch of salt and set aside.\n8. Sauté mushrooms with the remaining garlic for 3-4 minutes. Season with 1 teaspoon soy sauce and set aside.\n9. In the same skillet, cook the marinated beef over medium-high heat until browned and cooked through, about 5-6 minutes. Set aside.\n10. Wipe the skillet clean and fry the eggs sunny-side up or over easy.\n11. To assemble, divide the cooked rice among 4 bowls. Arrange the beef and vegetables in separate sections around the rice.\n12. Place a fried egg in the center of each bowl.\n13. Sprinkle with green onions and sesame seeds.\n14. Serve with gochujang sauce on the side or drizzled over the top.",
                'serves' => 4,
                'calories' => '470',
                'protein' => '24',
                'total_time' => 55,
                'tags' => "['Korean', 'High Protein', 'Dinner']"
            ],
            [
                'id' => 15,
                'meal_type' => 'Breakfast',
                'subcategory' => 'Smoothie Bowl',
                'recipe' => 'Mango Berry Smoothie Bowl',
                'description' => 'Frozen mango and mixed berries blended and topped with granola and fresh fruit.',
                'dish_type' => 'Breakfast',
                'prep_time' => 15,
                'cook_time' => 0,
                'difficulty' => 'Easy',
                'ingredients' => "1 cup frozen mango chunks\n1 cup frozen mixed berries (strawberries, blueberries, raspberries)\n1 ripe banana\n1/4 cup unsweetened almond milk or coconut milk\n1 tbsp honey or maple syrup (optional)\n\nFor toppings:\n1/4 cup granola\n1/2 banana, sliced\n1/4 cup fresh berries\n1 tbsp chia seeds\n1 tbsp coconut flakes\nHoney or maple syrup for drizzling",
                'instructions' => "1. Add frozen mango, frozen mixed berries, banana, and milk to a high-powered blender.\n2. Blend on low speed initially, then increase to high until smooth and creamy. You want a thick consistency that can be eaten with a spoon.\n3. If the mixture is too thick, add a little more milk, one tablespoon at a time. If too thin, add more frozen fruit.\n4. Add honey or maple syrup if desired and blend briefly to incorporate.\n5. Pour the smoothie mixture into a bowl.\n6. Arrange toppings artfully on top of the smoothie: sliced banana, fresh berries, granola, chia seeds, and coconut flakes.\n7. Drizzle with a small amount of honey or maple syrup if desired.\n8. Serve immediately with a spoon.",
                'serves' => 2,
                'calories' => '230',
                'protein' => '5',
                'total_time' => 15,
                'tags' => "['Vegan', 'Gluten-Free', 'Breakfast', 'Snack']"
            ]
        ];
    }
}

class RedbeanRecipeDataProvider implements RecipeDataProvider
{
    /**
     * @param array<int,mixed> $config
     */
    public function __construct(array $config = [])
    {
        $dbConnection = DatabaseConnection::getInstance();
        $dbConnection->setup($config);
    }

    /**
     * Get similar recipes based on embedding vector similarity using cosine similarity
     *
     * @param int $recipeId ID of the recipe to find similar recipes for
     * @param int $limit Maximum number of similar recipes to return
     * @return array List of similar recipes sorted by similarity score
     */
    public function getSimilarRecipesByEmbedding($recipeId, $limit = 5): array
    {
        $recipeId = (int)$recipeId;
        $limit = (int)$limit;

        $recipe = \R::load('recipes', $recipeId);

        $query = generateCosineSimilarityQuery(json_decode($recipe['embedding']), $limit);

        $similarRecipes = \R::getAll($query);

        return $similarRecipes;
    }

    public function getAllRecipes(): array
    {
        // Using RedBean's findAll which uses prepared statements internally
        $recipes = \R::findAll('recipes');
        return \R::exportAll($recipes);
    }

    public function getRecipeById($recipeId): ?array
    {
        // Cast to integer to prevent SQL injection
        $recipeId = (int)$recipeId;

        $recipe = \R::load('recipes', $recipeId);
        if ($recipe->id === 0 && !isset($recipe['recipe'])) {
            return null; // Recipe not found
        }
        return $recipe->export();
    }

    public function getRandomRecipeWithImage(): array
    {
        // Using parameterized query to prevent SQL injection
        $recipes = \R::findAll('recipes', 'WHERE imageURL IS NOT NULL');
        $rand_recipes = \R::exportAll($recipes);

        if (empty($rand_recipes)) {
            return [];
        }

        $randomIndex = array_rand($rand_recipes);
        $randomRecipe = $rand_recipes[$randomIndex];

        return $randomRecipe;
    }

    public function getCommentsForRecipe($recipeId): array
    {
        $comments = \R::findAll('discussion', 'WHERE recipe_id = ? ORDER BY comment_time ASC', [$recipeId]);

        $commentsWithUserData = [];
        foreach ($comments as $comment) {
            $commentUser = \R::load('user', $comment['user_id']);
            $commentsWithUserData[] = [
                'comment' => $comment,
                'user' => $commentUser
            ];
        }
        return $commentsWithUserData;
    }

    // Store a comment for a specific recipe
    public function addComment($recipeId, $userId, $commentBody): void
    {
        $comment = \R::dispense('discussion');
        $comment->recipe_id = $recipeId;
        $comment->user_id = $userId;
        $comment->comment_body = $commentBody;
        $comment->comment_time = date('Y-m-d H:i:s');

        \R::store($comment);
    }

    // Toggle like status for a recipe
    public function toggleLike($recipeId, $userId): void
    {
        $recipe = \R::load('recipes', $recipeId);
        if ($recipe->id) {
            $existingLike = \R::findOne('like', 'recipe_id = ? AND user_id = ?', [$recipeId, $userId]);

            if ($existingLike) {
                // If the user has already liked, remove the like
                \R::trash($existingLike);  // Delete the like record from the 'likes' table
                $liked = false;
            } else {
                // If the user hasn't liked the recipe, add the like
                $like = \R::dispense('like');  // Create a new like record
                $like->recipe_id = $recipeId;
                $like->user_id = $userId;
                \R::store($like);  // Save the new like record
                $liked = true;
            }

            // Count the total number of likes for the recipe by counting rows in the 'likes' table
            $likeCount = \R::count('like', 'recipe_id = ?', [$recipeId]);

            // Return the updated like count and whether the user has liked the recipe
        } else {
            // If the recipe doesn't exist, return an error
            echo json_encode([
                'error' => 'Recipe not found'
            ]);
        }
    }

    public function getLikeCount($recipe_id)
    {
        $likeCount = \R::count('like', 'recipe_id = ?', [$recipe_id]);
        return $likeCount;
    }

    public function isLikedByUser($recipe_id, $user_id): bool
    {

        // Check if there's a record in the 'like' table with the given user_id and recipe_id
        $likeExists = \R::count('like', 'recipe_id = ? AND user_id = ?', [$recipe_id, $user_id]);

        return $likeExists > 0; // Returns true if the user has liked the recipe, false otherwise
    }

    // Function to get liked recipes by user
    public function getLikedRecipes($user_id)
    {
        $likedRecipes = R::findAll('like', 'user_id = ?', [$user_id]);

        // Array to store recipe data
        $recipeData = [];

        // Loop through each liked recipe and fetch full recipe details
        foreach ($likedRecipes as $like) {
            $recipe = R::load('recipes', $like->recipe_id); // Assuming recipe_id is the foreign key in the 'like' table
            if ($recipe) {
                // Add recipe data to the array
                $recipeData[] = [
                    'id' => $recipe->id,
                    'name' => $recipe->recipe
                ];
            }
        }

        return $recipeData;
    }

    // Edit Recipe Comment Logic
    public function editComment($commentId, $newCommentBody, $userId)
    {
        // Load the comment
        $comment = R::load('discussion', $commentId);

        // Check if the user is the owner of the comment
        if ($comment->user_id === $userId) {
            $comment->comment_body = $newCommentBody;
            R::store($comment); // Save the updated comment
            return true;  // Return success
        }

        return false; // Return failure if user is not the author
    }


    // Delete Comment Logic
    public function deleteComment($commentId, $userId)
    {
        $comment = R::load('discussion', $commentId);
        if ($comment->user_id === $userId) {
            R::trash($comment); // Delete the comment
            return true;  // Return success
        }
        return false; // Return failure if user is not the author
    }
}

/**
 * Generate a MySQL query to find similar recipes by cosine similarity
 *
 * @param array $queryVector The query vector to compare against
 * @param int $limit The number of similar recipes to return
 * @return string The generated SQL query
 */
function generateCosineSimilarityQuery(array $queryVector, int $limit = 10): string
{
    $queryMagnitude = sqrt(array_sum(array_map(function ($x) {
        return $x * $x;
    }, $queryVector)));

    $dotProductTerms = [];
    foreach ($queryVector as $index => $value) {
        if ($value != 0) { // Skip zeros for efficiency
            $dotProductTerms[] = "JSON_EXTRACT(embedding, '$[$index]') * " . number_format($value, 8, '.', '');
        }
    }
    $dotProductSQL = implode(' + ', $dotProductTerms);

    $magnitudeTerms = [];
    for ($i = 0; $i < count($queryVector); $i++) {
        $magnitudeTerms[] = "POW(JSON_EXTRACT(embedding, '$[$i]'), 2)";
    }
    $magnitudeSQL = "SQRT(" . implode(' + ', $magnitudeTerms) . ")";

    $query = "
    SELECT 
        id, 
        (
            ($dotProductSQL) / 
            ($queryMagnitude * $magnitudeSQL)
        ) AS similarity_score
    FROM
        recipes
    WHERE
        embedding IS NOT NULL 
    ORDER BY
        similarity_score DESC
    LIMIT $limit;
    ";

    return $query;
}

    // Search by title or keywords
    if (!empty($search)) {
        $search = strtolower($search);
        $filteredRecipes = array_filter($filteredRecipes, function($recipe) use ($search) {
            return strpos(strtolower($recipe['recipe']), $search) !== false ||
                strpos(strtolower($recipe['description']), $search) !== false;
        });
    }

    // Filter by budget type
    if (!empty($priceRange)) {
        $filteredRecipes = $this->filterByBudget($filteredRecipes, $priceRange);
    }
    
    // Pagination logic: calculate the starting index
    $totalRecipes = count($filteredRecipes);
    $totalPages = ceil($totalRecipes / $perPage);
    $offset = ($page - 1) * $perPage;

    // Slice the filtered recipes array to get the correct page
    $paginatedRecipes = array_slice($filteredRecipes, $offset, $perPage);

    // Return both the paginated recipes and total page count
    return [
        'recipes' => $paginatedRecipes,
        'totalPages' => $totalPages,
        'currentPage' => $page
    ];
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
public function searchActionRedbean(?string $search = null, ?string $dietary = null, ?int $maxPrepTime = 60, ?string $mealType = null, ?string $priceRange = null, int $page = 1, int $perPage = 15) {
    // Calculate the offset for pagination
    $offset = ($page - 1) * $perPage;

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
        // Add conditions to check for dietary preference in multiple fields
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

    // Add price range filter if provided
    if ($priceRange) {
        $query .= ' AND price_range <= ?';
        $params[] = $priceRange;
    }

    // Apply pagination with LIMIT and OFFSET
    $query .= ' LIMIT ? OFFSET ?';
    $params[] = $perPage;
    $params[] = $offset;

    // Execute the query using RedBean
    $recipes = \R::getAll('SELECT * FROM recipes ' . $query, $params);


    // Loop through each recipe and clean the tags (optional)
    foreach ($recipes as &$recipe) {
        if (isset($recipe['tags'])) {
            $recipe['tags'] = is_string($recipe['tags']) ? json_decode($recipe['tags'], true) : $recipe['tags'];
            if (json_last_error() !== JSON_ERROR_NONE) {
                $recipe['tags'] = [];
            }
        }
    }

    // Return the data (you might also want to include the total count of recipes for pagination)
    $totalRecipes = \R::getCell('SELECT COUNT(*) FROM recipes ' . $query, $params);
    $totalPages = ceil($totalRecipes / $perPage);

    return [
        'recipes' => $recipes,
        'totalPages' => $totalPages,
        'currentPage' => $page,
    ];
}
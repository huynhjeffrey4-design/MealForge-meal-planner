<?php
declare(strict_types=1);
namespace Tests\Unit;

require_once __DIR__ . '/../../controllers/recipe.php';

use Tests\Support\UnitTester;

final class RecipeControllerCest
{
    private \RecipeController $controller;
    
    public function _before(UnitTester $I): void
    {
        $this->controller = new \RecipeController(new \MockRecipeDataProvider());
    }
    
    /**
     * Test getting all recipes without filters
     */
    public function testGetAllRecipes(UnitTester $T): void
    {
        // Act
        $recipes = $this->controller->getAllRecipes();
        
        // Assert
        $T->assertIsArray($recipes);
        $T->assertNotEmpty($recipes);
        
        // Check recipe structure for the first recipe
        $firstRecipe = $recipes[0];
        $T->assertArrayHasKey('id', $firstRecipe);
        $T->assertArrayHasKey('recipe', $firstRecipe); // Changed from 'title' to 'recipe'
        $T->assertArrayHasKey('description', $firstRecipe);
        $T->assertArrayHasKey('tags', $firstRecipe);
        $T->assertArrayHasKey('prep_time', $firstRecipe);
        // Removed 'price' assertion as it's not in the new schema
        $T->assertArrayHasKey('difficulty', $firstRecipe);
        // Removed 'image' assertion as it's not in the new schema
        $T->assertArrayHasKey('meal_type', $firstRecipe); // Added new required field
        $T->assertArrayHasKey('ingredients', $firstRecipe); // Added new required field
        $T->assertArrayHasKey('instructions', $firstRecipe); // Added new required field
    }
    
    /**
     * Test search with no parameters returns all recipes
     */
    public function testSearchWithNoParameters(UnitTester $T): void
    {
        // Act
        $allRecipes = $this->controller->getAllRecipes();
        $searchResults = $this->controller->searchAction();
        
        // Assert
        $T->assertEquals(15, count($searchResults["recipes"]));
    }
    
    /**
     * Test filtering by dietary preference
     */
    public function testFilterByDietaryPreference(UnitTester $T): void
    {
        // Act
        $vegetarianRecipes = $this->controller->searchAction(
				dietary: 'Vegetarian'
        )["recipes"];
        
        // Assert
        $T->assertNotEmpty($vegetarianRecipes);
    }
    
    
    /**
     * Test filtering by maximum preparation time
     */
    public function testFilterByMaxPrepTime(UnitTester $T): void
    {
        // Arrange
        $maxTime = 30;
        
        // Act
        $quickRecipes = $this->controller->searchAction(
            null,
            null,
            $maxTime,
            null,
            null
        )["recipes"];
        
        // Assert
        $T->assertNotEmpty($quickRecipes);
        
        // Check all returned recipes have prep time <= maxTime
        foreach ($quickRecipes as $recipe) {
            $T->assertLessThanOrEqual($maxTime, $recipe['prep_time']);
        }
    }
    
    /**
     * Test filtering by meal type
     */
    public function testFilterByMealType(UnitTester $T): void
    {
        // Act
        $breakfastRecipes = $this->controller->searchAction(
            null,
            null,
            null,
            'Breakfast',
            null
        )["recipes"];
        
        // Assert
        $T->assertNotEmpty($breakfastRecipes);
        
        // Updated to check that the meal_type field matches 'Breakfast' 
        // instead of checking tags
        foreach ($breakfastRecipes as $recipe) {
            $T->assertEquals('Breakfast', $recipe['meal_type']);
        }
    }
    
    
    /**
     * Test searching by keyword in recipe name
     */
    public function testSearchByRecipeNameKeyword(UnitTester $T): void
    {
        // Arrange
        $searchTerm = 'Quinoa';
        
        // Act
        $recipes = $this->controller->searchAction(
            $searchTerm,
            null,
            null,
            null,
            null
        )["recipes"];
        
        // Assert
        $T->assertNotEmpty($recipes);
        
        // Check all returned recipes have the search term in recipe name
        foreach ($recipes as $recipe) {
            $T->assertStringContainsStringIgnoringCase($searchTerm, $recipe['recipe']); // Changed from 'title' to 'recipe'
        }
    }
    
    /**
     * Test searching by keyword in description
     */
    public function testSearchByDescriptionKeyword(UnitTester $T): void
    {
        // Arrange
        $searchTerm = 'chickpeas';
        
        // Act
        $recipes = $this->controller->searchAction(
            $searchTerm,
            null,
            null,
            null,
            null
        )["recipes"];
        
        // Assert
        $T->assertNotEmpty($recipes);
        
        // Check all returned recipes have the search term in description
        foreach ($recipes as $recipe) {
            $descriptionContainsTerm = (strpos(strtolower($recipe['description']), strtolower($searchTerm)) !== false);
            $recipeNameContainsTerm = (strpos(strtolower($recipe['recipe']), strtolower($searchTerm)) !== false); // Changed from 'title' to 'recipe'
            $T->assertTrue($descriptionContainsTerm || $recipeNameContainsTerm);
        }
    }
    
    /**
     * Test searching by keyword in ingredients
     * Added new test for searching in ingredients
     */
    public function testSearchByIngredientsKeyword(UnitTester $T): void
    {
        // Arrange
        $searchTerm = 'feta';
        
        // Act
        $recipes = $this->controller->searchAction(
            $searchTerm,
            null,
            null,
            null,
            null
        )["recipes"];
        
        // Assert
        $T->assertNotEmpty($recipes);
        
        // Check all returned recipes have the search term in ingredients
        foreach ($recipes as $recipe) {
            $ingredientsContainsTerm = (strpos(strtolower($recipe['ingredients']), strtolower($searchTerm)) !== false);
            $descriptionContainsTerm = (strpos(strtolower($recipe['description']), strtolower($searchTerm)) !== false);
            $recipeNameContainsTerm = (strpos(strtolower($recipe['recipe']), strtolower($searchTerm)) !== false);
            
            $T->assertTrue($ingredientsContainsTerm || $descriptionContainsTerm || $recipeNameContainsTerm);
        }
    }
    
    /**
     * Test case insensitivity of search
     */
    public function testCaseInsensitiveSearch(UnitTester $T): void
    {
        // Arrange
        $lowerCaseSearch = 'mediterranean';
        $upperCaseSearch = 'MEDITERRANEAN';
        $perPage = 1000;

        // Act
        $lowerCaseResults = $this->controller->searchAction(
            search: $lowerCaseSearch,
            perPage: $perPage
        )["recipes"];

        $upperCaseResults = $this->controller->searchAction(
            search: $upperCaseSearch,
            perPage: $perPage
        )["recipes"];

        // Assert
        $T->assertEquals(count($lowerCaseResults), count($upperCaseResults));
    }
    
    /**
     * Test combined filtering
     */
    public function testCombinedFiltering(UnitTester $T): void
    {
        // Act
        $recipes = $this->controller->searchAction(
            dietary: 'Vegan', // dietary
            maxPrepTime: 30,             // maxPrepTime
            mealType: 'Breakfast',    // mealType
        )["recipes"];
        
        // Assert
        if (!empty($recipes)) {
            foreach ($recipes as $recipe) {
                // Check dietary preference in tags string
                $T->assertArrayHasKey('Vegan', $recipe['tags']);
                
                // Check mealType matches
                $T->assertEquals('Breakfast', $recipe['meal_type']);
                
                $T->assertLessThanOrEqual(30, $recipe['total_time']);
            }
        }
    }
    
    /**
     * Test get recipe by ID
     */
    public function testGetRecipeById(UnitTester $T): void
    {
        // Arrange
        $allRecipes = $this->controller->getAllRecipes();
        $firstRecipeId = $allRecipes[0]['id'];
        
        // Act
		$T->amGoingTo("get recipe by ID: $firstRecipeId");
        $recipe = $this->controller->getRecipeById($firstRecipeId);
        
        // Assert
        $T->assertNotNull($recipe);
        $T->assertEquals($firstRecipeId, $recipe['id']);
    }
    
    /**
     * Test get recipe by non-existent ID
     */
    public function testGetRecipeByNonExistentId(UnitTester $T): void
    {
        // Act
        $recipe = $this->controller->getRecipeById(9999);
        
        // Assert
        $T->assertNull($recipe);
    }
    
    /**
     * Test filtering by total time
     * Added new test for total time filter
     */
    public function testFilterByTotalTime(UnitTester $T): void
    {
        // Arrange
        $maxTotalTime = 20;
        
        // Act
        $quickTotalRecipes = $this->controller->searchAction(
            maxPrepTime: $maxTotalTime,
        )["recipes"];
        
        // Assert
        $T->assertNotEmpty($quickTotalRecipes);
        
        // Check all returned recipes have total_time <= maxTotalTime
        foreach ($quickTotalRecipes as $recipe) {
            $T->assertLessThanOrEqual($maxTotalTime, $recipe['total_time']);
        }
    }
}

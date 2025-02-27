<?php
declare(strict_types=1);
namespace Tests\Unit;

require_once __DIR__ . '/../../controllers/recipe.php';

use Tests\Support\UnitTester;
use App\Controllers\RecipeController;

final class RecipeControllerCest
{
    private RecipeController $controller;
    
    public function _before(UnitTester $I): void
    {
        $this->controller = new RecipeController();
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
        $T->assertArrayHasKey('title', $firstRecipe);
        $T->assertArrayHasKey('description', $firstRecipe);
        $T->assertArrayHasKey('tags', $firstRecipe);
        $T->assertArrayHasKey('prep_time', $firstRecipe);
        $T->assertArrayHasKey('price', $firstRecipe);
        $T->assertArrayHasKey('difficulty', $firstRecipe);
        $T->assertArrayHasKey('image', $firstRecipe);
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
        $T->assertEquals(count($allRecipes), count($searchResults));
    }
    
    /**
     * Test filtering by dietary preference
     */
    public function testFilterByDietaryPreference(UnitTester $T): void
    {
        // Act
        $vegetarianRecipes = $this->controller->searchAction(
            null,
            ['Vegetarian'],
            null,
            null,
            null
        );
        
        // Assert
        $T->assertNotEmpty($vegetarianRecipes);
        
        // Check all returned recipes have the Vegetarian tag
        foreach ($vegetarianRecipes as $recipe) {
            $T->assertContains('Vegetarian', $recipe['tags']);
        }
    }
    
    /**
     * Test filtering by multiple dietary preferences
     */
    public function testFilterByMultipleDietaryPreferences(UnitTester $T): void
    {
        // Act
        $recipes = $this->controller->searchAction(
            null,
            ['Vegetarian', 'Gluten-Free'],
            null,
            null,
            null
        );
        
        // Assert
        $T->assertNotEmpty($recipes);
        
        // Check all returned recipes have either Vegetarian or Gluten-Free tag
        foreach ($recipes as $recipe) {
            $T->assertTrue(
                in_array('Vegetarian', $recipe['tags']) ||
                in_array('Gluten-Free', $recipe['tags'])
            );
        }
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
        );
        
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
        );
        
        // Assert
        $T->assertNotEmpty($breakfastRecipes);
        
        // Check all returned recipes have the Breakfast tag
        foreach ($breakfastRecipes as $recipe) {
            $T->assertContains('Breakfast', $recipe['tags']);
        }
    }
    
    /**
     * Test filtering by price range - budget
     */
    public function testFilterByBudgetPriceRange(UnitTester $T): void
    {
        // Act
        $budgetRecipes = $this->controller->searchAction(
            null,
            null,
            null,
            null,
            'budget'
        );
        
        // Assert
        $T->assertNotEmpty($budgetRecipes);
        
        // Check all returned recipes have price < 5
        foreach ($budgetRecipes as $recipe) {
            $T->assertLessThan(5, $recipe['price']);
        }
    }
    
    /**
     * Test filtering by price range - moderate
     */
    public function testFilterByModeratePriceRange(UnitTester $T): void
    {
        // Act
        $moderateRecipes = $this->controller->searchAction(
            null,
            null,
            null,
            null,
            'moderate'
        );
        
        // Assert
        $T->assertNotEmpty($moderateRecipes);
        
        // Check all returned recipes have price between 5 and 10
        foreach ($moderateRecipes as $recipe) {
            $T->assertGreaterThanOrEqual(5, $recipe['price']);
            $T->assertLessThanOrEqual(10, $recipe['price']);
        }
    }
    
    /**
     * Test filtering by price range - premium
     */
    public function testFilterByPremiumPriceRange(UnitTester $T): void
    {
        // Act
        $premiumRecipes = $this->controller->searchAction(
            null,
            null,
            null,
            null,
            'premium'
        );
        
        // Assert
        $T->assertNotEmpty($premiumRecipes);
        
        // Check all returned recipes have price > 10
        foreach ($premiumRecipes as $recipe) {
            $T->assertGreaterThan(10, $recipe['price']);
        }
    }
    
    /**
     * Test searching by keyword in title
     */
    public function testSearchByTitleKeyword(UnitTester $T): void
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
        );
        
        // Assert
        $T->assertNotEmpty($recipes);
        
        // Check all returned recipes have the search term in title
        foreach ($recipes as $recipe) {
            $T->assertStringContainsStringIgnoringCase($searchTerm, $recipe['title']);
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
        );
        
        // Assert
        $T->assertNotEmpty($recipes);
        
        // Check all returned recipes have the search term in description
        foreach ($recipes as $recipe) {
            $descriptionContainsTerm = (strpos(strtolower($recipe['description']), strtolower($searchTerm)) !== false);
            $titleContainsTerm = (strpos(strtolower($recipe['title']), strtolower($searchTerm)) !== false);
            $T->assertTrue($descriptionContainsTerm || $titleContainsTerm);
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
        
        // Act
        $lowerCaseResults = $this->controller->searchAction($lowerCaseSearch);
        $upperCaseResults = $this->controller->searchAction($upperCaseSearch);
        
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
            null,           // search
            ['Vegetarian'], // dietary
            30,             // maxPrepTime
            'Breakfast',    // mealType
            'budget'        // priceRange
        );
        
        // Assert
        if (!empty($recipes)) {
            foreach ($recipes as $recipe) {
                $T->assertContains('Vegetarian', $recipe['tags']);
                $T->assertContains('Breakfast', $recipe['tags']);
                $T->assertLessThanOrEqual(30, $recipe['prep_time']);
                $T->assertLessThan(5, $recipe['price']);
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
}

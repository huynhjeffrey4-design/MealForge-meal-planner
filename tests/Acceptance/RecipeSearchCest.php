<?php
declare(strict_types=1);
namespace Tests\Acceptance;
use Tests\Support\AcceptanceTester;

final class RecipeSearchCest
{
    public function _before(AcceptanceTester $I): void
    {
        $_ENV['ENVIRONMENT'] = 'test';
    }
    
    /**
     * Test basic components are present.
     */
    public function testSearchPageComponents(AcceptanceTester $I): void
    {
        $I->amOnPage('/search.php');
		$I->see('Filters');
        $I->see('Apply Filters', 'button');
        $I->see('Showing'); // Recipe count indicates recipes are displaying
    }
    
    /**
     * Test searching for recipes by keyword
     */
    public function testSearchByKeyword(AcceptanceTester $I): void
    {
        // Arrange
        $searchTerm = 'Quinoa';
        
        // Act
        $I->amOnPage('/search.php');
        $I->submitForm('#search-form', ['search' => $searchTerm]);
        $I->click('Apply Filters');
        
        // Assert
        $I->seeInCurrentUrl('search=' . urlencode($searchTerm));
        $I->see("Mediterranean Quinoa Bowl", "h3"); // Should see recipe title containing the search term
    }
    
    /**
     * Test filtering recipes by dietary preference
     */
    public function testFilterByDietaryPreference(AcceptanceTester $I): void
    {
        // Act
        $I->amOnPage('/search.php');
		$I->see('Vegetarian', "label");

		$I->submitForm("#filter-form", ["dietary" => "Vegetarian"]);

        
        // Assert
        $I->seeInCurrentUrl('dietary=Vegetarian');
        $I->see('Vegetarian', '.bg-green-100'); // Should see Vegetarian tag in recipe results
    }
    
    /**
     * Test filtering recipes by maximum preparation time
     */
    public function testFilterByMaxPrepTime(AcceptanceTester $I): void
    {
        // Act
        $I->amOnPage('/search.php');
		$I->submitForm('#filter-form', ['max_prep_time' => 20]);
        
        // Assert
        $I->seeInCurrentUrl('max_prep_time=20');
        $I->dontSee('35 mins'); // Shouldn't see recipes with longer prep times
    }
    
    /**
     * Test filtering recipes by meal type
     */
    public function testFilterByMealType(AcceptanceTester $I): void
    {
        // Act
        $I->amOnPage('/search.php');
        $I->selectOption('meal_type', 'Breakfast');
		$I->click("Apply Filters");
        
        // Assert
        $I->seeInCurrentUrl('meal_type=Breakfast');
        $I->see('Breakfast', '.inline-block'); // Should see Breakfast tag in recipe results
    }
    
    
    /**
     * Test combining multiple filters
     */
    public function testCombinedFilters(AcceptanceTester $I): void
    {
        // Act
        $I->amOnPage('/search.php');
		$I->submitForm('#filter-form', [
				'max_prep_time' => 120,
				'dietary' => 'Vegetarian'
		]);
        
        // Assert
        $I->seeInCurrentUrl('dietary=Vegetarian');
        $I->seeInCurrentUrl('max_prep_time=120');
        $I->see('Vegetarian', 'span'); // Should see Vegan tag in results
    }
    
    /**
     * Test reset all filters functionality
     */
    public function testResetAllFilters(AcceptanceTester $I): void
    {
        // Arrange - First apply some filters
        $I->amOnPage('/search.php?dietary=Vegetarian&max_prep_time=30&meal_type=Breakfast');
        
        // Act - Click reset all
        $I->click('Reset All');
        
        // Assert
        $I->seeCurrentUrlEquals('/search.php');
        $I->dontSeeInCurrentUrl('dietary');
        $I->dontSeeInCurrentUrl('max_prep_time');
        $I->dontSeeInCurrentUrl('meal_type');
    }
    
    /**
     * Test no results scenario
     */
    public function testNoResultsScenario(AcceptanceTester $I): void
    {
        // Arrange - Apply very specific filters unlikely to match any recipes
        $searchUrl = '/search.php?search=nonexistentrecipe';
        
        // Act
        $I->amOnPage($searchUrl);
        
        // Assert
        $I->see('No recipes found', 'h3');
        $I->see('Try adjusting your filters or search criteria', 'p');
    }
    
    /**
     * Test search form preserves other filter values
     */
    public function testSearchFormPreservesFilters(AcceptanceTester $I): void
    {
        // Arrange - First apply some filters
        $I->amOnPage('/search.php');
        $I->selectOption('dietary', 'Vegetarian');
		$I->click('Apply Filters');
        
        // Act - Then search for something
        $I->submitForm('#search-form', ['search' => 'bowl']);
        
        // Assert
        $I->seeInCurrentUrl('search=bowl');
        $I->seeInCurrentUrl('dietary=Vegetarian');
    }
    
    /**
     * Test applying filters via Apply Filters button
     */
    public function testApplyFiltersButton(AcceptanceTester $I): void
    {
        // Arrange
        $I->amOnPage('/search.php');

		$I->submitForm('#filter-form', [
				'dietary' => 'Gluten-Free',
				'max_prep_time' => 45
		]);
        
        // Assert
        $I->seeInCurrentUrl('dietary=Gluten-Free');
        $I->seeInCurrentUrl('max_prep_time=45');
    }
    
    /**
     * Test back to dashboard navigation
     */
    public function testBackToDashboardNavigation(AcceptanceTester $I): void
    {
        // Arrange
		$email = 'test@email.com';
		$password = 'test';

		$I->amOnPage('/login.php');
		$I->fillField('email', $email);
		$I->fillField('password', $password);
		$I->click('Continue');
        $I->seeCurrentUrlEquals('/dashboard.php');
        
		$I->amOnPage('/search.php');

        // Act
        $I->click('Back to Dashboard');
        
        // Assert
        $I->seeCurrentUrlEquals('/dashboard.php');
    }
}

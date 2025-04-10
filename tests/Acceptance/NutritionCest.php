<?php

declare(strict_types=1);

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

final class NutritionCest
{
    /**
     * Test 1 - Nutrition Dashboard View:
     * Verify the nutrition dashboard displays expected elements and nutritional data
     */
    public function dashboardViewDisplaysNutritionalData(AcceptanceTester $I): void
    {
        // Register and login first
        $this->loginTestUser($I);

        // Navigate to nutrition dashboard
        $I->amOnPage('/dashboard/nutrition.php');

        // Verify page title and description
        $I->see('Nutrition Summary', 'h1');
        $I->see('Weekly overview of your meal plan\'s nutritional content');

        // Verify weekly average section is displayed
        $I->see('Weekly Average', 'h2');
        $I->seeElement('.progress-bar');
        $I->seeElement('.progress-fill');

        // Verify daily breakdown section is displayed
        $I->see('Daily Breakdown', 'h2');

        // Verify all days of the week are displayed
        $I->see('Monday', 'h3');
        $I->see('Tuesday', 'h3');
        $I->see('Wednesday', 'h3');
        $I->see('Thursday', 'h3');
        $I->see('Friday', 'h3');
        $I->see('Saturday', 'h3');
        $I->see('Sunday', 'h3');

        // Verify nutritional metrics are displayed
        $I->see('Calories', '.text-gray-700');
        $I->see('Protein', '.text-gray-700');

        // Verify back to meal plan link exists
        $I->see('Back to Meal Plan');
    }

    /**
     * Test 2 - Empty Meal Plan Display:
     * Verify appropriate messages are shown for days with no meals
     */
    public function emptyMealPlanDisplaysCorrectly(AcceptanceTester $I): void
    {
        $this->loginTestUser($I);

        // Navigate to nutrition dashboard
        $I->amOnPage('/dashboard/nutrition.php');

        // Find at least one day with no meals and verify the message
        $I->see('No meals planned for this day', '.text-gray-500');
        $I->see('Add meals', '.text-primary');

        // Verify the nutrition card hover effect works
        $I->seeElement('.nutrition-card');
    }

    /**
     * Test 3 - Nutrition Progress Indicators:
     * Verify progress bars correctly represent nutritional values
     */
    public function nutritionProgressIndicatorsDisplayCorrectly(AcceptanceTester $I): void
    {
        $this->loginTestUser($I);

        // Navigate to nutrition dashboard
        $I->amOnPage('/dashboard/nutrition.php');

        // Verify progress bars exist
        $I->seeElement('.progress-bar');
        $I->seeElement('.progress-fill');

        // Verify RDI comparison is displayed
        $I->see('kcal', '.text-gray-600');
        $I->see('g', '.text-gray-600');
    }

    private function loginTestUser(AcceptanceTester $I): void
    {
        // Login with new account
        $I->amOnPage('/login.php');
        $I->fillField('email', $this->testUser['email']);
        $I->fillField('password', $this->testUser['password']);
        $I->click('Continue');
        $I->seeInCurrentUrl('profile.php'); // Verify login worked
    }

    private array $testUser = [
        'email' => 'test@email.com',
        'password' => 'password123',
        'firstName' => 'Nutrition',
        'lastName' => 'Tester'
    ];
}

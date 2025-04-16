<?php

declare(strict_types=1);

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

final class RecipeCest
{
    /**
     * Test user credentials
     */
    private array $testUser = [
        'email' => 'test@email.com',
        'password' => 'password123',
        'first_name' => 'Testy'
    ];

    /**
     * Helper method to login a test user
     */
    private function loginTestUser(AcceptanceTester $I): void
    {
        $I->amOnPage('/login.php');
        $I->fillField('email', $this->testUser['email']);
        $I->fillField('password', $this->testUser['password']);
        $I->click('Continue');
        $I->seeInCurrentUrl('profile.php'); // Verify login worked
    }

    /**
     * Happy Path: Test sharing a recipe redirects to social page with correct parameters
     */
    public function testShareRecipeLink(AcceptanceTester $I): void
    {
        // Login first
        $this->loginTestUser($I);

        // Navigate to the recipe page
        $recipeId = 5;
        $I->amOnPage('/recipe.php?id=' . $recipeId);

        // Define expected parameters based on recipe ID 1 and the test user
        $expectedRecipeId = (string)$recipeId;
        $expectedRecipeName = 'Panuozzo sandwich'; // Name of recipe with ID 1
        $expectedUserFirstName = $this->testUser['first_name'];

        // Construct the expected URL part, ensuring parameters are URL encoded
        $expectedUrlPart = sprintf(
            'social.php?recipe_id=%s&recipe_name=%s&user_firstname=%s',
            $expectedRecipeId,
            urlencode($expectedRecipeName),
            urlencode($expectedUserFirstName)
        );

        // Find the share link (<a> tag) and click it
        // We target the <a> tag using a CSS selector that matches the start of its href attribute
        $I->click('a[href^="social.php?recipe_id=' . $expectedRecipeId . '"]');

        // Verify redirection to the social page with correct parameters
        $I->seeInCurrentUrl($expectedUrlPart);

        $I->see("I just made Panuozzo sandwich!");
    }

    public function recipeShowsRecommendations(AcceptanceTester $I)
    {
        $I->amOnPage('/recipe.php?id=1');
        $I->see('More like this');
    }

    /**
     * Test that a non-existent recipe shows a "Recipe Not Found" message
     */
    public function recipeNotFound(AcceptanceTester $I): void
    {
        // Visit a recipe ID that doesn't exist
        $I->amOnPage('/recipe.php?id=99999');

        // Verify the "Recipe Not Found" message is displayed
        $I->see('Recipe Not Found', 'h2');
        $I->see("The recipe you're looking for doesn't exist or was not specified.", 'p');

        // Verify the "Browse All Recipes" link is present
        $I->see('Browse All Recipes', 'a');
    }
}

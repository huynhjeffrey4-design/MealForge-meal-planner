<?php
declare(strict_types=1);
namespace Tests\Acceptance;
use Tests\Support\AcceptanceTester;

final class MatchCest
{
    public function _before(AcceptanceTester $I): void
    {
        $_ENV['ENVIRONMENT'] = 'test';
    }

    public function testPageLayout(AcceptanceTester $I): void
    {
        $I->amOnPage('/match.php');

        // Check if the main header "Recipe Suggestions" is present on the page
        $I->see('Recipe Suggestions', 'h2');

        // Check if the recipe name (h3) is displayed
        $I->seeElement('h3');
    }

    public function testCheckOnFirst(AcceptanceTester $I): void
    {
        // Navigate to the match page
        $I->amOnPage('/match.php');

        // Verify that the header "Recipe Suggestions" is displayed
        $I->see('Recipe Suggestions', 'h2');

        // Check if the recipe name (h3) is displayed
        $I->seeElement('h3');

        // Check for the placeholder text if the image is missing
        $I->see('Recipe Image Placeholder', 'span');

        // Verify that you are taken to the recipe display page
        // $I->seeInCurrentUrl('/display.php');
    }

    public function testSkipThreeTimesThenCheck(AcceptanceTester $I): void
    {
        // Navigate to the match page
        $I->amOnPage('/match.php');

        // Click the red X button three times to skip the recipes
        for ($i = 0; $i < 3; $i++) {
            $I->click('.bg-red-600');  // Using the background color as class for simplicity
            $I->wait(1); // Wait for the page to reload with a new recipe

            // Check if the "Recipe Suggestions" header is still there
            $I->see('Recipe Suggestions', 'h2');
        }

        // After skipping, check if the page still has the header
        $I->see('Recipe Suggestions', 'h2');

        // Verify that you are taken to the recipe display page
        // $I->seeInCurrentUrl('/display.php');
    }
}
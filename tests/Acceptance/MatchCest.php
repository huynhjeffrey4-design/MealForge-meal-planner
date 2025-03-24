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
        // Check for elements in the layout
        // Recipe Image
        $I->seeElement('img', ['alt' => 'Recipe Image']);
        $I->seeElement('span', ['class' => 'text-gray-500', 'text' => 'Recipe Image Placeholder']);
        // Recipe Name
        $I->seeElement('h3', ['class' => 'text-xl font-bold mb-4']);
        // Recipe Description
        $I->seeElement('p', ['class' => 'text-gray-600 text-sm mb-4']);
        // Recipe Tags
        $I->seeElement('.recipe-tags');
        // Recipe Total Time
        $I->seeElement('.prep-time');
        // Recipe Difficulty
        $I->seeElement('.difficulty');
        // Serves
        $I->seeElement('.serves');
        // Buttons (check if both the red X and green check buttons are present)
        $I->seeElement('button', ['class' => 'bg-red-600']);
        $I->seeElement('a', ['class' => 'bg-green-600']);
    }

    public function testCheckOnFirst(AcceptanceTester $I): void
    {
        // Navigate to the match page
        $I->amOnPage('/match.php');

        // Verify that the recipe details are displayed (image, name, description, difficulty, tags, time, serves)
        $I->seeElement('img', ['alt' => 'Recipe Image']);
        $I->see('Recipe Name', 'h3');
        $I->see('Description of recipe', 'p');
        $I->see('Difficulty: Easy', '.difficulty');
        $I->see('Vegetarian', '.recipe-tags');
        $I->see('30 mins', '.prep-time');
        $I->see('Serves 4', '.serves');

        // Click the green check button
        $I->click('.green-check');

        // Verify that you are taken to the recipe display page
        $I->seeInCurrentUrl('/display.php');
    }


    public function testSkipThreeTimesThenCheck(AcceptanceTester $I): void
    {
        // Navigate to the match page
        $I->amOnPage('/match.php');

        // Click the red X button three times to skip the recipes
        for ($i = 0; $i < 3; $i++) {
            $I->click('.red-x');
            $I->wait(1); // Wait for a new recipe to appear after each click

            // Verify that a new recipe name and image are displayed
            $I->seeElement('img', ['alt' => 'Recipe Image']);
            $I->seeElement('h3', ['class' => 'recipe-name']);
        }

        // After skipping 3 times, click the green check button
        $I->click('.green-check');

        // Verify that you are taken to the recipe display page
        $I->seeInCurrentUrl('/display.php');
    }

}
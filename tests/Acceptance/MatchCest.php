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
        $I->seeElement('img', ['alt' => 'Recipe Image']);
        $I->seeElement('h3', ['class' => 'recipe-name']);
        $I->seeElement('p', ['class' => 'recipe-description']);
        $I->seeElement('.recipe-tags');
        $I->seeElement('.prep-time');
        $I->seeElement('.serves');
        $I->seeElement('.difficulty');
        $I->seeElement('button', ['class' => 'red-x']);
        $I->seeElement('button', ['class' => 'green-check']);
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
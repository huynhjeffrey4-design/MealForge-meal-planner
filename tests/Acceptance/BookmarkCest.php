<?php

declare(strict_types=1);


namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

final class BookmarkCest
{
	/**
	 * Happy Path: Test bookmarking and unbookmarking a recipe
	 */
	public function bookmarkAndUnbookmarkRecipe(AcceptanceTester $I): void
	{
		// Login first
		$this->loginTestUser($I);

		$I->seeInCurrentUrl('recipe.php?id=1');
		$I->seeElement('i', ['data-lucide' => 'bookmark-plus']);

		// Click the bookmark button to bookmark the recipe
		$I->submitForm("#bookmark_form", []);
		$I->seeInCurrentUrl('recipe.php?id=1');

		$I->seeElement('i', ['data-lucide' => 'bookmark-check']);

		# unbookmark
		$I->submitForm("#bookmark_form", []);
		$I->seeInCurrentUrl('recipe.php?id=1');
		$I->seeElement('i', ['data-lucide' => 'bookmark-plus']);
	}

	/**
	 * Happy Path: If not logged in, get a message indicating they should log in.
	 */
	public function notLoggedIn(AcceptanceTester $I): void
	{
		$I->amOnPage('/recipe.php?id=1'); // Navigate to a recipe page
		$I->seeElement('i', ['data-lucide' => 'bookmark-plus']);

		$I->submitForm("#bookmark_form", []);

		$I->amOnPage('/recipe.php?id=1');
		$I->see("You must be logged in to bookmark a recipe", 'p');
		$I->click("Log in");
		$I->seeInCurrentUrl("/login.php");
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
	/**
	 * Test that recipes show up in dashboard/bookmarks
	 */
	public function bookmarksAppearInDashboardBookmarks(AcceptanceTester $I): void 
	{
		$this->loginTestUser($I);

		$I->amOnPage('/recipe.php?id=1');
		$I->seeElement('i', ['data-lucide' => 'bookmark-plus']);
		$I->submitForm("#bookmark_form", []);

		$I->amOnPage('/dashboard/bookmarks.php');
		$I->see('You have 1 bookmarked recipes');

		$I->see('Smoked salmon', 'a');
	}

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
		$I->amOnPage('/recipe.php?id=1'); // Navigate to a recipe page
	}



	/**
	 * Test user credentials
	 */
	private array $testUser = [
		'email' => 'test@email.com',
		'password' => 'password123'
	];
}

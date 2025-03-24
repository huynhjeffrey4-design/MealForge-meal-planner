<?php

declare(strict_types=1);


namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

final class BookmarkCest
{
    public function _before(AcceptanceTester $I): void
    {
    }

    public function loggedInBookmarkToggling(AcceptanceTester $I): void
    {
        $this->loginTestUser($I);
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

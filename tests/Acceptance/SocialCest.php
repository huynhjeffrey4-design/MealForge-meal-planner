<?php

declare(strict_types=1);
namespace Tests\Acceptance;
use Tests\Support\AcceptanceTester;

final class SocialCest
{
    public function socialPageLoggedOut(AcceptanceTester $I): void
    {
        $I->amOnPage('/social.php');
        $I->see('Recipe Social Feed');
        $I->see('Log in');
        $I->dontSee('Upload Post');
        $I->click('Log in');
        $I->seeCurrentUrlEquals('/login.php');
    }

    public function socialPageLoggedIn(AcceptanceTester $I): void
    {
        // NOTE: Precondition:
        //  1. Test-user is registered
        $email = 'test@email.com';
        $password = 'password123';

        // Log in
        $I->amOnPage('/login.php');
        $I->fillField('email', $email);
        $I->fillField('password', $password);
        $I->click('Continue');
        $I->seeCurrentUrlEquals('/profile.php');

        // Profile page
        $I->see('Social');
        $I->click('Social');
        $I->seeCurrentUrlEquals('/social.php');

        // Now logged in on social page, upload
        $I->see("Log out");
    }
}

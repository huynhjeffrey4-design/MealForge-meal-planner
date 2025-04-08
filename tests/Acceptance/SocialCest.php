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
}

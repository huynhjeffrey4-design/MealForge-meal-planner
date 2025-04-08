<?php

declare(strict_types=1);

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

final class IndexCest
{
    public function testIndex(AcceptanceTester $I): void
    {
        $I->amOnPage('/');
        $I->see('MealForge');
        $I->see('Personalized Recipe Recommendations');
        $I->see('Just for You');
    }

    public function testLinksToLogin(AcceptanceTester $I): void
    {
        $I->amOnPage('/');
        $I->click('Get Started');
        $I->seeCurrentUrlEquals('/login.php');

        $I->amOnPage('/');
        $I->click('Start Your Journey');
        $I->seeCurrentUrlEquals('/login.php');

        $I->amOnPage('/');
        $I->click('Get Started Now');
        $I->seeCurrentUrlEquals('/login.php');
    }
}

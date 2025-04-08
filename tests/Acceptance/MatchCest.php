<?php

declare(strict_types=1);

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

final class MatchCest
{
    public function _before(AcceptanceTester $I): void
    {
        $email = 'test@email.com';
        $password = 'password123';

        $I->amOnPage('/login.php');
        $I->fillField('email', $email);
        $I->fillField('password', $password);
        $I->click('Continue');
        $I->seeCurrentUrlEquals('/profile.php');
    }

    public function testPageLayout(AcceptanceTester $I): void
    {
        $I->amOnPage('/match.php');
        $I->see('Recipe Suggestions');
        $I->see('mins');
        $I->see('serves');
    }

    public function testSkipThreeTimesThenCheck(AcceptanceTester $I): void
    {
        $I->amOnPage('/match.php');
        $I->see('Recipe Suggestions');

        // Click the red X button three times to skip the recipes
        for ($i = 0; $i < 3; $i++) {
            $I->click('.bg-red-600');

            $I->amOnPage('/match.php');
            $I->see('Recipe Suggestions');
        }
    }
}

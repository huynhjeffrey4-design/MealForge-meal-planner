<?php

declare(strict_types=1);

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

final class LoginCest
{
    public function testSuccesfulLogin(AcceptanceTester $I): void
    {
        // NOTE: Precondition:
        //  1. Test-user is registered
        $email = 'test@email.com';
        $password = 'password123';

        $I->amOnPage('/login.php');
        $I->fillField('email', $email);
        $I->fillField('password', $password);
        $I->click('Continue');
        $I->seeInCurrentUrl('/profile.php');
    }

    public function testUnsuccesfulLogin(AcceptanceTester $I): void
    {
        // Arrange
        $email = 'invalid@invalid.com';
        $password = 'invalid';

        $I->amOnPage('/login.php');
        $I->fillField('email', $email);
        $I->fillField('password', $password);
        $I->click('Continue');
        $I->seeInCurrentUrl('/login.php');
        $I->see('Invalid email or password');
    }
}

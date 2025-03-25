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
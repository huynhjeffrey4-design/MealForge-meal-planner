# Testing Guide

This document outlines the requirements and process for writing and running tests using Codeception in our project.

## Overview

We use [Codeception](https://codeception.com/) as our testing framework. Codeception provides a unified testing experience with support for unit, integration, and acceptance testing.

## Test Types

### Unit Tests

Unit tests verify that individual components (classes, methods, functions) work correctly in isolation.

- Location: `tests/Unit/`
- Focus: Testing individual classes and methods
- No database or external service dependencies

### Acceptance Tests

Acceptance tests verify that the application works correctly from a user's perspective.

- Location: `tests/Acceptance/`
- Focus: Testing user flows and scenarios
- Simulates user interactions with the application
- Interaction with live database is available

## Writing Tests

### Test Structure

All test files should:
- Be named with a `Test` suffix (e.g., `UserControllerTest.php`)
- Extend the appropriate tester class (`UnitTester`, `IntegrationTester`, or `AcceptanceTester`)
- Include descriptive test method names that explain what is being tested

### Example Unit Test

```php
<?php

namespace Tests\Unit;

use App\Controllers\UserController;
use Tests\Support\UnitTester;

class UserControllerTest extends \Codeception\Test\Unit
{
    protected UnitTester $tester;
    
    protected function _before()
    {
        // Setup code runs before each test
    }

    protected function _after()
    {
        // Cleanup code runs after each test
    }

    public function testCreateUserValidatesInput()
    {
        $controller = new UserController();
        $result = $controller->validateUser('test@example.com', 'password123', 'John', 'Doe');
        $this->assertTrue($result);
    }
}
```

### Example Acceptance Test

```php
<?php

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

class LoginCest
{
    public function loginSuccessfully(AcceptanceTester $I)
    {
        $I->amOnPage('/login');
        $I->fillField('email', 'user@example.com');
        $I->fillField('password', 'password123');
        $I->click('Login');
        $I->seeCurrentUrlEquals('/dashboard');
        $I->see('Welcome back');
    }
}
```

## Running Tests

You can use `scripts/test.sh` to run all tests. Requires PHP server and mysql to be running,
which you can do via the docker-compose file in this repo: `docker compose up`.

### Running All Tests

```bash
vendor/bin/codecept run
```

### Running Specific Test Suites

```bash
vendor/bin/codecept run unit
vendor/bin/codecept run integration
vendor/bin/codecept run acceptance
```

### Running Individual Test Files

```bash
vendor/bin/codecept run tests/Unit/UserControllerTest.php
```

### Running Specific Test Methods

```bash
vendor/bin/codecept run tests/Unit/UserControllerTest.php:testCreateUserValidatesInput
```

## Test Environment

Tests should run in an isolated environment to prevent affecting production data:

1. Use a separate test database
2. Mock external services
3. Reset the database state between tests when necessary

## Continuous Integration

Tests are automatically run in our CI pipeline on every pull request and merge to main branches.

## Troubleshooting

If tests are failing:

1. Check that your local environment is properly configured
2. Ensure the test database is set up correctly
3. Verify that all dependencies are installed
4. Look for recent code changes that might have broken existing tests

## Resources

- [Codeception Documentation](https://codeception.com/docs)

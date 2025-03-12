<?php
declare(strict_types=1);

namespace Tests\Unit;
use Tests\Support\UnitTester;

require_once __DIR__ . '/../../controllers/user.php';

final class UserControllerCest
{
    private \UserController $controller;
    private \MockUserProvider $provider;

    public function _before(UnitTester $I): void
    {
        $this->provider = new \MockUserProvider();
        $this->controller = new \UserController($this->provider);
    }

    public function _after(UnitTester $I): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
          session_destroy();
        }
    }
    public function testSuccessfulLogin(UnitTester $T): void
    {
        // Arrange
        $email = 'test@example.com';
        $password = 'correct_password';
        $firstName = 'Test';
        $lastName = 'User';
        $result = $this->provider->createUser($email, $password, $firstName, $lastName);
        $T->assertNotFalse($result['success']);

        // Act
        $result = $this->controller->login($email, $password, false);

        // Assert
        $T->assertTrue($result['success']);
    }
    public function testCharacterEscape(UnitTester $T): void {
          // Escaped html characters
          $unescaped = '<script>alert("XSS")</script>@example.com';
		  $escaped_email = htmlspecialchars($unescaped);
          $password = 'password';
          $firstName = 'Secure';
          $lastName = 'Tester';
          $result = $this->provider->createUser($unescaped, $password, $firstName, $lastName);
          $T->assertNotFalse($result['success']);
          // Act
          $result = $this->controller->login($unescaped, $password, false);
          // Assert
          $T->assertTrue($result['success']);
		  $T->assertEquals($result['user']['email'], $escaped_email);
    }
    public function testFailedLoginWithIncorrectPassword(UnitTester $T): void
    {
        // Arrange
        $email = 'test@example.com';
        $this->provider->createUser($email, 'correct_password', 'Wrong', 'Password');

        // Act
        $result = $this->controller->login($email, 'wrong_password', false);

        // Assert
        $T->assertFalse($result['success']);
    }

    public function testFailedLoginWithNonexistentUser(UnitTester $T): void
    {
        // Act
        $result = $this->controller->login('nonexistent@example.com', 'any_password', false);

        // Assert
        $T->assertFalse($result['success']);
    }
}

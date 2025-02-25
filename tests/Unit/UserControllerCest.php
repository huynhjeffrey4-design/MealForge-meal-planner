<?php
declare(strict_types=1);

namespace Tests\Unit;
use Tests\Support\UnitTester;

require_once __DIR__ . '/../../controllers/control.php';
use App\Controllers\UserController;
use App\Controllers\MockUserProvider;

final class UserControllerCest
{
    private UserController $controller;
    private MockUserProvider $provider;
    public function _before(UnitTester $I): void
    {
        $this->provider = new MockUserProvider();
        $this->controller = new UserController($this->provider);
    }
    public function _after(UnitTester $I): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
          session_destroy();
        }
        if (isset($_SESSION['user_id'])) {
          unset($_SESSION['user_id']);
        }
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600);
        }
    }
    public function testSuccessfulLogin(UnitTester $T): void
    {
        // Arrange
        $email = 'test@example.com';
        $password = 'correct_password';
        $firstName = 'Test';
        $lastName = 'User';
        $userId = $this->provider->createUser($email, $password, $firstName, $lastName);
        $T->assertNotFalse($userId);
        // Act
        $result = $this->controller->login($email, $password, false);
        // Assert
        $T->assertTrue($result);
        $T->assertEquals($userId, $_SESSION['user_id']);
    }
    public function testCharacterEscape(UnitTester $T): void {
          // Escaped html characters
          $email = 'test@example.com';
          $unescaped = '<script>alert("XSS")</script>';
          $password = htmlspecialchars($unescaped);
          $firstName = 'Secure';
          $lastName = 'Tester';
          $userId = $this->provider->createUser($email, $password, $firstName, $lastName);
          $T->assertNotFalse($userId);
          // Act
          $result = $this->controller->login($email, $unescaped, false);
          // Assert
          $T->assertTrue($result);
          $T->assertEquals($userId, $_SESSION['user_id']);
    }
    public function testFailedLoginWithIncorrectPassword(UnitTester $T): void
    {
        // Arrange
        $email = 'test@example.com';
        $this->provider->createUser($email, 'correct_password', 'Wrong', 'Password');
        // Act
        $result = $this->controller->login($email, 'wrong_password', false);
        // Assert
        $T->assertFalse($result);
        $T->assertArrayNotHasKey('user_id', $_SESSION);
    }
    public function testFailedLoginWithNonexistentUser(UnitTester $T): void
    {
        // Act
        $result = $this->controller->login('nonexistent@example.com', 'any_password', false);
        // Assert
        $T->assertFalse($result);
        $T->assertArrayNotHasKey('user_id', $_SESSION);
    }
}

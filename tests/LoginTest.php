<?php
namespace Tests;
require_once __DIR__ . '/../control.php';

use PHPUnit\Framework\TestCase;
use App\Controllers\UserController;
use App\Controllers\MockUserProvider;

class LoginTest extends TestCase
{
    private UserController $controller;
    private MockUserProvider $provider;

    protected function setUp(): void
    {
        $this->provider = new MockUserProvider();
        $this->controller = new UserController($this->provider);
        session_start();
    }

    protected function tearDown(): void
    {
        $this->provider->reset();
        session_destroy();
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600);
        }
    }

    public function testSuccessfulLogin(): void
    {
        // Arrange
        $email = 'test@example.com';
        $password = 'correct_password';
        $userId = $this->provider->createUser($email, $password);
		$this->assertNotFalse($userId);

        // Act
        $result = $this->controller->login($email, $password, false);

        // Assert
        $this->assertTrue($result);
        $this->assertEquals($userId, $_SESSION['user_id']);
    }

	public function testCharacterEscape(): void {
		  // Escaped html characters
		  $email = 'test@example.com';
		  $unescaped = '<script>alert("XSS")</script>';
		  $password = htmlspecialchars($unescaped);
		  $userId = $this->provider->createUser($email, $password);
		  $this->assertNotFalse($userId);

		  // Act
		  $result = $this->controller->login($email, $unescaped, false);

		  // Assert
		  $this->assertTrue($result);
		  $this->assertEquals($userId, $_SESSION['user_id']);
	}

    public function testFailedLoginWithIncorrectPassword(): void
    {
        // Arrange
        $email = 'test@example.com';
        $this->provider->createUser($email, 'correct_password');

        // Act
        $result = $this->controller->login($email, 'wrong_password', false);

        // Assert
        $this->assertFalse($result);
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    public function testFailedLoginWithNonexistentUser(): void
    {
        // Act
        $result = $this->controller->login('nonexistent@example.com', 'any_password', false);

        // Assert
        $this->assertFalse($result);
        $this->assertArrayNotHasKey('user_id', $_SESSION);
    }

    /*public function testRememberMeFunctionality(): void*/
    /*{*/
    /*    // Arrange*/
    /*    $email = 'test@example.com';*/
    /*    $password = 'correct_password';*/
    /*    $this->provider->createUser($email, $password);*/
    /**/
    /*    // Act*/
    /*    $result = $this->controller->login($email, $password, true);*/
    /**/
    /*    // Assert*/
    /*    $this->assertTrue($result);*/
    /*    $this->assertArrayHasKey('remember_token', $_COOKIE);*/
    /*    $this->assertNotEmpty($_COOKIE['remember_token']);*/
    /*}*/

    public function testLoginWithEmptyCredentials(): void
    {
        // Act & Assert
        $this->assertFalse($this->controller->login('', '', false));
        $this->assertFalse($this->controller->login('test@example.com', '', false));
        $this->assertFalse($this->controller->login('', 'password', false));
    }
}


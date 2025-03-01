<?php
declare(strict_types=1);


namespace Tests\Unit;

require_once __DIR__ . '/../../controllers/control.php';
use App\Controllers\MysqliUserProvider;
use Tests\Support\UnitTester;

final class UserProviderCest
{
  private MysqliUserProvider $provider;
  public function _before(UnitTester $I): void
  {
	$_ENV['ENVIRONMENT']='test';
	$_ENV['DB_HOST']='127.0.0.1';
	$_ENV['DB_USERNAME']='root';
	$_ENV['DB_PASSWORD']='password';
	$_ENV['DB_NAME']='testdb' ;

	$this->provider = new MysqliUserProvider();
  }

	public function tryToCreateUser(UnitTester $T): void
{
    $email = 'test@example.com';
    $password = 'correct_password';
    $name_f = 'Testy';
    $name_l = 'Johnson';
    
    $userId = $this->provider->createUser($email, $password, $name_f, $name_l);
    $T->assertNotFalse($userId);
    
    // Option 1: Just verify other fields, not the actual hash value
    $T->seeInDatabase('users', [
        'email' => $email,
        'first_name' => $name_f,
        'last_name' => $name_l,
    ]);
    
    // Option 2: Query the database directly and verify the hash works
    $user = $T->grabFromDatabase('users', 'password_hash', ['email' => $email]);
    $T->assertTrue(password_verify($password, $user), 'Password hash verification should work');
}

public function tryToLoginWithValidCredentials(UnitTester $T): void
{
    // First create a user
    $email = 'login_test@email.com';
    $password = 'secure_password123';
    $name_f = 'Login';
    $name_l = 'Tester';
    
    $userId = $this->provider->createUser($email, $password, $name_f, $name_l);
    $T->assertNotFalse($userId, "Create a user");
    
    // Verify hash was stored correctly - optional but helps diagnose issues
    $storedHash = $T->grabFromDatabase('users', 'password_hash', ['email' => $email]);
    $T->assertTrue(password_verify($password, $storedHash), 
        "Hash in database matches our password");
    
    // Now try to login with correct credentials
    $loginResult = $this->provider->login($email, $password);
    
    // Assert that login was successful and returned the correct user ID
    $T->assertNotFalse($loginResult, "Log in with correct credentials");
    $T->assertEquals($userId, $loginResult, "Correct user ID returned");
	}
    
    public function tryToLoginWithInvalidCredentials(UnitTester $T): void
    {
        // First create a user
        $email = 'invalid_login_test@example.com';
        $password = 'correct_password456';
        $name_f = 'Invalid';
        $name_l = 'User';
        
        $userId = $this->provider->createUser($email, $password, $name_f, $name_l);
        $T->assertNotFalse($userId);
        
        // Test with incorrect password
        $loginResult = $this->provider->login($email, 'wrong_password');
        $T->assertFalse($loginResult, 'Login should fail with incorrect password');
        
        // Test with non-existent email
        $loginResult = $this->provider->login('nonexistent@example.com', $password);
        $T->assertFalse($loginResult, 'Login should fail with non-existent email');
    }
}

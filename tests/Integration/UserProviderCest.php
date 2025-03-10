<?php
declare(strict_types=1);
namespace Tests\Integration;

require_once __DIR__ . '/../../controllers/user.php';

use Tests\Support\IntegrationTester;

final class UserProviderCest
{
    private ?\RedBeanUserProvider $provider = null;
    private ?int $testUserId = null;
    private string $testEmail = 'test@example.com';
    private array $createdUserIds = []; // Array to store all created user IDs
    
    public function _before(IntegrationTester $I): void
    {
        $config = ([
            'host' => '127.0.0.1'
        ]);
        $this->provider = new \RedBeanUserProvider($config);

        $this->provider->deleteUserByEmail($this->testEmail);
    }
    
    public function _after(IntegrationTester $I): void
    {
        // Delete all users created during tests
        foreach ($this->createdUserIds as $userId) {
            $this->provider->deleteUser($userId);
        }
        
        // Reset the array
        $this->createdUserIds = [];
        
        // Also delete by email as a fallback
        $this->provider->deleteUserByEmail($this->testEmail);
    }
    
    // Helper method to create a test user for other tests
    private function createTestUser(IntegrationTester $I): array
    {
        $email = $this->testEmail;
        $password = 'correct_password';
        $name_f = 'Testy';
        $name_l = 'Johnson';
        
        $result = $this->provider->createUser($email, $password, $name_f, $name_l);
        if ($result['success']) {
            $this->testUserId = (int)$result['user']['id'];
            $this->createdUserIds[] = $this->testUserId; // Store the created user ID
        }
        
        return $result;
    }
    
    public function tryToCreateUser(IntegrationTester $I): void
    {
        $email = $this->testEmail;
        $password = 'correct_password';
        $name_f = 'Testy';
        $name_l = 'Johnson';
        
        $result = $this->provider->createUser($email, $password, $name_f, $name_l);
        
        $I->assertNotFalse($result['success'], 'User creation should be successful');
        
        // Option 1: Just verify other fields, not the actual hash value
        $I->seeInDatabase('user', [
            'email' => $email,
            'first_name' => $name_f,
            'last_name' => $name_l,
        ]);
        
        // Option 2: Query the database directly and verify the hash works
        $hash = $I->grabFromDatabase('user', 'password_hash', ['email' => $email]);
        $I->assertTrue(password_verify($password, $hash), 'Password hash verification should work');
        
        // Store user ID for cleanup
        $this->testUserId = (int)$result['user']['id'];
        $this->createdUserIds[] = $this->testUserId; // Store the created user ID
    }
    
    public function tryToCreateDuplicateUser(IntegrationTester $I): void
    {
        // First, create a user
        $result = $this->createTestUser($I);
        $I->assertTrue($result['success'], 'Initial user creation should succeed');
        
        // Try to create another user with the same email
        $duplicateResult = $this->provider->createUser(
            $this->testEmail, 
            'different_password', 
            'Different', 
            'Person'
        );
        
        // This should fail due to database constraint (unique email)
        $I->assertFalse($duplicateResult['success'], 'Duplicate user creation should fail');
        $I->assertStringContainsString('Email already in use', $duplicateResult['validation']->getError('email'));
    }
    
    public function tryToGetUserByEmail(IntegrationTester $I): void
    {
        // Create a test user first
        $result = $this->createTestUser($I);
        $I->assertTrue($result['success'], 'User creation should succeed');
        
        // Try to get the user by email
        $user = $this->provider->getUserByEmail($this->testEmail);
        
        $I->assertNotFalse($user, 'Should retrieve a user');
        $I->assertEquals($this->testEmail, $user['email'], 'Email should match');
        $I->assertEquals('Testy', $user['first_name'], 'First name should match');
        $I->assertEquals('Johnson', $user['last_name'], 'Last name should match');
    }
    
    public function tryToGetNonExistentUserByEmail(IntegrationTester $I): void
    {
        $nonExistentEmail = 'nonexistent@example.com';
        $user = $this->provider->getUserByEmail($nonExistentEmail);
        
        $I->assertFalse($user, 'Should return false for non-existent user');
    }
    
    public function tryToGetUserById(IntegrationTester $I): void
    {
        // Create a test user first
        $result = $this->createTestUser($I);
        $I->assertTrue($result['success'], 'User creation should succeed');
        
        // Try to get the user by ID
        $user = $this->provider->getUserById($this->testUserId);
        
        $I->assertNotFalse($user, 'Should retrieve a user');
        $I->assertEquals($this->testUserId, (int)$user['id'], 'ID should match');
        $I->assertEquals($this->testEmail, $user['email'], 'Email should match');
        $I->assertEquals('Testy', $user['first_name'], 'First name should match');
        $I->assertEquals('Johnson', $user['last_name'], 'Last name should match');
    }
    
    public function tryToGetNonExistentUserById(IntegrationTester $I): void
    {
        $nonExistentId = 99999; // Assuming this ID doesn't exist
        $user = $this->provider->getUserById($nonExistentId);
        
        $I->assertFalse($user, 'Should return false for non-existent user ID');
    }
    
    public function tryToUpdateUser(IntegrationTester $I): void
    {
        // Create a test user first
        $result = $this->createTestUser($I);
        $I->assertTrue($result['success'], 'User creation should succeed');
        
        // Update user data
        $newData = [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'phone_number' => '555-1234'
        ];
        
        $updateResult = $this->provider->updateUser($this->testUserId, $newData);
        
        $I->assertTrue($updateResult, 'User update should succeed');
        
        // Verify the update in the database
        $I->seeInDatabase('user', [
            'id' => $this->testUserId,
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'phone_number' => '555-1234'
        ]);
        
        // Also check with the getUserById method
        $updatedUser = $this->provider->getUserById($this->testUserId);
        $I->assertEquals('Updated', $updatedUser['first_name'], 'First name should be updated');
        $I->assertEquals('Name', $updatedUser['last_name'], 'Last name should be updated');
        $I->assertEquals('555-1234', $updatedUser['phone_number'], 'Phone number should be updated');
    }
    
    public function tryToUpdatePassword(IntegrationTester $I): void
    {
        // Create a test user first
        $result = $this->createTestUser($I);
        $I->assertTrue($result['success'], 'User creation should succeed');
        
        // Update password
        $newPassword = 'new_secure_password';
        $updateResult = $this->provider->updateUser($this->testUserId, ['password' => $newPassword]);
        
        $I->assertTrue($updateResult, 'Password update should succeed');
        
        // Verify the password hash has changed and can be verified
        $newHash = $I->grabFromDatabase('user', 'password_hash', ['id' => $this->testUserId]);
        $I->assertTrue(password_verify($newPassword, $newHash), 'New password should verify correctly');
        
        // The old password should no longer work
        $I->assertFalse(password_verify('correct_password', $newHash), 'Old password should no longer work');
    }
    
    public function tryToUpdateNonExistentUser(IntegrationTester $I): void
    {
        $nonExistentId = 99999; // Assuming this ID doesn't exist
        $updateResult = $this->provider->updateUser($nonExistentId, ['first_name' => 'Nobody']);
        
        $I->assertFalse($updateResult, 'Update for non-existent user should fail');
    }
    
    public function tryToDeleteUser(IntegrationTester $I): void
    {
        // Create a test user first
        $result = $this->createTestUser($I);
        $I->assertTrue($result['success'], 'User creation should succeed');
        
        // Store the ID to be deleted
        $userIdToDelete = $this->testUserId;
        
        // Delete the user
        $deleteResult = $this->provider->deleteUser($userIdToDelete);
        
        $I->assertTrue($deleteResult, 'User deletion should succeed');
        
        // Verify the user no longer exists
        $I->dontSeeInDatabase('user', ['id' => $userIdToDelete]);
        
        // Also check with getUserById
        $deletedUser = $this->provider->getUserById($userIdToDelete);
        $I->assertFalse($deletedUser, 'Deleted user should not be retrievable');
        
        // Remove the ID from our tracking array since we manually deleted it
        $this->createdUserIds = array_diff($this->createdUserIds, [$userIdToDelete]);
        
        // Unset testUserId since we've deleted the user
        unset($this->testUserId);
    }
    
    public function tryToDeleteNonExistentUser(IntegrationTester $I): void
    {
        $nonExistentId = 99999; // Assuming this ID doesn't exist
        $deleteResult = $this->provider->deleteUser($nonExistentId);
        
        $I->assertFalse($deleteResult, 'Deletion of non-existent user should fail');
    }
    
    public function tryEdgeCaseWithSpecialCharacters(IntegrationTester $I): void
    {
        // Test with email containing special characters (but still valid)
        $email = 'test.special+chars@example.com';
        $password = 'secure_password';
        $firstName = 'Tést'; // Non-ASCII character
        $lastName = "O'Connor"; // Apostrophe
        
        // Create user with special characters
        $result = $this->provider->createUser($email, $password, $firstName, $lastName);
        
        $I->assertTrue($result['success'], 'User with special characters should be created');
        
        // Store the user ID for cleanup
        $userId = (int)$result['user']['id'];
        $this->createdUserIds[] = $userId;
        
        // Verify retrieval
        $user = $this->provider->getUserByEmail($email);
        $I->assertNotFalse($user, 'User with special characters should be retrievable');
        
        // Check if HTML entities are properly handled (htmlspecialchars is called on email)
        $I->assertEquals($email, $user['email'], 'Email with special characters should be retrieved correctly');
        
        // Also delete this specific email in after()
        $this->provider->deleteUserByEmail($email);
    }
    
    public function trySecurityEdgeCases(IntegrationTester $I): void
    {
        // Test with potential SQL injection in the email
        $email = "test'); DROP TABLE user; --";
        $password = 'secure_password';
        $firstName = 'Secure';
        $lastName = 'Test';
        
        // This should fail safely without executing the SQL injection
        $result = $this->provider->createUser($email, $password, $firstName, $lastName);
        
        // We expect it to either fail validation or create the user with the literal string
        if ($result['success']) {
            $userId = (int)$result['user']['id'];
            $this->createdUserIds[] = $userId;
            
            // Table should still exist
            $I->seeInDatabase('user', ['id' => $userId]);
        }
        
        // Verify that the user table still exists (the DROP TABLE command didn't execute)
        $userCount = $I->grabNumRecords('user');
        $I->assertGreaterThan(0, $userCount, 'User table should still exist and contain records');
        
        // Also delete this specific email in after()
        $this->provider->deleteUserByEmail($email);
    }
}

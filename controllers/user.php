<?php

require_once __DIR__ . '/../setup.php';
require_once __DIR__ . '/../SetupRedbean.php';

require_once __DIR__ . '/valdiation.php';

class UserController
{
	private UserDataProviderInterface $provider;

	public function __construct(UserDataProviderInterface $provider)
	{
		$this->provider = $provider;

		// Create test accounts if they don't exist
		$test_accounts = [
			[
				'email' => 'test@email.com',
				'password' => 'password123',
				'firstName' => 'Testy',
				'lastName' => 'Johnson'
			],
		];

		foreach ($test_accounts as $account) {
			$existingUser = $this->provider->getUserByEmail($account['email']);
			if (!$existingUser) {
				$this->provider->createUser($account['email'], $account['password'], $account['firstName'], $account['lastName']);
			}
		}
	}

	/**
	 * Create a new user
	 * @param string $email
	 * @param string $password
	 * @param string $firstName
	 * @param string $lastName
	 * @return array Result with success status
	 */
	public function createUser(string $email, string $password, string $firstName, string $lastName): array
	{
		$validation = $this->validateUser($email, $password, $firstName, $lastName);
		if ($validation->hasErrors()) {
			return [
				'success' => false,
				'validation' => $validation
			];
		}

		$result = $this->provider->createUser($email, $password, $firstName, $lastName);

		if (!$result['success']) {
			$validation->addError('general', 'Failed to create user: ' . $result['validation']->getError('general'));
			return ['success' => false, 'validation' => $validation];
		}

		return ['success' => true, 'user' => $result['user']];
	}

	/**
	 * Delete user
	 * @param int $userId
	 * @return bool Success status
	 */
	public function deleteUser(int $userId): bool
	{
		return $this->provider->deleteUser($userId);
	}

	/**
	 * Login user
	 * @param string $email
	 * @param string $password
	 * @param bool $remember
	 * @return array Result with success status
	 */
	public function login(string $email, string $password, bool $remember): array
	{
		$user = $this->provider->getUserByEmail($email);

		if (!$user || !password_verify($password, $user['password_hash'])) {
			$validation = new ValidationResult();
			return ['success' => false, 'validation' => $validation->addError('general', 'Invalid email or password')];
		}

		return ['success' => true, 'user' => $user];
	}
	
	/**
	 * Reset user password
	 * @param string $email Email of the user
	 * @param string $newPassword New password to set
	 * @return array Result with success status
	 */
	public function resetPassword(string $email, string $newPassword): array
	{
		$validation = new ValidationResult();
		
		// Validate password
		$passwordError = $this->validatePassword($newPassword);
		if ($passwordError !== false) {
			$validation->addError('password', $passwordError);
			return ['success' => false, 'validation' => $validation];
		}
		
		// Get user by email
		$user = $this->provider->getUserByEmail($email);
		if (!$user) {
			$validation->addError('general', 'User not found');
			return ['success' => false, 'validation' => $validation];
		}
		
		// Update user password
		$userData = ['password' => $newPassword];
		$result = $this->provider->updateUser($user['id'], $userData);
		
		if (!$result) {
			$validation->addError('general', 'Failed to update password');
			return ['success' => false, 'validation' => $validation];
		}
		
		return ['success' => true];
	}

	/**
	 * Get user by ID
	 * @param int $userId
	 * @return array|false User data or false if not found
	 */
	public function getUserById(int $userId): array|false
	{
		return $this->provider->getUserById($userId);
	}

	/**
	 * Update user
	 * @param int $userId
	 * @param array $userData
	 * @return bool Success status
	 */
	public function updateUser(int $userId, array $userData): bool
	{
		$validation = new ValidationResult();

		if (isset($userData['email'])) {
			$emailError = $this->validateEmail($userData['email']);
			if ($emailError !== false) {
				$validation->addError('email', $emailError);
			}
		}

		if (isset($userData['password'])) {
			$passwordError = $this->validatePassword($userData['password']);
			if ($passwordError !== false) {
				$validation->addError('password', $passwordError);
			}
		}

		if (isset($userData['first_name'])) {
			$nameError = $this->validateName($userData['first_name']);
			if ($nameError !== false) {
				$validation->addError('first_name', $nameError);
			}
		}

		if (isset($userData['last_name'])) {
			$nameError = $this->validateName($userData['last_name']);
			if ($nameError !== false) {
				$validation->addError('last_name', $nameError);
			}
		}

		if ($validation->hasErrors()) {
			// TODO: Return and handle validation errors
			return false;
		}

		return $this->provider->updateUser($userId, $userData);
	}

	/**
	 * Validate user data
	 * @param string $email
	 * @param string $password
	 * @param string $firstName
	 * @param string $lastName
	 * @return ValidationResult
	 */
	public function validateUser(string $email, string $password, string $firstName, string $lastName): ValidationResult
	{
		$validation = new ValidationResult();
		$fields = ['email', 'password', 'firstName', 'lastName'];
		foreach ($fields as $field) {
			$error = $this->fieldErrors($field, $$field);
			if ($error !== false) {
				$validation->addError($field, $error);
			}
		}
		return $validation;
	}

	/**
	 * Validates a given field, returning false if it is valid, and an error message if it is not
	 * @param string $fieldName One of 'email', 'password', 'firstName', 'lastName'
	 * @param string $fieldValue The value to validate
	 * @return string|false Error message if invalid, false if valid
	 */
	public function fieldErrors(string $fieldName, string $fieldValue): string|false
	{
		if ($fieldName == 'email') {
			return $this->validateEmail($fieldValue);
		} else if ($fieldName == 'password') {
			return $this->validatePassword($fieldValue);
		} else if ($fieldName == 'firstName' || $fieldName == 'lastName') {
			return $this->validateName($fieldValue);
		} else {
			return "Unrecognized field name";
		}
	}

	/**
	 * Validate email
	 * @param string $email
	 * @return string|false Error message if invalid, false if valid
	 */
	private function validateEmail(string $email): string|false
	{
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return 'Invalid email address';
		}
		return false;
	}

	/**
	 * Validate password
	 * @param string $password
	 * @return string|false Error message if invalid, false if valid
	 */
	private function validatePassword(string $password): string|false
	{
		if (strlen($password) < 8) {
			return 'Password must be at least 8 characters';
		}
		return false;
	}

	/**
	 * Validate name
	 * @param string $name
	 * @return string|false Error message if invalid, false if valid
	 */
	private function validateName(string $name): string|false
	{
		if (strlen($name) < 2) {
			return 'Name must be at least 2 characters';
		}

		if (!preg_match('/^[a-zA-Z]+$/', $name)) {
			return 'Name must contain only letters';
		}

		return false;
	}
}

/**
 * Generate a remember token
 * @return string Token
 */
function generateRememberToken(): string
{
	return bin2hex(random_bytes(32));
}

interface UserDataProviderInterface
{
	// User Methods
	public function createUser(string $email, string $password, string $firstName, string $lastName): array;
	public function deleteUser(int $userId): bool;
	public function getUserByEmail(string $email): array|false;
	public function getUserById(int $userId): array|false;
	public function updateUser(int $userId, array $userData): bool;
}

function getUserController(): UserController
{
	$provider_t = env('PROVIDER_USER', '');
	$provider =  $provider_t === 'mock' ? new MockUserProvider() : new RedBeanUserProvider();
	return new UserController($provider);
}

class RedBeanUserProvider implements UserDataProviderInterface
{
	public function __construct(array $config = [])
	{
		$dbConnection = DatabaseConnection::getInstance();
		$dbConnection->setup($config);
	}

	/**
	 * Create a new user
	 */
	public function createUser(string $email, string $password, string $firstName, string $lastName): array
	{
	  // TODO: This should be a database invariant rather than provider responsibility
	  if ($this->getUserByEmail($email)) {
		$validation = new ValidationResult();
		return ['success' => false, 'validation' => $validation->addError('email', 'Email already in use')];
	  }

		try {
			$user = \R::dispense('user');
			$user->email = $email;
			$user->password_hash = password_hash($password, PASSWORD_DEFAULT);
			$user->first_name = $firstName;
			$user->last_name = $lastName;

			// Initialize profile fields with null values
			$user->date_of_birth = null;
			$user->gender = null;
			$user->phone_number = null;
			$user->profile_picture = 'prof_pics/default_avatar.png';
			$user->dietary_restrictions = null;
			$user->dietary_preferences = null;


			// Store and get ID
			$userId = \R::store($user);

			$user = \R::load('user', $userId);

			return ['success' => true, 'user' => $user];
		} catch (\Exception $e) {
			$validation = new ValidationResult();
			$validation->addError('general', 'Failed to create user in database: ' . $e->getMessage());
			return ['success' => false, 'validation' => $validation];
		}
	}

	/**
	 * Get user by email
	 */
	public function getUserByEmail(string $email): array|false
	{
		try {
			$user = \R::findOne('user', ' email = ? ', [$email]);
			if ($user === null) {
				return false;
			}

			$userData = $user->export();

			// Sanitize output
			$userData['email'] = htmlspecialchars($userData['email']);

			return $userData;
		} catch (\Exception $e) {
			// Log error if needed; assume user not found for now
			return false;
		}
	}

	/**
	 * Get user by ID
	 */
	public function getUserById(int $userId): array|false
	{
		try {
			$user = \R::load('user', $userId);

			if ($user->id === 0) {
				return false;
			}

			$userData = $user->export();

			// Sanitize output
			$userData['email'] = htmlspecialchars($userData['email']);

			return $userData;
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * Delete user
	 */
	public function deleteUser(int $userId): bool
	{
		try {
			// Find the user bean
			$user = \R::load('user', $userId);

			// Check if the user exists (ID will be 0 if not found)
			if ($user->id === 0) {
				return false;
			}

			// Delete the user
			\R::trash($user);
			return true;
		} catch (\Exception $e) {
			// Log error if needed
			return false;
		}
	}

	public function deleteUserByEmail(string $email): bool
	{
		try {
			// Find the user bean
			$user = \R::findOne('user', ' email = ? ', [$email]);
			// Check if the user exists (ID will be 0 if not found)
			if ($user === null) {
				return false;
			}
			// Delete the user
			\R::trash($user);
			return true;
		} catch (\Exception $e) {
			// Log error if needed
			return false;
		}
	}

	/**
	 * Update user
	 */
	public function updateUser(int $userId, array $userData): bool
	{
		try {
			// Get user
			$user = \R::load('user', $userId);

			if ($user->id === 0) {
				return false;
			}

			// Update user fields
			foreach ($userData as $key => $value) {
				// Special handling for password
				if ($key === 'password') {
					$user->password_hash = password_hash($value, PASSWORD_DEFAULT);
				} else {
					$user->$key = $value;
				}
			}

			\R::store($user);
			return true;
		} catch (\Exception $e) {
			return false;
		}
	}
}

class MockUserProvider implements UserDataProviderInterface
{
    // Change to static properties to persist data between instantiations
    private static array $users = [];
    private static int $nextId = 1;
    private static array $deletedUserIds = [];

    public function createUser(string $email, string $password, string $firstName, string $lastName): array
    {
        $validation = new ValidationResult();
        // Simulate email uniqueness check
        foreach (self::$users as $user) {
            if ($user['email'] === $email) {
                $validation->addError('email', 'Email already in use');
                return ['success' => false, 'validation' => $validation];
            }
        }

        $userId = self::$nextId++;
        self::$users[$userId] = [
            'id' => $userId,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'first_name' => $firstName,
            'last_name' => $lastName,
            // Include profile fields
            'date_of_birth' => null,
            'gender' => null,
            'phone_number' => null,
            'profile_picture' => null,
            'dietary_restrictions' => null,
            'dietary_preferences' => null,

			// Relation
			'sharedRecipeList' => []
        ];
        return ['success' => true, 'user' => self::$users[$userId]];
    }

    public function deleteUser(int $userId): bool
    {
        if (!isset(self::$users[$userId])) {
            return false;
        }
        unset(self::$users[$userId]);
        self::$deletedUserIds[] = $userId;
        return true;
    }

    public function getUserByEmail(string $email): array|false
    {
        foreach (self::$users as $userId => $user) {
            if ($user['email'] === $email) {
                $userData = $user;
                $userData['email'] = htmlspecialchars($userData['email']);
                return $userData;
            }
        }
        return false;
    }

    public function getUserById(int $userId): array|false
    {
        if (!isset(self::$users[$userId])) {
            return false;
        }
        $userData = self::$users[$userId];
        $userData['email'] = htmlspecialchars($userData['email']);
        return $userData;
    }

    public function updateUser(int $userId, array $userData): bool
    {
        if (!isset(self::$users[$userId])) {
            return false;
        }
        foreach ($userData as $key => $value) {
            // Special handling for password
            if ($key === 'password') {
                self::$users[$userId]['password_hash'] = password_hash($value, PASSWORD_DEFAULT);
            } else {
                self::$users[$userId][$key] = $value;
            }
        }
        return true;
    }

    // Helper methods for testing
    public function getAllUsers(): array
    {
        return self::$users;
    }

    public function getDeletedUserIds(): array
    {
        return self::$deletedUserIds;
    }

    public function reset(): void
    {
        self::$users = [];
        self::$nextId = 1;
        self::$deletedUserIds = [];
    }
}

<?php
namespace App\Controllers;

class UserController {
    private UserDataProviderInterface $provider;
    
    public function __construct(UserDataProviderInterface $provider) {
        $this->provider = $provider;
    }
    
    /**
     * Create a new user
     * @param string $email
     * @param string $password
     * @return int|false User ID if successful, false otherwise
     */
    public function createUser(string $email, string $password): int|false {
        return $this->provider->createUser($email, $password);
    }
    
    /**
     * Delete user
     * @param int $userId
     * @return bool Success status
     */
    public function deleteUser(int $userId): bool {
        return $this->provider->deleteUser($userId);
    }

	/**
	 * Login user
	 * @param string $email
	 * @param string $password
	 * @return bool User ID if successful, false otherwise
	 */
	public function login(string $email, string $password, bool $remember): bool {
		$email = htmlspecialchars($email);
		$password = htmlspecialchars($password);

		$login_res =  $this->provider->login($email, $password);
		if ($login_res !== false) {
			session_start();
			$_SESSION['user_id'] = $login_res;

			if ($remember) {
				// Set remember-me cookie (30 days)
				setcookie('remember_token', 
						 generateRememberToken(), 
						 time() + (86400 * 30), 
						 '/', 
						 '', 
						 true, // Secure
						 true  // HttpOnly
				);
			}
		}
		return $login_res;
	}
}

interface UserDataProviderInterface {
    public function createUser(string $email, string $password): int|false;
    public function deleteUser(int $userId): bool;
    public function login(string $email, string $password): int|false;
}

function getUserController(): UserController {
	return new UserController(getUserProvider());
}

function getUserProvider(): UserDataProviderInterface {
  $env = env('ENVIRONMENT', 'test');
  return $env == 'test' ? new MockUserProvider() : new MysqliUserProvider();
}


class MysqliUserProvider implements UserDataProviderInterface {
    private \mysqli $mysqli;
    
    public function __construct() {
        $this->mysqli = new \mysqli(
env('DB_HOST', 'localhost'),
		  env('DB_USERNAME', 'username'),
		  env('DB_PASSWORD', 'password'),
		  env('DB_DATABASE', 'your_database')
	  );
    }
    
    public function createUser(string $email, string $password): int|false {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $this->mysqli->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $email, $hashedPassword);
            
            $success = $stmt->execute();
            
            if ($success) {
                $userId = $this->mysqli->insert_id;
                $stmt->close();
                return (int) $userId;
            }
            
            $stmt->close();
            return false;
        } catch (\mysqli_sql_exception $e) {
            // Log error here if needed
            return false;
        }
    }
    
    public function deleteUser(int $userId): bool {
        try {
            $stmt = $this->mysqli->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            
            $success = $stmt->execute();
            $stmt->close();
            
            return $success;
        } catch (\mysqli_sql_exception $e) {
            // Log error here if needed
            return false;
        }
    }
    
    public function login(string $email, string $password): int|false {
        try {
            $stmt = $this->mysqli->prepare("SELECT id, password FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                $stmt->close();
                return false;
            }
            
            $user = $result->fetch_assoc();
            $stmt->close();
            
            if (password_verify($password, $user['password'])) {
                return (int) $user['id'];
            }
            
            return false;
        } catch (\mysqli_sql_exception $e) {
            // Log error here if needed
            return false;
        }
    }
}

class MockUserProvider implements UserDataProviderInterface 
{
	private array $users = [];
    private int $nextId = 1;
    private array $deletedUserIds = [];

	public function __construct() {
	  $this->createUser('peter@email.com', 'asdf');
	}
    
    public function createUser(string $email, string $password): int|false 
    {
        // Simulate email uniqueness check
        foreach ($this->users as $user) {
            if ($user['email'] === $email) {
                return false;
            }
        }
        
        // Create new user
        $userId = $this->nextId++;
        $this->users[$userId] = [
            'id' => $userId,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ];
        
        return $userId;
    }
    
    public function deleteUser(int $userId): bool 
    {
        if (!isset($this->users[$userId])) {
            return false;
        }
        
        unset($this->users[$userId]);
        $this->deletedUserIds[] = $userId;
        return true;
    }
    
    public function login(string $email, string $password): int|false 
    {
        foreach ($this->users as $user) {
            if ($user['email'] === $email && password_verify($password, $user['password'])) {
                return (int) $user['id'];
            }
        }
        
        return false;
    }
    
    // Helper methods for testing
    public function getUserById(int $userId): ?array 
    {
        return $this->users[$userId] ?? null;
    }
    
    public function getAllUsers(): array 
    {
        return $this->users;
    }
    
    public function getDeletedUserIds(): array 
    {
        return $this->deletedUserIds;
    }
    
    public function reset(): void 
    {
        $this->users = [];
        $this->nextId = 1;
        $this->deletedUserIds = [];
    }
}

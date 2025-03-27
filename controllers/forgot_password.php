<?php
require_once __DIR__ . '/../SetupRedbean.php';

class ForgotPasswordController {
  private $forgotPasswordProvider;

  public function __construct() {
  		$this->forgotPasswordProvider = new RedBeanForgotPasswordProvider();
  	}

    /**
	 * Looks up user by email, inserts reset token for user_id, and sends email with reset link.
     * @return array<bool>
     */
    public function forgotPassword(string $email): array {
        // Look up the user by email
        $user = \R::findOne('user', 'email = ?', [$email]);
        
        // Always return success even if user not found (security best practice)
        if (!$user) {
            return [
                'success' => true,
            ];
        }
        
        $userId = $user->id;
        $token = bin2hex(random_bytes(32));
        $this->forgotPasswordProvider->insertForgotPasswordToken($userId, $token);

        $this->sendEmail($email, $userId, $token);

        return [
            'token' => $token,
            'success' => true,
        ];
	}

	private function sendEmail(string $email, int $userId, string $token): void {
		$subject = 'Reset your password';
		$message = "Click the link below to reset your password:\n\n";
		$message .= "http://localhost/reset_password.php?user_id=$userId&token=$token";
		$headers = "From: webmaster@example.com";
		mail($email, $subject, $message, $headers);
	}

	/**
	 * Verifies if the provided token is valid for the given user_id
	 * Checks if the token matches the most recently requested token
	 * 
	 * @param int $userId The user ID to verify
	 * @param string $token The token to verify
	 * @return bool True if the token is valid, false otherwise
	 */
	public function verifyToken(int $userId, string $token): bool {
		$latestToken = $this->forgotPasswordProvider->getLatestToken($userId);
		return $latestToken === $token;
	}
	
	/**
	 * Invalidates a token after it has been used
	 * 
	 * @param int $userId The user ID
	 * @param string $token The token to invalidate
	 * @return bool True if successful, false otherwise
	 */
	public function invalidateToken(int $userId, string $token): bool {
		return $this->forgotPasswordProvider->invalidateToken($userId, $token);
	}
    
    /**
     * Gets user email by user ID
     * 
     * @param int $userId The user ID
     * @return string|null The email address or null if not found
     */
    public function getUserEmail(int $userId): ?string {
        $user = \R::load('user', $userId);
        return $user->id ? $user->email : null;
    }
}

class RedBeanForgotPasswordProvider {
    /**
     * @param array<int,mixed> $config
     */
    public function __construct(array $config = [])
    {
        $dbConnection = DatabaseConnection::getInstance();
        $dbConnection->setup($config);
    }

	public function insertForgotPasswordToken(int $userId, string $token): void
	{
		$resetToken = \R::dispense('forgot');
		$resetToken->user_id = $userId;
		$resetToken->token = $token;
		$resetToken->created_at = date('Y-m-d H:i:s');
		\R::store($resetToken);
	}

	public function getLatestToken(int $userId): ?string
	{
		$token = \R::findOne('forgot', 'user_id = ? ORDER BY created_at DESC LIMIT 1', [$userId]);
		return $token ? $token->token : null;
	}
	
	/**
	 * Invalidates a token by removing it from the database
	 * 
	 * @param int $userId The user ID
	 * @param string $token The token to invalidate
	 * @return bool True if successful, false otherwise
	 */
	public function invalidateToken(int $userId, string $token): bool
	{
		try {
			$tokenBean = \R::findOne('forgot', 'user_id = ? AND token = ?', [$userId, $token]);
			if ($tokenBean) {
				\R::trash($tokenBean);
				return true;
			}
			return false;
		} catch (\Exception $e) {
			return false;
		}
	}
}

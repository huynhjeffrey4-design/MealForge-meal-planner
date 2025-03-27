<?php
require_once __DIR__ . '/../SetupRedbean.php';

class ForgotPasswordController {
  private $forgotPasswordProvider;

  public function __construct() {
  		$this->forgotPasswordProvider = new RedBeanForgotPasswordProvider();
  	}

    /**
	 * Inserts reset token for given email, and sends email to user with reset link.
     * @return array<bool>
     */
    public function forgotPassword(string $email): array {
  		$token = bin2hex(random_bytes(32));
  		$this->forgotPasswordProvider->insertForgotPasswordToken($email, $token);

		$this->sendEmail($email, $token);

  		return [
			'token' => $token,
  			'success' => true,
  		];
	}

	private function sendEmail(string $email, string $token): void {
		$subject = 'Reset your password';
		$message = "Click the link below to reset your password:\n\n";
		$message .= "http://localhost/reset_password.php?email=$email&token=$token";
		$headers = "From: webmaster@example.com";
		mail($email, $subject, $message, $headers);
	}

	/**
	 * Verifies if the provided token is valid for the given email
	 * Checks if the token matches the most recently requested token
	 * 
	 * @param string $email The email address to verify
	 * @param string $token The token to verify
	 * @return bool True if the token is valid, false otherwise
	 */
	public function verifyToken(string $email, string $token): bool {
		$latestToken = $this->forgotPasswordProvider->getLatestToken($email);
		return $latestToken === $token;
	}
	
	/**
	 * Invalidates a token after it has been used
	 * 
	 * @param string $email The email address
	 * @param string $token The token to invalidate
	 * @return bool True if successful, false otherwise
	 */
	public function invalidateToken(string $email, string $token): bool {
		return $this->forgotPasswordProvider->invalidateToken($email, $token);
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

	public function insertForgotPasswordToken(string $email, string $token): void
	{
		$resetToken = \R::dispense('forgot');
		$resetToken->email = $email;
		$resetToken->token = $token;
		$resetToken->created_at = date('Y-m-d H:i:s');
		\R::store($resetToken);
	}

	public function getLatestToken(string $email): ?string
	{
		$token = \R::findOne('forgot', 'email = ? ORDER BY created_at DESC LIMIT 1', [$email]);
		return $token ? $token->token : null;
	}
	
	/**
	 * Invalidates a token by removing it from the database
	 * 
	 * @param string $email The email address
	 * @param string $token The token to invalidate
	 * @return bool True if successful, false otherwise
	 */
	public function invalidateToken(string $email, string $token): bool
	{
		try {
			$tokenBean = \R::findOne('forgot', 'email = ? AND token = ?', [$email, $token]);
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

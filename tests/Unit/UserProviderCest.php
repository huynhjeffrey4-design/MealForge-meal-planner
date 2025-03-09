<?php
declare(strict_types=1);

namespace Tests\Unit;

require_once __DIR__ . '/../../controllers/control.php';

use Tests\Support\UnitTester;

final class UserProviderCest
{
	private \RedBeanUserProvider $provider;
	public function _before(UnitTester $I): void
	{
	  $config = ([
		'host' => '127.0.0.1'
	  ]);


	   $this->provider = new \RedBeanUserProvider($config);
	}

	public function tryToCreateUser(UnitTester $T): void
	{
		$email = 'test@example.com';
		$password = 'correct_password';
		$name_f = 'Testy';
		$name_l = 'Johnson';

		$res = $this->provider->createUser($email, $password, $name_f, $name_l);
		$T->assertNotFalse($res['success'], 'User creation should be successful');

		// Option 1: Just verify other fields, not the actual hash value
		$T->seeInDatabase('user', [
			'email' => $email,
			'first_name' => $name_f,
			'last_name' => $name_l,
		]);

		// Option 2: Query the database directly and verify the hash works
		$user = $T->grabFromDatabase('user', 'password_hash', ['email' => $email]);
		$T->assertTrue(password_verify($password, $user), 'Password hash verification should work');
	}
}

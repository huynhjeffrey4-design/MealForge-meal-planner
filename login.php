<?php
require_once __DIR__ . '/controllers/user.php';

session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (empty($_POST['email']) || empty($_POST['password'])) {
		$error = 'Please enter both email and password';
	} else {
		$userController = getUserController();

		$res = $userController->login($_POST['email'], $_POST['password'], isset($_POST['remember']));

		if ($res['success']) {
			$_SESSION['user'] = $res['user'];
			header('Location: profile.php');
			exit;
		} else {
			$error = $res['validation']->getError('general');
		}
	}
}

$registration_success = isset($_SESSION['registration_success']) && $_SESSION['registration_success'] === true;
if ($registration_success) {
	unset($_SESSION['registration_success']);
}
?>


<!doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Login to your MealForge account</title>
	<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
	<style>
		.bg-primary {
			background-color: #00A651;
		}

		.text-primary {
			color: #00A651;
		}

		.hover\:bg-primary-dark:hover {
			background-color: #009048;
		}

		.focus\:ring-primary:focus {
			--tw-ring-color: rgba(0, 166, 81, 0.5);
		}

		.focus\:border-primary:focus {
			border-color: #00A651;
		}
	</style>
</head>

<body class="bg-white min-h-screen">
	<div class="min-h-screen flex flex-col md:flex-row">
		<!-- Brand Section -->
		<div class="bg-primary w-full md:w-1/2 p-6 md:p-10 lg:p-20">
			<div class="max-w-2xl mx-auto">
				<h1 class="text-2xl font-bold mb-6 md:mb-10 text-white">MealForge</h1>
				<h2 class="text-3xl md:text-4xl lg:text-5xl font-bold mb-4 text-white">Login to your MealForge account</h2>
				<p class="text-white/90 text-lg md:text-xl">Your meal planner, powered by your needs.</p>
			</div>
		</div>

		<!-- Form Section -->
		<div class="w-full md:w-1/2 p-6 md:p-10 lg:p-20 m-auto">
			<div class="max-w-2xl mx-auto">
				<?php if ($registration_success): ?>
					<div class="bg-green-50 border border-green-400 text-green-700 p-4 rounded-md mb-6">
						Registration successful! Please login with your new account.
					</div>
				<?php endif; ?>

				<?php if ($error): ?>
					<div id="login-error" class="bg-red-50 border border-red-400 text-red-700 p-4 rounded-md mb-6">
						<?php echo htmlspecialchars($error); ?>
					</div>
				<?php endif; ?>

				<form method="POST" action="" class="space-y-6">
					<div class="space-y-2">
						<label for="email" class="block font-medium text-gray-700">Email</label>
						<input
							type="email"
							id="email"
							name="email"
							required
							class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
					</div>

					<div class="space-y-2">
						<label for="password" class="block font-medium text-gray-700">Password</label>
						<input
							type="password"
							id="password"
							name="password"
							required
							class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
					</div>

					<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
						<div class="flex items-center space-x-2">
							<input
								type="checkbox"
								id="remember"
								name="remember"
								class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary">
							<label for="remember" class="text-sm text-gray-600">Remember me</label>
						</div>
						<a href="forgot-password.php" class="text-primary hover:text-primary/80">
							Forgot your password?
						</a>
					</div>

					<button
						type="submit"
						class="w-full bg-primary hover:bg-primary-dark text-white py-4 rounded-lg transition-colors font-medium text-lg">
						Continue
					</button>

					<div class="text-center text-gray-600">
						Don't have a MealForge account?
						<a href="registration.php" class="text-primary hover:text-primary/80 font-medium">
							Create one now
						</a>
					</div>
				</form>
			</div>
		</div>
	</div>
</body>

</html>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>MealForge - Personalized Recipe Recommendations</title>
	<!-- Tailwind CSS -->
	<script src="https://cdn.tailwindcss.com"></script>
	<!-- Lucide Icons -->
	<script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="min-h-screen bg-green-50">
	<!-- Navigation -->
	<nav class="bg-white shadow-sm">
		<div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
			<div class="text-2xl font-bold text-green-700">MealForge</div>

			
			<div class="space-x-6">
				<a href="About.php">

					<button class="text-gray-600 hover:text-green-700">About</button>
				</a>
				<a href="login.php">
					<button class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
						Get Started
					</button>
				</a>
			</div>
		</div>
	</nav>

	<!-- Hero Section -->
	<div class="bg-gradient-to-b from-green-50 to-cream-50 py-20">
		<div class="max-w-6xl mx-auto px-4">
			<div class="flex flex-col items-center text-center">
				<h1 class="text-5xl font-bold text-gray-800 mb-6">
					Personalized Recipe Recommendations
					<br>
					<span class="text-green-600">Just for You</span>
				</h1>
				<p class="text-xl text-gray-600 mb-8 max-w-2xl">
					Tell us about your dietary preferences and budget, and we'll forge the perfect meal plan tailored to your needs.
				</p>
				<a href="login.php">
					<button class="bg-green-600 text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-green-700 flex items-center gap-2">
						Start Your Journey
						<i data-lucide="chevron-right" class="w-5 h-5"></i>
					</button>
				</a>
			</div>
		</div>
	</div>

	<!-- Features Section -->
	<div class="py-20 bg-white">
		<div class="max-w-6xl mx-auto px-4">
			<h2 class="text-3xl font-bold text-center text-gray-800 mb-12">
				How MealForge Works
			</h2>
			<div class="grid grid-cols-1 md:grid-cols-3 gap-12">
				<!-- Feature 1 -->
				<div class="bg-green-50 p-6 rounded-lg text-center">
					<div class="flex justify-center mb-4">
						<i data-lucide="utensils" class="w-8 h-8 text-green-600"></i>
					</div>
					<h3 class="text-xl font-semibold text-gray-800 mb-2">Dietary Preferences</h3>
					<p class="text-gray-600">Tell us about your dietary restrictions, allergies, and preferences.</p>
				</div>

				<!-- Feature 2 -->
				<div class="bg-green-50 p-6 rounded-lg text-center">
					<div class="flex justify-center mb-4">
						<i data-lucide="dollar-sign" class="w-8 h-8 text-green-600"></i>
					</div>
					<h3 class="text-xl font-semibold text-gray-800 mb-2">Budget Friendly</h3>
					<p class="text-gray-600">Set your budget and we'll recommend recipes that won't break the bank.</p>
				</div>

				<!-- Feature 3 -->
				<div class="bg-green-50 p-6 rounded-lg text-center">
					<div class="flex justify-center mb-4">
						<i data-lucide="heart" class="w-8 h-8 text-green-600"></i>
					</div>
					<h3 class="text-xl font-semibold text-gray-800 mb-2">Personalized Results</h3>
					<p class="text-gray-600">Get recipe recommendations that perfectly match your needs.</p>
				</div>
			</div>
		</div>
	</div>

	<!-- CTA Section -->
	<div class="bg-green-600 py-20">
		<div class="max-w-6xl mx-auto px-4 text-center">
			<h2 class="text-3xl font-bold text-white mb-6">
				Ready to Transform Your Meal Planning?
			</h2>
			<p class="text-xl text-green-50 mb-8 max-w-2xl mx-auto">
				Join thousands of happy users who have discovered their perfect meal plans with MealForge.
			</p>
			<a href="login.php">
				<button class="bg-white text-green-600 px-8 py-4 rounded-lg text-lg font-semibold hover:bg-green-50 flex items-center gap-2 mx-auto">
					Get Started Now
					<i data-lucide="arrow-right" class="w-5 h-5"></i>
				</button>
			</a>
		</div>
	</div>

	<!-- Footer -->
	<footer class="bg-gray-800 text-white py-12">
		<div class="max-w-6xl mx-auto px-4">
			<div class="grid grid-cols-1 md:grid-cols-4 gap-8">
				<div>
					<h3 class="text-xl font-bold mb-4">MealForge</h3>
					<p class="text-gray-400">
						Personalizing meal planning for a healthier, happier you.
					</p>
				</div>
				<div>
					<h4 class="font-semibold mb-4">Company</h4>
					<ul class="space-y-2 text-gray-400">
						<li>About Us</li>
						<li>Careers</li>
						<li>Contact</li>
					</ul>
				</div>
				<div>
					<h4 class="font-semibold mb-4">Resources</h4>
					<ul class="space-y-2 text-gray-400">
						<li>Blog</li>
						<li>Recipes</li>
						<li>Support</li>
					</ul>
				</div>
				<div>
					<h4 class="font-semibold mb-4">Legal</h4>
					<ul class="space-y-2 text-gray-400">
						<li>Privacy Policy</li>
						<li>Terms of Service</li>
						<li>Cookie Policy</li>
					</ul>
				</div>
			</div>
		</div>
	</footer>

	<script>
		// Initialize Lucide icons
		lucide.createIcons();
	</script>
</body>

</html>

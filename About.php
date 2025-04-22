<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>About - MealForge</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
      .button {
      font-family: 'Josefin Sans', sans-serif;
      color: blue;
      padding: 10px 10px;
      border: none;
      border-radius: 6px;
      text-decoration: none;
      font-weight: bold;
      font-size: 15px;
      cursor: pointer;
    }

    .button:hover {
      background-color: #D9D9D9;
    }
  
    .sr-only {
      position: absolute;
      width: 1px;
      height: 1px;
      padding: 0;
      margin: -1px;
      overflow: hidden;
      clip: rect(0, 0, 0, 0);
      white-space: nowrap;
      border: 0;
    }
    </style>
    
</head>

<body class="min-h-screen bg-green-50">
  <!-- Navigation -->
  <nav class="bg-white shadow-sm fixed top-0 w-full z-50">
    <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
      <a href="index.php" class="text-2xl font-bold text-green-700">MealForge</a>
      <div class="space-x-6">
        <a href="login.php">
          <button class="bg-green-700 text-white px-4 py-2 rounded-lg hover:bg-green-700">
            Get Started
          </button>
        </a>
      </div>
    </div>
  </nav>

  <!-- Main Content -->
  <div class="max-w-6xl mx-auto px-4 pt-32">
    <!-- Hero Image -->
    <div class="relative w-full h-64 mb-6">
      <img src="image_1.png" alt="Healthy Meals" class="w-full h-full object-cover rounded-lg shadow" />
      <div class="absolute inset-0 flex items-center justify-center">
        <h1 class="text-white text-3xl font-bold drop-shadow-md">About MealForge</h1>
      </div>
    </div>

    <!-- Highlight -->
    <div class="bg-gray-700 text-white font-bold px-6 py-4 rounded mb-6">
      Your meal planner, powered by your needs.
    </div>

    <!-- Content -->
    <div class="space-y-6 text-gray-700">
      <p>
        At MealForge, our mission is to bring balanced and healthy recipes to the table based on your needs.
        We believe eating well should be simple, affordable, and tailored to your unique preferences.
        Whether you're a busy student, a working professional, or a family on a budget, you deserve the finest of meals all accessible to you.
      </p>

      <h3 class="text-xl font-semibold text-gray-800">Our Story</h3>
      <p>
        MealForge was born out of a simple idea: meal planning shouldn't be stressful.
        As students juggling classes, part-time jobs, and meal prep, we struggled to eat healthily on a budget.
        We built MealForge to empower students and busy individuals to take control of their meals—without the unnecessary stress.
      </p>

      <h3 class="text-xl font-semibold text-gray-800">Why MealForge?</h3>
      <ul class="list-none space-y-2">
        <li><span aria-hidden="true">🛒</span> <strong>Local Grocery Integration:</strong> Save time and money with real-time store prices and deals.</li>
        <li><span aria-hidden="true">🌿</span> <strong>Health-First Approach:</strong> Prioritize nutrition without sacrificing flavor.</li>
        <li><span aria-hidden="true">🎓</span> <strong>Student-Friendly:</strong> Designed for busy schedules and tight budgets.</li>
        <li><span aria-hidden="true">🆓</span> <strong>Completely Free:</strong> Save your budget towards grocery shopping instead.</li>
      </ul>

      <h3 class="text-xl font-semibold text-gray-800">Ready to Simplify Meal Planning?</h3>
      <p>Join thousands of users already enjoying stress-free meals. Sign up today—it's free!</p>

      <a href="login.php">
				<button class="bg-white text-green-700 px-8 py-4 rounded-lg text-lg font-bold hover:bg-green-50 flex items-center gap-2 mx-auto">
					Get Started Now
					<i data-lucide="arrow-right" class="w-5 h-5"></i>
				</button>
			</a>
    </div>
  </div>

  <script>
    lucide.createIcons();
  </script>
</body>
</html>

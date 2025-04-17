<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../controllers/user.php';
require_once __DIR__ . '/../controllers/recipe.php';

$userId = (int)$_SESSION['user']['id'];
$userController = getUserController();
$user = $userController->getUserById($userId);

if (!isset($_SESSION['meal_plan'])) {
    $_SESSION['meal_plan'] = [
        'Monday' => [],
        'Tuesday' => [],
        'Wednesday' => [],
        'Thursday' => [],
        'Friday' => [],
        'Saturday' => [],
        'Sunday' => []
    ];
}

function extractNumericValue($str)
{
    if (empty($str)) {
        return 0;
    }
    preg_match('/(\d+(\.\d+)?)/', $str, $matches);
    return isset($matches[1]) ? floatval($matches[1]) : 0;
}

$recipeController = new RecipeController(null);

$dailyTotals = [];
foreach ($_SESSION['meal_plan'] as $day => $recipeIds) {
    $dailyTotals[$day] = [
        'calories' => 0,
        'protein' => 0
    ];

    foreach ($recipeIds as $recipeId) {
        $recipe = $recipeController->getRecipeById($recipeId);

        if ($recipe) {
            if (isset($recipe['calories'])) {
                $dailyTotals[$day]['calories'] += is_numeric($recipe['calories']) ?
                    (float)$recipe['calories'] : extractNumericValue($recipe['calories']);
            }

            if (isset($recipe['protein'])) {
                $dailyTotals[$day]['protein'] += is_numeric($recipe['protein']) ?
                    (float)$recipe['protein'] : extractNumericValue($recipe['protein']);
            }
        }
    }
}

$weeklyAverage = [
    'calories' => 0,
    'protein' => 0
];

$daysWithMeals = 0;
foreach ($dailyTotals as $day => $nutrients) {
    // Only count days that have meals
    if (count($_SESSION['meal_plan'][$day]) > 0) {
        $daysWithMeals++;
        foreach ($nutrients as $nutrient => $value) {
            $weeklyAverage[$nutrient] += $value;
        }
    }
}

if ($daysWithMeals > 0) {
    foreach ($weeklyAverage as $nutrient => $total) {
        $weeklyAverage[$nutrient] = $total / $daysWithMeals;
    }
}

// Reference daily intake values (approximate)
$rdi = [
    'calories' => 2000, // Average adult
    'protein' => 50 // g
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>MealForge - Nutrition Summary</title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
	<link type="text/css" href="../static/css/dashboard.css" rel="stylesheet">
	<script src="https://cdn.tailwindcss.com"></script>
	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
	<script>
		tailwind.config = {
			theme: {
				extend: {
					colors: {
						primary: {
							DEFAULT: '#00A651',
							dark: '#008c44'
						}
					}
				}
			}
		}
	</script>
	<style>
		.nutrition-card {
			transition: transform 0.2s;
		}

		.nutrition-card:hover {
			transform: translateY(-5px);
		}

		.progress-bar {
			height: 8px;
			border-radius: 4px;
			background-color: #e5e7eb;
			overflow: hidden;
		}

		.progress-fill {
			height: 100%;
			border-radius: 4px;
		}
	</style>
</head>

<body>
	<?php include '../header.php'; ?>

	<div class="min-h-screen main-container">
		<!-- Main Content Area -->
		<div class="p-5 overflow-y-auto bg-gray-50 w-full">
			<div class="text-center mb-8">
				<h1 class="text-4xl font-bold text-gray-800">Nutrition Summary</h1>
				<p class="text-gray-600 mt-2">Weekly overview of your meal plan's nutritional content</p>
			</div>

			<!-- Weekly Average Card -->
			<div class="bg-white rounded-lg shadow-sm p-6 mb-8">
				<h2 class="text-2xl font-bold text-gray-800 mb-4">Weekly Average</h2>

				<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
					<div class="flex items-center justify-center">
						<div class="text-center">
							<div class="text-5xl font-bold text-primary mb-2"><?= round($weeklyAverage['calories']) ?></div>
							<div class="text-gray-600">calories per day</div>
						</div>
					</div>

					<div class="space-y-4">
						<?php foreach ($weeklyAverage as $nutrient => $value): ?>
							<?php
                            $percentage = ($rdi[$nutrient] > 0) ? min(100, ($value / $rdi[$nutrient]) * 100) : 0;
						    $colorClass = '';

						    if ($percentage < 50) {
						        $colorClass = 'bg-blue-500';
						    } elseif ($percentage < 85) {
						        $colorClass = 'bg-green-500';
						    } elseif ($percentage < 100) {
						        $colorClass = 'bg-yellow-500';
						    } else {
						        $colorClass = 'bg-red-500';
						    }

						    $unit = ($nutrient === 'calories') ? 'kcal' : 'g';
						    $label = ucfirst($nutrient);
						    ?>
							<div>
								<div class="flex justify-between mb-1">
									<span class="text-gray-700 font-medium"><?= $label ?></span>
									<span class="text-gray-600"><?= round($value, 1) ?><?= $unit ?> / <?= $rdi[$nutrient] ?><?= $unit ?></span>
								</div>
								<div class="progress-bar">
									<div class="progress-fill <?= $colorClass ?>" style="width: <?= $percentage ?>%"></div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

			<!-- Daily Breakdown -->
			<h2 class="text-2xl font-bold text-gray-800 mb-4">Daily Breakdown</h2>

			<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
				<?php foreach ($dailyTotals as $day => $nutrients): ?>
					<div class="bg-white rounded-lg shadow-sm p-4 nutrition-card">
						<h3 class="text-lg font-bold text-center mb-4"><?= $day ?></h3>

						<?php if (count($_SESSION['meal_plan'][$day]) > 0): ?>
							<div class="space-y-3">
								<div class="flex justify-between">
									<span class="text-gray-700">Calories:</span>
									<span class="font-medium"><?= round($nutrients['calories']) ?> kcal</span>
								</div>
								<div class="flex justify-between">
									<span class="text-gray-700">Protein:</span>
									<span class="font-medium"><?= round($nutrients['protein'], 1) ?> g</span>
								</div>
							</div>

							<div class="mt-4 pt-4 border-t border-gray-200">
								<div class="text-sm text-gray-600">
									<span class="font-medium"><?= count($_SESSION['meal_plan'][$day]) ?></span> meal(s) planned
								</div>
							</div>
						<?php else: ?>
							<div class="py-8 text-center text-gray-500">
								<p>No meals planned for this day</p>
								<a href="../dashboard.php" class="text-primary hover:text-primary-dark mt-2 inline-block">
									<i class="fa fa-plus mr-1" aria-hidden="true"></i> Add meals <span class="sr-only">to this day's meal plan</span>
								</a>
							</div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="mt-8 text-center">
				<a href="../dashboard.php" class="px-4 py-2 bg-primary text-white rounded hover:bg-primary-dark inline-block">
					<i class="fa fa-arrow-left mr-2" aria-hidden="true"></i> Back to Meal Plan <span class="sr-only">dashboard</span>
				</a>
			</div>
		</div>
	</div>
</body>

</html>

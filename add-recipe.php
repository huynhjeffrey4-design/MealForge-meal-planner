<?php
require_once 'SetupRedbean.php';
require_once 'controllers/user.php';
DatabaseConnection::getInstance()->setup();
session_start();

$day = $_GET['day'] ?? ($_POST['day'] ?? '');
$recipe = $_SESSION['submitted_recipe'] ?? null;
if (!isset($_SESSION['draft_recipe'])) {
    $_SESSION['draft_recipe'] = [
        'ingredients' => [],
        'instructions' => []
    ];
}


if (!isset($_GET['step']) && isset($_SESSION['draft_recipe']['ingredients'])) {
    $_SESSION['draft_recipe']['ingredients'] = [];
}
$step = $_GET['step'] ?? 1;
if ($step == 1 && !isset($_SESSION['submitted_recipe'])) {
    $_SESSION['draft_recipe'] = [
        'ingredients' => [],
        'instructions' => []
    ];
}


// Step 1
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_step1'])) {
    $_SESSION['draft_recipe']['step1'] = [
        'meal_name' => $_POST['meal_name'],
        'description' => $_POST['description'],
        'prep_time' => $_POST['prep_time'],
        'cook_time' => $_POST['cook_time'],
        'difficulty' => $_POST['difficulty'],
        'serves' => $_POST['serves'],
        'meal_type' => $_POST['meal_type']
    ];
    header("Location: ?step=2&day=" . urlencode($day));
    exit;
}

// Step 2 - Ingredients
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_step2'])) {
        if (empty($_SESSION['draft_recipe']['ingredients'])) {
            $_SESSION['step2_error'] = "Please add at least one ingredient before continuing.";
            header("Location: ?step=2&day=" . urlencode($day));
            exit;
        }
        header("Location: ?step=3&day=$day");
        exit;
    } elseif (isset($_POST['add_ingredient'])) {
        $_SESSION['draft_recipe']['ingredients'][] = [
            'name' => $_POST['ingredient_name'],
            'qty' => $_POST['ingredient_qty'],
            'unit' => $_POST['ingredient_unit']
        ];
        header("Location: ?step=2&day=$day");
        exit;
    } elseif (isset($_POST['delete_ingredient'])) {
        unset($_SESSION['draft_recipe']['ingredients'][$_POST['delete_ingredient']]);
        $_SESSION['draft_recipe']['ingredients'] = array_values($_SESSION['draft_recipe']['ingredients']);
        header("Location: ?step=2&day=$day");
        exit;
    }
}

// Step 3 - Instructions (Single Box)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_step3'])) {
    $_SESSION['draft_recipe']['instructions'] = explode("\n", trim($_POST['instructions']));
    header("Location: ?step=4&day=" . urlencode($day));
    exit;
}


// Step 4 - Tags + Optional Image + Nutrition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_step4'])) {
    $_SESSION['draft_recipe']['step4'] = [
        'calories' => $_POST['calories'],
        'protein' => $_POST['protein']
    ];

    if (!is_dir('uploads')) {
        mkdir('uploads', 0777, true);
    }

    if (isset($_FILES['image']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
        if ($_FILES['image']['error'] === 0) {
            $img = 'uploads/' . uniqid() . '_' . basename($_FILES['image']['name']);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $img)) {
                $_SESSION['draft_recipe']['step4']['image'] = $img;
            }
        }
    }
    header("Location: ?step=5&day=" . urlencode($day));
    exit;
}

// Step 5 - Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_recipe'])) {
    $userId = $_SESSION['user']['id'] ?? -1;
    if ($userId == -1) {
        die("❌ Please log in before adding a recipe!");
    }
    $userController = new UserController(new RedBeanUserProvider());
    $fullName = "Anonymous";
    $user = $userController->getUserByID($userId);
    if ($user && isset($user['first_name'], $user['last_name'])) {
        $fullName = $user['first_name'] . ' ' . $user['last_name'];
    }

    $draft = $_SESSION['draft_recipe'];
    $recipe = R::dispense('mealplan');
    $recipe->user_id = $userId;
    $recipe->day = $day;
    $recipe->creator = $fullName;
    $recipe->recipe = $fullName . "'s " . $draft['step1']['meal_name'];
    $recipe->description = $draft['step1']['description'];
    $recipe->prep_time = $draft['step1']['prep_time'];
    $recipe->cook_time = $draft['step1']['cook_time'];
    $recipe->difficulty = $draft['step1']['difficulty'];
    $recipe->serves = $draft['step1']['serves'];
    $recipe->meal_type = $draft['step1']['meal_type'];
    $recipe->image = $draft['step4']['image'] ?? '';
    $recipe->calories = $draft['step4']['calories'];
    $recipe->protein  = $draft['step4']['protein'];
    $recipe->instructions = $draft['instructions'][0] ?? '';
    $recipe->ingredients = implode(', ', array_map(function ($i) {
        return "{$i['qty']} {$i['unit']} {$i['name']}";
    }, $draft['ingredients']));


    $id = R::store($recipe);
    $_SESSION['submitted_recipe'] = $recipe;
    unset($_SESSION['draft_recipe']);
    header("Location: ?step=6&day=" . urlencode($day));
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add a New Recipe - MealForge</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-green-50 p-6">
<div class="max-w-2xl mx-auto bg-white p-6 rounded-xl shadow">

    <?php if ($step == 1): ?>
        <h2 class="text-2xl font-bold mb-4">Step 1: Basic Info</h2>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="day" value="<?= htmlspecialchars($day) ?>">
            <input type="hidden" name="user_id" id="user_id_field">

            <div class="flex items-center">
                <label for="meal_name" class="w-40 font-medium">Meal Name:</label>
                <input id="meal_name" name="meal_name" class="flex-1 border border-gray-300 rounded px-2 py-1 text-sm leading-tight" required>
            </div>

            <div class="flex items-center">
                <label for="meal_type" class="w-40 font-medium">Meal Type:</label>
                <select id="meal_type" name="meal_type" class="flex-1 border border-gray-300 rounded px-2 py-1 text-sm leading-tight" required>
                    <option value="" disabled selected>Select meal type</option>
                    <option>Breakfast</option>
                    <option>Lunch</option>
                    <option>Dinner</option>
                    <option>Snack</option>
                    <option>Dessert</option>
                </select>
            </div>

            <div class="flex items-center">
                <label for="description" class="w-40 font-medium">Description:</label>
                <textarea id="description" name="description" rows="1" class="flex-1 border border-gray-300 rounded px-2 py-1 text-sm leading-tight resize-y min-h-[32px]" required></textarea>
            </div>

            <div class="flex items-center">
                <label for="prep_time" class="w-40 font-medium">Prep Time (min):</label>
                <input id="prep_time" name="prep_time" type="number" min="0" class="flex-1 border border-gray-300 rounded px-2 py-1 text-sm leading-tight" required>
            </div>
            <div class="flex items-center">
                <label for="cook_time" class="w-40 font-medium">Cook Time (min):</label>
                <input id="cook_time" name="cook_time" type="number" min="0" class="flex-1 border border-gray-300 rounded px-2 py-1 text-sm leading-tight" required>
            </div>

            <div class="flex items-center">
                <label for="serves" class="w-40 font-medium">Serves:</label>
                <input id="serves" name="serves" type="number" min="0" class="flex-1 border border-gray-300 rounded px-2 py-1 text-sm leading-tight" required>
            </div>

            <div class="flex items-center">
                <label for="difficulty" class="w-40 font-medium">Difficulty:</label>
                <select id="difficulty" name="difficulty" class="flex-1 border border-gray-300 rounded px-2 py-1 text-sm leading-tight" required>
                    <option value="" disabled selected>Select difficulty</option>
                    <option>Easy</option>
                    <option>Medium</option>
                    <option>Hard</option>
                </select>
            </div>

            <div class="flex justify-end">
                <button name="submit_step1" type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg transition-colors font-medium text-lg">
                    Next Step<span class="sr-only"> to Step 2</span>
                </button>
            </div>
        </form>

        <script>
            window.addEventListener("message", (event) => {
                const data = event.data;
                if (data.fromRecipe) {
                    document.querySelector("#meal_name").value = data.name || '';
                    document.querySelector("#description").value = data.description || '';
                    document.querySelector("#prep_time").value = data.prepTime || '';
                    document.querySelector("#cook_time").value = data.cookTime || '';
                    document.querySelector("#difficulty").value = data.difficulty || '';
                    document.querySelector("#serves").value = data.serves || '';
                    document.querySelector("#meal_type").value = data.type || '';
                }
                if (data.type === "USER_ID") {
                    sessionStorage.setItem("user_id", data.userId);
                    const uidInput = document.getElementById("user_id_field");
                    if (uidInput) uidInput.value = data.userId;
                }
            });
        </script>

    <?php elseif ($step == 2): ?>
        <h2 class="text-2xl font-bold mb-4">Step 2: Ingredients</h2>
        <a href="?step=1&day=<?= urlencode($day) ?>" class="text-sm text-blue-500 hover:underline mb-4 inline-block">Previous Step</a>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="day" value="<?= htmlspecialchars($day) ?>">
            <div class="grid grid-cols-3 gap-2">
                <input name="ingredient_name" placeholder="Name" class="border p-2 rounded w-full" required>
                <input name="ingredient_qty" type="text" placeholder="Quantity" class="border p-2 rounded w-full" required>
                <input name="ingredient_unit" placeholder="Unit" class="border p-2 rounded w-full" required>
            </div>
            <div class="flex flex-col gap-2">
                <button name="add_ingredient" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    + Add Ingredient
                </button>
                <a href="?step=3&day=<?= urlencode($day) ?>" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-center flex items-center justify-center">
                    Next Step
                </a>
            </div>
        </form>
        <div class="mt-6 space-y-2">
            <?php foreach ($_SESSION['draft_recipe']['ingredients'] as $i => $item): ?>
                <div class="p-3 border rounded bg-gray-50 flex justify-between items-center">
                    <div>
                        <div><strong>Name:</strong> <?= $item['name'] ?></div>
                        <div><strong>Amount:</strong> <?= $item['qty'] ?> <?= $item['unit'] ?></div>
                    </div>
                    <form method="POST" class="ml-4">
                        <input type="hidden" name="delete_ingredient" value="<?= $i ?>">
                        <button aria-label="Delete item" class="text-red-600 hover:text-red-800">❌</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

    <?php elseif ($step == 3): ?>
    <h2 class="text-2xl font-bold mb-4">Step 3: Instructions</h2>
    <a href="?step=2&day=<?= urlencode($day) ?>" class="text-sm text-blue-500 hover:underline mb-4 inline-block">Previous Step</a>
    <form method="POST">
        <input type="hidden" name="day" value="<?= htmlspecialchars($day) ?>">

        <label class="block font-medium mb-2" for="instructions">Instructions:</label>
        <textarea name="instructions" id="instructions" rows="10" class="w-full border border-gray-300 rounded p-2 mb-4" placeholder="Write all steps here..." required></textarea>

        <div class="flex flex-col gap-2">
            <button name="submit_step3" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">Next Step</button>
        </div>
    </form>

    <?php elseif ($step == 4): ?>
        <h2 class="text-2xl font-bold mb-4">Step 4: Image & Nutrition</h2>
        <a href="?step=3&day=<?= urlencode($day) ?>" class="text-sm text-blue-500 hover:underline mb-4 inline-block">Previous Step</a>

        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="hidden" name="day" value="<?= htmlspecialchars($day) ?>">

            <div class="flex items-center gap-2">
                <label for="image" class="font-medium whitespace-nowrap">Upload Image:</label>
                <div class="flex-1 w-full">
                    <label for="image" class="block text-sm font-medium text-gray-700">
                        Upload Image <span class="text-xs text-gray-500">(2 MB limit)</span>
                    </label>
                    <input type="file" id="image" name="image" accept="image/jpeg, image/png, image/gif"
                           class="w-full p-2 border border-gray-300 rounded-md">
                </div>
            </div>

            <div class="flex items-center gap-2">
                <label for="calories" class="font-medium whitespace-nowrap">Calories:</label>
                <input id="calories" name="calories" type="number" class="w-full border border-gray-300 rounded px-2 py-1 text-sm leading-tight">
            </div>

            <div class="flex items-center gap-2">
                <label for="protein" class="font-medium whitespace-nowrap">Protein (g):</label>
                <input id="protein" name="protein" type="number" class="w-full border border-gray-300 rounded px-2 py-1 text-sm leading-tight">
            </div>

            <div class="flex justify-end">
                <button name="submit_step4" class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg transition-colors font-medium text-lg">
                    Next Step<span class="sr-only"> to Step 5</span>
                </button>
            </div>
        </form>

    <?php elseif ($step == 5): ?>
    <h2 class="text-2xl font-bold mb-4">Step 5: Preview</h2>
    <?php $r = $_SESSION['draft_recipe']; ?>

    <p><span class="font-semibold">Meal Name:</span> <span class="text-gray-800"><?= htmlspecialchars($r['step1']['meal_name']) ?></span></p>
    <p class="mt-2"><span class="font-semibold">Description:</span> <span class="text-gray-800"><?= htmlspecialchars($r['step1']['description']) ?></span></p>

    <p class="mt-2"><span class="font-semibold">Meal Type:</span> <span class="text-gray-800"><?= htmlspecialchars($r['step1']['meal_type']) ?></span></p>
    <p class="mt-2"><span class="font-semibold">Prep Time:</span> <?= htmlspecialchars($r['step1']['prep_time']) ?> mins |
        <span class="font-semibold">Cook Time:</span> <?= htmlspecialchars($r['step1']['cook_time']) ?> mins |
        <span class="font-semibold">Serves:</span> <?= htmlspecialchars($r['step1']['serves']) ?>
    </p>

    <h3 class="font-semibold mt-2">Nutrition:</h3>
    <p class="text-gray-800"><?= htmlspecialchars($r['step4']['calories']) ?> cal, <?= htmlspecialchars($r['step4']['protein']) ?>g protein</p>

    <h3 class="font-semibold mt-2">Ingredients:</h3>
    <ul class="list-disc list-inside text-gray-800">
        <?php foreach ($r['ingredients'] as $i): ?>
            <li><?= htmlspecialchars($i['qty']) ?> <?= htmlspecialchars($i['unit']) ?> <?= htmlspecialchars($i['name']) ?></li>
        <?php endforeach; ?>
    </ul>

    <h3 class="font-semibold mt-2">Instructions:</h3>
    <p class="text-gray-800 whitespace-pre-line"><?= nl2br(htmlspecialchars($r['instructions'][0] ?? '')) ?></p>

    <?php if (!empty($r['step4']['image'])): ?>
    <img src="<?= $r['step4']['image'] ?>" alt="Submitted recipe image" class="w-48 mt-4 rounded">
    <?php endif; ?>

    <form method="POST" id="finalForm" class="mt-6">
        <input type="hidden" name="day" value="<?= htmlspecialchars($day) ?>">
        <input type="hidden" name="user_id" id="user_id_field">
        <button name="submit_recipe" class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg transition-colors font-medium text-lg">
            Submit Recipe
        </button>
    </form>


    <?php elseif ($step == 6): ?>
    <h2 class="text-2xl font-bold text-green-600 mb-4">Recipe Submitted!</h2>
    <script>
        // Send addMeal once, then replace the iframe's content to prevent reload loops
        const recipeData = <?= json_encode($recipe) ?>;
        const day = <?= json_encode($day) ?>;
        const fullMeal = {
            id: Date.now(),
            name: recipeData.recipe || 'Untitled',
            image: recipeData.image || 'static/images/default-recipe.jpg',
            description: recipeData.description || '',
            ingredients: (recipeData.ingredients || '').split(', '),
            instructions: recipeData.instructions || '',
            nutrition: [
                `${recipeData.calories || 0} cal`,
                `${recipeData.protein || 0}g protein`
            ],
            prepTime: recipeData.prep_time || 'N/A',
            cookTime: recipeData.cook_time || 'N/A',
            difficulty: recipeData.difficulty || 'N/A',
            serves: recipeData.serves || 'N/A',
            mealType: recipeData.meal_type || 'N/A',
            type: recipeData.meal_type || 'N/A'
        };

        window.parent.postMessage({ action: 'addMeal', recipe: fullMeal, day }, '*');

        // Immediately replace the page to blank to avoid message re-sending on reload
        setTimeout(() => {
            window.location.replace('about:blank');
        }, 200);
    </script>
    <?php endif; ?>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const form = document.querySelector('form[enctype="multipart/form-data"]');
        const fileInput = document.getElementById('image');

        form?.addEventListener('submit', function (e) {
            const file = fileInput?.files[0];
            const maxSize = 2 * 1024 * 1024; // 2 MB

            if (file && file.size > maxSize) {
                e.preventDefault();
                alert("Image is too large. Max size is 2 MB.");
            }
        });
    });
</script>
</body>
</html>

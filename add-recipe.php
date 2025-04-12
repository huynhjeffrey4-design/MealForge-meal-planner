<?php
require_once 'SetupRedbean.php';
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


$step = $_GET['step'] ?? 1;

// Step 1
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_step1'])) {
    $_SESSION['draft_recipe']['step1'] = [
      'meal_name' => $_POST['meal_name'],
      'description' => $_POST['description'],
      'prep_time' => $_POST['prep_time'],
      'cook_time' => $_POST['cook_time'],
      'difficulty' => $_POST['difficulty'],
      'servings' => $_POST['servings'],
      'meal_type' => $_POST['meal_type']
    ];
    header("Location: ?step=2&day=" . urlencode($day));
    exit;
}

// Step 2 - Ingredients
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_step2'])) {
    if (empty($_SESSION['draft_recipe']['ingredients'])) {
        $_SESSION['step2_error'] = "Please add at least one ingredient before continuing.";
        header("Location: ?step=2&day=" . urlencode($day));
        exit;
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_step2'])) {
    $_SESSION['draft_recipe']['ingredients'][] = [
      'name' => $_POST['ingredient_name'],
      'qty' => $_POST['ingredient_qty'],
      'unit' => $_POST['ingredient_unit']
    ];
    header("Location: ?step=3&day=$day");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_ingredient'])) {
    $_SESSION['draft_recipe']['ingredients'][] = [
      'name' => $_POST['ingredient_name'],
      'qty' => $_POST['ingredient_qty'],
      'unit' => $_POST['ingredient_unit']
    ];
    header("Location: ?step=2&day=$day");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ingredient'])) {
    unset($_SESSION['draft_recipe']['ingredients'][$_POST['delete_ingredient']]);
    $_SESSION['draft_recipe']['ingredients'] = array_values($_SESSION['draft_recipe']['ingredients']);
    header("Location: ?step=2&day=$day");
    exit;
}

// Step 3 - Instructions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_instruction'])) {
    $_SESSION['draft_recipe']['instructions'][] = $_POST['instruction_text'];
    header("Location: ?step=3&day=$day");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_instruction'])) {
    unset($_SESSION['draft_recipe']['instructions'][$_POST['delete_instruction']]);
    $_SESSION['draft_recipe']['instructions'] = array_values($_SESSION['draft_recipe']['instructions']);
    header("Location: ?step=3&day=" . urlencode($day));
}

// Step 4 - Tags + Optional Image + Nutrition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_step4'])) {
    $_SESSION['draft_recipe']['step4'] = [
      'tags' => explode(',', $_POST['tags']),
      'calories' => $_POST['calories'],
      'protein' => $_POST['protein'],
      'carbs' => $_POST['carbs'],
      'fat' => $_POST['fat']
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
    // ✅ 加在这里！调试后端收到的 POST 数据
    error_log("🔥 后端收到的 POST: " . print_r($_POST, true));
    $userId = $_POST['user_id'] ?? null;  // ✅ 从 POST 拿，而不是 SESSION
    if (!$userId) {
        die("❌ 请先登录再添加菜谱！");
    }
    $_SESSION['user_id'] = $userId; // ✅ 如果你想在 session 里也存一份（可选）
    $draft = $_SESSION['draft_recipe'];

    $recipe = R::dispense('mealplan');  // 存到 meal_plan 表

    $recipe->user_id = $userId; // 👈 就放在这里！先放 user id

    $recipe->day = $day;  // 你可以记录是星期几的 meal plan
    $recipe->recipe = $draft['step1']['meal_name'];
    $recipe->description = $draft['step1']['description'];
    $recipe->prep_time = $draft['step1']['prep_time'];
    $recipe->cook_time = $draft['step1']['cook_time'];
    $recipe->difficulty = $draft['step1']['difficulty'];
    $recipe->serves = $draft['step1']['servings'];
    $recipe->meal_type = $draft['step1']['meal_type'];
    $recipe->image = $draft['step4']['image'] ?? '';
    $recipe->calories = $draft['step4']['calories'];
    $recipe->protein  = $draft['step4']['protein'];
    $recipe->carbs    = $draft['step4']['carbs'];
    $recipe->fat      = $draft['step4']['fat'];
    $recipe->instructions =  implode("\n", $draft['instructions']);
    $recipe->ingredients = implode(', ', array_map(function ($i) {
        return "{$i['qty']} {$i['unit']} {$i['name']}";
    }, $draft['ingredients']));
    $recipe->tags = json_encode($draft['step4']['tags']);

    $id = R::store($recipe);

    $draft['id'] = $id;
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
  <title>Add Recipe</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-green-50 p-6">
  <div class="max-w-2xl mx-auto bg-white p-6 rounded-xl shadow">
    <?php if ($step == 1): ?>
      <h2 class="text-2xl font-bold mb-4">Step 1: Basic Info</h2>
      <form method="POST">
        <input type="hidden" name="day" value="<?= htmlspecialchars($day) ?>">
        <input type="hidden" name="user_id" id="user_id_field"> <!-- ✅ 加在这里！ -->
        <input name="meal_name" placeholder="Meal Name" class="w-full border p-2 mb-2" required>
        <textarea name="description" placeholder="Description" class="w-full border p-2 mb-2" required></textarea>
        <input name="prep_time" type="number" placeholder="Prep Time (min)" class="w-full border p-2 mb-2" required>
        <input name="cook_time" type="number" placeholder="Cook Time (min)" class="w-full border p-2 mb-2" required>
        <select name="difficulty" class="w-full border p-2 mb-2" required>
          <option>Easy</option><option>Medium</option><option>Hard</option>
        </select>
        <input name="servings" type="number" placeholder="Servings" class="w-full border p-2 mb-2" required>
        <select name="meal_type" class="w-full border p-2 mb-4" required>
          <option>Breakfast</option><option>Lunch</option><option>Dinner</option><option>Snack</option><option>Dessert</option>
        </select>
        <div class="flex justify-end">
  <button name="submit_step1" class="bg-blue-600 text-white px-4 py-2 rounded">Next -></button>
</div>
      </form>
      <script>
window.addEventListener("message", (event) => {
  const data = event.data;
  if (data.fromRecipe) {
    if (data.name) document.querySelector("input[name='meal_name']").value = data.name;
    if (data.description) document.querySelector("textarea[name='description']").value = data.description;
    if (data.prepTime) document.querySelector("input[name='prep_time']").value = data.prepTime;
    if (data.cookTime) document.querySelector("input[name='cook_time']").value = data.cookTime;
    if (data.difficulty) document.querySelector("select[name='difficulty']").value = data.difficulty;
    if (data.servings) document.querySelector("input[name='servings']").value = data.servings;
    if (data.type) document.querySelector("select[name='meal_type']").value = data.type;
  }

 
  if (data.type === "USER_ID") {

    sessionStorage.setItem("user_id", data.userId);

   
    const uidInput = document.getElementById("user_id_field");
    if (uidInput) {
      uidInput.value = data.userId;
  
    }
  }
});
</script>

    <?php elseif ($step == 2): ?>

      <h2 class="text-2xl font-bold mb-4">Step 2: Ingredients</h2>
      <a href="?step=1&day=<?= urlencode($day) ?>" class="text-sm text-blue-500 hover:underline mb-4 inline-block"><- Back</a>
  <form method="POST" class="space-y-4">
    <input type="hidden" name="day" value="<?= htmlspecialchars($day) ?>">

    <div class="grid grid-cols-3 gap-2">
      <input name="ingredient_name" placeholder="Name" class="border p-2 rounded w-full" required>
      <input name="ingredient_qty" type="number" placeholder="Qty" class="border p-2 rounded w-full" required>
      <input name="ingredient_unit" placeholder="Unit" class="border p-2 rounded w-full" required>
    </div>


<div class="grid grid-cols-2 gap-2">
  <button name="add_ingredient" class="bg-green-600 text-white px-4 py-2 rounded w-full">+ Add Ingredient</button>

  <a href="?step=3&day=<?= urlencode($day) ?>" class="w-full bg-blue-600 text-white px-4 py-2 rounded text-center flex items-center justify-center">Next -></a>
</div>


    </div>
  </form>




  <ul class="list-disc list-inside space-y-1 mt-4">
    <?php foreach ($_SESSION['draft_recipe']['ingredients'] as $i => $item): ?>
      <li class="flex justify-between items-center">
      <?= $item['name'] ?> <?= $item['qty'] ?> <?= $item['unit'] ?>

        <form method="POST" class="inline ml-2">
          <input type="hidden" name="delete_ingredient" value="<?= $i ?>">
          <button class="text-red-500 hover:text-red-700">❌</button>
        </form>
      </li>
        <?php endforeach; ?>
      </ul>


      <?php elseif ($step == 3): ?>
      <h2 class="text-2xl font-bold mb-4">Step 3: Instructions</h2>
<a href="?step=2&day=<?= urlencode($day) ?>" class="text-sm text-blue-500 hover:underline mb-4 inline-block"> <- Back</a>
<form method="POST" class="mb-4">
  <input type="hidden" name="day" value="<?= htmlspecialchars($day) ?>">
  <textarea name="instruction_text" class="w-full border p-2 mb-2" required></textarea>
  
  <div class="flex gap-2">
    <button name="add_instruction" class="w-1/2 bg-green-600 text-white px-4 py-2 rounded text-center">+ Add Step</button>
    <a href="?step=4&day=<?= urlencode($day) ?>" class="w-1/2 bg-blue-600 text-white px-4 py-2 rounded text-center flex items-center justify-center">Next -></a>
  </div>
</form>
      <ol class="list-decimal list-inside mb-4">
        <?php foreach ($_SESSION['draft_recipe']['instructions'] as $i => $inst): ?>
          <li><?= htmlspecialchars($inst) ?>
            <form method="POST" class="inline">
              <input type="hidden" name="delete_instruction" value="<?= $i ?>">
              <button class="text-red-500  hover:text-red-700">❌</button>
            </form>
          </li>
        <?php endforeach; ?>
      </ol>
    

    <?php elseif ($step == 4): ?>
      <h2 class="text-2xl font-bold mb-4">Step 4: Tags, Image & Nutrition</h2>
      <a href="?step=3&day=<?= urlencode($day) ?>" class="text-sm text-blue-500 hover:underline mb-4 inline-block"><- Back</a>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="day" value="<?= htmlspecialchars($day) ?>">
        <input name="tags" placeholder="Tags (comma separated)" class="w-full border p-2 mb-2" required>
        <input type="file" name="image" accept="image/*" class="mb-2">
        <input name="calories" type="number" placeholder="Calories" class="w-full border p-2 mb-2">
        <input name="protein" type="number" placeholder="Protein" class="w-full border p-2 mb-2">
        <input name="carbs" type="number" placeholder="Carbs" class="w-full border p-2 mb-2">
        <input name="fat" type="number" placeholder="Fat" class="w-full border p-2 mb-4">
        <div class="flex justify-end">
         <button name="submit_step4" class="bg-blue-600 text-white px-4 py-2 rounded">Next -></button>
       </div>
      </form>

    <?php elseif ($step == 5): ?>
      <h2 class="text-2xl font-bold mb-4">Step 5: Preview</h2>
   
      <?php $r = $_SESSION['draft_recipe']; ?>
      <p><strong><?= $r['step1']['meal_name'] ?></strong> — <?= $r['step1']['description'] ?></p>
      <p><em>Prep: <?= $r['step1']['prep_time'] ?> min | Cook: <?= $r['step1']['cook_time'] ?> min | Servings: <?= $r['step1']['servings'] ?></em></p>
      <ul class="list-disc list-inside">
        <?php foreach ($r['ingredients'] as $i): ?>
          <li><?= $i['qty'] ?> <?= $i['unit'] ?> <?= $i['name'] ?></li>
        <?php endforeach; ?>
      </ul>
      <ol class="list-decimal list-inside">
        <?php foreach ($r['instructions'] as $i): ?>
          <li><?= $i ?></li>
        <?php endforeach; ?>
      </ol>
      <p>Tags: <?= implode(', ', $r['step4']['tags']) ?></p>
      <p>Nutrition: <?= $r['step4']['calories'] ?> cal, <?= $r['step4']['protein'] ?>g protein, <?= $r['step4']['carbs'] ?>g carbs, <?= $r['step4']['fat'] ?>g fat</p>
      <?php if (!empty($r['step4']['image'])): ?>
        <img src="<?= $r['step4']['image'] ?>" class="w-48 mt-4 rounded">
      <?php endif; ?>

      <form method="POST" id="finalForm">
        <input type="hidden" name="day" value="<?= htmlspecialchars($day) ?>">
        <input type="hidden" name="user_id" id="user_id_field"> 
        <button name="submit_recipe" class="bg-green-600 text-white px-4 py-2 rounded mt-4">Submit Recipe</button>
      </form>
      <!--  Share Recipe Button -->
<button id="shareButton" class="bg-yellow-500 text-white px-4 py-2 rounded mt-4 ml-2"> Share to the MealForge Community Feed</button>
<span id="shareMessage" class="ml-4 text-green-600 hidden"> Shared successfully!</span>

      <script>
  let shared = false;
  document.getElementById('shareButton').addEventListener('click', function (e) {
    e.preventDefault();

    if (shared) return; 
    shared = true;

    const data = {
      action: 'shareRecipe',
      recipe: <?= json_encode($_SESSION['draft_recipe'] ?? []) ?>
    };

    fetch('social.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    })
    .then(res => res.text())
    .then(res => {
     
      document.getElementById('shareMessage').classList.remove('hidden');
    })
    .catch(err => {
      console.error("❌ Failed to share:", err);
    });
  });
</script>
      <script>
  document.getElementById("finalForm")?.addEventListener("submit", () => {
    const uidInput = document.getElementById("user_id_field");
    const userId = sessionStorage.getItem("user_id");
    if (uidInput && userId) {
      uidInput.value = userId;

    } else {
      console.warn("user_id null");
    }
  });
</script>

    <?php elseif ($step == 6): ?>
      <h2 class="text-2xl font-bold text-green-600 mb-4"> Recipe Submitted!</h2>
     
     <script>
       const recipeData = <?php echo json_encode($recipe); ?>;
       const day        = <?php echo json_encode($day); ?>;

       const fullMeal = {
        id: Date.now(),
      name: recipeData.recipe || 'Untitled',
      image: recipeData.image || 'static/images/default-recipe.jpg',
      description: recipeData.description || '',
      ingredients: (recipeData.ingredients || '').split(', '),
      instructions: (recipeData.instructions || '').split('\n'),



        nutrition: [
         `${recipeData.calories || 0} cal`,
         `${recipeData.protein || 0}g protein`,
         `${recipeData.carbs || 0}g carbs`,
         `${recipeData.fat || 0}g fat`
        ],
        prepTime: recipeData.prep_time || 'N/A',         
        cookTime: recipeData.cook_time || 'N/A',         
        difficulty: recipeData.difficulty || 'N/A',
        servings: recipeData.serves || 'N/A',
        mealType: recipeData.meal_type || 'N/A',         
        type: recipeData.type || recipeData.meal_type || 'N/A',
        tags: JSON.parse(recipeData.tags || '[]')
       };

        window.parent.postMessage({
          action: 'addMeal',
          recipe: fullMeal,
          day: day
        }, '*');
   

  

document.getElementById("finalForm")?.addEventListener("submit", () => {
  const uidInput = document.getElementById("user_id_field");
  const userId = sessionStorage.getItem("user_id");
  if (uidInput && userId) {
    uidInput.value = userId;

  } else {
    console.warn(" user_id null");
  }
});
   </script>
 <?php endif; ?>
  </div>
</body>
</html>

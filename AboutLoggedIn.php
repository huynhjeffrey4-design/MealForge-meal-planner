<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MealForge</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
 
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f3f3f3;
      margin: 0;
      padding-top: 4rem; /* Space for fixed header */
    }

    .card {
      width: 90%;
      max-width: 1300px;
      margin: 20px auto;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .header {
      background-color: #00A63E;
      color: white;
      padding: 20px;
      font-size: 24px;
      font-weight: bold;
      text-align: center;
    }
    
    .image-container {
      position: relative;
      width: 100%;
      height: 200px;
    }

    .image {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .image-title {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      font-family: Arial, sans-serif;
      font-weight: bold;
      font-size: 26px;
      color: white;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
      width: 100%;
      text-align: center;
      box-sizing: border-box;
      padding: 0 15px;
    }

    .highlight {
      font-family: Arial, sans-serif;
      font-weight: bold;
      font-size: 16px;
      color: #FFFFFF;
      background-color: #D3D3D3;
      padding: 25px 15px;
      width: 100%;
      box-sizing: border-box;
      margin: 0;
      text-align: center;
    }

    .content {
      padding: 20px;
      font-family: 'Josefin Sans', sans-serif;
    }

    h2, h3 {
      color: #333;
    }

    p {
      color: #555;
      margin-bottom: 16px;
    }

    ul {
      list-style-type: none;
      padding-left: 0;
    }

    ul li {
      margin-bottom: 10px;
      padding-left: 10px;
      border-left: 3px solid #00A63E;
    }

    .button-container {
      text-align: center;
      margin-top: 20px;
    }
    
    .button {
      font-family: 'Josefin Sans', sans-serif;
      color: white;
      background-color:  #15803D;
      padding: 12px 20px;
      border: none;
      border-radius: 6px;
      text-decoration: none;
      font-weight: bold;
      font-size: 16px;
      cursor: pointer;
      display: inline-block;
    }

    .button:hover {
      background-color: #116530;
    }
    
    @media (max-width: 768px) {
      .image-title {
        font-size: 22px;
      }
      
      .highlight {
        padding: 15px 10px;
        font-size: 14px;
      }
      
      .content {
        padding: 15px;
      }
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

<body>
  <?php include 'header.php'; ?>

  <div class="card">
    <div class="header">MealForge</div>
    
    <div class="image-container">
      <img src="image_1.png" alt="Healthy Meals" class="image">
      <div class="image-title">About MealForge</div>
    </div>
    
    <div class="highlight">Your meal planner, powered by your needs.</div>

    <div class="content">
      <p>At MealForge, our mission is to bring balanced and healthy recipes to the table based on your needs. We believe eating well should be simple, affordable, and tailored to your unique preferences. Whether you're a busy student, a working professional, or a family on a budget, you deserve the finest of meals all accessible to you.</p>

      <h3>Our Story</h3>
      <p>MealForge was born out of a simple idea: meal planning shouldn't be stressful. As students juggling classes, part-time jobs, and meal prep, we struggled to eat healthily on a budget. We built MealForge to empower students and busy individuals to take control of their meals—without the unnecessary stress.</p>

      <h3>Why MealForge?</h3>
      <ul>
        <li><span aria-hidden="true">🛒</span> Local Grocery Integration: Save time and money with real-time store prices and deals.</li>
        <li><span aria-hidden="true">🌿</span> Health-First Approach: Prioritize nutrition without sacrificing flavor.</li>
        <li><span aria-hidden="true">🎓</span> Student-Friendly: Designed for busy schedules and tight budgets.</li>
        <li><span aria-hidden="true">🆓</span> Completely Free: Save your budget towards grocery shopping instead.</li>
      </ul>

      <h3>Ready to Simplify Meal Planning?</h3>
      <p>Join thousands of users already enjoying stress-free meals.</p>
      <div class="button-container">
        <a href="dashboard.php" class="button">Go to Dashboard<span class="sr-only"> to start planning meals</span></a>
      </div>
    </div>
  </div>
</body>
</html>

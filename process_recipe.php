<?php
session_start();
require_once 'dbconnect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $recipe_name = mysqli_real_escape_string($conn, $_POST['recipe_name']);
    $recipe_preptime = (int)$_POST['recipe_preptime'];
    $recipe_cookingtime = (int)$_POST['recipe_cookingtime'];
    $recipe_ingredient = mysqli_real_escape_string($conn, $_POST['recipe_ingredient']);
    $recipe_cookstep = mysqli_real_escape_string($conn, $_POST['recipe_cookstep']);
    $image_url = mysqli_real_escape_string($conn, $_POST['image_url']);
    $user_id = $_SESSION['user_id'];

    // Validate input
    if (empty($recipe_name) || empty($recipe_ingredient) || empty($recipe_cookstep) || empty($image_url)) {
        $_SESSION['error'] = "All fields are required";
        header("Location: add_recipe.php");
        exit();
    }

    // Insert recipe into database
    $sql = "INSERT INTO recipe (recipe_name, recipe_preptime, recipe_cookingtime, recipe_ingredient, recipe_cookstep, image_url, user_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siisssi", $recipe_name, $recipe_preptime, $recipe_cookingtime, $recipe_ingredient, $recipe_cookstep, $image_url, $user_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Recipe added successfully!";
        header("Location: recipepage/index.php"); // Redirect to recipe page
        exit();
    } else {
        $_SESSION['error'] = "Error adding recipe: " . $conn->error;
        header("Location: add_recipe.php");
        exit();
    }

    $stmt->close();
} else {
    header("Location: add_recipe.php");
    exit();
}

$conn->close();
?> 
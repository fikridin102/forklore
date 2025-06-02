<?php
include('dbconnect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $recipe_name = trim($_POST['recipe_name']);
    $category = trim($_POST['category']);
    $recipe_preptime = trim($_POST['recipe_preptime']);
    $recipe_cookingtime = trim($_POST['recipe_cookingtime']);
    $recipe_ingredient = trim($_POST['recipe_ingredient']);
    $recipe_cookstep = trim($_POST['recipe_cookstep']);
    $user_id = trim($_POST['user_id']);

    // Validate required fields
    if (empty($recipe_name) || empty($category) || empty($recipe_preptime) || 
        empty($recipe_cookingtime) || empty($recipe_ingredient) || empty($recipe_cookstep) || 
        empty($user_id)) {
        header("Location: all_recipes.php?error=missing_fields");
        exit();
    }

    // Handle file upload
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        if (!mkdir($target_dir, 0777, true)) {
            header("Location: all_recipes.php?error=upload_dir_failed");
            exit();
        }
    }

    // Check if file was uploaded
    if (!isset($_FILES["recipe_image"]) || $_FILES["recipe_image"]["error"] != 0) {
        header("Location: all_recipes.php?error=no_file");
        exit();
    }

    $file_extension = strtolower(pathinfo($_FILES["recipe_image"]["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["recipe_image"]["tmp_name"]);
    if($check === false) {
        header("Location: all_recipes.php?error=invalid_image");
        exit();
    }

    // Check file size (5MB max)
    if ($_FILES["recipe_image"]["size"] > 5000000) {
        header("Location: all_recipes.php?error=file_too_large");
        exit();
    }

    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
        header("Location: all_recipes.php?error=invalid_format");
        exit();
    }

    // Try to upload file
    if (move_uploaded_file($_FILES["recipe_image"]["tmp_name"], $target_file)) {
        // Insert into database
        $sql = "INSERT INTO recipe (recipe_name, category, recipe_preptime, recipe_cookingtime, recipe_ingredient, recipe_cookstep, image_url, user_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            unlink($target_file);
            header("Location: all_recipes.php?error=db_prepare_failed");
            exit();
        }

        $stmt->bind_param("ssiisssi", $recipe_name, $category, $recipe_preptime, $recipe_cookingtime, $recipe_ingredient, $recipe_cookstep, $target_file, $user_id);
        
        if ($stmt->execute()) {
            header("Location: all_recipes.php?success=1");
        } else {
            // If database insert fails, delete the uploaded file
            unlink($target_file);
            header("Location: all_recipes.php?error=db_insert_failed");
        }
        $stmt->close();
    } else {
        header("Location: all_recipes.php?error=upload_failed");
    }
} else {
    header("Location: all_recipes.php");
}
$conn->close();
?>

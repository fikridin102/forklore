<?php
session_start();
include('../dbconnect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $user_email = trim($_POST['user_email']);
    $user_fullname = trim($_POST['user_fullname']);

    $errors = [];
    if (empty($username)) $errors[] = "Username is required.";
    if (empty($password)) $errors[] = "Password is required.";
    if (empty($user_email)) $errors[] = "Email is required.";
    if (empty($user_fullname)) $errors[] = "Full name is required.";
    if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (count($errors) > 0) {
        foreach ($errors as $error) {
            echo "<div class='error' style='color:red;margin-bottom:10px;'>$error</div>";
        }
    } else {
        // Use prepared statement for SQL safety
        $query = "INSERT INTO user (username, user_password, user_email, user_fullname) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssss", $username, $password, $user_email, $user_fullname);
        if ($stmt->execute()) {
            echo "<script>alert('Registration successful!'); window.location.href = 'user_profile.php';</script>";
        } else {
            echo "<script>alert('Error: " . $conn->error . "'); window.history.back();</script>";
        }
    }
}
?>

<?php
session_start();
include('../dbconnect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Protect against SQL injection
    $username = $conn->real_escape_string($username);

    // Query to check login credentials
    $query = "SELECT * FROM user WHERE username = '$username'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc(); // Fetch user details
        $storedPassword = $user['user_password'];

        // Check if password is hashed or not
        if (password_verify($password, $storedPassword)) {
            // Login successful for hashed password
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];

                header('Location: user_profile.php');

            exit();
        } elseif ($password === $storedPassword) {
            // Login successful for plaintext password
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];

                header('Location:  user_profile.php');
            exit();
        } else {
            $error = "Invalid username, password, or role!";
        }
    } else {
        $error = "Invalid username, password, or role!";
    }
}
?>
<?php
session_start();
include('../dbconnect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $user_email = $_POST['user_email'];
    $user_fullname = $_POST['user_fullname'];

    // Sanitize inputs
    $username = $conn->real_escape_string($username);
    $user_email = $conn->real_escape_string($user_email);
    $user_fullname = $conn->real_escape_string($user_fullname);
    $password = $conn->real_escape_string($password);

    // Encrypt the password

    // Insert the user into the database
    $query = "INSERT INTO user (username, user_password, user_email, user_fullname) 
              VALUES ('$username', '$password', '$user_email', '$user_fullname')";

    if ($conn->query($query) === TRUE) {
        echo "<script>
            alert('Registration successful!');
            window.location.href = 'user_profile.php';
        </script>";
    } else {
        echo "<script>
            alert('Error: " . $conn->error . "');
            window.history.back();
        </script>";
    }
}
?>

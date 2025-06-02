<?php
session_start();
include('../dbconnect.php');

// Check user session
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: ../login.php");
    exit();
}

// Fetch user data
$result = $conn->query("SELECT * FROM user WHERE user_id = '$user_id'");
$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = $conn->real_escape_string($_POST['user_fullname']);
    $email = $conn->real_escape_string($_POST['user_email']);
    $password = $conn->real_escape_string($_POST['password']);
    $preferences = $conn->real_escape_string($_POST['preferences']);

    $profileImgPath = $user['profileImg'];
    if (!empty($_FILES['profileImg']['name'])) {
        $targetDir = "uploads/";
        $profileImgPath = $targetDir . basename($_FILES["profileImg"]["name"]);
        move_uploaded_file($_FILES["profileImg"]["tmp_name"], $profileImgPath);
    }

    $updateQuery = "UPDATE user SET 
        user_fullname = '$fullname',
        user_email = '$email',
        pefr = '$preferences',
        profileImg = '$profileImgPath'"
        . ($password ? ", user_password = '$password'" : "") .
        " WHERE user_id = '$user_id'";

    if ($conn->query($updateQuery) === TRUE) {
        echo "<script>alert('Profile updated successfully'); window.location.href='user_profile.php';</script>";
    } else {
        echo "<script>alert('Error: " . $conn->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="your-style.css">
    <style>
         @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700;800&display=swap");

* {
    margin: 0;
}

body {
    background-color: #e8f5ff;
  font-family: "Poppins", sans-serif;
    overflow: hidden;
}

  input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="file"] {
            width: 95%;
            padding: 8px;
            font-size: 14px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .main button {
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .main button:hover {
            background-color: #0056b3;
        }

        .main form label {
            display: block;
            margin-top: 15px;
            font-size: 15px;
        }

/* NavbarTop */
.navbar-top {
    background-color: #fff;
    color: #333;
    box-shadow: 0px 4px 8px 0px grey;
    height: 70px;
}

.title {
    padding-top: 15px;
    position: absolute;
    left: 45%;
}

.navbar-top ul {
    float: right;
    list-style-type: none;
    margin: 0;
    overflow: hidden;
    padding: 18px 50px 0 40px;
}

.navbar-top ul li {
    float: left;
}

.navbar-top ul li a {
    color: #333;
    padding: 14px 16px;
    text-align: center;
    text-decoration: none;
}

.icon-count {
    background-color: #ff0000;
    color: #fff;
    float: right;
    font-size: 11px;
    left: -25px;
    padding: 2px;
    position: relative;
}

/* End */

/* Sidenav */
.sidenav {
    background-color: #fff;
    color: #333;
    border-bottom-right-radius: 25px;
    height: 86%;
    left: 0;
    overflow-x: hidden;
    padding-top: 20px;
    position: absolute;
    top: 70px;
    width: 250px;
}

.profile {
    margin-bottom: 20px;
    margin-top: -12px;
    text-align: center;
}

.profile img {
    border-radius: 50%;
    box-shadow: 0px 0px 5px 1px grey;
}

.name {
    font-size: 20px;
    font-weight: bold;
    padding-top: 20px;
}

.job {
    font-size: 16px;
    font-weight: bold;
    padding-top: 10px;
}

.url, hr {
    text-align: center;
}

.url hr {
    margin-left: 20%;
    width: 60%;
}

.url a {
    color: #818181;
    display: block;
    font-size: 20px;
    margin: 10px 0;
    padding: 6px 8px;
    text-decoration: none;
}

.url a:hover, .url .active {
    background-color: #e8f5ff;
    border-radius: 28px;
    color: #000;
    margin-left: 14%;
    width: 65%;
}

/* End */

/* Main */
.main {
    margin-top: 2%;
    margin-left: 29%;
    font-size: 28px;
    padding: 0 10px;
    width: 58%;
}

.main h2 {
    color: #333;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 24px;
    margin-bottom: 10px;
}

.main .card {
    background-color: #fff;
    border-radius: 18px;
    box-shadow: 1px 1px 8px 0 grey;
    height: auto;
    margin-bottom: 20px;
    padding: 20px 0 20px 50px;
}

.main .card table {
    border: none;
    font-size: 16px;
    height: 270px;
    width: 80%;
}

.edit {
    position: absolute;
    color: #e7e7e8;
    right: 14%;
}


    </style>
</head>
<body>

<div class="navbar-top">
    <div class="title">
        <h1>Edit Profile</h1>
    </div>
</div>

<div class="sidenav">
    <div class="profile">
        <img src="<?= $user['profileImg'] ?? 'default.png' ?>" alt="Profile Photo" width="100" height="100">
        <div class="name"><?= htmlspecialchars($user['user_fullname']) ?></div>
    </div>

    <div class="sidenav-url">
        <div class="url">
            <a href="profile.php">Back to Profile</a>
            <hr align="center">
        </div>
    </div>
</div>

<div class="main">
    <h2>Edit Info</h2>
    <div class="card">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <label>Full Name</label>
                <input type="text" name="user_fullname" value="<?= htmlspecialchars($user['user_fullname']) ?>" required>

                <label>Email</label>
                <input type="email" name="user_email" value="<?= htmlspecialchars($user['user_email']) ?>" required>

                <label>New Password</label>
                <input type="password" name="password" placeholder="Leave blank to keep current password">

                <label>Preferences</label>
                <input type="text" name="preferences" value="<?= htmlspecialchars($user['pefr']) ?>" placeholder="e.g. Vegetarian, Gluten-Free">

                <label>Profile Photo</label><br>
                <?php if (!empty($user['profileImg'])): ?>
                    <img src="<?= $user['profileImg'] ?>" width="80" height="80" style="margin-top:10px;"><br><br>
                <?php endif; ?>
                <input type="file" name="profileImg">

                <button type="submit">Save Changes</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>

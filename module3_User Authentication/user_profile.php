<?php
session_start();
include('../dbconnect.php');

// Make sure user is logged in
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: index.php");
    exit();
}

// Get user data
$result = $conn->query("SELECT * FROM user WHERE user_id = '$user_id'");
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile</title>
    <link rel="stylesheet" href="your-style.css"> <!-- Link to your CSS file -->
</head>
<body>

<div class="navbar-top">
    <div class="title">
        <h1>Profile</h1>
    </div>
</div>

<div class="sidenav">
    <div class="profile">
        <img src="<?= $user['profileImg'] ?? 'default.png' ?>" alt="Profile Photo" width="100" height="100">
        <div class="name"><?= htmlspecialchars($user['user_fullname']) ?></div>
    </div>

    <div class="sidenav-url">
        <div class="url">
            <a href="#profile" class="active">Profile</a>
            <hr align="center">
        </div>
        <div class="url">
            <a href="edit_profile.php">Edit Profile</a>
            <hr align="center">
        </div>
                <div class="url">
            <a href="logout.php">Logout</a>
            <hr align="center">
        </div>
    </div>
</div>

<div class="main">
    <h2>Personal Info</h2>
    <div class="card">
        <div class="card-body">
            <i class="fa fa-pen fa-xs edit"></i>
            <table>
                <tbody>
                    <tr>
                        <td>Full name</td>
                        <td>:</td>
                        <td><?= htmlspecialchars($user['user_fullname']) ?></td>
                    </tr>
                    <tr>
                        <td>Username</td>
                        <td>:</td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td>:</td>
                        <td><?= htmlspecialchars($user['user_email']) ?></td>
                    </tr>
                    <tr>
                        <td>Preferences</td>
                        <td>:</td>
                        <td><?= htmlspecialchars($user['pefr'] ?? 'Not set') ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>


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
<?php
session_start();
include('../dbconnect.php');


if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM user WHERE user_id = '$user_id'");
$user = $result ? $result->fetch_assoc() : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f5f7fa;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .navbar {
            background-color: #fff;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .navbar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .back-button {
            text-decoration: none;
            background: #4f8cff;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: background-color 0.3s;
        }

        .back-button:hover {
            background: #3a7bff;
        }

        .page-title {
            font-size: 1.5rem;
            color: #333;
            font-weight: 600;
        }

        .profile-container {
            display: flex;
            gap: 30px;
            margin-top: 100px;
        }

        .sidebar {
            width: 300px;
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            height: fit-content;
        }

        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: block;
            object-fit: cover;
            border: 4px solid #4f8cff;
        }

        .profile-name {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 30px;
        }

        .nav-links {
            list-style: none;
        }

        .nav-links li {
            margin-bottom: 10px;
        }

        .nav-links a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: #666;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .nav-links a:hover, .nav-links a.active {
            background: #4f8cff;
            color: white;
        }

        .nav-links i {
            margin-right: 10px;
            width: 20px;
        }

        .main-content {
            flex: 1;
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .section-title {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .info-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            transition: transform 0.3s;
        }

        .info-card:hover {
            transform: translateY(-5px);
        }

        .info-label {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .info-value {
            color: #333;
            font-size: 1.1rem;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .profile-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <a href="../all_recipes.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
            <h1 class="page-title">Profile</h1>
        </div>
    </nav>

    <div class="container">
        <div class="profile-container">
            <div class="sidebar">
                <img src="<?= isset($user['profileImg']) && $user['profileImg'] ? $user['profileImg'] : 'default.png' ?>" alt="Profile Photo" class="profile-image">
                <h2 class="profile-name"><?= isset($user['user_fullname']) ? htmlspecialchars($user['user_fullname']) : 'Unknown User' ?></h2>
                <ul class="nav-links">
                    <li>
                        <a href="#profile" class="active">
                            <i class="fas fa-user"></i> Profile
                        </a>
                    </li>
                    <li>
                        <a href="edit_profile.php">
                            <i class="fas fa-edit"></i> Edit Profile
                        </a>
                    </li>
                    <li>
                        <a href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>

            <div class="main-content">
                <h2 class="section-title">Personal Information</h2>
                <?php if ($user): ?>
                <div class="info-grid">
                    <div class="info-card">
                        <div class="info-label">Full Name</div>
                        <div class="info-value"><?= htmlspecialchars($user['user_fullname']) ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Username</div>
                        <div class="info-value"><?= htmlspecialchars($user['username']) ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?= htmlspecialchars($user['user_email']) ?></div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Preferences</div>
                        <div class="info-value"><?= htmlspecialchars($user['pefr'] ?? 'Not set') ?></div>
                    </div>
                </div>
                <?php else: ?>
                <div class="info-grid">
                    <div class="info-card">
                        <div class="info-label">Error</div>
                        <div class="info-value">User data not found.</div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
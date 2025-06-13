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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $fullname = trim($_POST['user_fullname']);
    $email = trim($_POST['user_email']);
    $password = trim($_POST['password']);
    $preferences = trim($_POST['preferences']);

    $errors = [];
    if (empty($fullname)) $errors[] = "Full name is required.";
    if (empty($email)) $errors[] = "Email is required.";
    if (empty($preferences)) $errors[] = "Preferences are required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (count($errors) > 0) {
        foreach ($errors as $error) {
            echo "<div class='error' style='color:red;margin-bottom:10px;'>$error</div>";
        }
    } else {
        $profileImgPath = $user['profileImg'];
        if (!empty($_FILES['profileImg']['name'])) {
            $targetDir = "uploads/";
            $profileImgPath = $targetDir . basename($_FILES["profileImg"]["name"]);
            move_uploaded_file($_FILES["profileImg"]["tmp_name"], $profileImgPath);
        }
        $updateQuery = "UPDATE user SET user_fullname=?, user_email=?, pefr=?, profileImg=?";
        $params = [$fullname, $email, $preferences, $profileImgPath];
        $types = "ssss";
        if (!empty($password)) {
            $updateQuery .= ", user_password=?";
            $params[] = $password;
            $types .= "s";
        }
        $updateQuery .= " WHERE user_id=?";
        $params[] = $user_id;
        $types .= "i";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            echo "<script>alert('Profile updated successfully'); window.location.href='user_profile.php';</script>";
        } else {
            echo "<script>alert('Error: " . $conn->error . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
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

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #4f8cff;
            box-shadow: 0 0 0 2px rgba(79, 140, 255, 0.1);
        }

        .profile-image-upload {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        .profile-image-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #4f8cff;
        }

        .file-upload {
            position: relative;
            overflow: hidden;
            display: inline-block;
        }

        .file-upload input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
        }

        .file-upload-label {
            display: inline-block;
            padding: 8px 16px;
            background: #4f8cff;
            color: white;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .file-upload-label:hover {
            background: #3a7bff;
        }

        .submit-button {
            background: #4f8cff;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .submit-button:hover {
            background: #3a7bff;
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
            <a href="user_profile.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Back to Profile
            </a>
            <h1 class="page-title">Edit Profile</h1>
        </div>
    </nav>

    <div class="container">
        <div class="profile-container">
            <div class="sidebar">
                <img src="<?= isset($user['profileImg']) && $user['profileImg'] ? $user['profileImg'] : 'default.png' ?>" alt="Profile Photo" class="profile-image">
                <h2 class="profile-name"><?= isset($user['user_fullname']) ? htmlspecialchars($user['user_fullname']) : 'Unknown User' ?></h2>
                <ul class="nav-links">
                    <li>
                        <a href="user_profile.php">
                            <i class="fas fa-user"></i> Profile
                        </a>
                    </li>
                    <li>
                        <a href="edit_profile.php" class="active">
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
                <h2 class="section-title">Edit Personal Information</h2>
                <?php if ($user): ?>
                <form method="POST" enctype="multipart/form-data">
                    <div class="profile-image-upload">
                        <img src="<?= isset($user['profileImg']) && $user['profileImg'] ? $user['profileImg'] : 'default.png' ?>" alt="Profile Preview" class="profile-image-preview" id="profile-preview">
                        <div class="file-upload">
                            <label class="file-upload-label">
                                <i class="fas fa-camera"></i> Change Photo
                                <input type="file" name="profileImg" accept="image/*" onchange="previewImage(this)">
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="user_fullname">Full Name</label>
                        <input type="text" id="user_fullname" name="user_fullname" class="form-control" value="<?= htmlspecialchars($user['user_fullname']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="user_email">Email</label>
                        <input type="email" id="user_email" name="user_email" class="form-control" value="<?= htmlspecialchars($user['user_email']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="password">New Password (leave blank to keep current)</label>
                        <input type="password" id="password" name="password" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="preferences">Preferences</label>
                        <select id="preferences" name="preferences" class="form-control" required>
                            <option value="">-- Select Preference --</option>
                            <option value="Vegetarian" <?= isset($user['pefr']) && $user['pefr'] == 'Vegetarian' ? 'selected' : '' ?>>Vegetarian</option>
                            <option value="Vegan" <?= isset($user['pefr']) && $user['pefr'] == 'Vegan' ? 'selected' : '' ?>>Vegan</option>
                            <option value="Gluten-Free" <?= isset($user['pefr']) && $user['pefr'] == 'Gluten-Free' ? 'selected' : '' ?>>Gluten-Free</option>
                            <option value="All" <?= isset($user['pefr']) && $user['pefr'] == 'All' ? 'selected' : '' ?>>All</option>
                        </select>
                    </div>

                    <button type="submit" class="submit-button">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
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

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profile-preview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>

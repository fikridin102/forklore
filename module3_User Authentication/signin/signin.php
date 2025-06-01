<?php

session_start();
include('../../dbconnect.php');

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: signin.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'login') {
            // Handle login
            $username = $_POST['username'];
            $password = $_POST['password'];

            // Protect against SQL injection
            $stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $storedPassword = $user['user_password'];

                // Check if password is hashed or not
                if (password_verify($password, $storedPassword) || $password === $storedPassword) {
                    // Login successful
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    
                    header('Location: ../../all_recipes.php');
                    exit();
                } else {
                    $error = "Invalid password!";
                }
            } else {
                $error = "User not found!";
            }
            $stmt->close();
        } elseif ($_POST['action'] === 'register') {
            // Handle registration
            $username = $_POST['reg_username']; 
            $email = $_POST['reg_email'];
            $password = $_POST['reg_password'];

            // Check if username already exists
            $stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error = "Username already exists!";
            } else {
                // Hash the password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Insert new user
                $stmt = $conn->prepare("INSERT INTO user (username, user_email, user_password, userRole) VALUES (?, ?, ?, 'user')");
                $stmt->bind_param("sss", $username, $email, $hashedPassword);
                
                if ($stmt->execute()) {
                    $success = "Registration successful! Please login.";
                } else {
                    $error = "Registration failed!";
                }
            }
            $stmt->close();
        }
    }
}
?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width" , initial-scale=1.0>
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <Title>Sign Up</Title>
    <link rel="stylesheet" type="text/css" href="../../assets/styling/signin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    


</head>

<body>
    <div class="container">
      <div class="forms-container">
        <div class="signin-signup">



          <form action="" method="post" class="sign-in-form">

            <h2 class="title">login</h2>
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>
            <input type="hidden" name="action" value="login">
            <div class="input-field">
              <i class="fas fa-user"></i>
              <input name="username" type="text" placeholder="Username" required />
            </div>
            <div class="input-field">
              <i class="fas fa-lock"></i>
              <input name="password" type="password" placeholder="Password" required />
            </div>
            <button type="submit" id="button-1" class="button">Login</button>

          </form>
          <form action="" method="post" class="sign-up-form">
            <h2 class="title">Sign up</h2>
            <input type="hidden" name="action" value="register">
            <div class="input-field">
              <i class="fas fa-user"></i>
              <input name="reg_username" type="text" placeholder="username" required />
            </div>
            <div class="input-field">
              <i class="fas fa-envelope"></i>
              <input name="reg_email" type="email" placeholder="Email" required />
              <!-- 'required' attribute ensures that the field must be filled before submitting -->
          </div>
            <div class="input-field">
              <i class="fas fa-lock"></i>
              <input name="reg_password" type="password" placeholder="password" required />
            </div>
            <button type="submit" id="button-2" class="button">Sign Up</button>
            <p class="social-text"></p>
            
          </form>
        </div>
      </div>

      <div class="panels-container">
        <div class="panel left-panel">
          <div class="content">
            <h3>New here ?</h3>
            <p>
                Feast On The Global Plate: Explore the world, one dish at a time.
            </p>
            <button class="btn transparent" id="sign-up-btn">
              Sign up
            </button>
          </div>
          <img src="img/log.svg" class="image" alt="" /> <!-- TODO: Add log.svg or update path -->
        </div>
        <div class="panel right-panel">
          <div class="content">
            <h3>One of us ?</h3>
            <p>
            Enter The Recipe Vault: Unlock a world of delicious possibilities.
            </p>
            <button class="btn transparent" id="sign-in-btn">
              <a href="../signin/signin.html"></a>
              Sign in
            </button>
          </div>
          <img src="img/register.svg" class="image" alt="" /> <!-- TODO: Add register.svg or update path -->
        </div>
      </div>
    </div>
  </body>
    
 <script src="signin.js"></script>
</html>
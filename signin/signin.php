<?php
//fdghfhgfhfghfghfghfghfghfghf
session_start();
include('../db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Protect against SQL injection
    $username = $conn->real_escape_string($username);

    // Query to check login credentials
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc(); // Fetch user details
        $storedPassword = $user['userpass'];

        // Check if password is hashed or not
        if (password_verify($password, $storedPassword)) {
            // Login successful for hashed password
            $_SESSION['userID'] = $user['userID'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['userRole'] = $user['userRole'];

                header('Location: ../choicepage/choice.html');

            exit();
        } elseif ($password === $storedPassword) {
            // Login successful for plaintext password
            $_SESSION['userID'] = $user['userID'];
            $_SESSION['username'] = $user['username'];

                header('Location: ../choicepage/choice.html');
            exit();
        } else {
            $error = "Invalid username, password, or role!";
        }
    } else {
        $error = "Invalid username, password, or role!";
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
    <link rel="stylesheet" type="text/css" href="../assets/styling/signin.css">
    


</head>

<body>
    <div class="container">
      <div class="forms-container">
        <div class="signin-signup">




          <form action="#" method="post" class="sign-in-form">
            <h2 class="title">login</h2>
            <div class="input-field">
              <i class="fas fa-user"></i>
              <input name="username" type="text" placeholder="Username" />
            </div>
            <div class="input-field">
              <i class="fas fa-lock"></i>
              <input name="password" type="password" placeholder="Password" />
            </div>
            <button type="submit" id="button-1" class="button">Login</button>

          </form>
          <form action="#" method="post" class="sign-up-form">
            <h2 class="title">Sign up</h2>
            <div class="input-field">
              <i class="fas fa-user"></i>
              <input type="text" placeholder="username" />
            </div>
            <div class="input-field">
              <i class="fas fa-envelope"></i>
              <input type="email" placeholder="Email" required />
              <!-- 'required' attribute ensures that the field must be filled before submitting -->
          </div>
            <div class="input-field">
              <i class="fas fa-lock"></i>
              <input type="password" placeholder="password" />
            </div>
            <button id="button-1" class="button"><a href="../choicepage/choice.html">Sign Up</a></button>
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
          <img src="img/log.svg" class="image" alt="" />
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
          <img src="img/register.svg" class="image" alt="" />
        </div>
      </div>
    </div>
  </body>
    
 <script src="../signin/signin.js"></script>
</html>
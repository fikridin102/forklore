<?php
session_start();
include('dbconnect.php');

?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width" , initial-scale=1.0>
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <Title>Sign Up</Title>
    <link rel="stylesheet" type="text/css" href="assets/styling/signin.css">
    


</head>

<body>
    <div class="container">
      <div class="forms-container">
        <div class="signin-signup">



 
          <form action="module3_User Authentication/login.php" method="POST" class="sign-in-form">
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
          <form action="module3_User Authentication/register.php" method="post" class="sign-up-form">
            <h2 class="title">Sign up</h2>
              <div class="input-field">
              <i class="fas fa-user"></i>
              <input type="text" name="user_fullname" placeholder="Full Name" />
            </div>
            <div class="input-field">
              <i class="fas fa-user"></i>
              <input type="text" name="username" placeholder="Username" />
            </div>
            <div class="input-field">
              <i class="fas fa-envelope"></i>
              <input type="email" name="user_email" placeholder="Email" required />
             
          </div>
            <div class="input-field">
              <i class="fas fa-lock"></i>
              <input type="password" name="password" placeholder="Password" />
            </div>
            <button id="button-1" type="submit" class="button">Sign Up<</button>
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
    
 <script src="module3_User Authentication/signin.js"></script>
</html>
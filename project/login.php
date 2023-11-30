<?php include('server.php') ?>
<!DOCTYPE html>
<html>

<head>
  <title>Login - VC Voting System</title>
  <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
  <div class="header">
    <h2>Login</h2>
    <?php if (isset($vcVotingSystemMessage)): ?>
      <p style="font-weight: bold; color: black;">
        <?php echo $vcVotingSystemMessage; ?>
      </p>
    <?php endif; ?>
  </div>

  <form method="post" action="login.php">
    <?php include('errors.php'); ?>
    <div class="input-group">
      <label>Registered Email ID</label>
      <input type="text" name="email">
    </div>
    <div class="input-group">
      <label>Password</label>
      <input type="password" name="password">
    </div>
    <div class="input-group">
      <button type="submit" class="btn" name="login_user">Login</button>
      <a href="upload.php" class="home-link"><img src='home.webp' alt='Dynamic Image' style='width: 40px;'></a>
    </div>

  </form>
</body>

</html>
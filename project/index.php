<?php 
  session_start(); 

  if (!isset($_SESSION['voter_id'])) {
  	$_SESSION['msg'] = "You must verify first";
  	header('location: upload.php');
  }
  if (isset($_GET['logout'])) {
  	session_destroy();
  	unset($_SESSION['voter_id']);
  	header("location: upload.php");
  }
?>

<!DOCTYPE html>
<html>
<head>
	<title>Home</title>
	<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>

<div class="header">
	<h2>Home Page</h2>
  	<!-- notification message -->
  	<?php if (isset($_SESSION['success'])) : ?>
      <div class="error success" >
      	<h3>
          <?php 
          	echo $_SESSION['success']; 
          	unset($_SESSION['success']);
          ?>
      	</h3>
      </div>
  	<?php endif ?>

    <!-- logged in user information -->
    <?php  if (isset($_SESSION['voter_id'])) : ?>
    	<p>Welcome <strong><?php echo $_SESSION['username']; ?></strong></p>
		<p>Your Voter ID is <strong><?php echo $_SESSION['voter_id']; ?></strong></p>
    	<p> <a href="index.php?logout='1'" style="color: red;">logout</a></p>
    <?php endif ?>
</div>

</body>
</html>
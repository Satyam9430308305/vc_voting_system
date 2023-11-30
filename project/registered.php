<?php include('server.php') ?>
<!DOCTYPE html>
<html>

<head>
	<title>Registered!!</title>
	<link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>

	<!-- notification message -->
	<?php if (isset($_SESSION['success'])): ?>
		<div class="header">
			<h2>Registered</h2>
			<div class="error success">
				<h3>
					<?php
					echo $_SESSION['success'];

					?>
				</h3>
			</div>
			<p>Thank You for registering, <strong>
					<?php echo $_SESSION['username']; ?>
				</strong></p><br>
			<p>Your Voter ID is <strong>
					<?php echo $_SESSION['voter_id']; ?>
				</strong></p>
			<p>Your account has been successfully registered. <br>Shares have been sent to your registered email.</p>
			<br><br><br>
			<p> <a href="upload.php" style="color: black;">VERIFY</a></p>
		</div>
	<?php endif ?>
	
	<?php
	unset($_SESSION['success']);
	unset($_SESSION['voter_id']);
	unset($_SESSION['username']);
	?>

</body>
</html>
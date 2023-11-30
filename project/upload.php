<?php include('server.php') ?>
<!DOCTYPE html>
<html>

<head>
  <title>Upload - VC Voting System  </title>
  <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
  <div class="header">
    <h2>Verification</h2>
    <?php if (isset($vcVotingSystemMessage)): ?>
      <p style="font-weight: bold; color: black;">
        <?php echo $vcVotingSystemMessage; ?>
      </p>
    <?php endif; ?>
  </div>

  <form method="post" action="upload.php" enctype="multipart/form-data">
    <?php include('errors.php'); ?>
    <div class="input-group">
      <label>Voter ID</label>
      <input type="text" name="voter_id">
    </div>
    <div class="input-group">
      <label>ID Image</label>
      <input type="file" name="image" accept="image/*">
    </div>
    <div class="input-group">
      <button type="submit" class="btn" name="uploaded">Upload</button>
    </div>
    <p>
      Not yet a member? <a href="register.php">Sign up</a>
    </p>
  </form>
</body>

</html>
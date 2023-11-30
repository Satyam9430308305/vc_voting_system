<?php
include('server.php');
$voter_id = isset($_GET['voter_id']) ? urldecode($_GET['voter_id']) : null;
?>
<!DOCTYPE html>
<html>

<head>
  <title>Verification - VC Voting System </title>
  <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
  <div class="header_img">
    <h2>Verification</h2>
    <?php if (isset($vcVotingSystemMessage)): ?>
      <p style="font-weight: bold; color: black;">
        <?php echo $vcVotingSystemMessage; ?>
      </p>
    <?php endif; ?>
  </div>

  <form method="post" action="verify.php?voter_id=<?php echo urlencode($voter_id); ?>" enctype="multipart/form-data"
    style="width:50%">
    <?php include('errors.php'); ?>
    <div class="error success">
      <?php
      try {
        // Logic to determine the dynamic image source
        $uploadsDirectory = "uploads";
        $imageSource = $uploadsDirectory . DIRECTORY_SEPARATOR . $voter_id . "_merged.png";

        // Check if the image file exists
        if (!file_exists($imageSource)) {
          throw new Exception("Image not found: $imageSource");
        }

        // Display the image
        echo "<img src='$imageSource' alt='Dynamic Image' style='width: 60%;'>";

      } catch (Exception $e) {
        // Handle the exception
        echo $voter_id . "<p>Error: " . $e->getMessage() . "</p>";
      }
      ?>
    </div>
    
    <?php if (empty($e)): ?>
      <div class="input-group">
        <label for="enteredValue">Enter the CAPTCHA shown in the image:</label>
        <input type="text" name="captcha" required>
        <button type="submit" class="btn" name="verify_user">Verify</button>
        <p class="home-link"> <a href="upload.php">Reupload ?</a></p>
      </div>
    <?php else: ?>
      <div class="input-group">
        <p style='text-align:center'> <a href="upload.php">Reupload ?</a></p>
      </div>
    <?php endif; ?>

  </form>

</body>

</html>
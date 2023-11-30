<?php
session_start();

// initializing variables
$voter_id = "";
$username = "";
$email = "";
$errors = array();
$vcVotingSystemMessage = "Welcome to Voting System!®";


//voter id generator
function generateUniqueID($conn)
{
  // Prefix for the unique ID
  $prefix = "VC";

  do {
    // Seed the random number generator with the current timestamp
    mt_srand(time());

    // Generate a random 9-digit number
    $randomNumber = mt_rand(100000000, 999999999);

    // Concatenate the prefix and random number
    $uniqueID = $prefix . $randomNumber;

    // Check if the ID already exists in the database
    $query = "SELECT COUNT(*) FROM login WHERE voter_id = '$uniqueID'";
    $result = mysqli_query($conn, $query);

    if (!$result) {
      die("Error checking for existing ID: " . mysqli_error($conn));
    }

    $count = mysqli_fetch_row($result)[0];

  } while ($count > 0);

  return $uniqueID;
}


// connect to the database
$db = mysqli_connect('localhost', 'root', '', 'voting_system');

// REGISTER USER
if (isset($_POST['reg_user'])) {
  // receive all input values from the form
  $username = mysqli_real_escape_string($db, $_POST['username']);
  $email = mysqli_real_escape_string($db, $_POST['email']);
  $password_1 = mysqli_real_escape_string($db, $_POST['password_1']);
  $password_2 = mysqli_real_escape_string($db, $_POST['password_2']);

  // form validation: ensure that the form is correctly filled ...
  // by adding (array_push()) corresponding error unto $errors array
  if (empty($username)) {
    array_push($errors, "Username is required");
  }
  if (empty($email)) {
    array_push($errors, "Email is required");
  }
  if (empty($password_1)) {
    array_push($errors, "Password is required");
  } elseif ($password_1 != $password_2) {
    array_push($errors, "The two passwords do not match");
  }

  // first check the database to make sure 
  // a user does not already exist with the same  email
  $user_check_query = "SELECT * FROM login WHERE email='$email' LIMIT 1";
  $result = mysqli_query($db, $user_check_query);
  $user = mysqli_fetch_assoc($result);
  if ($user) { // if user exists
    if ($user['email'] === $email) {
      array_push($errors, "email already exists");
    }
  }


  // Finally, register user if there are no errors in the form
  if (count($errors) == 0) {
    $password = md5($password_1); //hash the password before saving in the database

    //generate voter_id
    $newUniqueID = generateUniqueID($db);

    $query = "INSERT INTO login (voter_id,username, email, password) 
  			  VALUES('$newUniqueID','$username', '$email', '$password')";
    $result = mysqli_query($db, $query);
    $_SESSION['voter_id'] = $newUniqueID;
    $_SESSION['username'] = $username;
    $_SESSION['success'] = "You are now Registered";

    if ($result) {
      // Registration successful
      // Trigger the generation of shares using generate_shares.py
      $output = shell_exec("python generate_shares.py $email $newUniqueID");
      if (strpos($output, "Error") !== false) {
        // If Python script returns an error, handle accordingly
        unset($_SESSION['success']);
        unset($_SESSION['voter_id']);
        $query = "DELETE FROM login WHERE voter_id = '$uniqueID'";
        $result = mysqli_query($db, $query);
        $query = "DELETE FROM shares WHERE voter_id = '$uniqueID'";
        $result = mysqli_query($db, $query);
        header("Location: reg_error.php");
      } else {
        header("Location: registered.php");
      }
    } else {
      unset($_SESSION['success']);
      unset($_SESSION['voter_id']);
      $query = "DELETE FROM login WHERE voter_id = '$uniqueID'";
      $result = mysqli_query($db, $query);
      $query = "DELETE FROM shares WHERE voter_id = '$uniqueID'";
      $result = mysqli_query($db, $query);
      header("Location: reg_error.php");
    }
  }
}

//upload
if (isset($_POST['uploaded'])) {
  $voter_id = mysqli_real_escape_string($db, $_POST['voter_id']);
  $target_dir = "uploads/"; // Set your upload directory
  $image = $_FILES["image"]["name"];

  if (empty($voter_id)) {
    array_push($errors, $err = "Voter ID is required");
  }
  if (empty($image)) {
    array_push($errors, $err = "ID Image is required");
  }

  $image = $target_dir . basename($_FILES["image"]["name"]);
  $query = "SELECT * FROM login WHERE voter_id = '$voter_id'";
  $result = mysqli_query($db, $query);
  //move_uploaded_file($_FILES["image"]["tmp_name"], $image);

  if (count($errors) == 0 && mysqli_num_rows($result) > 0) {
    // Handle the uploaded image
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($image, PATHINFO_EXTENSION));

    // Check if image file is a actual image or fake image
    if (isset($_POST["uploaded"])) {
      $check = getimagesize($_FILES["image"]["tmp_name"]);
      if ($check == false) {
        $uploadOk = 0;
        $err = "File is not an image.";
        array_push($errors, $err);
      } elseif ($_FILES["image"]["size"] > 10000000) {
        // Check file size
        $uploadOk = 0;
        $err = "Sorry, your file is too large.";
        array_push($errors, $err);
      } elseif ($imageFileType != "png") {
        //png only
        $uploadOk = 0;
        $err = "Sorry, only png file is allowed.";
        array_push($errors, $err);
      }
      if ($uploadOk == 1) {
        //move file to upload
        move_uploaded_file($_FILES["image"]["tmp_name"], $image);
        $merged = shell_exec("python decrypt.py $voter_id $image");
        header("location: verify.php?voter_id=$voter_id");



      }
    } else if (count($errors) == 0) {
      array_push($errors, $err = "Voter ID not found!");
    }
  }

}

//verify
if (isset($_POST['verify_user'])) {
  $text = mysqli_real_escape_string($db, $_POST['captcha']);
  $voter_id = mysqli_real_escape_string($db, $_GET['voter_id']);
  if (empty($text)) {
    array_push($errors, "CAPTCHA is required");
  }
  if (count($errors) == 0) {
    $query = "SELECT captcha FROM shares WHERE voter_id='$voter_id'";
    $results = mysqli_query($db, $query);
    $row = mysqli_fetch_assoc($results);

    $captcha = $row['captcha'];

    if ($text == $captcha) {
      $vcVotingSystemMessage = "Please enter email and password";
      header('location: login.php');
    } else {
      array_push($errors, "Unable to fetch captcha from database");
    }
  } else {
    array_push($errors, "Unable to fetch captcha from database");
  }
}


// LOGIN USER
if (isset($_POST['login_user'])) {
  $email = mysqli_real_escape_string($db, $_POST['email']);
  $password = mysqli_real_escape_string($db, $_POST['password']);

  if (empty($email)) {
    array_push($errors, "Email ID required n");
  }
  if (empty($password)) {
    array_push($errors, "Password is required");
  }

  if (count($errors) == 0) {
    $password = md5($password);
    $query = "SELECT * FROM login WHERE email='$email' AND password='$password'";
    $results = mysqli_query($db, $query);
    if (mysqli_num_rows($results) == 1) {
      $query = "SELECT * FROM login WHERE email='$email' AND password='$password'";
      $results = mysqli_query($db, $query);
      $row = mysqli_fetch_assoc($results);

      $voter_id = $row['voter_id']; 
      $username = $row['username'];
      $voter_id = mysqli_real_escape_string($db, $voter_id);
      $_SESSION['voter_id'] = $voter_id;
      
      $_SESSION['success'] = "You are now logged in";
      $user_check_query = "SELECT username FROM login WHERE voter_id='$voter_id'";
      $result = mysqli_query($db, $user_check_query);
      $user = mysqli_fetch_assoc($result);
      $username = $user['username'];
      $username = mysqli_real_escape_string($db, $username);
      $_SESSION['username'] = $username;
      header('location: index.php');
    } else {
      unset($email);
      unset($password);
      array_push($errors, "Wrong username/password combination");
    }
  }
}

?>
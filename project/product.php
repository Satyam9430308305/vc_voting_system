<!DOCTYPE html>
<html>
<body>

    <?php
    session_start();

    // initializing variables
    $username = "";
    $email = "";
    $errors = array();

    // connect to the database
    $db = mysqli_connect('localhost', 'root', '', 'login page');

    if ($db->connect_error) {
        die("Connection failed: " . $db->connect_error);
    }

    $sql = "SELECT id, username, email, password FROM users";
    $result = $db->query($sql);

    if ($result->num_rows > 0) {
        // output data of each row
        while ($row = $result->fetch_assoc()) {
            print "<br> -id: " . $row["id"] . "<br> - Name: " . $row["username"] . "<br> - Email: " . $row["email"] . "<br> - password: " . $row["password"] . "<br>";

        }
    } else {
        print "0 results";
    }

    $db->close();
    ?>
</body>
</html>
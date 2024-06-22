<?php
require('mysqli_connection.php');
session_start();
$mysqli = new mysqli($host, $username, $password, $dbname);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

# For login
if (isset($_POST['login'])) {
    $emailusername = $_POST['email_username'];
    $password = $_POST['password'];

    // Validate input
    if (empty($emailusername) || empty($password)) {
        $_SESSION['login_error'] ='Please fill in all fields.';
        header("Location: index1.php");
        exit;
    }

    $query = "SELECT * FROM `registered_user` WHERE `email`= ? OR `username`=?";
    $statement = $mysqli->prepare($query);
    $statement->bind_param("ss", $emailusername, $emailusername);
    $statement->execute();

    // Get the result
    $result = $statement->get_result();

    if ($result->num_rows == 1) {
        $result_fetch = $result->fetch_assoc();
        if (password_verify($password, $result_fetch['password'])) {
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $result_fetch['username'];
            $_SESSION['id'] = $result_fetch['id']; // Set the user's ID

            header("Location:index1.php"); // Redirect to the user dashboard
            exit;
        } else {
            $_SESSION['login_error'] ='Incorrect Password';
            header("Location: index1.php");
            exit;
        }
    } else {
        $_SESSION['login_error'] ='Email or Username Not Registered.';
            header("Location: index1.php");
            exit;
    }
    
    // Close the statement
    $statement->close();
}

# For registration
if (isset($_POST['register'])) {
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validate input
    if (empty($fullname) || empty($username) || empty($email) || empty($password)) {
        $_SESSION['register_error'] = 'Please fill in all fields. Try to register again.';
        header("Location: index1.php");
        exit;
    }
    // Validate full name
    if (str_word_count($fullname) < 2) {
        $_SESSION['register_error'] = 'Full name should contain at least two words. Please try to register again using spaces.';
        header("Location: index1.php");
        exit;
    }
// Validate username
if (!preg_match("/^[a-zA-Z0-9_]{5,}$/", $username)) {
    $_SESSION['register_error'] = 'Invalid username. Please try to register again with username of at least five characters, using only letters, numbers, and underscores.';
    header("Location: index1.php");
    exit;
}

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['register_error'] = 'Invalid email format.Try with a valid email.';
    header("Location: index1.php");
    exit;
        
    }
// Validate password strength
$uppercase = preg_match('@[A-Z]@', $password);
$lowercase = preg_match('@[a-z]@', $password);
$number = preg_match('@[0-9]@', $password);
$specialChar = preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password);
$noSpaces = (strpos($password, ' ') === false); // Check for spaces
$length = strlen($password) >= 8; // Minimum length of 12 characters
$atLeastThreeTypes = $uppercase + $lowercase + $number + $specialChar >= 3; // At least three character types

if (!$uppercase || !$lowercase || !$number || !$specialChar || !$noSpaces || !$length || !$atLeastThreeTypes) {
    $_SESSION['register_error'] = 'Password must meet enhanced criteria:\\n\\n- At least 8 characters long\\n- Contains at least one uppercase letter\\n- Contains at least one lowercase letter\\n- Contains at least one number\\n- Contains at least one special character\\n- Does not contain spaces\\n- Uses at least three different character types (uppercase, lowercase, number, special character';
    header("Location: index1.php");
    exit;
}
// Check if the username is already taken
$check_username_query = "SELECT * FROM `registered_user` WHERE `username` = ?";
$check_username_statement = $mysqli->prepare($check_username_query);

// Bind parameter
$check_username_statement->bind_param("s", $username);

// Execute the query
$check_username_statement->execute();

// Get the result
$result_username = $check_username_statement->get_result();

// Check if any rows exist in the result set
if ($result_username->num_rows > 0) {
    $_SESSION['register_error'] = 'Username already taken. Try a different one.';
    header("Location: index1.php");
    exit;
}
$check_username_statement->close(); // Close the statement before proceeding with registration

// Check if the email is already registered
$check_email_query = "SELECT * FROM `registered_user` WHERE `email` = ?";
$check_email_statement = $mysqli->prepare($check_email_query);

// Bind parameter
$check_email_statement->bind_param("s", $email);

// Execute the query
$check_email_statement->execute();

// Get the result
$result_email = $check_email_statement->get_result();

// Check if any rows exist in the result set
if ($result_email->num_rows > 0) {
    $_SESSION['register_error'] = 'Email already registered. Try a different one.';
    header("Location: index1.php");
    exit;
}
$check_email_statement->close(); // Close the statement before proceeding with registration
       
$password = password_hash($password, PASSWORD_BCRYPT);

        // Prepare the INSERT query
        $insert_query= "INSERT INTO `registered_user`(`full_name`, `username`, `email`, `password`) VALUES (?,?,?,?)";
        $insert_statement = $mysqli->prepare($insert_query);

        // Bind parameters
        $insert_statement->bind_param("ssss", $fullname, $username, $email, $password);

        // Execute the query
        $result = $insert_statement->execute();

        if ($result) {
            echo "
            <script>
                alert('Registration successful.Now you can login');
                window.location.href = 'index1.php';
            </script>";
        } else {
            echo "
            <script>
                alert('Cannot run Query');
                window.location.href = 'index1.php';
            </script>";
        }
    

    // Close the statement
    $insert_statement->close();
}

$mysqli->close();

<?php
require("mysqli_connection.php"); 

$mysqli = new mysqli($host, $username, $password, $dbname);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
if (isset($_POST['Login'])) {  //checks if form is submitted or not
    $adminEmail = $_POST['AdminEmail'];  //retrieve eamil and password from forms
    $adminPassword = $_POST['AdminPassword'];

    $query = "SELECT * FROM `admin_login` WHERE `Admin_Email` = ? LIMIT 1";//Limit 1 ensures one row is retrieved
    $statement = $mysqli->prepare($query); //prepare sql query

    $statement->bind_param("s", $adminEmail);
    $statement->execute();

    // Get the result
    $result = $statement->get_result();
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $storedPassword = $row['Admin_Password']; // Get the stored hashed password
    
        if ($adminPassword == $storedPassword) {
            // Password is correct, perform login actions
            session_start();
            $_SESSION['AdminLoginId'] = $adminEmail;
            header("location: adminpanel.php");
            exit();
        } else {
            echo "<script>alert('Incorrect Password');</script>";
        }
    } else {
        echo "<script>alert('Admin not found');</script>";
    }

    // Close the statement
    $statement->close();
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Form</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.1/css/all.css">
    <link rel="stylesheet" type="text/css" href="css/adminlogin.css">
</head>

<body>
    <div class="login-form">
        <center>
            <h2>ADMIN LOGIN PANEL</h2>
        </center>
        <center>
        <form method="POST" onsubmit="return validateForm()" name="loginForm">
        <center>
                    <div class="input-field">
                        <div class="icon-box">
                            <i class="fas fa-user"></i>
                        </div>
                        <input type="text" placeholder="Admin Email" name="AdminEmail">
                    </div>
                </center>
                <center>
                    <div class="input-field">
                        <div class="icon-box">
                            <i class="fas fa-lock"></i>
                        </div>
                        <input type="password" placeholder="Password" name="AdminPassword">
                    </div>
                </center>

                <button type="submit" name="Login">Login</button>

                <div class="extra">
                    <a href="forgotpassword.php"> Forgot Password ?</a>
                </div>

            </form>
        </center>
    </div>
    <script>
        function validateForm() {
            var email = document.forms["loginForm"]["AdminEmail"].value;
            var password = document.forms["loginForm"]["AdminPassword"].value;

            // Email validation
            if (email.trim() === "") {
                alert("Please enter your email.");
                return false;
            } else if (!isValidEmail(email)) {
                alert("Please enter a valid email address.");
                return false;
            }

            // Password validation
            if (password.trim() === "") {
                alert("Please enter your password.");
                return false;
            }

            return true;
        }

        function isValidEmail(email) {
            //email format validation
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
        </script>
</body>

</html>



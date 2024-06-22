<?php
require('mysqli_connection.php');
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User-Login and Register</title>
    <link rel="stylesheet" href="css/indexcss.css">

</head>

<body>
    <header>
        <div class="logo-container">
            <img src="images/logo.jpg" alt="Logo">
            <h2>Online Mock Test</h2>
        </div>
        <nav class="nav-links">
            <a href="home.php">Home</a>
            <a href="aboutus.php">About Us</a>
            <a href="services.php">Services</a>
            <a href="contactus.php">Contact Us</a>
        </nav>
        <nav class="nav">
            <?php
            require('mysqli_connection.php'); // Include your MySQLi connection script here.

            if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true) {
                echo "
                <div class='welcome-container'>
                <span class='welcome-message'>Welcome, $_SESSION[username]!</span>
                    <a href='user_dashboard.php' class='dashboard-link'>User Dashboard</a>
                    <a href='logout.php' class='logout-link-attractive'>Logout</a>
                    ";
            } else {
                echo "
                    <button type='button' class='login' onclick=\"popup('login-popup')\">Login</button>
                    <button type='button' class='register' onclick=\"popup('register-popup')\">Register</button>
                ";
            }
            ?>
        </nav>
    </header>


    <div class="popup-container" id="login-popup" style="display: none;">
        <div class="popup">
            <form method="POST" action="login_register.php">
                <h2>
                    <span>USER LOGIN</span>
                    <button type="reset" onclick="popup('login-popup')">X</button>
                </h2>
                <input type="text" placeholder="E-mail or Username" name="email_username" value="<?php echo isset($_POST['email_username']) ? htmlspecialchars($_POST['email_username']) : ''; ?>">
                <input type="password" placeholder="Password" name="password">
                <button type="submit" class="login-button" name="login">LOGIN</button>
            </form>
            <button type="button" class="register-link" onclick="toggleForms()">Don't have an account? Register</button>
        </div>
    </div>

    <div class="popup-container" id="register-popup" style="display: none;">
        <div class="popup">
            <form method="POST" action="login_register.php">
                <h2>
                    <span>USER REGISTRATION</span>
                    <button type="reset" onclick="popup('register-popup')">X</button>
                </h2>
                <input type="text" placeholder="Full Name" name="fullname" value="<?php echo isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : ''; ?>">
            <input type="text" placeholder="Username" name="username" value="<?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : ''; ?>">
            <input type="email" placeholder="E-mail" name="email" value="<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>">
                <input type="password" placeholder="Password" name="password">
                <button type="submit" class="register-button" name="register">REGISTER</button>
            </form>
            <button type="button" class="login-link" onclick="toggleForms()">Already have an account? Login</button>
        </div>
    </div>

    <div class="footer">
        <footer>
            <div class="container">
                <p>&copy; <?php echo date("Y"); ?> Online Mock Test. All rights reserved.</p>
                <ul>
                    <li><a href="privacypolicy.php">Privacy Policy</a></li>
                    <li><a href="terms.php">Terms of Service</a></li>
                    <li><a href="contactus.php">Contact Us</a></li>
                </ul>
            </div>
        </footer>
    </div>

    <script>
        function popup(popupId) {
            const get_popup = document.getElementById(popupId);
            if (get_popup.style.display == "flex") {
                get_popup.style.display = "none";
            } else {
                get_popup.style.display = "flex";
            }
        }

        function toggleForms() {
            const registerForm = document.getElementById('register-popup');
            const loginForm = document.getElementById('login-popup');

            if (registerForm.style.display === 'flex') {
                registerForm.style.display = 'none';
                loginForm.style.display = 'flex';
            } else {
                registerForm.style.display = 'flex';
                loginForm.style.display = 'none';
            }
        }
        <?php
        if (isset($_SESSION['login_error'])) {
            echo "window.onload = function() { popup('login-popup'); alert('{$_SESSION['login_error']}'); };";
            unset($_SESSION['login_error']);
        }

        if (isset($_SESSION['register_error'])) {
            echo "window.onload = function() { popup('register-popup'); alert('{$_SESSION['register_error']}'); };";
            unset($_SESSION['register_error']);
        }
        ?>
    </script>

</body>

</html>

<?php

session_start();
require("mysqli_connection.php"); 


$host = 'localhost';
$dbname = 'myproject';
$username = 'root';
$password = '';

// Create a MySQLi connection
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user data from the database
$query = "SELECT * FROM registered_user WHERE is_deleted = 0";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $users = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $users = array();
}

// Add user to the database
if (isset($_POST['add_user'])) {
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password

    // Check if the username or email already exists
    $checkDuplicateQuery = $conn->prepare("SELECT id FROM registered_user WHERE username = ? OR email = ?");
    $checkDuplicateQuery->bind_param("ss", $username, $email);
    $checkDuplicateQuery->execute();
    $checkDuplicateQuery->store_result();

    if ($checkDuplicateQuery->num_rows > 0) {
        // Username or email already exists, set a session message
        $_SESSION['message'] = 'Username or email already exists. Please choose a different one.';
    } else {

       

        // Prepare and bind the insert statement
        $insertQuery = $conn->prepare("INSERT INTO registered_user (full_name, username, email, password) VALUES (?, ?, ?, ?)");
        $insertQuery->bind_param("ssss", $fullname, $username, $email, $password);
        $insertQuery->execute();
        $insertQuery->close();

        
        // Set a session message for successful user addition
        $_SESSION['message'] = 'User added successfully.';
    }

    header("Location: usermanagement.php");
    exit();
}

// Delete user from the database
if (isset($_GET['delete_user'])) {
    $userId = $_GET['delete_user'];

    // Check for and delete associated records in user_scores table
    $deleteScoresQuery = $conn->prepare("DELETE FROM user_scores WHERE id = ?");
    $deleteScoresQuery->bind_param("i", $userId);
    $deleteScoresQuery->execute();
    $deleteScoresQuery->close();

    // Check for and delete associated records in user_responses table
    $deleteResponsesQuery = $conn->prepare("DELETE FROM user_responses WHERE id = ?");
    $deleteResponsesQuery->bind_param("i", $userId);
    $deleteResponsesQuery->execute();
    $deleteResponsesQuery->close();

    // Then, delete the user from the registered_user table
    $deleteUserQuery = $conn->prepare("DELETE FROM registered_user WHERE id = ?");
    $deleteUserQuery->bind_param("i", $userId);
    $deleteUserQuery->execute();
    $deleteUserQuery->close();


  
    $_SESSION['message'] = 'User deleted successfully.';
    header("Location: usermanagement.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Online Mock Test</title>
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"> -->
    <link rel="stylesheet" href="css/usermanagement.css">
    <a href="adminpanel.php" class="back-link"><i class="fas fa-arrow-left"></i> </a>
    <style>
        .error {
            color: red;
        }
    </style>

</head>

<body>
    <div class="container">
        <h1>User Management</h1>
        <div class="user-list">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($users as $user) {
                        echo "<tr>";
                        echo "<td>" . $user['id'] . "</td>";
                        echo "<td>" . $user['full_name'] . "</td>";
                        echo "<td>" . $user['username'] . "</td>";
                        echo "<td>" . $user['email'] . "</td>";
                        echo "<td>";
                        echo "<a href='javascript:void(0);' onclick='confirmUserDeletion(" . $user['id'] . ")'>Delete</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <div class="add-user-form">
            <h2>Add User</h2>
            <form method="POST" action="usermanagement.php" onsubmit="return validateForm()">
                <input type="text" name="fullname" id="fullname" placeholder="Full Name" required><span id="fullnameError" class="error"></span><br>
                <input type="text" name="username" id="username" placeholder="Username" required><span id="usernameError" class="error"></span><br>
                <input type="email" name="email" id="email" placeholder="Email" required><span id="emailError" class="error"></span><br>
                <input type="password" name="password" id="password" placeholder="Password" required><span id="passwordError" class="error"></span><br>
                <button type="submit" name="add_user">Add User</button>
            </form>
        </div>
    </div>
    <script>
        // Display session message as an alert
        <?php
        if (!empty($_SESSION['message'])) {
            echo 'alert("' . $_SESSION['message'] . '");';
            // Clear the session message to avoid displaying it again on page reload
            unset($_SESSION['message']);
        }
        ?>
        function viewUser(userId) {
            // Implement the logic to display user details (e.g., modal or alert)
            alert('Viewing user ID: ' + userId);
        }

        function confirmUserDeletion(userId) {
            if (confirm("Are you sure you want to delete this user?")) {
                // If the user confirms, redirect to the delete_user.php script
                window.location.href = "usermanagement.php?delete_user=" + userId;
            }
        }

        // Validation logic
        function validateForm() {
            var fullname = document.getElementById("fullname").value;
            var username = document.getElementById("username").value;
            var email = document.getElementById("email").value;
            var password = document.getElementById("password").value;

            var fullnameError = document.getElementById("fullnameError");
            var usernameError = document.getElementById("usernameError");
            var emailError = document.getElementById("emailError");
            var passwordError = document.getElementById("passwordError");

            fullnameError.innerHTML = "";
            usernameError.innerHTML = "";
            emailError.innerHTML = "";
            passwordError.innerHTML = "";

            var isValid = true;

            // Validate Full Name
            if (fullname.trim() === "") {
                fullnameError.innerHTML = "Full Name is required";
                isValid = false;
            }else {
                // Check if Full Name has at least two words
    var words = fullname.trim().split(/\s+/);
    if (words.length < 2) {
        fullnameError.innerHTML = "Full Name should consist of at least two words";
        isValid = false;
            }
            // Additional check for letters and spaces
    else if (!/^[a-zA-Z\s]+$/.test(fullname)) {
        fullnameError.innerHTML = "Full Name can only contain letters and spaces";
        isValid = false;
    }
    }

            // Validate Username
            if (username.trim() === "") {
                usernameError.innerHTML = "Username is required";
                isValid = false;
            } else if (username.trim().length < 5) {
                usernameError.innerHTML = "Username must be at least 5 characters";
                isValid = false;
            }

            // Validate Email
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                emailError.innerHTML = "Invalid email format";
                isValid = false;
            }

            // Validate Password (at least 6 characters)
    if (password.length < 8) {
        passwordError.innerHTML = "Password must be at least 8 characters";
        isValid = false;
    } else {
        // Validate Password Complexity
        var passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,}$/;
        if (!passwordRegex.test(password)) {
            passwordError.innerHTML = "Password must include at least one lowercase letter, one uppercase letter, one digit, and one special character from @$!%*?&";
            isValid = false;
        }
    }

    return isValid;
}
    </script>
</body>

</html>


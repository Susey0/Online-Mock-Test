<?php
require('mysqli_connection.php');
session_start();

// Create a MySQLi connection
$mysqli = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}


// Fetch a list of available question sets
$sqlGetQuestionSets = 'SELECT set_id, set_name, number_of_questions FROM question_sets';
$stmtGetQuestionSets = $mysqli->query($sqlGetQuestionSets);
$questionSets = [];

// Check for errors in query execution
if (!$stmtGetQuestionSets) {
    die("Query failed: " . $mysqli->error);
}

while ($row = $stmtGetQuestionSets->fetch_assoc()) {
    $questionSets[] = $row;
}

// Close the statement
$stmtGetQuestionSets->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="css/userdashboard.css">
</head>

<body>
    <header>
        <h1>Welcome, <?php echo $_SESSION['username']; ?></h1>
            <a href="index1.php" class="back-link"><i class="fas fa-arrow-left"></i> </a>

    </header>


    <div class="container">
        <h2>Available Question Sets:</h2>
        <table>
            <thead>
                <tr>
                    <th>Set Name</th>
                    <th>Number of Questions</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($questionSets as $questionSet) : ?>
                    <tr>
                        <td><?php echo $questionSet['set_name']; ?></td>
                        <td><?php echo $questionSet['number_of_questions']; ?></td>
                        <td>
                            <a href="take_exam.php?set_id=<?php echo $questionSet['set_id']; ?>">Take Test</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Online Mock Test. All rights reserved.</p>
    </footer>
    

</body>

</html>

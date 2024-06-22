<?php
session_start();
require("mysqli_connection.php");

// Variables to store success messages and error messages
$addSuccessMessage = "";
$deleteSuccessMessage = "";
$error = "";

$host = 'localhost';
$db = 'myproject';
$user = 'root';
$password = '';

$mysqli = new mysqli($host, $user, $password, $db);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Add new exam
if (isset($_POST['add_exam'])) {
    $examName = $_POST['exam_name'];

    // Check if the exam with the same name already exists
    $checkDuplicateQuery = $mysqli->prepare("SELECT exam_id FROM exams WHERE exam_name = ?");
    $checkDuplicateQuery->bind_param("s", $examName);
    $checkDuplicateQuery->execute();
    $checkDuplicateQuery->store_result();

    if ($checkDuplicateQuery->num_rows > 0) {
        $error = "Exam with the same name already exists!";
    } else {
        // Insert exam into the database using prepared statement
        $sqlAddExam = 'INSERT INTO exams (exam_name) VALUES (?)';
        $stmtAddExam = $mysqli->prepare($sqlAddExam);
        $stmtAddExam->bind_param('s', $examName);

        if ($stmtAddExam->execute()) {
            $addSuccessMessage = "Exam added successfully!";
        } else {
            $error = "Error adding exam: " . $stmtAddExam->error;
        }

        // Close the prepared statement
        $stmtAddExam->close();
    }

    // Close the duplicate check statement
    $checkDuplicateQuery->close();
}

// Delete exam
if (isset($_GET['delete_exam'])) {
    $examId = $_GET['delete_exam'];

     // Check if there are associated question banks, question sets, or questions
     $checkAssociationsQuery = $mysqli->prepare("SELECT COUNT(*) FROM question_bank  WHERE exam_id = ?");
     $checkAssociationsQuery->bind_param("i", $examId);
     $checkAssociationsQuery->execute();
     $checkAssociationsQuery->bind_result($associationCount);
     $checkAssociationsQuery->fetch();
     $checkAssociationsQuery->close();

     if ($associationCount > 0) {
        $error = "You can't delete the exam. Delete associated question banks, question sets, and questions first.";
    } else {

    // Delete exam from the database using prepared statement
    $sqlDeleteExam = 'DELETE FROM exams WHERE exam_id = ?';
    $stmtDeleteExam = $mysqli->prepare($sqlDeleteExam);
    $stmtDeleteExam->bind_param('i', $examId);

    if ($stmtDeleteExam->execute()) {
        $deleteSuccessMessage = "Exam deleted successfully!";

        // Renumber exam_id values
        $mysqli->query('SET @count = 0;');
        $mysqli->query('UPDATE exams SET exam_id = @count:=@count+1;');
    } else {
        $error = "Error deleting exam: " . $stmtDeleteExam->error;
    }

    // Close the prepared statement
    $stmtDeleteExam->close();
}
}

// Retrieve all exams using MySQLi
$sqlAllExams = 'SELECT * FROM exams ORDER BY exam_id';
$resultAllExams = $mysqli->query($sqlAllExams);
$exams = array();

if ($resultAllExams->num_rows > 0) {
    while ($row = $resultAllExams->fetch_assoc()) {
        $exams[] = $row;
    }
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Management</title>
    <link rel="stylesheet" href="css/exammanagement.css">
    <script>
        <?php
        if (!empty($error)) {
            echo 'alert("' . $error . '");';
        } elseif (!empty($addSuccessMessage)) {
            echo 'alert("' . $addSuccessMessage . '");';
        } elseif (!empty($deleteSuccessMessage)) {
            echo 'alert("' . $deleteSuccessMessage . '");';
        }
        ?>
    </script>
</head>
<body>
    <a href="javascript:history.back()" class="back-link">&#x2190;</a>
    <a href="javascript:history.forward()" class="forward-link">&#x2192;</a>

    <h1>Exam Management</h1>

    <!-- Form to add new exam -->
    <form action="" method="POST">
        <label for="exam_name">Exam Name:</label>
        <input type="text" name="exam_name" id="exam_name" required>
        <button type="submit" name="add_exam">Add Exam</button>
    </form>

    <!-- Display all exams -->
    <h2>All Exams:</h2>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Exam Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($exams as $exam) : ?>
                    <tr>
                        <td><?php echo $exam['exam_name']; ?></td>
                        <td>
                            <div class="actions">
                                <a href="?delete_exam=<?php echo $exam['exam_id']; ?>" onclick="return confirm('Are you sure you want to delete this exam?')">Delete</a>
                            </div>
                            <div class="actions">
                                <a href="manage_question_banks.php?exam_id=<?php echo $exam['exam_id']; ?>">Manage Question Banks</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
session_start();
require("mysqli_connection.php");

// Variables to store success messages and error messages
$addSuccessMessage = "";
$deleteSuccessMessage = "";
$error = "";
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'myproject';
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if exam_id is set
if (isset($_GET['exam_id'])) {
    $examId = $_GET['exam_id'];

    // Retrieve the exam name
    $sqlExamName = 'SELECT exam_name FROM exams WHERE exam_id = ?';

    $stmtExamName = $conn->prepare($sqlExamName);

    if (!$stmtExamName) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmtExamName->bind_param('i', $examId);
    $stmtExamName->execute();

    $result = $stmtExamName->get_result();

    if ($result->num_rows > 0) {
        $examRow = $result->fetch_assoc();
        $examName = $examRow['exam_name'];
    } else {
        // Handle case where exam is not found
        echo "Invalid exam selected.";
        exit();
    }

    // Add new question bank
    if (isset($_POST['add_question_bank'])) {
        $questionBankName = $_POST['question_bank_name'];

        // Check if the question bank with the same name already exists
        $checkDuplicateQuery = $conn->prepare("SELECT question_bank_id FROM question_bank WHERE exam_id = ? AND question_bank_name = ?");
        $checkDuplicateQuery->bind_param("is", $examId, $questionBankName);
        $checkDuplicateQuery->execute();
        $checkDuplicateQuery->store_result();

        if ($checkDuplicateQuery->num_rows > 0) {
            // Question bank with the same name already exists, set an error message
            $error = "Question bank with the same name already exists!";
        } else {
            // Insert question bank into database
            $sqlAddQuestionBank = 'INSERT INTO question_bank (exam_id, question_bank_name) VALUES (?, ?)';
            $stmtAddQuestionBank = $conn->prepare($sqlAddQuestionBank);

            if (!$stmtAddQuestionBank) {
                die("Error preparing statement: " . $conn->error);
            }

            $stmtAddQuestionBank->bind_param('is', $examId, $questionBankName);

            if ($stmtAddQuestionBank->execute()) {
                $addSuccessMessage = "Question bank added successfully!";
                // Introduce a delay to allow JavaScript alert to execute before redirection
                echo '<script>
                        alert("' . $addSuccessMessage . '");
                        setTimeout(function(){
                            window.location.href = "manage_question_banks.php?exam_id=' . $examId . '";
                        }, 1); // Delay in milliseconds
                      </script>';
                exit();
            } else {
                $error = "Error adding question bank: " . $stmtAddQuestionBank->error;
            }

            // Close the statement
            $stmtAddQuestionBank->close();
        }

        // Close the duplicate check statement
        $checkDuplicateQuery->close();
    }

    // Delete a question bank
    if (isset($_GET['delete_question_bank_id'])) {
        $questionBankToDelete = $_GET['delete_question_bank_id'];

        // Check if there are associated question sets or questions
        $sqlCheckAssociations = 'SELECT set_id FROM question_sets WHERE question_bank_id = ? LIMIT 1';
        $stmtCheckAssociations = $conn->prepare($sqlCheckAssociations);
        $stmtCheckAssociations->bind_param('i', $questionBankToDelete);
        $stmtCheckAssociations->execute();
        $resultAssociations = $stmtCheckAssociations->get_result();

        if ($resultAssociations->num_rows > 0) {
            $error = "You can't delete the question bank. Delete associated question sets and questions first.";
        } else {
            // Delete the question bank from the database

        $sqlDeleteQuestionBank = 'DELETE FROM question_bank WHERE question_bank_id = ?';
        $stmtDeleteQuestionBank = $conn->prepare($sqlDeleteQuestionBank);

        if (!$stmtDeleteQuestionBank) {
            die("Error preparing statement: " . $conn->error);
        }

        $stmtDeleteQuestionBank->bind_param('i', $questionBankToDelete);
        $stmtDeleteQuestionBank->execute();

        // Set success message for deleting question bank
        $deleteSuccessMessage = "Question bank deleted successfully!";
        echo '<script>
                alert("' . $deleteSuccessMessage . '");
                setTimeout(function(){
                    window.location.href = "manage_question_banks.php?exam_id=' . $examId . '";
                }, 1); // Delay in milliseconds
              </script>';
        exit();

        // Close the statement
        $stmtDeleteQuestionBank->close();
    }
    // Close the statement for checking associations
    $stmtCheckAssociations->close();
}

    // Retrieve all question banks for the selected exam
    $sqlAllQuestionBanks = 'SELECT * FROM question_bank WHERE exam_id = ? ORDER BY question_bank_id';
    $stmtAllQuestionBanks = $conn->prepare($sqlAllQuestionBanks);

    if (!$stmtAllQuestionBanks) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmtAllQuestionBanks->bind_param('i', $examId);
    $stmtAllQuestionBanks->execute();

    $resultAllQuestionBanks = $stmtAllQuestionBanks->get_result();
    $questionBanks = [];

    while ($row = $resultAllQuestionBanks->fetch_assoc()) {
        $questionBanks[] = $row;
    }

    // Close the statement
    $stmtAllQuestionBanks->close();

    // Close the connection
    $stmtExamName->close();
    $conn->close();
} else {
    echo "Required parameter not provided.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Question Banks</title>
    <link rel="stylesheet" href="css/manage_question_bank.css">
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

    <h1>Manage Question Banks for Exam: <?php echo isset($examName) ? $examName : "No Exam Selected"; ?></h1>

    <!-- Form to add new question bank -->
    <form action="" method="POST">
        <label for="question_bank_name">Question Bank Name:</label>
        <input type="text" name="question_bank_name" id="question_bank_name" required>
        <button type="submit" name="add_question_bank">Add Question Bank</button>
    </form>

    <!-- Display all question banks -->
    <h2>All Question Banks:</h2>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Question Bank Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($questionBanks as $questionBank) : ?>
                    <tr>
                        <td><?php echo $questionBank['question_bank_name']; ?></td>
                        <td>
                            <div class="actions">
                                <a href="manage_question_banks.php?exam_id=<?php echo $examId; ?>&delete_question_bank_id=<?php echo $questionBank['question_bank_id']; ?>" onclick="return confirm('Are you sure you want to delete this question bank?')">Delete</a>
                            </div>
                            <div class="actions">
                                <a href="manage_sets.php?question_bank_id=<?php echo $questionBank['question_bank_id']; ?>">Manage Sets</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <a href="adminpanel.php" class="back-link admin-link">Go to Admin Panel</a> <!-- Add this line -->

</body>

</html>

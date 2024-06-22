<?php
session_start(); // Add this line at the beginning to start the session

require('mysqli_connection.php');

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

if (isset($_GET['question_bank_id'])) {
    $questionBankId = $_GET['question_bank_id'];

    $sqlQuestionBankName = 'SELECT question_bank_name FROM question_bank WHERE question_bank_id = ?';
    $stmtQuestionBankName = $conn->prepare($sqlQuestionBankName);
    $stmtQuestionBankName->bind_param('i', $questionBankId);
    $stmtQuestionBankName->execute();
    $resultQuestionBankName = $stmtQuestionBankName->get_result();
    $questionBankName = '';

    if ($resultQuestionBankName->num_rows > 0) {
        $row = $resultQuestionBankName->fetch_assoc();
        $questionBankName = $row['question_bank_name'];

        $sqlAllSets = 'SELECT * FROM question_sets WHERE question_bank_id = ?';
        $stmtAllSets = $conn->prepare($sqlAllSets);
        $stmtAllSets->bind_param('i', $questionBankId);
        $stmtAllSets->execute();
        $resultAllSets = $stmtAllSets->get_result();
        $sets = [];

        while ($rowSet = $resultAllSets->fetch_assoc()) {
            $sets[] = $rowSet;
        }

        if (isset($_POST['add_set'])) {
            $setName = $_POST['set_name'];
            $numberOfQuestions = $_POST['number_of_questions'];

            // Check if the set with the same name already exists
            $checkDuplicateQuery = $conn->prepare("SELECT set_id FROM question_sets WHERE question_bank_id = ? AND set_name = ?");
            $checkDuplicateQuery->bind_param("is", $questionBankId, $setName);
            $checkDuplicateQuery->execute();
            $checkDuplicateQuery->store_result();

            if ($checkDuplicateQuery->num_rows > 0) {
                $error = "Set with the same name already exists!";
            } else {
                // Insert set into database
                $sqlAddSet = 'INSERT INTO question_sets (question_bank_id, set_name, number_of_questions) VALUES (?, ?, ?)';
                $stmtAddSet = $conn->prepare($sqlAddSet);
                $stmtAddSet->bind_param('iss', $questionBankId, $setName, $numberOfQuestions);

                if ($stmtAddSet->execute()) {
                    $_SESSION['message'] = "Set added successfully!";
                    header("Location: manage_sets.php?question_bank_id=$questionBankId");
                    exit();
                } else {
                    $error = "Error adding set: " . $stmtAddSet->error;
                }

                // Close the statement
                $stmtAddSet->close();
            }

            // Close the duplicate check statement
            $checkDuplicateQuery->close();
        }

        if (isset($_POST['delete_set'])) {
            $setIdToDelete = $_POST['set_id'];

            // Check if there are associated questions
    $sqlCheckAssociations = 'SELECT question_id FROM questions WHERE set_id = ? LIMIT 1';
    $stmtCheckAssociations = $conn->prepare($sqlCheckAssociations);
    $stmtCheckAssociations->bind_param('i', $setIdToDelete);
    $stmtCheckAssociations->execute();
    $resultAssociations = $stmtCheckAssociations->get_result();

    if ($resultAssociations->num_rows > 0) {
        $error = "You can't delete the set. Delete associated questions first.";
             } else {

            // Delete related user_scores records
            $sqlDeleteUserScores = 'DELETE FROM user_scores WHERE set_id = ?';
            $stmtDeleteUserScores = $conn->prepare($sqlDeleteUserScores);
            $stmtDeleteUserScores->bind_param('i', $setIdToDelete);
            $stmtDeleteUserScores->execute();

            // Delete the set from the database
            $sqlDeleteSet = 'DELETE FROM question_sets WHERE set_id = ?';
            $stmtDeleteSet = $conn->prepare($sqlDeleteSet);

            if (!$stmtDeleteSet) {
                die("Error preparing statement: " . $conn->error);
            }

            $stmtDeleteSet->bind_param('i', $setIdToDelete);
            $stmtDeleteSet->execute();

            // Set success message for deleting set
            $_SESSION['message'] = "Set deleted successfully!";
            header("Location: manage_sets.php?question_bank_id=$questionBankId");
            exit();

            // Close the statement
            $stmtDeleteSet->close();
        }
  // Close the statement for checking associations
  $stmtCheckAssociations->close();
    }
} else {

$error = "Question bank not found. Please choose a valid question bank.";

}
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Sets</title>
    <link rel="stylesheet" href="css/manage_sets.css">
</head>

<body>
    <a href="javascript:history.back()" class="back-link">&#x2190;</a>

    <h1>Manage Sets for Question Bank: <?php echo isset($questionBankName) ? $questionBankName : "No Question Bank Selected"; ?></h1>

    <?php if (isset($questionBankName)) : ?>
        <!-- Form to add new set -->
        <form action="" method="POST">
            <input type="hidden" name="question_bank_id" value="<?php echo $questionBankId; ?>">
            <label for="set_name">Set Name:</label>
            <input type="text" name="set_name" id="set_name" required>
            <label for="number_of_questions">Number of Questions:</label>
            <input type="number" name="number_of_questions" id="number_of_questions" required>
            <button type="submit" name="add_set">Add Set</button>
        </form>

        <!-- Display all sets -->
        <h2>All Sets:</h2>
        <ul>
            <?php foreach ($sets as $set) : ?>
                <li>
                    <a href="manage_questions.php?set_id=<?php echo $set['set_id']; ?>">
                        <?php echo $set['set_name']; ?>
                    </a>
                    <form method="POST" action="">
                        <input type="hidden" name="set_id" value="<?php echo $set['set_id']; ?>">
                        <button type="submit" name="delete_set" onclick="return confirm('Are you sure you want to delete this set?')">Delete</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if (!empty($error)) : ?>
            <script>
                alert("<?php echo $error; ?>");
            </script>
        <?php endif; ?>

        <?php if (isset($_SESSION['message']) && !empty($_SESSION['message'])) : ?>
            <script>
                alert("<?php echo $_SESSION['message']; ?>");
            </script>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
    <?php else : ?>
        <h2>No question bank selected</h2>
    <?php endif; ?>

</body>

</html>
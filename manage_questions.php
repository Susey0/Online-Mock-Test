<?php
session_start(); // Start the session
require("mysqli_connection.php");

$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'myproject';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";
$setId = "";
$set = [];
$questions = [];

if (isset($_GET['set_id'])) {
    $setId = $_GET['set_id'];

    $sqlSet = 'SELECT * FROM question_sets WHERE set_id = ?';
    $stmtSet = $conn->prepare($sqlSet);
    $stmtSet->bind_param('i', $setId);
    $stmtSet->execute();
    $set = $stmtSet->get_result()->fetch_assoc();

    if ($set) {
        if (isset($_POST['add_question'])) {
            $question_text = $_POST['question_text'];
            $number_of_options = $_POST['number_of_options'];
            $correct_option = $_POST['correct_option'];

            $sqlCheckQuestion = 'SELECT * FROM questions WHERE set_id = ? AND question_text = ?';
            $stmtCheckQuestion = $conn->prepare($sqlCheckQuestion);
            $stmtCheckQuestion->bind_param('is', $setId, $question_text);
            $stmtCheckQuestion->execute();

            $resultCheckQuestion = $stmtCheckQuestion->get_result();

            if ($resultCheckQuestion->num_rows > 0) {
                $error = "Error: This question already exists in the set.";
                echo '<script>alert("' . $error . '");</script>';
            } else {
                try {
                    $conn->begin_transaction();

                    $sqlAddQuestion = 'INSERT INTO questions (set_id, question_text, number_of_options, correct_option) VALUES (?, ?, ?, ?)';
                    $stmtAddQuestion = $conn->prepare($sqlAddQuestion);
                    $stmtAddQuestion->bind_param('isii', $setId, $question_text, $number_of_options, $correct_option);
                    $stmtAddQuestion->execute();

                    $questionId = $stmtAddQuestion->insert_id;

                    for ($i = 1; $i <= $number_of_options; $i++) {
                        $optionText = $_POST["option_text_$i"];
                        $isCorrect = ($i == $correct_option) ? 1 : 0;

                        $sqlAddOption = 'INSERT INTO question_options (question_id, option_text, is_correct) VALUES (?, ?, ?)';
                        $stmtAddOption = $conn->prepare($sqlAddOption);
                        $stmtAddOption->bind_param('isi', $questionId, $optionText, $isCorrect);
                        $stmtAddOption->execute();
                    }

                    $conn->commit();
                    $_SESSION['message'] = "Question added successfully!";
                    header("Location: manage_questions.php?set_id=$setId");
                    exit();
                } catch (mysqli_sql_exception $e) {
                    $conn->rollback();
                    $error = "Error adding question: " . $e->getMessage();
                    echo '<script>alert("' . $error . '");</script>';
                }
            }
        }

        if (isset($_GET['delete_question'])) {
            $questionIdToDelete = $_GET['delete_question'];

            try {
                $conn->begin_transaction();

                $sqlDeleteUserResponses = 'DELETE FROM user_responses WHERE question_id = ?';
                $stmtDeleteUserResponses = $conn->prepare($sqlDeleteUserResponses);
                $stmtDeleteUserResponses->bind_param('i', $questionIdToDelete);
                $stmtDeleteUserResponses->execute();

                $sqlDeleteOptions = 'DELETE FROM question_options WHERE question_id = ?';
                $stmtDeleteOptions = $conn->prepare($sqlDeleteOptions);
                $stmtDeleteOptions->bind_param('i', $questionIdToDelete);
                $stmtDeleteOptions->execute();

                $sqlDeleteQuestion = 'DELETE FROM questions WHERE question_id = ?';
                $stmtDeleteQuestion = $conn->prepare($sqlDeleteQuestion);
                $stmtDeleteQuestion->bind_param('i', $questionIdToDelete);
                $stmtDeleteQuestion->execute();

                $conn->commit();
                $_SESSION['message'] = "Question deleted successfully!";

                header("Location: manage_questions.php?set_id=$setId");
                exit();
            } catch (mysqli_sql_exception $e) {
                $conn->rollback();
                $error = "Error deleting question: " . $e->getMessage();
                echo '<script>alert("' . $error . '");</script>';
            }
        }

        $sqlGetQuestions = 'SELECT * FROM questions WHERE set_id = ?';
        $stmtGetQuestions = $conn->prepare($sqlGetQuestions);
        $stmtGetQuestions->bind_param('i', $setId);
        $stmtGetQuestions->execute();
        $questionsResult = $stmtGetQuestions->get_result();

        while ($question = $questionsResult->fetch_assoc()) {
            $questionId = $question['question_id'];
            $sqlGetOptions = 'SELECT * FROM question_options WHERE question_id = ?';
            $stmtGetOptions = $conn->prepare($sqlGetOptions);
            $stmtGetOptions->bind_param('i', $questionId);
            $stmtGetOptions->execute();
            $optionsResult = $stmtGetOptions->get_result();

            $options = [];
            while ($option = $optionsResult->fetch_assoc()) {
                $options[] = $option;
            }

            $questions[] = [
                'question' => $question,
                'options' => $options,
            ];
        }
    } else {
        // Handle case where set is not found
        echo "Invalid sets selected.";

    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Questions</title>
    <link rel="stylesheet" href="css/managequestions.css">
</head>

<body>
    <script>
        <?php
        if (isset($_SESSION['message'])) {
            echo 'alert("' . $_SESSION['message'] . '");';
            unset($_SESSION['message']);
        }
        ?>
    </script>
    <a href="javascript:history.back()" class="back-link">&#x2190;</a>
    <div class="page-header">
        <h1>Manage Questions for Set: <?php echo isset($set['set_name']) ? $set['set_name'] : "No Set Selected"; ?></h1>
        <div class="admin-link-container">
            <a href="adminpanel.php" class="back-link admin-link">Go to Admin Panel</a>
        </div>
    </div>

    <?php if (isset($set['set_name'])) : ?>
        <form action="" method="POST">
            <input type="hidden" name="set_id" value="<?php echo $setId; ?>">
            <label for="question_text">Question:</label>
            <textarea name="question_text" id="question_text" rows="4" cols="50" required></textarea>
            <br>

            <label for="number_of_options">Number of Options:</label>
            <input type="number" name="number_of_options" id="number_of_options" min="2" max="10" required>
            <br>

            <label for="options">Options:</label>
            <?php for ($i = 1; $i <= 4; $i++) : ?>
                <input type="text" name="option_text_<?php echo $i; ?>" id="option_text_<?php echo $i; ?>" placeholder="Option <?php echo $i; ?>" required>
                <input type="radio" name="correct_option" id="correct_option_<?php echo $i; ?>" value="<?php echo $i; ?>" required>
                <label for="correct_option_<?php echo $i; ?>">Correct</label>
                <br>
            <?php endfor; ?>

              <!-- Correct Option -->
              <label for="correct_option">Correct Option:</label>
            <select name="correct_option" id="correct_option">
                <?php for ($i = 1; $i <= 4; $i++) : ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>


            <button type="submit" name="add_question">Add Question</button>
        </form>

        <?php foreach ($questions as $questionData) : ?>
            <div class="question">
                <h2><?php echo nl2br($questionData['question']['question_text']); ?></h2>
                <ul>
                    <?php foreach ($questionData['options'] as $option) : ?>
                        <li>
                            <?php echo $option['option_text']; ?>
                            <?php if ($option['is_correct']) : ?>
                                (Correct)
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <a href="edit_question.php?question_id=<?php echo $questionData['question']['question_id']; ?>">Edit</a>
                <button onclick="confirmDelete(<?php echo $questionData['question']['question_id']; ?>)">Delete</button>
            </div>
        <?php endforeach; ?>

        <script>
            function confirmDelete(questionId) {
                if (confirm("Are you sure you want to delete this question?")) {
                    window.location.href = `manage_questions.php?set_id=<?php echo $setId; ?>&delete_question=${questionId}`;
                }
            }
        </script>
    <?php else : ?>
        <h2>No set selected</h2>
    <?php endif; ?>
</body>

</html>

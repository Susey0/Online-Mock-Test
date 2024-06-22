<?php
require("mysqli_connection.php");
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'myproject';

$mysqli = new mysqli($host, $username, $password, $dbname);

if ($mysqli->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$error = "";
$questionId = "";
$question = [];
$options = [];
$questionUpdated = false;

if (isset($_GET['question_id'])) {//retrieves question_id from
    $questionId = $_GET['question_id'];

    // Fetch the question details
    $sqlGetQuestion = 'SELECT * FROM questions WHERE question_id = ?';
    $stmtGetQuestion = $conn->prepare($sqlGetQuestion);
    $stmtGetQuestion->bind_param('i', $questionId);
    $stmtGetQuestion->execute();
    $question = $stmtGetQuestion->get_result()->fetch_assoc();

    if ($question) {
        // Fetch the options for the question
        $sqlGetOptions = 'SELECT * FROM question_options WHERE question_id = ?';
        $stmtGetOptions = $conn->prepare($sqlGetOptions);
        $stmtGetOptions->bind_param('i', $questionId);
        $stmtGetOptions->execute();
        $optionsResult = $stmtGetOptions->get_result();

        while ($option = $optionsResult->fetch_assoc()) {
            $options[] = $option;
        }
        if (isset($_POST['update_question'])) {
            $updatedQuestionText = $_POST['updated_question_text'];
            $updatedCorrectOption = $_POST['updated_correct_option'];

            try {
                $conn->begin_transaction();

                // Update the question text and correct option in the questions table
                $sqlUpdateQuestion = 'UPDATE questions SET question_text = ?, correct_option = ? WHERE question_id = ?';
                $stmtUpdateQuestion = $conn->prepare($sqlUpdateQuestion);
                $stmtUpdateQuestion->bind_param('sii', $updatedQuestionText, $updatedCorrectOption, $questionId);
                $stmtUpdateQuestion->execute();

                // Delete existing options for the question
                $sqlDeleteOptions = 'DELETE FROM question_options WHERE question_id = ?';
                $stmtDeleteOptions = $conn->prepare($sqlDeleteOptions);
                $stmtDeleteOptions->bind_param('i', $questionId);
                $stmtDeleteOptions->execute();
                // Insert updated options
                for ($i = 1; $i <= 4; $i++) {
                    $optionText = $_POST["updated_option_text_$i"];
                    $isCorrect = isset($_POST["is_correct_$i"]) ? 1 : 0;

                    $sqlAddOption = 'INSERT INTO question_options (question_id, option_text, is_correct) VALUES (?, ?, ?)';
                    $stmtAddOption = $conn->prepare($sqlAddOption);
                    $stmtAddOption->bind_param('isi', $questionId, $optionText, $isCorrect);
                    $stmtAddOption->execute();
                }

                $conn->commit();
                $questionUpdated = true; 

                header("Location: manage_questions.php?set_id=" . $question['set_id']);
                exit();
            } catch (mysqli_sql_exception $e) {
                $conn->rollback();
                $error = "Error updating question: " . $e->getMessage();
            }
        }
    } else {
        // Handle case where question is not found
    }        echo "Invalid question selected.";

}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Question</title>
    <link rel="stylesheet" href="css/managequestions.css">
</head>

<body>
    <h1>Edit Question</h1>

    <?php if ($question) : ?>
        <form action="" method="POST">
            <input type="hidden" name="question_id" value="<?php echo $questionId; ?>">

            <label for="updated_question_text">Question:</label>
            <textarea name="updated_question_text" id="updated_question_text" rows="4" cols="50" required><?php echo $question['question_text']; ?></textarea>
            <br>

            <!-- Updated Correct Option -->
            <label for="updated_correct_option">Correct Option:</label>
            <select name="updated_correct_option" id="updated_correct_option">
                <?php for ($i = 1; $i <= 4; $i++) : ?>
                    <option value="<?php echo $i; ?>" <?php if (isset($question['correct_option']) && $i == $question['correct_option']) echo 'selected'; ?>><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
            <br>
            <!-- Updated Options -->
            <label for="options">Options:</label>
            <?php for ($i = 1; $i <= 4; $i++) : ?>
                <input type="text" name="updated_option_text_<?php echo $i; ?>" id="updated_option_text_<?php echo $i; ?>" placeholder="Option <?php echo $i; ?>" value="<?php echo $options[$i - 1]['option_text']; ?>" required>
                <input type="checkbox" name="is_correct_<?php echo $i; ?>" id="is_correct_<?php echo $i; ?>" value="1" <?php if ($options[$i - 1]['is_correct'] == 1) echo 'checked'; ?>>
                <label for="is_correct_<?php echo $i; ?>">Correct</label>
                <br>
            <?php endfor; ?>

            <button type="submit" name="update_question" id="update_question_button">Update Question</button>
        </form>

        <?php if (!empty($error)) : ?>
            <p>Error: <?php echo $error; ?></p>
        <?php endif; ?>
        <script>
            // Get the "Update Question" button by its ID
            var updateButton = document.getElementById('update_question_button');

            // Add a click event listener to the button
            updateButton.addEventListener('click', function() {
                alert('Question updated successfully!');
            });
        </script>

    <?php else : ?>
        <h2>Question not found</h2>
    <?php endif; ?>
</body>

</html>
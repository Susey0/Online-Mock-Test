<?php
require('mysqli_connection.php');
session_start();

// Create a MySQLi connection
$mysqli = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if (isset($_GET['set_id'])) {
    $set_id = $_GET['set_id'];

    // Fetch questions for the selected set
    $sqlGetQuestions = 'SELECT * FROM questions WHERE set_id = ?';
    $stmtGetQuestions = $mysqli->prepare($sqlGetQuestions);
    $stmtGetQuestions->bind_param('i', $set_id);
    $stmtGetQuestions->execute();
    $questionsResult = $stmtGetQuestions->get_result();

    $questions = [];

    while ($question = $questionsResult->fetch_assoc()) {
        // Fetch options for the current question
        $sqlGetOptions = 'SELECT * FROM question_options WHERE question_id = ?';
        $stmtGetOptions = $mysqli->prepare($sqlGetOptions);
        $stmtGetOptions->bind_param('i', $question['question_id']);
        $stmtGetOptions->execute();
        $optionsResult = $stmtGetOptions->get_result();

        $options = [];

        while ($option = $optionsResult->fetch_assoc()) {
            $options[] = $option;
        }

        $question['options'] = $options;
        $questions[] = $question;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Test</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <link rel="stylesheet" href="css/takeexam.css">
</head>

<body>
    <header>
        <a href="user_dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> </a>
        <h1>All the Best!!!</h1>

    </header>

    <div id="timer">
        Time Left: <span id="countdown"></span>
    </div>

    <div id="formContainer"> <!-- Add a container for the form to show/hide it -->
        <div class="container">
            <?php if (!empty($questions)) : ?>
                <form id="examForm" action="submit_exam.php?set_id=<?php echo $set_id; ?>" method="POST" onsubmit="return validateForm()">
                    <?php foreach ($questions as $question) : ?>
                        <h2><?php echo nl2br($question['question_text']); ?></h2>
                        <ul>
                            <?php foreach ($question['options'] as $option) : ?>
                                <li>
                                    <input type="radio" name="answers[<?php echo $question['question_id']; ?>]" value="<?php echo $option['option_id']; ?>">
                                    <?php echo $option['option_text']; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endforeach; ?>
                    <input type="submit" value="Submit Answers">
                    <input type="hidden" name="set_id" value="<?php echo $set_id; ?>">
                </form>
            <?php else : ?>
                <p>No questions available for this test.</p>
            <?php endif; ?>
        </div>
    </div>

   
    <script>
        var timerInterval;
        var formSubmitted = false; // Add a flag to track form submission

        // Set the exam duration in seconds
        var examDuration = 1800; // 30 seconds

        // Get the current time in seconds
        var currentTime = <?php echo time(); ?>;

        // Calculate the end time of the exam
        var endTime = currentTime + examDuration;

        // Update the countdown timer every second
        var countdownElement = document.getElementById("countdown");

        function updateTimer() {
            var now = Math.max(0, endTime - Math.floor(Date.now() / 1000));
            var minutes = Math.floor(now / 60);
            var seconds = now % 60;

            var minutesDisplay = (minutes < 10) ? "0" + minutes : minutes;
            var secondsDisplay = (seconds < 10) ? "0" + seconds : seconds;

            countdownElement.textContent = minutesDisplay + ":" + secondsDisplay;

            // Time's up, show the alert and submit the form
            if (now <= 0 && !formSubmitted) {
                clearInterval(timerInterval); // Stop the timer
                console.log("Time's up! Your exam will be submitted."); // Debug line
                alert("Time's up! Your exam will be submitted.");
                formSubmitted = true; // Set the flag to prevent multiple submissions
                document.getElementById("examForm").submit(); // Submit the form
            }
        }

        // Initial call to set the timer
        updateTimer();

        // Update the timer every second
        timerInterval = setInterval(updateTimer, 1000);

        function validateForm() {
            var form = document.getElementById("examForm");
            var radioInputs = form.querySelectorAll("input[type='radio']");
            var selectedOptions = Array.from(radioInputs).filter(input => input.checked);

            // Check if at least one option is selected for each question
            var questions = <?php echo json_encode($questions); ?>;
            for (var i = 0; i < questions.length; i++) {
                var question = questions[i];
                var selectedOptionsForQuestion = selectedOptions.filter(input => input.name === "answers[" + question.question_id + "]");

                if (selectedOptionsForQuestion.length === 0) {
                    alert("Please select an option for Question " + (i + 1) + " before submitting.");
                    return false; // Prevent form submission
                }
            }

            // Confirm submission before form submission
            var confirmSubmit = confirm("Are you sure you want to submit the test?");
            return confirmSubmit;
        }
        window.addEventListener('beforeunload', function(event) {

            if (!formSubmitted) {
                var message = 'You have not completed the test. Are you sure you want to leave?';
                (event || window.event).returnValue = message; // For IE and Firefox
                return message; // For Chrome and Safari

            }
        });
        // Add this code when the form is successfully submitted
        document.getElementById("examForm").addEventListener("submit", function() {
            formSubmitted = true;
        });
    </script>


</body>

</html>
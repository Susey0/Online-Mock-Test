<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Results</title>
    <link rel="stylesheet" href="css/examresult.css">
</head>

<body>
    <?php
    require('mysqli_connection.php'); 
    session_start();
    $mysqli = new mysqli($host, $username, $password, $dbname);
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    // Check if score, percentage, and set_id parameters are set in the URL
    if (isset($_GET['score'], $_GET['percentage'], $_GET['set_id'])) {
        $userScore = $_GET['score'];
        $percentageScore = $_GET['percentage'];
        $examId = $_GET['set_id'];

        // Fetch user's name from the database
        $userId = $_SESSION['id'];
        $sqlUserName = "SELECT full_name FROM registered_user WHERE id = $userId";
        $resultUserName = $mysqli->query($sqlUserName);

        if ($resultUserName->num_rows === 1) {
            $userNameRow = $resultUserName->fetch_assoc();
            $userName = $userNameRow['full_name'];

            echo "
            <div class='score-container'>
                <p class='greeting-message'>Hello, $userName!<br> Your Achieved Score: </p>
                <p class='score-value'>$userScore</p>

                <p><a href='index1.php'>Go back to Home Page</a></p>
            </div>
            <div>
                <p><center>Let's Check Your Selected Options:<br>
                Red color is shown for the incorrect options you have selected, and green color is shown for the correct options.</center></p>
            </div>
            
            ";

            // Fetch user responses from the database, including question text
            $sqlUserResponses = "SELECT ur.question_id, ur.selected_option_text, qo.option_text, qo.is_correct, q.question_text
            FROM user_responses ur
            JOIN question_options qo ON ur.question_id = qo.question_id
            JOIN questions q ON ur.question_id = q.question_id
            WHERE ur.id = $userId AND ur.set_id = $examId";
            $resultUserResponses = $mysqli->query($sqlUserResponses);

            if ($resultUserResponses->num_rows > 0) {
                $userResponses = [];

                while ($row = $resultUserResponses->fetch_assoc()) {
                    $userResponses[] = $row;
                }

                // Initialize a variable to store question text
                $currentQuestion = "";

                foreach ($userResponses as $response) {
                    // Check if this is a new question
                    if ($response['question_id'] !== $currentQuestion) {
                        // Display the question text
                        echo "<h3>" . nl2br($response['question_text']) . "</h3>";
                        $currentQuestion = $response['question_id'];
                    }

                    // Determine the option class for styling (correct, incorrect, or empty for unselected)
                    $optionClass = '';
                    if ($response['is_correct']) {
                        $optionClass = 'correct';
                    } elseif ($response['option_text'] === $response['selected_option_text']) {
                        $optionClass = 'incorrect';
                    }

                    echo "<label class='$optionClass'>";
                    echo "<input type='radio' disabled";
                    if ($optionClass === 'incorrect') {
                        echo " checked";
                    }
                    echo ">";
                    echo "Option: " . $response['option_text'];
                    echo "</label>";
                }

                // Generate the content for the scorecard
                $scorecardContent = "User: $userName\n";
                $scorecardContent .= "Score: $userScore\n";
                $scorecardContent .= "Percentage: $percentageScore%\n\n";

                foreach ($userResponses as $response) {
                    $scorecardContent .= "Question: " . nl2br($response['question_text']) . "\n";
                    $scorecardContent .= "Selected Option: " . $response['selected_option_text'] . "\n";
                    $scorecardContent .= "Correct Option: " . $response['option_text'] . "\n";
                    $scorecardContent .= "Result: " . ($response['is_correct'] ? 'Correct' : 'Incorrect') . "\n\n";
                }

                // Provide a link to download the scorecard
                $scorecardFileName = "scorecard_$userName.txt";
                file_put_contents($scorecardFileName, $scorecardContent);

                echo "<p><a href='$scorecardFileName' download>Download Scorecard</a></p>";
            } else {
                echo "No user responses available.";
            }
        } else {
            echo "Unable to fetch user's name.";
        }
    } else {
        echo "Score, percentage, or set_id not provided.";
    }

    // Close the MySQLi connection
    $mysqli->close();
    ?>
</body>

</html>

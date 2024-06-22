<?php
require('mysqli_connection.php');
session_start();

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['answers'])) {
    // Create a MySQLi connection
    $mysqli = new mysqli($host, $username, $password, $dbname);

    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    // Initialize variables to store user's score and total questions
    $userScore = 0;
    $totalQuestions = 0;

    // Check if 'set_id' is provided in the URL
    if (isset($_GET['set_id'])) {
        $examId = $_GET['set_id'];
    } else {
        echo "No 'set_id' provided in the URL.";
        exit;
    }

    // Get the user's ID from the session 
    $userId = $_SESSION['id'];

    // Initialize an array to store user responses for further insertion
    $userResponses = [];

    // Loop through the user responses
    foreach ($_POST['answers'] as $questionId => $selectedOptionId) {
        // Query to fetch the correct option for the current question
        $sql = "SELECT is_correct, option_text FROM question_options WHERE question_id = ? AND option_id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('ii', $questionId, $selectedOptionId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            // Check if the selected option is correct (is_correct = 1)
            if ($row['is_correct'] == 1) {
                // Increment the user's score if the answer is correct
                $userScore++;
            }

            // Get the selected option text
            $selectedOptionText = $row['option_text'];

            // Store the user response for insertion
            $userResponses[] = [
                'user_id' => $userId,
                'exam_id' => $examId,
                'set_id' => $examId, 
                'question_id' => $questionId,
                'selected_option_text' => $selectedOptionText,
                'response_date' => date("Y-m-d H:i:s") // Current date and time
            ];
        }

        // Increment the total number of questions
        $totalQuestions++;
    }

    // Calculate the user's percentage score
    if ($totalQuestions > 0) {
        $percentageScore = ($userScore / $totalQuestions) * 100;
    } else {
        $percentageScore = 0; 
    }

    $_SESSION['user_responses'] = $userResponses; 

    foreach ($userResponses as $response) {
        $sql = "INSERT INTO user_responses (id, exam_id, set_id, question_id, selected_option_text, response_date) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('iiisss', $response['user_id'], $response['exam_id'], $response['set_id'], $response['question_id'], $response['selected_option_text'], $response['response_date']);
        if (!$stmt->execute()) {
            echo "Error inserting user response: " . $stmt->error;
        }
    }

    // Insert the user's score into the database 
    $sql = "INSERT INTO user_scores (id, exam_id, set_id, score, exam_date) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('iiii', $userId, $examId, $examId, $userScore); 

    if ($stmt->execute()) {
        header("Location: exam_results.php?score=$userScore&percentage=$percentageScore&set_id=$examId");
        exit;
    } else {
        // Handle the error if the insertion fails
        echo "Error: " . $mysqli->error;
    }

    $mysqli->close();
} else {
    echo "No user responses received.";
}
?>

<?php
require('mysqli_connection.php');

// Check if 'exam_id' is set in the query parameters
$examId = isset($_GET['exam_id']) ? $_GET['exam_id'] : null;

// Create a MySQLi connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare the SQL query to fetch detailed information about the exam
$sql = "SELECT
    q.question_id,
    q.question_text,
    GROUP_CONCAT(DISTINCT qo.option_text ORDER BY qo.option_id) AS all_options,
    MAX(CASE WHEN qo.is_correct = 1 THEN qo.option_text END) AS correct_option,
    ur.selected_option_text AS user_response
FROM
    questions q
LEFT JOIN
    question_options qo ON q.question_id = qo.question_id
LEFT JOIN
    user_responses ur ON q.question_id = ur.question_id AND ur.exam_id = ?
WHERE
    q.set_id = ?
GROUP BY
    q.question_id
";

// Prepare and execute the SQL statement
$stmt = $conn->prepare($sql);

// Bind parameters
$stmt->bind_param('ii', $examId, $examId);

// Execute the statement
$stmt->execute();

// Check for errors
if ($stmt->error) {
    die('Error: ' . $stmt->error);
}

// Get the result set
$result = $stmt->get_result();

// Close the statement
$stmt->close();

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Exam</title>
    <!-- Add your CSS styles here -->
    <link rel="stylesheet" href="css/viewexam.css">
</head>

<body>
    <div class="exam-container">
        <a href="javascript:history.back()" class="back-link">&#x2190; Back</a>
        <h1>View Exam Details</h1>
        <table>
            <thead>
                <tr>
                    <th>Question</th>
                    <th>Correct Option</th>
                    <th>All Options</th>
                    <th>User's Selected Option</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Check if there are results before trying to fetch
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                ?>
                        <tr>
                            <td><?php echo nl2br($row['question_text']); ?></td>
                            <td><?php echo $row['correct_option']; ?></td>
                            <td>
                                <?php
                                // Split the options and display them vertically
                                $options = explode(",", $row['all_options']);
                                foreach ($options as $option) {
                                    echo "<div>$option</div>";
                                }
                                ?>
                            </td>

                            <td><?php echo $row['user_response']; ?></td>
                        </tr>
                <?php
                    }
                } else {
                    echo '<tr><td colspan="4">No details found for this exam.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</body>

</html>

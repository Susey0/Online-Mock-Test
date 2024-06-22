<?php
// Include the database connection file
require('mysqli_connection.php');

// Define default filtering parameters
$filterOption = isset($_GET['filter_option']) ? $_GET['filter_option'] : 'a-z';
$sortField = isset($_GET['sort']) ? $_GET['sort'] : 'full_name';
$sortOrder = isset($_GET['order']) ? $_GET['order'] : 'ASC';

// Create a MySQLi connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare the SQL query with sorting
$sql = "SELECT us.exam_id, us.score, us.exam_date, qs.set_name, ru.full_name
        FROM user_scores us
        INNER JOIN question_sets qs ON us.set_id = qs.set_id
        INNER JOIN registered_user ru ON us.id = ru.id";

// Apply sorting based on the selected option
switch ($sortField) {
    case 'full_name':
        $sql .= " ORDER BY ru.full_name $sortOrder";
        break;
    case 'score':
        $sql .= " ORDER BY us.score $sortOrder";
        break;
    default:
        $sql .= " ORDER BY ru.full_name ASC";
}

// Prepare and execute the SQL statement
$stmt = $conn->prepare($sql);

// Check for errors
if ($stmt->error) {
    die('Error: ' . $stmt->error);
}

// Execute the statement
$stmt->execute();

// Get the result set
$result = $stmt->get_result();

// Check for errors in getting results
if (!$result) {
    die('Error in fetching results: ' . $conn->error);
}

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
    <title>Test Result Analysis</title>
    <link rel="stylesheet" href="css/testresultanalysis.css">
</head>
<body>
    <div class="result-container">
        <a href="javascript:history.back()" class="back-link">&#x2190;</a>
        <h1>Exam Results Analysis</h1>

        <table>
            <thead>
                <tr>
                    <th>
                        <form method="get">
                            <button type="submit" name="sort" value="full_name" class="sort-btn">
                                User Name
                            </button>
                            <input type="hidden" name="order" value="<?php echo $sortOrder === 'ASC' ? 'DESC' : 'ASC'; ?>">
                            <input type="hidden" name="filter_option" value="<?php echo $filterOption; ?>">
                        </form>
                    </th>
                    <th>Exam Name</th>
                    <th>
                        <form method="get">
                            <button type="submit" name="sort" value="score" class="sort-btn">
                                Score
                            </button>
                            <input type="hidden" name="order" value="<?php echo $sortOrder === 'ASC' ? 'DESC' : 'ASC'; ?>">
                            <input type="hidden" name="filter_option" value="<?php echo $filterOption; ?>">
                        </form>
                    </th>
                    <th>Exam Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Check if there are results before trying to fetch
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        ?>
                        <tr>
                            <td><?php echo $row['full_name']; ?></td>
                            <td><?php echo $row['set_name']; ?></td>
                            <td><?php echo $row['score']; ?></td>
                            <td><?php echo $row['exam_date']; ?></td>
                            <td><a href="view_exam.php?exam_id=<?php echo $row['exam_id']; ?>">View Exam</a></td>
                        </tr>
                        <?php
                    }
                } else {
                    echo '<tr><td colspan="5">No results found.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>

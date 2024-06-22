<?php
session_start();
$host = 'localhost';
$dbname = 'myproject';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$examId = isset($_GET['exam_id']) ? $_GET['exam_id'] : null;//retrieves exam_id if exists

// Retrieve dynamic data
$totalUsers = 0;
$totalExams = 0;
$recentExams = [];

// Retrieve total users
$sqlTotalUsers = 'SELECT COUNT(*) AS total_users FROM registered_user';
$resultTotalUsers = $conn->query($sqlTotalUsers);
if ($resultTotalUsers) {
    $rowTotalUsers = $resultTotalUsers->fetch_assoc();
    $totalUsers = $rowTotalUsers['total_users'];
}

// Retrieve total exams
$sqlTotalExams = 'SELECT COUNT(*) AS total_exams FROM exams';
$resultTotalExams = $conn->query($sqlTotalExams);
if ($resultTotalExams) {
    $rowTotalExams = $resultTotalExams->fetch_assoc();
    $totalExams = $rowTotalExams['total_exams'];
}
// Retrieve total exams
$sqlTotalScores = 'SELECT COUNT(*) AS total_scores FROM user_scores';
$resultTotalScores = $conn->query($sqlTotalScores);
if ($resultTotalScores) {
    $rowTotalScores = $resultTotalScores->fetch_assoc();
    $totalScores = $rowTotalScores['total_scores'];
}

// Retrieve recent exams
$sqlRecentExams = 'SELECT exam_name FROM exams ORDER BY exam_id DESC LIMIT 4';
$resultRecentExams = $conn->query($sqlRecentExams);
if ($resultRecentExams) {
    while ($rowRecentExams = $resultRecentExams->fetch_assoc()) {
        $recentExams[] = $rowRecentExams;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="css/adminpanel.css">

</head>

<body>
    <div class="sidebar">
        <div class="top">
            <div class="logo">
                <i class="bx bxl-codepen"></i>
                <span>Online Mock Test</span>
            </div>
            <i class="bx bx-menu" id="btn"></i>
        </div>
        <div class="user">
            <img src="images/logo.jpg" alt="me" class="user-img">
            <div>
                <p class="bold">Sushma Sapkota</p>
                <p>Admin</p>
            </div>
        </div>

        <ul>
            <li>
                <a href="#">
                    <i class="bx bxs-grid-alt"></i>
                    <span class="nav-item">Dashboard</span>
                    <span class="tooltip">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="usermanagement.php">
                    <i class="bx bx-user-circle"></i>
                    <span class="nav-item">User Management</span>
                    <span class="tooltip">User Management</span>
                </a>
            </li>
            <li>
                <a href="exammanagement.php">
                    <i class="bx bx-book-content"></i>
                    <span class="nav-item">Exam Management</span>
                    <span class="tooltip">Exam Management</span>
                </a>
            </li>
            <li>
            <a href="testresultanalysis.php?exam_id=<?php echo $examId; ?>">

            <i class="bx bx-bar-chart-alt-2"></i>
                    <span class="nav-item">Test Analysis</span>
                    <span class="tooltip">Test Analysis</span>
                </a>
            </li>
            <li>
                <a href="logoutadmin.php">
                    <i class="bx bx-log-out"></i>
                    <span class="nav-item">Logout</span>
                    <span class="tooltip">Logout</span>
                </a>
            </li>
        </ul>
    </div>
    <div class="main-content">
        <div class="container">
            <h1>Dashboard</h1>
            <div class="dashboard-content">
                <h2>Welcome to the Dashboard!</h2>
                <div class="stats">
                    <!-- Display dynamic stats here -->
                    <div class="stat-card">
                        <i class="bx bx-user"></i>
                        <div class="stat-info">
                            <p>Total Users</p>
                            <h3><?php echo $totalUsers; ?></h3>
                        </div>
                    </div>
                    <div class="stat-card">
                        <i class="bx bx-list-check"></i>
                        <div class="stat-info">
                            <p>Total Exams</p>
                            <h3><?php echo $totalExams; ?></h3>
                        </div>
                    </div>
                    <div class="stat-card">
                        <i class="bx bx-bar-chart-alt-2"></i>
                        <div class="stat-info">
                            <p>Exam Results</p>
                            <h3><?php echo $totalScores; ?></h3>

                        </div>
                    </div>
                </div>
                <div class="recent-exams">
                    <h3>Recent Exams</h3>
                    <ul>
                        <?php foreach ($recentExams as $exam) : ?>
                            <li><?php echo $exam['exam_name']; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- Add your JavaScript code here -->
    <script>
        let btn = document.querySelector('#btn');
        let sidebar = document.querySelector('.sidebar');

        btn.onclick = function() {
            sidebar.classList.toggle('active');
        };
</script>
</body>
</html>
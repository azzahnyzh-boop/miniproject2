<?php
// Start session 
session_start();
include 'config.php';

// Ensure only student can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle AJAX search request
if (isset($_GET['search'])) {
    $search = "%" . $_GET['search'] . "%";
    $stmt = $conn->prepare("
        SELECT c.course_code, c.course_name, c.id
        FROM registrations r
        JOIN courses c ON r.course_id = c.id
        WHERE r.user_id = ? AND (c.course_code LIKE ? OR c.course_name LIKE ?)
    ");
    $stmt->bind_param("iss", $user_id, $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>".htmlspecialchars($row['course_code'])."</td>
                    <td>".htmlspecialchars($row['course_name'])."</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='2'>No courses found.</td></tr>";
    }
    exit(); 
}

// Get all registered courses for initial display
$stmt = $conn->prepare("
    SELECT c.course_code, c.course_name, c.id
    FROM registrations r
    JOIN courses c ON r.course_id = c.id
    WHERE r.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Registered Courses</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <script>
    function liveSearch() {
        let query = document.getElementById("searchBox").value;
        fetch("courses.php?search=" + encodeURIComponent(query))
            .then(response => response.text())
            .then(data => {
                document.getElementById("courseTable").innerHTML = data;
            });
    }
    </script>
</head>
<body class="student-page">
    <?php include 'header.php'; ?>

    <!-- Search + Heading box -->
    <div class="form-box mb-4">
        <h2 class="text-center mb-4">My Registered Courses</h2>
        <div class="input-group">
            <input type="text" id="searchBox" class="form-control" placeholder="Search course..." onkeyup="liveSearch()">
            <button class="btn btn-primary" onclick="liveSearch()">Search</button>
        </div>
    </div>

    <!-- Course table box -->
    <div class="form-box">
        <h3 class="mb-3 text-center">Courses List</h3>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Course Code</th>
                    <th>Course Name</th>
                </tr>
            </thead>
            <tbody id="courseTable">
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['course_code']) ?></td>
                            <td><?= htmlspecialchars($row['course_name']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="2">You have not registered any courses yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Back to dashboard -->
        <div class="mt-3 text-center">
            <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>

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

// Handle AJAX drop request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['course_id']) && isset($_POST['ajax'])) {
    $course_id = $_POST['course_id'];

    $stmt = $conn->prepare("DELETE FROM registrations WHERE user_id=? AND course_id=?");
    $stmt->bind_param("ii", $user_id, $course_id);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Course dropped successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }
    exit();
}

// Handle AJAX search request
if (isset($_GET['search'])) {
    $search = "%" . $_GET['search'] . "%";
    $stmt = $conn->prepare("
        SELECT c.id, c.course_code, c.course_name 
        FROM registrations r
        JOIN courses c ON r.course_id = c.id
        WHERE r.user_id = ? AND (c.course_code LIKE ? OR c.course_name LIKE ?)
    ");
    $stmt->bind_param("iss", $user_id, $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr id='row-".$row['id']."'>
                    <td>".htmlspecialchars($row['course_code'])."</td>
                    <td>".htmlspecialchars($row['course_name'])."</td>
                    <td><button class='btn btn-danger btn-sm' onclick='dropCourse(".$row['id'].")'>Drop</button></td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='3'>No courses found.</td></tr>";
    }
    exit();
}

// Get all registered courses for initial display
$stmt = $conn->prepare("
    SELECT c.id, c.course_code, c.course_name 
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
    <title>Drop Registered Courses</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <script>
    function liveSearch() {
        let query = document.getElementById("searchBox").value;
        fetch("drop_courses.php?search=" + encodeURIComponent(query))
            .then(response => response.text())
            .then(data => {
                document.getElementById("courseTable").innerHTML = data;
            });
    }

    function dropCourse(courseId) {
        fetch("drop_courses.php", {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: "course_id=" + courseId + "&ajax=1"
        })
        .then(response => response.text())
        .then(data => {
            document.getElementById("message").innerHTML = data;
            if (data.includes("success")) {
                document.getElementById("row-" + courseId).remove();
            }
        });
    }
    </script>
</head>
<body class="student-page">
    <?php include 'header.php'; ?>
    <!-- Search + Heading box -->
    <div class="form-box mb-4">
        <h2 class="text-center mb-4">Drop Registered Courses</h2>
        <div id="message"></div>
        <div class="input-group">
            <input type="text" id="searchBox" class="form-control" placeholder="Search course..." onkeyup="liveSearch()">
            <button class="btn btn-primary" onclick="liveSearch()">Search</button>
        </div>
    </div>

    <!-- Course table box -->
    <div class="form-box">
        <h3 class="mb-3 text-center">My Courses</h3>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Course Code</th>
                    <th>Course Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="courseTable">
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr id="row-<?= $row['id'] ?>">
                            <td><?= htmlspecialchars($row['course_code']) ?></td>
                            <td><?= htmlspecialchars($row['course_name']) ?></td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm" onclick="dropCourse(<?= $row['id'] ?>)">Drop</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3">You have not registered any courses yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Back to dashboard button -->
        <div class="mt-3 text-center">
            <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>

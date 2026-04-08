<?php
// Start session
session_start();
include 'config.php';

// Ensure only admin can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Handle AJAX delete request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['course_id']) && isset($_POST['ajax'])) {
    $course_id = $_POST['course_id'];

    // First delete all registrations for this course
    $stmt = $conn->prepare("DELETE FROM registrations WHERE course_id=?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();

    // Then delete the course itself
    $stmt = $conn->prepare("DELETE FROM courses WHERE id=?");
    $stmt->bind_param("i", $course_id);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Course deleted successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }
    exit();
}

// Handle AJAX search request
if (isset($_GET['search'])) {
    $search = "%" . $_GET['search'] . "%";
    $stmt = $conn->prepare("SELECT * FROM courses WHERE course_code LIKE ? OR course_name LIKE ?");
    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();

    // Display search results dynamically
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            echo "<tr id='row-".$row['id']."'>
                    <td>".htmlspecialchars($row['course_code'])."</td>
                    <td>".htmlspecialchars($row['course_name'])."</td>
                    <td><button class='btn btn-danger btn-sm' onclick='deleteCourse(".$row['id'].")'>Delete</button></td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='3'>No courses found.</td></tr>";
    }
    exit();
}

// Get all courses for initial display
$result = $conn->query("SELECT * FROM courses");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Courses (Admin)</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <script>
    function liveSearch() {
        let query = document.getElementById("searchBox").value;
        fetch("delete_courses.php?search=" + encodeURIComponent(query))
            .then(response => response.text())
            .then(data => {
                document.getElementById("courseTable").innerHTML = data;
            });
    }

    // Function to delete course via AJAX
    function deleteCourse(courseId) {
        if (!confirm("Are you sure you want to delete this course?")) return;

        fetch("delete_courses.php", {
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
<body class="admin-page">
    <?php include 'header.php'; ?>

    <!-- Search + Heading box -->
    <div class="form-box mb-4">
        <h2 class="text-center mb-4">Delete Courses</h2>
        <div id="message"></div>
        <div class="input-group">
            <input type="text" id="searchBox" class="form-control" placeholder="Search course..." onkeyup="liveSearch()">
            <button class="btn btn-primary" onclick="liveSearch()">Search</button>
        </div>
    </div>

    <!-- Course table box -->
    <div class="form-box">
        <h3 class="mb-3 text-center">All Courses</h3>
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
                    <?php while($row = $result->fetch_assoc()) { ?>
                    <tr id="row-<?= $row['id'] ?>">
                        <td><?= htmlspecialchars($row['course_code']) ?></td>
                        <td><?= htmlspecialchars($row['course_name']) ?></td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm" onclick="deleteCourse(<?= $row['id'] ?>)">Delete</button>
                        </td>
                    </tr>
                    <?php } ?>
                <?php else: ?>
                    <tr><td colspan="3">No courses available.</td></tr>
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

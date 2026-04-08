<?php
// Start session
session_start();
include 'config.php';

// Ensure only admin can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";

// Handle AJAX update request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['course_id']) && isset($_POST['ajax'])) {
    $course_id   = $_POST['course_id'];
    $course_code = $_POST['course_code'];
    $course_name = $_POST['course_name'];

    // Update course record
    $stmt = $conn->prepare("UPDATE courses SET course_code=?, course_name=? WHERE id=?");
    $stmt->bind_param("ssi", $course_code, $course_name, $course_id);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Course updated successfully!</div>";
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
                    <form onsubmit='updateCourse(event, ".$row['id'].")'>
                        <td><input type='text' name='course_code' value='".htmlspecialchars($row['course_code'])."' class='form-control' required></td>
                        <td><input type='text' name='course_name' value='".htmlspecialchars($row['course_name'])."' class='form-control' required></td>
                        <td><button type='submit' class='btn btn-warning btn-sm'>Update</button></td>
                    </form>
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
    <title>Update Courses (Admin)</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <script>
    function liveSearch() {
        let query = document.getElementById("searchBox").value;
        fetch("update_courses.php?search=" + encodeURIComponent(query))
            .then(response => response.text())
            .then(data => {
                document.getElementById("courseTable").innerHTML = data;
            });
    }

    // Function to update course via AJAX
    function updateCourse(event, courseId) {
        event.preventDefault();
        let form = event.target;
        let formData = new URLSearchParams(new FormData(form)).toString();

        fetch("update_courses.php", {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: formData + "&ajax=1&course_id=" + courseId
        })
        .then(response => response.text())
        .then(data => {
            document.getElementById("message").innerHTML = data;
        });
    }
    </script>
</head>
<body class="admin-page">
    <?php include 'header.php'; ?>

    <!-- Search + Heading box -->
    <div class="form-box mb-4">
        <h2 class="text-center mb-4">Update Course Information</h2>
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
                        <form onsubmit="updateCourse(event, <?= $row['id'] ?>)">
                            <td><input type="text" name="course_code" value="<?= htmlspecialchars($row['course_code']) ?>" class="form-control" required></td>
                            <td><input type="text" name="course_name" value="<?= htmlspecialchars($row['course_name']) ?>" class="form-control" required></td>
                            <td><button type="submit" class="btn btn-warning btn-sm">Update</button></td>
                        </form>
                    </tr>
                    <?php } ?>
                <?php else: ?>
                    <tr><td colspan="3">No courses found.</td></tr>
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

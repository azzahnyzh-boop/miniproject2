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
    $stmt = $conn->prepare("SELECT * FROM courses WHERE course_code LIKE ? OR course_name LIKE ?");
    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();

    while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>".htmlspecialchars($row['course_code'])."</td>
                <td>".htmlspecialchars($row['course_name'])."</td>
                <td>
                    <form onsubmit='registerCourse(event, ".$row['id'].")' enctype='multipart/form-data'>
                        <input type='file' name='document' class='form-control form-control-sm mb-2'>
                        <button class='btn btn-success btn-sm'>Register</button>
                    </form>
                </td>
              </tr>";
    }
    exit();
}

// Handle AJAX registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['course_id']) && isset($_POST['ajax'])) {
    $course_id = $_POST['course_id'];

    $check = $conn->prepare("SELECT id FROM registrations WHERE user_id=? AND course_id=?");
    $check->bind_param("ii", $user_id, $course_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "<div class='alert alert-warning'>You have already registered for this course.</div>";
    } else {
        $filePath = NULL;

        if (isset($_FILES['document']) && $_FILES['document']['error'] == UPLOAD_ERR_OK) {
            $file = $_FILES['document'];
            $allowed = ['pdf','png','jpg'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {
                echo "<div class='alert alert-danger'>Invalid file type!</div>";
                exit();
            } elseif ($file['size'] > 2*1024*1024) {
                echo "<div class='alert alert-danger'>File too large! Max 2MB.</div>";
                exit();
            } else {
                $uploadDir = __DIR__ . "/uploads/";
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $newName = time() . "_" . basename($file['name']);
                $target = $uploadDir . $newName;

                if (move_uploaded_file($file['tmp_name'], $target)) {
                    $filePath = "uploads/" . $newName; 
                }
            }
        }

        $stmt = $conn->prepare("INSERT INTO registrations (user_id, course_id, file_path) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $course_id, $filePath);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Course registered successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
        }
    }
    exit();
}

$result = $conn->query("SELECT * FROM courses");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register Course</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <script>
    function liveSearch() {
        let query = document.getElementById("searchBox").value;
        fetch("register_courses.php?search=" + encodeURIComponent(query))
            .then(response => response.text())
            .then(data => {
                document.getElementById("courseTable").innerHTML = data;
            });
    }

    function registerCourse(event, courseId) {
        event.preventDefault();
        let form = event.target;
        let formData = new FormData(form);
        formData.append("course_id", courseId);
        formData.append("ajax", 1);

        fetch("register_courses.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            document.getElementById("message").innerHTML = data;
        });
    }
    </script>
</head>
<body class="student-page">
    <?php include 'header.php'; ?>

    <!-- Search + Heading box -->
    <div class="form-box mb-4">
        <h2 class="text-center mb-4">Register Course</h2>
        <div class="input-group">
            <input type="text" id="searchBox" class="form-control" placeholder="Search course..." onkeyup="liveSearch()">
            <button class="btn btn-primary" onclick="liveSearch()">Search</button>
        </div>
    </div>

    <div id="message"></div>

    <!-- Course table box -->
    <div class="form-box">
        <h3 class="mb-3 text-center">Available Courses</h3>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="courseTable">
                <?php while($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($row['course_code']) ?></td>
                    <td><?= htmlspecialchars($row['course_name']) ?></td>
                    <td>
                        <form onsubmit="registerCourse(event, <?= $row['id'] ?>)" enctype="multipart/form-data">
                            <input type="file" name="document" class="form-control form-control-sm mb-2">
                            <button type="submit" class="btn btn-success btn-sm">Register</button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
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

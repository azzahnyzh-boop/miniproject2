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

// Handle add course form submission
if (isset($_POST['add'])) {
    $code = $_POST['course_code'];
    $name = $_POST['course_name'];

    // Insert new course into database
    $stmt = $conn->prepare("INSERT INTO courses (course_code, course_name) VALUES (?, ?)");
    $stmt->bind_param("ss", $code, $name);
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success text-center'>Course added successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger text-center'>Error: " . $stmt->error . "</div>";
    }
}

// Get all courses for display
$result = $conn->query("SELECT * FROM courses");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Add Courses</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="admin-page">
    <?php include 'header.php'; ?>

    <!-- Add course box -->
    <div class="form-box mb-4">
        <h2 class="text-center mb-4">Add New Course</h2>

        <!-- Feedback message -->
        <?php if (!empty($message)) echo $message; ?>

        <!-- Add course form -->
        <form method="POST" autocomplete="off">
            <!-- Course code field -->
            <div class="mb-3">
                <label class="form-label">Course Code</label>
                <input type="text" name="course_code" class="form-control" required>
            </div>

            <!-- Course name field -->
            <div class="mb-3">
                <label class="form-label">Course Name</label>
                <input type="text" name="course_name" class="form-control" required>
            </div>

            <!-- Submit button -->
            <button type="submit" name="add" class="btn btn-success w-100">Add Course</button>
        </form>
    </div>

    <!-- Course list box -->
    <div class="form-box">
        <h3 class="mb-3 text-center">All Courses</h3>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($row['course_code']) ?></td>
                    <td><?= htmlspecialchars($row['course_name']) ?></td>
                </tr>
                <?php } ?>
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

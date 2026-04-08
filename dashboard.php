<?php
// Start session 
session_start();
include 'config.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard PCRS</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard-page container mt-5">
<?php include 'header.php'; ?>

    <!-- Welcome card -->
    <div class="form-box text-center">
        <h2>Welcome, <?= htmlspecialchars($username); ?>!</h2>
        <p>You are logged in as <strong><?= ucfirst($role); ?></strong>.</p>
    </div>

    <?php if ($role == 'admin'): ?>
        <!-- Admin Panel -->
        <div class="form-box mt-4">
            <h3 class="mb-3 text-center">Admin Panel</h3>
            <ul class="list-group">
                <li class="list-group-item">
                    <a href="admin_courses.php" class="btn btn-outline-primary w-100">Add New Courses</a>
                </li>
                <li class="list-group-item">
                    <a href="update_courses.php" class="btn btn-outline-warning w-100">Update Course Information</a>
                </li>
                <li class="list-group-item">
                    <a href="delete_courses.php" class="btn btn-outline-danger w-100">Delete Courses</a>
                </li>
            </ul>
            <!-- Logout full merah di bawah box Admin Panel -->
            <div class="mt-3 text-center">
                <a href="logout.php" class="btn btn-danger btn-sm" style="width:150px;">Logout</a>
            </div>
        </div>
        
    <?php else: ?>
        <!-- Student Panel -->
        <div class="form-box mt-4">
            <h3 class="mb-3 text-center">Student Panel</h3>
            <ul class="list-group">
                <li class="list-group-item">
                    <a href="register_courses.php" class="btn btn-outline-success w-100">Register Course</a>
                </li>
                <li class="list-group-item">
                    <a href="courses.php" class="btn btn-outline-primary w-100">View Registered Courses</a>
                </li>
                <li class="list-group-item">
                    <a href="drop_courses.php" class="btn btn-outline-danger w-100">Drop Registered Courses</a>
                </li>
            </ul>
            <!-- Logout full merah di bawah box Student Panel -->
            <div class="mt-3 text-center">
                <a href="logout.php" class="btn btn-danger btn-sm" style="width:150px;">Logout</a>
            </div>
        </div>
    <?php endif; ?>

<?php include 'footer.php'; ?>
</body>
</html>

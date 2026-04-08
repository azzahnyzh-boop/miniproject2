<?php
// Start session
session_start();
include 'config.php';

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_user = $_POST['login_user'];
    $login_pass = $_POST['login_pass'];

    // Prepare SQL statement to fetch user details
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username=?");
    $stmt->bind_param("s", $login_user);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $db_username, $hashed_password, $role);
    $stmt->fetch();

    // Verify user exists and password matches
    if ($stmt->num_rows > 0 && password_verify($login_pass, $hashed_password)) {
        // Store user info
        $_SESSION['user_id'] = $id;
        $_SESSION['username'] = $db_username;
        $_SESSION['role'] = $role;

        // Redirect to dashboard
        header("Location: dashboard.php");
        exit();
    } else {
        // Show error message
        $message = "<div class='alert alert-danger text-center'>Invalid login!</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login PCRS</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">
    <?php include 'header.php'; ?>

    <!-- Login box -->
    <div class="form-box">
        <h2 class="text-center mb-4">Login</h2>

        <!-- Display error message if login fails -->
        <?php if (!empty($message)) echo $message; ?>

        <!-- Login form -->
        <form method="POST" autocomplete="off">
            <input type="text" style="display:none">
            <input type="password" style="display:none">

            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="login_user" class="form-control" required autocomplete="off">
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="login_pass" class="form-control" required autocomplete="off">
            </div>

            <button type="submit" class="btn btn-success w-100">Login</button>

            <div class="mt-3 text-center">
                <a href="register.php" class="btn btn-link">Don't have an account? Register</a>
            </div>
        </form>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>

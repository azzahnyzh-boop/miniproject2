<?php
// Include database 
include 'config.php';

// Handle registration form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash password
    $role     = $_POST['role'];

    // Check if username already exists
    $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        // Show warning if username is taken
        $message = "<div class='alert alert-warning text-center'>Username already exists. Please choose another.</div>";
    } else {
        // Insert new user into database
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $password, $role);

        if ($stmt->execute()) {
            // Redirect to login page after successful registration
            header("Location: login.php");
            exit();
        } else {
            // Show error if insert fails
            $message = "<div class='alert alert-danger text-center'>Error: " . $stmt->error . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register PCRS</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="register-page">
    <?php include 'header.php'; ?>

    <!-- Register box -->
    <div class="form-box">
        <h2 class="text-center mb-4">Register New User</h2>

        <!-- Display message -->
        <?php if (!empty($message)) echo $message; ?>

        <!-- Registration form -->
        <form method="POST" autocomplete="off">
            <!-- Username field -->
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required autocomplete="off">
            </div>

            <!-- Password field -->
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required autocomplete="new-password">
            </div>

            <!-- Role selection -->
            <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select" required>
                    <option value="student">Student</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <!-- Register button -->
            <button type="submit" class="btn btn-success w-100">Register</button>

            <!-- Link to login page -->
            <div class="mt-3 text-center">
                <a href="login.php" class="btn btn-link">Already have an account? Login</a>
            </div>
        </form>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>

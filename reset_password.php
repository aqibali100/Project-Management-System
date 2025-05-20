<?php
require './config/db.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST['token'];
    $new_password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Check if token is valid
    $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $email = $row['email'];

        // Update password
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$new_password, $email]);

        // Delete the used token
        $conn->prepare("DELETE FROM password_resets WHERE token = ?")->execute([$token]);

        $success = "Password updated successfully.";
    } else {
        $error = "Invalid or expired reset token.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Project Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <form method="POST" class="shadow p-4 mt-5 mx-auto" style="max-width: 400px;">
        <h3 class="display-6 mb-4 text-center">RESET PASSWORD</h3>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="mb-3">
            <label class="form-label">Add New Password</label>
            <input type="password" class="form-control" name="password" required placeholder="Enter Your Password">
        </div>

        <button type="submit" class="btn btn-primary w-100">Reset Password</button>

        <a href="./login.php"><b>Sign in</b></a><br>

    </form>
</body>

</html>
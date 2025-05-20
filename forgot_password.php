<?php
require './config/db.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        $token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$email, $token, $expires_at]);

        $reset_link = "http://localhost/pms/reset_password.php?token=$token";

        $subject = "Password Reset Link";
        $message = "Click the following link to reset your password: $reset_link";
        $headers = "From: projectmanagementsystem.com";

        if (mail($email, $subject, $message, $headers)) {
            $success = "Reset link has been sent to your email.";
        } else {
            $error = "Failed to send reset email.";
        }
    } else {
        $error = "No account found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Project Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <body class="login-body">
        <form method="POST" class="shadow p-4 mt-5 mx-auto" style="max-width: 400px; max-height: 450px;">
            <h3 class="display-6 mb-4 text-center">FORGOT PASSWORD</h3>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" required placeholder="Enter Your Email">
            </div>

            <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
            <a href="./login.php"><b>Sign in</b></a><br>
        </form>
        </div>
    </body>

</html>
<?php
session_start();
require './config/db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $pass = $_POST['password'] ?? '';

    if (empty($email) || empty($pass)) {
        $error = "Email and password are required!";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($pass, $user['password'])) {
            $_SESSION = array_merge($_SESSION, [
                'user_id' => $user['id'],
                'role' => $user['role'],
                'name' => $user['name'],
                'email' => $user['email'],
                'city' => $user['city'],
                'stack' => $user['stack'],
                'designation' => $user['designation'],
                'contact' => $user['contact'],
                'image' => $user['image'],
            ]);

            header("Location: ./index.php");
            exit();
        } else {
            $error = "Invalid email or password!";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Project Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="login-body">
    <form method="POST" class="shadow p-4 mt-5 mx-auto" style="max-width: 400px; max-height: 450px;">
        <h3 class="display-6 mb-4 text-center">LOGIN</h3>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" id="msg-box"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <div class=" mb-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" placeholder="Enter Your Email">
        </div>
        <div class="mb-3">
            <label for="exampleInputPassword1" class="form-label">Password</label>
            <input type="password" class="form-control" name="password" id="exampleInputPassword1" placeholder="Enter Your Password">
        </div>

        <button type="submit" class="btn btn-primary w-100">Login</button>
        <a href="./forgot_password.php"><b>Forgot Password?</b></a><br>

    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        setTimeout(function() {
            const msgBox = document.getElementById("msg-box");
            if (msgBox) {
                msgBox.style.display = "none";
            }
        }, 3000);
    </script>

</body>

</html>
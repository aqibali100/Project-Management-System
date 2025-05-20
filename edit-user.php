<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include './config/db.php';
require './vendor/autoload.php';

$recipient_name = $_SESSION['name'];
$recipient_role = $_SESSION['role'];
$recipient_id = $_SESSION['user_id'];

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ./login.php");
    exit();
}

// Get user details
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = intval($_GET['id']);

    $stmt = $conn->prepare("SELECT name, email, contact, role, image, password, city, stack, designation FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "User not found.";
        exit();
    }
} else {
    echo "Invalid user ID.";
    exit();
}

// Update user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['user_name']);
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact']);
    $city = trim($_POST['city']);
    $stack = trim($_POST['stack']);
    $designation = trim($_POST['designation']);
    $role = $_POST['role'];
    $password = $_POST['password'];
    $imagePath = $user['image'];

    if (isset($_FILES['profile']) && $_FILES['profile']['error'] === 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (in_array($_FILES['profile']['type'], $allowedTypes)) {
            $uploadDir = 'uploads/';
            $fileName = time() . '_' . basename($_FILES['profile']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['profile']['tmp_name'], $targetPath)) {
                $imagePath = $targetPath;
            }
        }
    }

    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $updateQuery = "UPDATE users SET name = :name, email = :email, contact = :contact, role = :role, city = :city, stack = :stack, designation = :designation, image = :image, password = :password WHERE id = :id";
        $params = [
            'name' => $username,
            'email' => $email,
            'contact' => $contact,
            'role' => $role,
            'city' => $city,
            'stack' => $stack,
            'designation' => $designation,
            'image' => $imagePath,
            'password' => $hashedPassword,
            'id' => $user_id
        ];
    } else {
        $updateQuery = "UPDATE users SET name = :name, email = :email, contact = :contact, role = :role, city = :city, stack = :stack, designation = :designation, image = :image WHERE id = :id";
        $params = [
            'name' => $username,
            'email' => $email,
            'contact' => $contact,
            'role' => $role,
            'city' => $city,
            'stack' => $stack,
            'designation' => $designation,
            'image' => $imagePath,
            'id' => $user_id
        ];
    }

    $stmt = $conn->prepare($updateQuery);
    if ($stmt->execute($params)) {
        $recipient = $recipient_name;
        $name = $username;
        $title = 'User Updated';
        $message = "A new user $username with role $role has been Updated by $recipient.($recipient_role)";
        $is_read = 0;
        $created_by = $recipient_id;
        $created_at = date('Y-m-d H:i:s');
        $url = 'user.php';

        $notifStmt = $conn->prepare("INSERT INTO notifications (recipient,name, title, message, is_read, created_by, created_at, url) VALUES (:recipient, :name, :title, :message, :is_read, :created_by, :created_at, :url)");
        $notifStmt->bindParam(':recipient', $recipient);
        $notifStmt->bindParam(':name', $name);
        $notifStmt->bindParam(':title', $title);
        $notifStmt->bindParam(':message', $message);
        $notifStmt->bindParam(':is_read', $is_read);
        $notifStmt->bindParam(':created_by', $created_by);
        $notifStmt->bindParam(':created_at', $created_at);
        $notifStmt->bindParam(':url', $url);
        $notifStmt->execute();

        $countStmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE name = :name AND is_read = 0");
        $countStmt->bindParam(':name', $name);
        $countStmt->execute();
        $unreadCount = $countStmt->fetchColumn();

        // Pusher
        $options = array(
            'cluster' => 'ap2',
            'useTLS' => true
        );

        $pusher = new Pusher\Pusher(
            '0efd8a2b41e70eba694f',
            '0fc3959916defd1ee531',
            '1994632',
            $options
        );

        $data = [
            'message' => $message,
            'unreadCount' => $unreadCount
        ];
        $pusher->trigger('my-channel', 'my-event', $data);
        header("Location: user.php?msg=updated");
        exit();
    } else {
        $error = "Failed to update user.";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User | Project Management System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <input type="checkbox" id="checkbox">
    <?php include "inc/header.php" ?>
    <div class="body">
        <?php include "inc/sidebar.php" ?>
        <section class="section-1">
            <h4 class="title">Edit User <a href="user.php">Users</a></h4>
            <form class="form-1" method="POST" enctype="multipart/form-data">
                <div class="input-holder">
                    <label>Username</label>
                    <input type="text" name="user_name" class="input-1" placeholder="Username" value="<?= htmlspecialchars($user['name']) ?>"><br>
                </div>
                <div class="input-holder">
                    <label>Email</label>
                    <input type="email" name="email" class="input-1" placeholder="Email" value="<?= htmlspecialchars($user['email']) ?>"><br>
                </div>
                <div class="input-holder">
                    <label>Contact Number</label>
                    <input type="number" name="contact" class="input-1" placeholder="Contact" value="<?= htmlspecialchars($user['contact']) ?>"><br>
                </div>
                <div class="input-holder">
                    <lable>Designation</lable>
                    <input type="text" name="designation" class="input-1" placeholder="Designation" value="<?= htmlspecialchars($user['designation']) ?>"><br>
                </div>
                <div class="input-holder">
                    <lable>Stack</lable>
                    <input type="text" name="stack" class="input-1" placeholder="Stack" value="<?= htmlspecialchars($user['stack']) ?>"><br>
                </div>
                <div class="input-holder">
                    <lable>City</lable>
                    <input type="text" name="city" class="input-1" placeholder="City" value="<?= htmlspecialchars($user['city']) ?>"><br>
                </div>
                <div class="input-holder">
                    <label>Role</label>
                    <select class="input-1" name="role">
                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="team_leader" <?= $user['role'] === 'team_leader' ? 'selected' : '' ?>>Team Leader</option>
                        <option value="project_manager" <?= $user['role'] === 'project_manager' ? 'selected' : '' ?>>Project Manager</option>
                        <option value="employee" <?= $user['role'] === 'employee' ? 'selected' : '' ?>>Employee</option>
                    </select>
                </div>
                <div class="input-holder">
                    <label>Profile Picture</label>
                    <input type="file" name="profile" class="input-1"><br>
                    <img src="<?= $user['image'] ?>" width="80" alt="Current Profile">

                </div>
                <div class="input-holder">
                    <label>Password</label>
                    <input type="password" name="password" class="input-1" placeholder="Password"><br>
                </div>

                <button class="edit-btn">Update User</button>
            </form>
        </section>
    </div>

    <script type="text/javascript">
        var active = document.querySelector("#navList li:nth-child(2)");
        active.classList.add("active");
    </script>

</body>

</html>
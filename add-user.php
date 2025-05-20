<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include "./config/db.php";
require './vendor/autoload.php';
$recipient_name = $_SESSION['name'];
$recipient_role = $_SESSION['role'];
$recipient_id = $_SESSION['user_id'];


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "admin") {
	header("Location: ./login.php");
	exit();
}

// Add User
if (isset($_POST['submit'])) {
	$user_name = $_POST['user_name'];
	$email = $_POST['email'];
	$contact = $_POST['contact'];
	$role = $_POST['role'];
	$city = $_POST['city'];
	$stack = $_POST['stack'];
	$designation = $_POST['designation'];

	$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

	$profile_path = '';

	if (!empty($_FILES['profile']['name'])) {
		$profile_name = basename($_FILES['profile']['name']);
		$profile_tmp = $_FILES['profile']['tmp_name'];
		$upload_dir = 'uploads/';
		$profile_path = $upload_dir . $profile_name;

		if (is_uploaded_file($profile_tmp)) {
			if (!move_uploaded_file($profile_tmp, $profile_path)) {
				die("Failed to move uploaded file.");
			}
		} else {
			die("Invalid file upload.");
		}
	}

	try {
		$stmt = $conn->prepare("INSERT INTO users (name, email, contact, role, password, image, city, stack, designation) VALUES (:name, :email, :contact, :role, :password, :image, :city, :stack, :designation)");

		$stmt->bindParam(':name', $user_name);
		$stmt->bindParam(':email', $email);
		$stmt->bindParam(':contact', $contact);
		$stmt->bindParam(':role', $role);
		$stmt->bindParam(':password', $password);
		$stmt->bindParam(':image', $profile_path);
		$stmt->bindParam(':city', $city);
		$stmt->bindParam(':stack', $stack);
		$stmt->bindParam(':designation', $designation);

		if ($stmt->execute()) {
			$recipient = $recipient_name;
			$name = $user_name;
			$title = 'New User Added';
			$message = "A new user $user_name with role $role has been added by $recipient.($recipient_role)";
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
		} else {
			$errorInfo = $stmt->errorInfo();
		}
	} catch (PDOException $e) {
	}
}

?>


<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Create User | Project Management System</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">
</head>

<body>
	<input type="checkbox" id="checkbox">
	<?php include "inc/header.php" ?>
	<div class="body">
		<?php include "inc/sidebar.php" ?>
		<section class="section-1">
			<h4 class="title">Add User <a href="user.php">Users</a></h4>
			<form class="form-1"
				method="POST"
				enctype="multipart/form-data">
				<?php if (isset($_GET['error'])) { ?>
					<div class="danger" role="alert">
						<?php echo stripcslashes($_GET['error']); ?>
					</div>
				<?php } ?>

				<?php if (isset($_GET['success'])) { ?>
					<div class="success" role="alert">
						<?php echo stripcslashes($_GET['success']); ?>
					</div>
				<?php } ?>
				<div class="input-holder">
					<lable>Username</lable>
					<input type="text" name="user_name" class="input-1" placeholder="Username" required><br>
				</div>
				<div class="input-holder">
					<lable>Email</lable>
					<input type="email" name="email" class="input-1" placeholder="Email" required><br>
				</div>
				<div class="input-holder">
					<lable>Contact Number</lable>
					<input type="number" name="contact" class="input-1" placeholder="Contact"><br>
				</div>
				<div class="input-holder">
					<lable>Designation</lable>
					<input type="text" name="designation" class="input-1" placeholder="Designation"><br>
				</div>
				<div class="input-holder">
					<lable>Stack</lable>
					<input type="text" name="stack" class="input-1" placeholder="Stack"><br>
				</div>
				<div class="input-holder">
					<lable>City</lable>
					<input type="text" name="city" class="input-1" placeholder="City"><br>
				</div>
				<div class="input-holder">
					<lable>Role</lable>
					<select class="input-1" name="role">
						<option value="admin">Admin</option>
						<option value="project_manager">Project Manager</option>
						<option value="employee">Employee</option>
					</select>
				</div>
				<div class="input-holder">
					<lable>Profile Picture</lable>
					<input type="file" name="profile" class="input-1" placeholder="Profile Picture"><br>
				</div>
				<div class="input-holder">
					<lable>Password</lable>
					<input type="password" name="password" class="input-1" placeholder="Password"><br>
				</div>

				<button class="edit-btn" type="submit" name="submit">Add</button>
			</form>

		</section>
	</div>

	<script type="text/javascript">
		var active = document.querySelector("#navList li:nth-child(2)");
		active.classList.add("active");
	</script>
</body>

</html>
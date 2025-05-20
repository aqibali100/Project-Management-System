<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include './config/db.php';
require './vendor/autoload.php';
$user_id = $_SESSION['user_id'];
$currentUserRole = $_SESSION['role'];

if (!isset($_SESSION['user_id'])) {
	header("Location: ./login.php");
	exit();
}

// Fetch projects
try {
	$stmt = $conn->prepare("SELECT id, project_name FROM projects");
	$stmt->execute();
	$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
	echo "Error fetching projects: " . $e->getMessage();
	$projects = 0;
}

// Fetch users role based
try {
	if ($currentUserRole === 'admin') {
		$roleToFetch = 'project_manager';
	} elseif ($currentUserRole === 'project_manager') {
		$roleToFetch = 'employee';
	} else {
		$roleToFetch = null;
	}

	if ($roleToFetch) {
		$stmt = $conn->prepare("SELECT id, name FROM users WHERE role = :role");
		$stmt->execute(['role' => $roleToFetch]);
		$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
	} else {
		$users = [];
	}
} catch (PDOException $e) {
	echo "Error: " . $e->getMessage();
}

// create task
if (isset($_POST['submit'])) {
	$task_name = $_POST['title'];
	$description = $_POST['description'];
	$project_name = $_POST['project_name'];
	$assigned_to = $_POST['assigned_to'];
	$priority = $_POST['priority'];
	$status = $_POST['status'];
	$start_date = $_POST['start_date'];
	$due_date = $_POST['due_date'];
	$created_by = $_SESSION['user_id'];

	$uploadDir = 'uploads/';
	$uploadedFiles = [];

	if (!empty($_FILES['attachment']['name'][0])) {
		foreach ($_FILES['attachment']['name'] as $key => $name) {
			$tmp_name = $_FILES['attachment']['tmp_name'][$key];
			$error = $_FILES['attachment']['error'][$key];

			if ($error === UPLOAD_ERR_OK) {
				$uniqueName = time() . '_' . basename($name);
				$uploadPath = $uploadDir . $uniqueName;
				if (move_uploaded_file($tmp_name, $uploadPath)) {
					$uploadedFiles[] = $uploadPath;
				}
			}
		}
	}

	$attachmentsJson = json_encode($uploadedFiles);

	try {
		$sql = "INSERT INTO tasks 
            (title, description, project_name, assigned_to, priority, status, start_date, due_date, attachments, created_by) 
            VALUES (:title, :description, :project_name, :assigned_to, :priority, :status, :start_date, :due_date, :attachments, :created_by)";

		$stmt = $conn->prepare($sql);
		$stmt->execute([
			':title' => $task_name,
			':description' => $description,
			':project_name' => $project_name,
			':assigned_to' => $assigned_to,
			':priority' => $priority,
			':status' => $status,
			':start_date' => $start_date,
			':due_date' => $due_date,
			':attachments' => $attachmentsJson,
			':created_by' => $created_by
		]);

		header("Location: ./tasks.php");
	} catch (PDOException $e) {
	}
}

?>

<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Create Task | Project Management System</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">
</head>

<body>
	<input type="checkbox" id="checkbox">
	<?php include "inc/header.php" ?>
	<div class="body">
		<?php include "inc/sidebar.php" ?>
		<section class="section-1">
			<h4 class="title">Create Task </h4>
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
					<lable>Title</lable>
					<input type="text" name="title" class="input-1" placeholder="Enter Your Task Title" required><br>
				</div>
				<div class="input-holder">
					<lable>Description</lable>
					<textarea type="text" name="description" class="input-1" placeholder="Enter Your Task Description" required></textarea><br>
				</div>
				<div class="input-holder">
					<lable>Project Name</lable>
					<select name="project_name" class="input-1" required>
						<option value="0">Select Project</option>
						<?php if ($projects != 0) {
							foreach ($projects as $project) {
						?>
								<option value="<?= $project['id'] ?>"><?= $project['project_name'] ?></option>
						<?php }
						} ?>
					</select><br>
				</div>
				<div class="input-holder">
					<lable>Assigned to</lable>
					<select name="assigned_to" class="input-1" required>
						<option value="0">Select User</option>
						<?php if ($users != 0) {
							foreach ($users as $user) {
						?>
								<option value="<?= $user['id'] ?>"><?= $user['name'] ?></option>
						<?php }
						} ?>
					</select><br>
				</div>
				<div class="input-holder">
					<lable>Priority</lable>
					<select name="priority" class="input-1">
						<option value="0">Select One</option>
						<option value="low">Low</option>
						<option value="medium">Medium</option>
						<option value="high">High</option>
					</select><br>
				</div>
				<div class="input-holder">
					<lable>Status</lable>
					<select name="status" class="input-1">
						<option value="0">Select One</option>
						<option value="not_started">Not Started</option>
						<option value="in_progress">In Progress</option>
						<option value="completed">Completed</option>
						<option value="hold">Hold</option>
					</select><br>
				</div>
				<div class="input-holder">
					<label>Attachment</label>
					<input type="file" name="attachment[]" class="input-1" placeholder="Attachment File" multiple><br>
					<div id="preview-container"></div>
				</div>
				<div class="input-holder">
					<lable>Start Date</lable>
					<input type="date" name="start_date" class="input-1" placeholder="Start Date"><br>
				</div>
				<div class="input-holder">
					<lable>Due Date</lable>
					<input type="date" name="due_date" class="input-1" placeholder="Due Date"><br>
				</div>

				<button class="edit-btn" type="submit" name="submit">Create Task</button>
			</form>

		</section>
	</div>

	<script type="text/javascript">
		var active = document.querySelector("#navList li:nth-child(4)");
		active.classList.add("active");
	</script>
	<script type="text/javascript">
		document.querySelector('input[type="file"]').addEventListener('change', function(e) {
			const previewContainer = document.getElementById('preview-container');
			previewContainer.innerHTML = ''; // clear previous previews

			[...e.target.files].forEach(file => {
				const reader = new FileReader();
				reader.onload = function(e) {
					const img = document.createElement('img');
					img.src = e.target.result;
					img.style.width = '80px';
					img.style.margin = '5px';
					previewContainer.appendChild(img);
				};
				if (file.type.startsWith('image/')) {
					reader.readAsDataURL(file);
				}
			});
		});
	</script>
</body>

</html>
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include './config/db.php';
$user_id = $_SESSION['user_id'];
$created_by = $_SESSION['user_id'];
$currentUserRole = $_SESSION['role'] ?? null;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
	header("Location: ./login.php");
	exit();
}

// Fetch task details
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
	$task_id = intval($_GET['id']);

	try {
		$stmt = $conn->prepare("
            SELECT 
                tasks.*, 
                projects.project_name AS project_name,
                users.name AS assigned_user_name
            FROM tasks 
            LEFT JOIN projects ON tasks.project_name = projects.id 
            LEFT JOIN users ON tasks.assigned_to = users.id
            WHERE tasks.id = :id
        ");
		$stmt->execute([
			':id' => $task_id,
		]);
		$task = $stmt->fetch(PDO::FETCH_ASSOC);
	} catch (PDOException $e) {
	}
}

// fetch users role based
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

// Update Task Details
if (isset($_POST['submit'])) {
	$task_id = intval($_GET['id']);
	$title = $_POST['title'] ?? '';
	$description = $_POST['description'] ?? '';
	$project_name = $_POST['project_name'] ?? '';
	$assigned_to = $_POST['assigned_to'] ?? 0;
	$priority = $_POST['priority'] ?? '';
	$status = $_POST['status'] ?? '';
	$start_date = $_POST['start_date'] ?? null;
	$due_date = $_POST['due_date'] ?? null;

	$stmt = $conn->prepare("SELECT attachments FROM tasks WHERE id = :id AND created_by = :created_by");
	$stmt->execute([':id' => $task_id, ':created_by' => $created_by]);
	$task = $stmt->fetch(PDO::FETCH_ASSOC);
	$existing_attachments = [];

	if ($task && !empty($task['attachments'])) {
		$existing_attachments = json_decode($task['attachments'], true);
		if (!is_array($existing_attachments)) {
			$existing_attachments = [];
		}
	}

	if (!empty($_FILES['attachment']['name'][0])) {
		$upload_dir = 'uploads/';
		foreach ($_FILES['attachment']['tmp_name'] as $key => $tmp_name) {
			$filename = basename($_FILES['attachment']['name'][$key]);
			$target_file = $upload_dir . uniqid() . '_' . $filename;

			if (move_uploaded_file($tmp_name, $target_file)) {
				// Add the relative path or filename to attachments array
				$existing_attachments[] = $target_file;
			}
		}
	}

	$attachments_json = json_encode($existing_attachments);
	$updateStmt = $conn->prepare("UPDATE tasks SET title = :title, description = :description, project_name = :project_name, assigned_to = :assigned_to, priority = :priority, status = :status, start_date = :start_date, due_date = :due_date, attachments = :attachments WHERE id = :id AND created_by = :created_by");

	$updated = $updateStmt->execute([
		':title' => $title,
		':description' => $description,
		':project_name' => $project_name,
		':assigned_to' => $assigned_to,
		':priority' => $priority,
		':status' => $status,
		':start_date' => $start_date,
		':due_date' => $due_date,
		':attachments' => $attachments_json,
		':id' => $task_id,
		':created_by' => $created_by
	]);

	if ($updated) {
		header("Location: ./tasks.php");
		exit();
	}
}

?>

<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Edit Task | Project Management System</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">
</head>

<body>
	<input type="checkbox" id="checkbox">
	<?php include "inc/header.php" ?>
	<div class="body">
		<?php include "inc/sidebar.php" ?>
		<section class="section-1">
			<h4 class="title">Edit Task </h4>
			<form class="form-1" method="POST" enctype="multipart/form-data">
				<?php if (isset($_GET['error'])) { ?>
					<div class="danger" role="alert">
						<?= stripcslashes($_GET['error']); ?>
					</div>
				<?php } ?>

				<?php if (isset($_GET['success'])) { ?>
					<div class="success" role="alert">
						<?= stripcslashes($_GET['success']); ?>
					</div>
				<?php } ?>

				<div class="input-holder">
					<label>Title</label>
					<input type="text" name="title" class="input-1" placeholder="Enter Your Task Title"
						value="<?= htmlspecialchars($task['title'] ?? '') ?>" required><br>
				</div>

				<div class="input-holder">
					<label>Description</label>
					<textarea name="description" class="input-1" placeholder="Enter Your Task Description" required><?= htmlspecialchars($task['description'] ?? '') ?></textarea><br>
				</div>

				<div class="input-holder">
					<label>Project Name</label>
					<input type="text" name="project_name" class="input-1" placeholder="Enter Your Project Name"
						value="<?= htmlspecialchars($task['project_name'] ?? '') ?>" readonly><br>
				</div>

				<div class="input-holder">
					<label>Assigned to</label>
					<select name="assigned_to" class="input-1" required>
						<option value="0">Select User</option>
						<?php
						if (!empty($users)) {
							foreach ($users as $user) {
								$selected = (isset($task['assigned_to']) && $user['id'] == $task['assigned_to']) ? 'selected' : '';
								echo "<option value='" . htmlspecialchars($user['id']) . "' $selected>" . htmlspecialchars($user['name']) . "</option>";
							}
						}
						?>
					</select><br>
				</div>

				<div class="input-holder">
					<label>Priority</label>
					<select name="priority" class="input-1">
						<option value="0">Select One</option>
						<?php
						$priorities = ['low', 'medium', 'high'];
						foreach ($priorities as $p) {
							$selected = ($p == ($task['priority'] ?? '')) ? 'selected' : '';
							echo "<option value='$p' $selected>" . ucfirst($p) . "</option>";
						}
						?>
					</select><br>
				</div>

				<div class="input-holder">
					<label>Status</label>
					<select name="status" class="input-1">
						<option value="0">Select One</option>
						<?php
						$statuses = [
							'not_started' => 'Not Started',
							'in_progress' => 'In Progress',
							'completed' => 'Completed',
							'hold' => 'Hold'
						];
						foreach ($statuses as $key => $label) {
							$selected = ($key == ($task['status'] ?? '')) ? 'selected' : '';
							echo "<option value='$key' $selected>$label</option>";
						}
						?>
					</select><br>
				</div>

				<div class="input-holder">
					<label>Attachment</label>
					<input type="file" name="attachment[]" class="input-1" multiple><br>

					<div id="preview-container" style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px;">
						<?php
						if (!empty($task['attachments'])) {
							$attachments = json_decode($task['attachments'], true);
							if (is_array($attachments)) {
								foreach ($attachments as $file) {
									$file = str_replace('\\', '/', $file);
									echo '<div class="attachment-preview" style="position: relative; display: inline-block;">';
									echo "<img src='$file' alt='Attachment' style='width: 100px; height: 100px; object-fit: cover; border: 1px solid #ccc; border-radius: 4px;'>";
									echo "<button class='delete-image-btn' data-task-id='{$task['id']}' data-file='" . htmlspecialchars($file, ENT_QUOTES) . "' 
                          style='position: absolute; top: -10px; right: -10px; background: red; color: white; border-radius: 50%; border:none; cursor:pointer; padding: 0 6px; font-weight: bold;'>×</button>";
									echo '</div>';
								}
							}
						}
						?>
					</div>
				</div>

				<div class="input-holder">
					<label>Start Date</label>
					<input type="date" name="start_date" class="input-1"
						value="<?= $task['start_date'] ?? '' ?>"><br>
				</div>

				<div class="input-holder">
					<label>Due Date</label>
					<input type="date" name="due_date" class="input-1"
						value="<?= $task['due_date'] ?? '' ?>"><br>
				</div>

				<button class="edit-btn" type="submit" name="submit">Update Task</button>
			</form>


		</section>
	</div>

	<script type="text/javascript">
		var active = document.querySelector("#navList li:nth-child(3)");
		active.classList.add("active");
	</script>
	<script type="text/javascript">
		document.querySelector('input[type="file"]').addEventListener('change', function(e) {
			const previewContainer = document.getElementById('preview-container');
			previewContainer.innerHTML = '';

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
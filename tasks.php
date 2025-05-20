<?php
session_start();
include './config/db.php';
$created_by = $_SESSION['user_id'];

if (!isset($_SESSION['user_id'])) {
	header("Location: ./login.php");
	exit();
}

if (isset($_GET['delete_id'])) {
	$deleteId = $_GET['delete_id'];
	$query = "DELETE FROM tasks WHERE id = $deleteId";
	$result = $conn->query($query);
	header("Location: tasks.php");
}

try {
    $stmt = $conn->prepare("
        SELECT 
            tasks.*, 
            users.name AS assigned_user_name,
            projects.project_name AS project_name
        FROM tasks 
        LEFT JOIN users ON tasks.assigned_to = users.id 
        LEFT JOIN projects ON tasks.project_name = projects.id
        WHERE tasks.created_by = :created_by 
        ORDER BY tasks.id DESC
    ");
    $stmt->bindParam(':created_by', $created_by, PDO::PARAM_INT);
    $stmt->execute();
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $tasks = [];
}


?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Tasks | Project Management System</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/style.css">
</head>

<body>
	<input type="checkbox" id="checkbox">
	<?php include "inc/header.php" ?>
	<div class="body">
		<?php include "inc/sidebar.php" ?>
		<section class="section-1">
			<h4 class="title-2">
				All Tasks
				<a href="create_task.php" class="btn">Create Task</a>
			</h4>
			<?php if (isset($_GET['success'])) { ?>
				<div class="success" role="alert">
					<?php echo stripcslashes($_GET['success']); ?>
				</div>
			<?php } ?>
			<?php if ($tasks != 0) { ?>
				<table class="main-table">
					<tr>
						<th>#</th>
						<th>Title</th>
						<th>Project Name</th>
						<th>Assigned To</th>
						<th>Due Date</th>
						<th>Priority</th>
						<th>Status</th>
						<th>Action</th>
					</tr>
					<?php $i = 0;
					foreach ($tasks as $task) { ?>
						<tr>
							<td><?= ++$i ?></td>
							<td><?= $task['title'] ?></td>
							<td><?= $task['project_name'] ?></td>
							<td><?= htmlspecialchars($task['assigned_user_name']) ?></td>
							<td><?php if ($task['due_date'] == "") echo "No Deadline";
								else echo $task['due_date'];
								?></td>
							<td><?= ucwords($task['priority']) ?></td>
							<td><?= ucwords(str_replace('_', ' ', $task['status'])) ?></td>
							<td>
								<a href="edit-task.php?id=<?= $task['id'] ?>" class="edit-btn">Edit</a>
								<a href="?delete_id=<?= $task['id'] ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this task?');">Delete</a>
							</td>
						</tr>
					<?php	} ?>
				</table>
			<?php } else { ?>
				<h3>Empty</h3>
			<?php  } ?>

		</section>
	</div>

	<script type="text/javascript">
		var active = document.querySelector("#navList li:nth-child(5)");
		active.classList.add("active");
	</script>
</body>

</html>